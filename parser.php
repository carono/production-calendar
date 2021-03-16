<?php
require 'vendor/autoload.php';

$year = isset($argv[1]) ? $argv[1] : date('Y');
$uri = "http://www.consultant.ru/law/ref/calendar/proizvodstvennye/$year/";

$ch = curl_init($uri);
curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'Content-Type: text/html; charset=utf-8' ]);
curl_setopt($ch, CURLOPT_FAILONERROR, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

if(($result = curl_exec($ch)) === false)
{
    throw new \Exception(curl_error($ch), curl_errno($ch));
}

$content = phpQuery::newDocument($result);
$dates = file_exists('holidays.json') ? json_decode(file_get_contents('holidays.json'), true) : [];
$dates[$year] = [
    'holidays' => [],
    'works' => [],
    'preholidays' => [],
    'weekend' => [],
    'nowork' => [],
];
$m = 0;
foreach ($content->find('.cal') as $table) {
    $query = pq($table);
    $m++;
    foreach ($query->find('td') as $td) {
        $month = str_pad($m, 2, '0', STR_PAD_LEFT);
        $day = str_pad(preg_replace('/[\D]/', '', pq($td)->text()), 2, '0', STR_PAD_LEFT);
        if (pq($td)->hasClass('inactively')) {
            continue;
        }
        $date = $year.'-'.$month.'-'.$day;
        $idx = 'works';
        if (pq($td)->hasClass('holiday')) {
            $idx = 'holidays';
        }
        if (in_array(date('w', strtotime($date)), ['6', '0'], true)) {
            $idx = 'weekend';
        }
        if (pq($td)->hasClass('nowork')) {
            $idx = 'nowork';
        }

        $dates[$year][$idx][] = $date;
        if (pq($td)->hasClass('preholiday')) {
            $dates[$year]['preholidays'][] = $date;
        }
    }
}

file_put_contents("holidays.json", json_encode($dates));