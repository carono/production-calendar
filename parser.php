<?php
require 'vendor/autoload.php';

$client = new \GuzzleHttp\Client();
$year = isset($argv[1]) ? $argv[1] : date('Y');
$uri = "http://www.consultant.ru/law/ref/calendar/proizvodstvennye/$year/";

$response = $client->get($uri);
$content = phpQuery::newDocument($response->getBody()->getContents());
$dates = file_exists('holidays.json') ? json_decode(file_get_contents('holidays.json'), true) : [];
$dates[$year] = [
    'holidays' => [],
    'works' => [],
    'preholidays' => [],
    'weekend' => []
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
        $date = $year . '-' . $month . '-' . $day;
        $idx = 'works';
        if (pq($td)->hasClass('holiday')) {
            $idx = 'holidays';
        }
        if (in_array(date('w', strtotime($date)), ['6', '0'], true)) {
            $idx = 'weekend';
        }
	
        $dates[$year][$idx][] = $date;
        if (pq($td)->hasClass('preholiday')) {
            $dates[$year]['preholidays'][] = $date;
        }
		if (pq($td)->hasClass('work')) {
			$dates[$year]['works'][] = $date;
        }
    }
}

file_put_contents("holidays.json", json_encode($dates));