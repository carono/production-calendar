<?php

namespace Webmasterskaya\ProductionCalendar;

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
	protected const OUTPUT_PATH = 'data/holidays.json';
	protected const BACKUP_PATH = 'data/backup.holidays.json';

	public static function execute($arg = null)
	{
		if ($arg instanceof \Composer\Script\Event)
		{
			$args = $arg->getArguments();
			$arg  = $args[0] ?? null;
		}

		$arg = trim($arg);

		if (strtolower($arg) === 'all')
		{
			static::updateAll();
		}
		else
		{
			$arg = (int) $arg;
			static::update($arg ?: null);
		}
	}

	public function __invoke($arg = null)
	{
		static::execute($arg);
	}

	public static function updateAll()
	{
		$output = dirname(__FILE__) . '/' . ltrim(static::OUTPUT_PATH, '/');
		$backup = dirname(__FILE__) . '/' . ltrim(static::BACKUP_PATH, '/');

		if (file_exists($output))
		{
			copy($output, $backup);
			unlink($output);
		}

		$year     = 2013;
		$cur_year = date('Y');

		try
		{
			while ($year <= $cur_year)
			{
				static::update($year++);
			}

			try
			{
				static::update($year);
			}
			catch (\Exception $e)
			{
				//do nothing
			}
		}
		catch (\Exception $e)
		{
			if (file_exists($backup))
			{
				copy($backup, $output);
			}
		}

		if (file_exists($backup))
		{
			unlink($backup);
		}
	}

	public static function update($year = null)
	{
		if (empty($year))
		{
			$year = date('Y');
		}

		if ($year == 2020)
		{
			$year = '2020b';
		}

		$uri = "https://www.consultant.ru/law/ref/calendar/proizvodstvennye/$year/";

		$output = dirname(__FILE__) . '/' . ltrim(static::OUTPUT_PATH, '/');

		$ch = curl_init($uri);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/html; charset=utf-8']);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (($result = curl_exec($ch)) === false)
		{
			throw new \Exception(curl_error($ch), curl_errno($ch));
		}

		$document = new DomDocument();

		// Отключает ошибки парсинга HTML5 элементов
		libxml_use_internal_errors(true);
		$document->loadHTML($result);
		libxml_use_internal_errors(false);

		$xpath = new DomXPath($document);

		$tables_nodes = $xpath->query("//*/table[contains(concat(' ', normalize-space(@class), ' '), cal)]");

		if (file_exists($output))
		{
			$dates = json_decode(file_get_contents($output), true, 128, JSON_OBJECT_AS_ARRAY);
			if (json_last_error() !== JSON_ERROR_NONE)
			{
				throw new \Exception(json_last_error_msg(), 'error');
			}
		}
		else
		{
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

		/** @var DOMElement $table_node */
		foreach ($tables_nodes as $table_node)
		{
			$m++;
			$tds_nodes = $table_node->getElementsByTagName('td');

			/** @var DOMElement $td_node */
			foreach ($tds_nodes as $td_node)
			{
				$month = str_pad($m, 2, '0', STR_PAD_LEFT);

				$day = str_pad(
					preg_replace('/[\D]/', '', $td_node->textContent), 2,
					'0',
					STR_PAD_LEFT
				);

				$td_classname = $td_node->getAttribute('class');

				if (strpos($td_classname, 'inactively') !== false)
				{
					continue;
				}

				$date = $year . '-' . $month . '-' . $day;
				$idx  = 'works';

				if (strpos($td_classname, 'holiday') !== false)
				{
					$idx = 'holidays';
				}

				if (in_array(date('w', strtotime($date)), ['6', '0'], true))
				{
					$idx = 'weekend';
				}

				if (strpos($td_classname, 'nowork') !== false)
				{
					$idx = 'nowork';
				}

				$dates[$year][$idx][] = $date;

				if (strpos($td_classname, 'preholiday') !== false)
				{
					$dates[$year]['preholidays'][] = $date;
				}
			}
		}

		if (
			!empty($dates[$year]['holidays'])
			&& !empty($dates[$year]['works'])
			&& !empty($dates[$year]['preholidays'])
			&& !empty($dates[$year]['weekend'])
		)
		{
			$dates_json = json_encode($dates);

			if (json_last_error() !== JSON_ERROR_NONE)
			{
				throw new \Exception(json_last_error_msg(), 'error');
			}

			file_put_contents($output, $dates_json);
		}
	}
}