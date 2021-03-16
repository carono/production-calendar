<?php
require 'Calendar.php';
use webmasterskaya\production\Calendar;

$expect = [true, false, '2016-05-10', true, true, true];
$res = [];
$res['2016-05-09 is holiday'] = Calendar::isHoliday('2016-05-09'); // true
$res['2016-05-09 is working'] = Calendar::isWorking('2016-05-09'); // false
$res['first working day after 2016-05-07'] = Calendar::find('2016-05-07')->working()->format(); // 2016-05-10
$res['2016-02-20 is weekend'] = Calendar::isWeekend('2016-02-20'); // true
$res['2016-02-20 is pre holiday'] = Calendar::isPreHoliday('2016-02-20'); // true
$res['2017-01-01 is holiday'] = Calendar::find('2016-12-31')->next()->isHoliday(); //true
$res['2020-04-20 is no working'] = Calendar::isNoWorking('2020-04-20'); //true

print_r($res);
foreach ($res as $result) {
    assert($result === current($expect));
    next($expect);
}
