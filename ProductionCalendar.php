<?php

namespace carono\production;


class Calendar
{
    public $format = 'Y-m-d';
    private static $_instance;
    protected static $holidays;
    /**
     * @var \DateTime
     */
    private static $date;

    public function __toString()
    {
        return $this->date()->format($this->format);
    }

    public function date()
    {
        return self::$date;
    }

    public function timestamp()
    {
        return self::date()->getTimestamp();
    }

    /**
     * @param \DateTime|string $date
     *
     * @param array            $weekend
     *
     * @return bool
     */
    public static function isWorking($date, $weekend = [6, 0])
    {
        return !self::isHoliday($date) && !self::isWeekend($date, $weekend);
    }

    public static function isHoliday($date)
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        return in_array($date->format('Y-m-d'), self::getHolidaysByYear($date));
    }

    public static function isWeekend($date, $weekend = [6, 0])
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        return in_array($date->format('w'), $weekend);
    }

    protected static function getHolidaysByYear($year)
    {
        if (!is_numeric($year) && is_string($year)) {
            $year = (new \DateTime($year))->format('Y');
        } elseif ($year instanceof \DateTime) {
            $year = $year->format('Y');
        }
        $holidays = self::getHolidays();
        return isset($holidays[$year]['holidays']) ? $holidays[$year]['holidays'] : [];
    }

    public function day()
    {
        return $this;
    }

    public function working()
    {
        while (!self::isWorking(self::$date)) {
            $this->next();
        }
        return $this;
    }

    public function next()
    {
        self::$date->add(new \DateInterval('P1D'));
        return $this;
    }

    public static function find($date = null)
    {
        if (self::$_instance) {
            return self::$_instance;
        } else {
            $json = file_get_contents(__DIR__ . '/holidays.json');
            self::$_instance = new self();
            self::$date = new \DateTime($date ? $date : 'now');
            self::$holidays = json_decode($json, true);
            return self::$_instance;
        }
    }

    public static function getHolidays()
    {
        if (!self::$_instance) {
            self::find();
        }
        return self::$holidays;
    }

    private function __construct()
    {
    }

    protected function __clone()
    {
    }
}