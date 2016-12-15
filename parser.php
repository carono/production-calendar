<?php
require 'vendor/autoload.php';

$client = new \GuzzleHttp\Client();
$year = isset($argv[1]) ? $argv[1] : date('Y');
$uri = "http://www.consultant.ru/law/ref/calendar/proizvodstvennye/$year/";

$response = $client->get($uri);
$content = phpQuery::newDocument($response->getBody()->getContents());
$dates = file_exists('holidays.json') ? json_decode(file_get_contents('holidays.json'), true) : [];
$dates[$year] = [];
$m = 0;
foreach ($content->find('.cal') as $table) {
    $query = pq($table);
    $m++;
    foreach ($query->find('td.work,td.preholiday,td.weekend') as $td) {
        $month = str_pad($m, 2, '0', STR_PAD_LEFT);
        $day = str_pad(preg_replace('/[^\d]/', '', pq($td)->text()), 2, '0', STR_PAD_LEFT);
        $idx = pq($td)->hasClass('weekend') ? 'holiday' : 'work';
        $date = $year . '-' . $month . '-' . $day;
        if ($idx == 'holiday' && in_array(date('w', strtotime($date)), [6, 0])) {
            $idx = 'weekend';
        }
        $dates[$year][$idx][] = $date;
    }
}
file_put_contents("holidays.json", json_encode($dates));
