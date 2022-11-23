<?php

namespace Webmasterskaya\ProductionCalendar\Tests;

use PHPUnit\Framework\TestCase;
use Webmasterskaya\ProductionCalendar\Calendar;

final class CalendarTest extends TestCase
{
	/**
	 * Просто проверим корректность работы синглтона
	 *
	 * @throws \Exception
	 * @testdox Корректное создание и вызов синглтона
	 */
	public function testCreatesAValidSingletonInstance()
	{
		$firstCall  = Calendar::getInstance();
		$secondCall = Calendar::getInstance();

		$this->assertInstanceOf(Calendar::class, $firstCall);
		$this->assertSame($firstCall, $secondCall);
	}

	/**
	 * Проверяем корректность обработки входных дат в разных форматах
	 *
	 * @throws \Exception
	 * @testdox Корректное создание и вызов синглтона с указанием дат в разных форматах
	 */
	public function testCreatesAValidSingletonInstanceWithDate()
	{
		$matrix = [
			null,
			'11.12.2013',
			'2013-11-12',
			'now',
			new \DateTime('11.12.2013')
		];

		foreach ($matrix as $case)
		{
			$this->assertInstanceOf(Calendar::class, Calendar::find($case));
		}
	}

	/**
	 * Проверяем корректность определния предпраздничного дня
	 *
	 * @throws \Exception
	 * @testdox Корректное определние предпраздничного дня
	 */
	public function testCorrectDefinitionOfThePreHoliday()
	{
		$this->assertFalse(Calendar::isPreHoliday('03.01.2020'));
		$this->assertFalse(Calendar::isPreHoliday('23.02.2020'));
		$this->assertFalse(Calendar::isPreHoliday('06.10.2020'));
		$this->assertTrue(Calendar::isPreHoliday('03.11.2020'));
		$this->assertTrue(Calendar::isPreHoliday('31.12.2020'));
	}

	/**
	 * Проверяем корректность определния праздничного дня
	 *
	 * @throws \Exception
	 * @testdox Корректное определние праздничного дня
	 */
	public function testCorrectDefinitionOfTheHoliday()
	{
		$this->assertTrue(Calendar::isHoliday('03.01.2020'));
		$this->assertTrue(Calendar::isHoliday('23.02.2020'));
		$this->assertFalse(Calendar::isHoliday('06.10.2020'));
	}

	/**
	 * Проверяем корректность определния выходного дня
	 *
	 * @throws \Exception
	 * @testdox Корректное определние выходного дня
	 */
	public function testCorrectDefinitionOfTheWeekend()
	{
		$this->assertFalse(Calendar::isWeekend('03.01.2020'));
		$this->assertTrue(Calendar::isWeekend('23.02.2020'));
		$this->assertFalse(Calendar::isWeekend('06.10.2020'));
	}

	/**
	 * Проверяем корректность определния нерабочего дня
	 *
	 * @throws \Exception
	 * @testdox Корректное определние нерабочего дня
	 */
	public function testCorrectDefinitionOfTheNoWorking()
	{
		$this->assertFalse(Calendar::isNoWorking('23.02.2020'));
		$this->assertFalse(Calendar::isNoWorking('24.02.2020'));
		$this->assertFalse(Calendar::isNoWorking('05.05.2020'));
		$this->assertTrue(Calendar::isNoWorking('06.05.2020'));
		$this->assertTrue(Calendar::isNoWorking('24.06.2020'));
	}

	/**
	 * Проверяем корректность определния рабочего дня
	 *
	 * @testdox Корректное определние рабочего дня
	 */
	public function testCorrectDefinitionOfTheWorking()
	{
		$this->assertFalse(Calendar::isWorking('23.02.2020'));
		$this->assertFalse(Calendar::isWorking('24.02.2020'));
		$this->assertTrue(Calendar::isWorking('06.05.2020'));
		$this->assertTrue(Calendar::isWorking('11.06.2020'));
		$this->assertTrue(Calendar::isWorking('24.06.2020'));
		$this->assertTrue(Calendar::isWorking('20.08.2020'));
	}

	/**
	 * Проверяем корректность определния рабочего дня, признаного нерабочим
	 *
	 * @throws \Exception
	 * @testdox Корректное определние рабочего дня, признаного нерабочим
	 */
	public function testCorrectSimultaneousDefinitionOfTheWorkingAndNoWorking()
	{
		$this->assertTrue(Calendar::isWorking('24.06.2020'));
		$this->assertTrue(Calendar::isNoWorking('24.06.2020'));
	}

	/**
	 * Проверяем корректность определния праздничного дня выпадающего на выходной
	 *
	 * @throws \Exception
	 * @testdox Корректное определние праздничного дня выпадающего на выходной
	 */
	public function testCorrectSimultaneousDefinitionOfTheHolidayAndWeekend()
	{
		$this->assertTrue(Calendar::isHoliday('23.02.2020'));
		$this->assertTrue(Calendar::isHoliday('23.02.2020'));
		$this->assertFalse(Calendar::isWeekend('24.02.2020'));
		$this->assertTrue(Calendar::isHoliday('24.02.2020'));
	}
}
