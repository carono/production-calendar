<?php

namespace webmasterskaya\production;

/**
 * Class Calendar
 *
 * @package webmasterskaya\production
 */
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

    public static function date()
    {
        return static::$date;
    }

    public function timestamp()
    {
        return static::date()->getTimestamp();
    }

    /**
     * @param \DateTime|string $date
     *
     * @param array $weekend
     *
     * @return bool
     */
    public static function isWorking($date, $weekend = [6, 0])
    {
        return static::isPreHoliday($date) || (!static::isHoliday($date) && !static::isWeekend($date, $weekend));
    }

    protected static function findDateInArray($date, $array)
    {
        $date = static::prepareDate($date);
        return in_array($date->format('Y-m-d'), $array);

    }

    public static function isPreHoliday($date)
    {
        return static::findDateInArray($date, static::getPreHolidaysByYear($date));
    }

    /**
     * @param $date
     * @return bool
     */
    public static function isHoliday($date = null)
    {
        return (static::isWeekend($date) && !static::isPreHoliday($date)) || static::findDateInArray($date, static::getHolidaysByYear($date));
    }

    public static function isWeekend($date, $weekend = [6, 0])
    {
        $date = static::prepareDate($date);
        return in_array($date->format('w'), $weekend);
    }

    public static function isNoWorking($date)
    {
        return static::findDateInArray($date, static::getNoWorkingByYear($date));
    }

    /**
     * @param integer|string|\DateTime $year
     * @return array
     */
    public static function getHolidaysByYear($year)
    {
        if (!is_numeric($year)) {
            $year = static::prepareDate($year)->format('Y');
        }
        $holidays = static::getHolidays();
        return isset($holidays[$year]['holidays']) ? $holidays[$year]['holidays'] : [];
    }

    /**
     * @param integer|string|\DateTime $year
     * @return array
     */
    public static function getWorkingsByYear($year)
    {
        if (!is_numeric($year)) {
            $year = static::prepareDate($year)->format('Y');
        }
        $holidays = static::getHolidays();
        return isset($holidays[$year]['workings']) ? $holidays[$year]['workings'] : [];
    }

    /**
     * @param integer|string|\DateTime $year
     * @return array
     */
    public static function getPreHolidaysByYear($year)
    {
        if (!is_numeric($year)) {
            $year = static::prepareDate($year)->format('Y');
        }
        $holidays = static::getHolidays();
        return isset($holidays[$year]['preholidays']) ? $holidays[$year]['preholidays'] : [];
    }

    /**
     * @param integer|string|\DateTime $year
     * @return array
     * @throws \Exception
     */
    public static function getNoWorkingByYear($year)
    {
        if (!is_numeric($year)) {
            $year = static::prepareDate($year)->format('Y');
        }
        $holidays = static::getHolidays();

        return isset($holidays[$year]['nowork']) ? $holidays[$year]['nowork'] : [];
    }

    /**
     * @return $this
     */
    public function day()
    {
        return $this;
    }

    /**
     * @param null|string $format
     * @return string
     */
    public function format($format = null)
    {
        return $this->date()->format($format ? $format : $this->format);
    }

    /**
     * @return $this
     */
    public function working()
    {
        while (!static::isWorking(static::$date)) {
            $this->next();
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function holiday()
    {
        while (!static::isHoliday(static::$date) && static::haveData()) {
            $this->next();
        }
        return $this;
    }

    protected static function haveData($date = null)
    {
        $date = $date ? static::prepareDate($date) : static::date();
        return isset(static::getHolidays()[$date->format('Y')]);

    }

    /**
     * @return $this
     */
    public function preHoliday()
    {
        while (!static::isPreHoliday(static::$date) && static::haveData($this->date())) {
            $this->next();
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function noWorking()
    {
        while (!static::isNoWorking(static::$date) && static::haveData($this->date())) {
            $this->next();
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function next()
    {
        static::$date->add(new \DateInterval('P1D'));
        return $this;
    }

    /**
     * @return $this
     */
    public function prev()
    {
        static::$date->sub(new \DateInterval('P1D'));
        return $this;
    }

    public static function getInstance()
    {
        return static::$_instance ?: static::find();
    }

    /**
     * @param null|string|\DateTime $date
     * @return Calendar
     */
    public static function find($date = null)
    {
        static::$date = static::prepareDate($date);
        if (static::$_instance) {
            return static::$_instance;
        } else {
            $json = file_get_contents(__DIR__ . '/holidays.json');
            static::$_instance = new self();
            static::$holidays = json_decode($json, true);
            return static::$_instance;
        }
    }

    /**
     * @param string|\DateTime $date
     * @return \DateTime
     */
    protected static function prepareDate($date)
    {
        if (is_null($date) && static::$date) {
            $date = static::$date;
        } elseif (!$date instanceof \DateTime) {
            $date = new \DateTime($date);
        }
        return $date;
    }

    public static function getHolidays()
    {
        if (!static::$_instance) {
            static::find();
        }
        return static::$holidays;
    }

    private function __construct()
    {
    }

    protected function __clone()
    {
    }
}