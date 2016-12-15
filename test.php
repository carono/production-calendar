<?php
require 'ProductionCalendar.php';

$date = \carono\production\Calendar::find('2016-02-20')->working();
var_dump($date->date()->format('Y-m-d'));