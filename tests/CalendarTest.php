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
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::getInstance
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
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::find
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
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::isPreHoliday
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
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::isHoliday
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
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::isWeekend
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
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::isNoWorking
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
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::isWorking
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
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::isWorking
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::isNoWorking
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
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::isHoliday
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::isWeekend
	 */
	public function testCorrectSimultaneousDefinitionOfTheHolidayAndWeekend()
	{
		$this->assertTrue(Calendar::isHoliday('23.02.2020'));
		$this->assertTrue(Calendar::isWeekend('23.02.2020'));
		$this->assertFalse(Calendar::isWeekend('24.02.2020'));
		$this->assertTrue(Calendar::isHoliday('24.02.2020'));
	}

	/**
	 * Проверяем корректность возвращаемой текущей даты
	 *
	 * @throws \Exception
	 * @testdox Корректное возврат текущей даты
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::find
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::date
	 */
	public function testDate()
	{
		$this->assertEquals(new \DateTime('20.08.2020'), Calendar::find('2020-08-20')->date());
	}

	/**
	 * Проверяем корректность сдвига текущей даты на один день вперёд
	 *
	 * @throws \Exception
	 * @testdox Корректный сдвиг текущей даты на один день вперёд
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::find
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::next
	 */
	public function testNext()
	{
		$this->assertEquals(new \DateTime('21.08.2020'), Calendar::find('2020-08-20')->next()->date());
		$this->assertEquals(new \DateTime('01.01.2021'), Calendar::find('2020-12-31')->next()->date());
	}

	/**
	 * Проверяем корректность сдвига текущей даты на один день назад
	 *
	 * @throws \Exception
	 * @testdox Корректный сдвиг текущей даты на один день назад
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::find
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::prev
	 */
	public function testPrev()
	{
		$this->assertEquals(new \DateTime('19.08.2020'), Calendar::find('2020-08-20')->prev()->date());
		$this->assertEquals(new \DateTime('31.12.2019'), Calendar::find('2020-01-01')->prev()->date());
	}

	/**
	 * Проверяем корректность форматирования даты
	 *
	 * @throws \Exception
	 * @testdox Корректное форматирование даты
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::find
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::format
	 */
	public function testFormat()
	{
		$this->assertEquals('2020-10-01', Calendar::find('01.10.2020')->format());
		$this->assertEquals('01.10.2020', Calendar::find('2020-10-01')->format('d.m.Y'));
	}

	/**
	 * Проверяем корректность форматирования даты в UNIX-timestamp
	 *
	 * @throws \Exception
	 * @testdox Корректное форматирование даты в UNIX-timestamp
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::find
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::timestamp
	 */
	public function testTimestamp()
	{
		$this->assertEquals(1601510400, Calendar::find('01.10.2020')->timestamp());
	}

	/**
	 * Проверяем корректность преобразования в строку
	 *
	 * @throws \Exception
	 * @testdox Корректное преобразование в строку
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::find
	 * @covers  \Webmasterskaya\ProductionCalendar\Calendar::__toString
	 */
	public function testToString()
	{
		$this->assertEquals('2020-10-01', (string) Calendar::find('01.10.2020'));
	}

	public function testGetHolidaysListByInterval()
	{
		// TODO: реализовать тест метода
	}

	public function testGetWorkingListByInterval()
	{
		// TODO: реализовать тест метода
	}

	public function testGetNoWorkingListByInterval()
	{
		// TODO: реализовать тест метода
	}

	public function testGetPreHolidayListByInterval()
	{
		// TODO: реализовать тест метода
	}

	public function testWorking()
	{
		// TODO: реализовать тест метода
	}

	public function testHoliday()
	{
		// TODO: реализовать тест метода
	}

	public function testPreHoliday()
	{
		// TODO: реализовать тест метода
	}

	public function testNoWorking()
	{
		// TODO: реализовать тест метода
	}
}
