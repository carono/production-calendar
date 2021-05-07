<?php

namespace webmasterskaya\ProductionCalendar;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Class Updater
 *
 * @package webmasterskaya\ProductionCalendar
 */
class Updater
{
    public static function updateAll()
    {
        $outputPath = __DIR__.'/holidays.json';
        $bcPath     = __DIR__ . '/holidays_bc.json';

        if (file_exists($outputPath)) {
            copy($outputPath, $bcPath);
            unlink($outputPath);
        }

        $year = 2013;
        $cur_year = date('Y');

        try {
            while ($year <= $cur_year) {
                static::update($year);
                $year++;
            }
            unlink($bcPath);
        } catch (\Exception $e) {
            copy($bcPath . '/holidays_bc.json', $outputPath);
        }
    }

    public static function update($year = null)
    {
        if (empty($year)) {
            $year = date('Y');
        }

        $uri
            = "http://www.consultant.ru/law/ref/calendar/proizvodstvennye/$year/";

        $ch = curl_init($uri);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, ['Content-Type: text/html; charset=utf-8']
        );
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (($result = curl_exec($ch)) === false) {
            throw new \Exception(curl_error($ch), curl_errno($ch));
        }

        $document = new DomDocument();

        // Отключает ошибки парсинга HTML5 элементов
        libxml_use_internal_errors(true);
        $document->loadHTML($result);
        libxml_use_internal_errors(false);

        $xpath = new DomXPath($document);

        $tables_nodes = $xpath->query(
            "//*/table[contains(concat(' ', normalize-space(@class), ' '), cal)]"
        );

        $outputPath = __DIR__.'/holidays.json';

        if (file_exists($outputPath)) {
            $dates = json_decode(file_get_contents($outputPath), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception(json_last_error_msg(), 'error');
            }
        } else {
            $dates = [];
        }

        $dates[$year] = [
            'holidays'    => [],
            'works'       => [],
            'preholidays' => [],
            'weekend'     => [],
            'nowork'      => [],
        ];
        $m = 0;
        /** @var DOMElement $node */
        foreach ($tables_nodes as $table_node) {
            $m++;
            $tds_nodes = $table_node->getElementsByTagName('td');
            /** @var DOMElement $td_node */
            foreach ($tds_nodes as $td_node) {
                $month = str_pad($m, 2, '0', STR_PAD_LEFT);

                $day = str_pad(
                    preg_replace('/[\D]/', '', $td_node->textContent), 2,
                    '0',
                    STR_PAD_LEFT
                );

                $td_classname = $td_node->getAttribute('class');

                if (strpos($td_classname, 'inactively') !== false) {
                    continue;
                }

                $date = $year.'-'.$month.'-'.$day;
                $idx = 'works';
                if (strpos($td_classname, 'holiday') !== false) {
                    $idx = 'holidays';
                }
                if (in_array(
                    date('w', strtotime($date)), ['6', '0'], true
                )
                ) {
                    $idx = 'weekend';
                }
                if (strpos($td_classname, 'nowork') !== false) {
                    $idx = 'nowork';
                }

                $dates[$year][$idx][] = $date;
                if (strpos($td_classname, 'preholiday') !== false) {
                    $dates[$year]['preholidays'][] = $date;
                }
            }
        }

        if (
            !empty($dates[$year]['holidays'])
            && !empty($dates[$year]['works'])
            && !empty($dates[$year]['preholidays'])
            && !empty($dates[$year]['weekend'])
        ) {
            $dates_json = json_encode($dates);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception(json_last_error_msg(), 'error');
            }

            file_put_contents($outputPath, $dates_json);
        }
    }
}