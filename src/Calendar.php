<?php

namespace Webmasterskaya\ProductionCalendar;

class Calendar
{
	/**
	 * @var string
	 */
	public $format = 'Y-m-d';

	/**
	 * @var static
	 */
	private static $_instance;

	/**
	 * @var array
	 */
	protected static $holidays;

	/**
	 * @var \DateTime
	 */
	private static $date;

	/**
	 * Возвращает дату, отформатированную согласно установленному формату
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->date()->format($this->format);
	}

	/**
	 * Возвращает дату, в виде объекта \DateTime
	 *
	 * @return \DateTime
	 */
	public static function date()
	{
		return static::$date;
	}

	/**
	 * Возвращает временную метку Unix
	 *
	 * @return int
	 */
	public function timestamp()
	{
		return static::date()->getTimestamp();
	}

	/**
	 * Проверяет, является ли дата рабочим днём
	 *
	 * @param   \DateTime|string  $date     Дата, которую нужно проверить
	 *
	 * @param   array             $weekend  Массив с номераи дней, которые принято считать выходными. 0 - воскресенье, 6 - суббота
	 *
	 * @return bool
	 */
	public static function isWorking($date, array $weekend = [6, 0])
	{
		return static::isPreHoliday($date) || (!static::isHoliday($date) && !static::isWeekend($date, $weekend));
	}

	/**
	 * Проверяет, входит ли указанная дата в массив
	 *
	 * @param   \DateTime|string  $date   Дата, которую нужно найти
	 * @param   array             $array  Массив дат, среди которых производится поиск
	 *
	 * @return bool
	 */
	protected static function findDateInArray($date, array $array)
	{
		$date = static::prepareDate($date);

		return in_array($date->format('Y-m-d'), $array);

	}

	/**
	 * Проверяет, является ли дата предпраздничным днём
	 *
	 * @param   \DateTime|string  $date  Дата, которую нужно проверить
	 *
	 * @return bool
	 */
	public static function isPreHoliday($date)
	{
		return static::findDateInArray($date, static::getPreHolidaysByYear($date));
	}

	/**
	 * Проверяет, является ли дата праздничным днём
	 *
	 * @param   \DateTime|string  $date  Дата, которую нужно проверить
	 *
	 * @return bool
	 */
	public static function isHoliday($date = null)
	{
		return (static::isWeekend($date) && !static::isPreHoliday($date))
			|| static::findDateInArray(
				$date, static::getHolidaysByYear($date)
			);
	}

	/**
	 * Проверяет, является ли дата выходным днём
	 *
	 * @param   \DateTime|string  $date     Дата, которую нужно проверить
	 *
	 * @param   array             $weekend  Массив с номераи дней, которые принято считать выходными. 0 - воскресенье, 6 - суббота
	 *
	 * @return bool
	 */
	public static function isWeekend($date, array $weekend = [6, 0])
	{
		$date = static::prepareDate($date);

		return in_array($date->format('w'), $weekend);
	}

	/**
	 * Проверяет, является ли дата нерабочим днём
	 *
	 * @param   \DateTime|string  $date  Дата, которую нужно проверить
	 *
	 * @throws \Exception
	 * @return bool
	 */
	public static function isNoWorking($date)
	{
		return static::findDateInArray($date, static::getNoWorkingByYear($date));
	}

	/**
	 * Возвращает массив праздничныз дней в году
	 *
	 * @param   integer|string|\DateTime  $year  Год, для которого нужно получить список праздничных дней
	 *
	 * @return array
	 */
	public static function getHolidaysByYear($year)
	{
		if (!is_numeric($year))
		{
			$year = static::prepareDate($year)->format('Y');
		}
		$holidays = static::getHolidays();

		return $holidays[$year]['holidays'] ?? [];
	}

	/**
	 * Возвращает массив рабочих дней в году
	 *
	 * @param   integer|string|\DateTime  $year  Год, для которого нужно получить список рабочих дней
	 *
	 * @return array
	 */
	public static function getWorkingsByYear($year)
	{
		if (!is_numeric($year))
		{
			$year = static::prepareDate($year)->format('Y');
		}
		$holidays = static::getHolidays();

		return $holidays[$year]['workings'] ?? [];
	}

	/**
	 * Возвращает массив предпраздничных дней в году
	 *
	 * @param   integer|string|\DateTime  $year  Год, для которого нужно получить список предпраздничных дней
	 *
	 * @return array
	 */
	public static function getPreHolidaysByYear($year)
	{
		if (!is_numeric($year))
		{
			$year = static::prepareDate($year)->format('Y');
		}
		$holidays = static::getHolidays();

		return $holidays[$year]['preholidays'] ?? [];
	}

	/**
	 * Возвращает массив нерабочих дней в году
	 *
	 * @param   integer|string|\DateTime  $year  Год, для которого нужно получить список нерабочих дней
	 *
	 * @throws \Exception
	 * @return array
	 */
	public static function getNoWorkingByYear($year)
	{
		if (!is_numeric($year))
		{
			$year = static::prepareDate($year)->format('Y');
		}
		$holidays = static::getHolidays();

		return $holidays[$year]['nowork'] ?? [];
	}

	/**
	 * @param   integer|string|\DateTime  $date_from
	 * @param   integer|string|\DateTime  $date_to
	 * @param   string|null               $format
	 *
	 * @return array
	 */
	public static function getHolidaysListByInterval($date_from, $date_to, string $format = null)
	{
		$date_from = static::prepareDate($date_from);
		$date_to   = static::prepareDate($date_to);

		if ($date_from > $date_to)
		{
			$date_tmp  = $date_to;
			$date_to   = $date_from;
			$date_from = $date_tmp;
			unset($date_tmp);
		}

		$holidaysList = [];

		$lastHoliday    = Calendar::find($date_from)->holiday();
		$holidaysList[] = $lastHoliday->format($format);
		while ($lastHoliday->date() <= $date_to)
		{
			$lastHoliday    = $lastHoliday->next()->holiday();
			$holidaysList[] = $lastHoliday->format($format);
		}

		return $holidaysList;
	}

	/**
	 * Возвращает дату, отформатированную согласно переданному формату
	 *
	 * @param   string|null  $format  Шаблон результирующей строки с датой. см. https://www.php.net/manual/ru/datetime.format.php
	 *
	 * @return string
	 */
	public function format(string $format = null)
	{
		return $this->date()->format($format ?: $this->format);
	}

	/**
	 * Возвращает текущую дату
	 *
	 * @return $this
	 */
	public function day()
	{
		return $this;
	}

	/**
	 * Возвращает дату ближайшего рабочего дня
	 *
	 * @return $this
	 */
	public function working()
	{
		while (!static::isWorking(static::$date))
		{
			$this->next();
		}

		return $this;
	}

	/**
	 * Возвращает дату ближайшего праздничного дня
	 *
	 * @return $this
	 */
	public function holiday()
	{
		while (!static::isHoliday(static::$date) && static::haveData())
		{
			$this->next();
		}

		return $this;
	}

	/**
	 * Проверяет, содержится ли заданная дата в справочнике дат библиотеки
	 *
	 * @param   \DateTime|string  $date  Дата, которую нужно проверить
	 *
	 * @return bool
	 */
	protected static function haveData($date = null)
	{
		$date = $date ? static::prepareDate($date) : static::date();

		return isset(static::getHolidays()[$date->format('Y')]);
	}

	/**
	 * Возвращает дату ближайшего предпраздничного дня
	 *
	 * @return $this
	 */
	public function preHoliday()
	{
		while (!static::isPreHoliday(static::$date) && static::haveData($this->date()))
		{
			$this->next();
		}

		return $this;
	}

	/**
	 * Возвращает дату ближайшего нерабочего дня
	 *
	 * @throws \Exception
	 * @return $this
	 */
	public function noWorking()
	{
		while (!static::isNoWorking(static::$date) && static::haveData($this->date()))
		{
			$this->next();
		}

		return $this;
	}

	/**
	 * Возвращает дату следующего дня
	 *
	 * @return $this
	 */
	public function next()
	{
		static::$date->add(new \DateInterval('P1D'));

		return $this;
	}

	/**
	 * Возвращает дату предыдущего дня
	 *
	 * @return $this
	 */
	public function prev()
	{
		static::$date->sub(new \DateInterval('P1D'));

		return $this;
	}

	/**
	 * Возвращает экземпляр класса
	 *
	 * @return $this
	 */
	public static function getInstance()
	{
		return static::$_instance ?: static::find();
	}

	/**
	 * Инициализирует экземпляр класса с указанной датой
	 *
	 * @param   null|string|\DateTime  $date  Дата, с которой нужно инициализировать класс. null - сегодняшняя дата
	 *
	 * @return Calendar
	 */
	public static function find($date = null)
	{
		static::$date = static::prepareDate($date);

		if (!static::$_instance)
		{
			$json              = file_get_contents(dirname(__FILE__) . '/data/holidays.json');
			static::$_instance = new self();
			static::$holidays  = json_decode($json, true);
		}

		return static::$_instance;
	}

	/**
	 * Преобразует дату в объект \DateTime
	 *
	 * @param   string|\DateTime  $date  Объект или строка даты/времени. Объяснение корректных форматов см по ссылке https://www.php.net/manual/ru/datetime.formats.php
	 *
	 * @throws \Exception
	 * @return \DateTime
	 */
	protected static function prepareDate($date)
	{
		if (is_null($date) && static::$date)
		{
			$date = static::$date;
		}
		elseif (!$date instanceof \DateTime)
		{
			$date = new \DateTime($date);
		}

		return $date;
	}

	/**
	 * Возвращает справочник дат
	 *
	 * @return array
	 */
	public static function getHolidays()
	{
		if (!static::$_instance)
		{
			static::find();
		}

		return static::$holidays;
	}

	/**
	 * Это singleton класс
	 */
	private function __construct()
	{
	}

	/**
	 * Это singleton класс
	 */
	protected function __clone()
	{
	}
}