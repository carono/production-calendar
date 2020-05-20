<?php

namespace webmasterskaya\production;

/**
 * Class Calendar
 *
 * @package webmasterskaya\production
 */
class Calendar
{
    protected static $holidays;
    private static $_instance;
    /**
     * @var \DateTime
     */
    private static $date;
    public $format = 'Y-m-d';

    private function __construct()
    {
    }

    public static function isNoWorking($date)
    {
        return self::findDateInArray($date, self::getNoWorkingByYear($date));
    }

    protected static function findDateInArray($date, $array)
    {
        $date = self::prepareDate($date);

        return in_array($date->format('Y-m-d'), $array);

    }

    /**
     * @param string|\DateTime $date
     * @return \DateTime
     * @throws \Exception
     */
    protected static function prepareDate($date)
    {
        if (is_null($date) && self::$date) {
            $date = self::$date;
        } elseif (!$date instanceof \DateTime) {
            $date = new \DateTime($date);
        }

        return $date;
    }

    /**
     * @param integer|string|\DateTime $year
     * @return array
     * @throws \Exception
     */
    public static function getNoWorkingByYear($year)
    {
        if (!is_numeric($year)) {
            $year = self::prepareDate($year)->format('Y');
        }
        $holidays = self::getHolidays();

        return isset($holidays[$year]['nowork']) ? $holidays[$year]['nowork'] : [];
    }

    public static function getHolidays()
    {
        if (!self::$_instance) {
            self::find();
        }

        return self::$holidays;
    }

    /**
     * @param null|string|\DateTime $date
     * @return Calendar
     */
    public static function find($date = null)
    {
        self::$date = self::prepareDate($date);
        if (self::$_instance) {
            return self::$_instance;
        } else {
            $json = file_get_contents(__DIR__.'/holidays.json');
            self::$_instance = new self();
            self::$holidays = json_decode($json, true);

            return self::$_instance;
        }
    }

    /**
     * @param integer|string|\DateTime $year
     * @return array
     */
    public static function getWorkingsByYear($year)
    {
        if (!is_numeric($year)) {
            $year = self::prepareDate($year)->format('Y');
        }
        $holidays = self::getHolidays();

        return isset($holidays[$year]['workings']) ? $holidays[$year]['workings'] : [];
    }

    public function __toString()
    {
        return $this->date()->format($this->format);
    }

    public static function date()
    {
        return self::$date;
    }

    public function timestamp()
    {
        return self::date()->getTimestamp();
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
        while (!self::isWorking(self::$date)) {
            $this->next();
        }

        return $this;
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
        return self::isPreHoliday($date) || (!self::isHoliday($date) && !self::isWeekend($date, $weekend));
    }

    public static function isPreHoliday($date)
    {
        return self::findDateInArray($date, self::getPreHolidaysByYear($date));
    }

    /**
     * @param integer|string|\DateTime $year
     * @return array
     */
    public static function getPreHolidaysByYear($year)
    {
        if (!is_numeric($year)) {
            $year = self::prepareDate($year)->format('Y');
        }
        $holidays = self::getHolidays();

        return isset($holidays[$year]['preholidays']) ? $holidays[$year]['preholidays'] : [];
    }

    /**
     * @param $date
     * @return bool
     */
    public static function isHoliday($date = null)
    {
        return (self::isWeekend($date) && !self::isPreHoliday($date)) || self::findDateInArray(
                $date,
                self::getHolidaysByYear($date)
            );
    }

    public static function isWeekend($date, $weekend = [6, 0])
    {
        $date = self::prepareDate($date);

        return in_array($date->format('w'), $weekend);
    }

    /**
     * @param integer|string|\DateTime $year
     * @return array
     */
    public static function getHolidaysByYear($year)
    {
        if (!is_numeric($year)) {
            $year = self::prepareDate($year)->format('Y');
        }
        $holidays = self::getHolidays();

        return isset($holidays[$year]['holidays']) ? $holidays[$year]['holidays'] : [];
    }

    /**
     * @return $this
     */
    public function next()
    {
        self::$date->add(new \DateInterval('P1D'));

        return $this;
    }

    /**
     * @return $this
     */
    public function holiday()
    {
        while (!self::isHoliday(self::$date) && self::haveData()) {
            $this->next();
        }

        return $this;
    }

    protected static function haveData($date = null)
    {
        $date = $date ? self::prepareDate($date) : self::date();

        return isset(self::getHolidays()[$date->format('Y')]);
    }

    /**
     * @return $this
     */
    public function preHoliday()
    {
        while (!self::isPreHoliday(self::$date) && self::haveData($this->date())) {
            $this->next();
        }

        return $this;
    }

    protected function __clone()
    {
    }
}