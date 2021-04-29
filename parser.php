<?php
require 'vendor/autoload.php';

$year = isset($argv[1]) ? $argv[1] : date('Y');

if ($year == 'all') {
    \webmasterskaya\ProductionCalendar\Updater::updateAll();
} else {
    \webmasterskaya\ProductionCalendar\Updater::update($year);
}