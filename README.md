[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/carono/production-calendar/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/carono/production-calendar/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/carono/production-calendar/v/stable)](https://packagist.org/packages/carono/production-calendar)
[![Total Downloads](https://poser.pugx.org/carono/production-calendar/downloads)](https://packagist.org/packages/carono/production-calendar)
[![License](https://poser.pugx.org/carono/production-calendar/license)](https://packagist.org/packages/carono/production-calendar)

# Производственный календарь

Список праздников в соответствии с производственным календарём Российской Федерации 2012-2021гг  
Данные предоставлены сайтом http://www.consultant.ru/law/ref/calendar/proizvodstvennye

|Метод|Результат|Описание|
|---|:--:|---|
|Calendar::isWorking('2016-05-09')|`false`|9мая выходной день
|Calendar::isHoliday('2016-05-09')|`true`|9мая это праздник
|Calendar::find('2016-05-07')->working()->format()|2016-05-10|Рабочий день с 7мая (включительно) это 10мая
|Calendar::isPreHoliday('2016-02-20')|`true`|20 февраля предпраздничный день (укороченный)
|Calendar::isNoWorking('2020-04-20')|`true`|20 апреля 2020 года объявлен нерабочим днём
|Calendar::find('2016-12-31')->next()->isWorking()|`false`|1 января выходной день

`isHoliday()` - проверяет не на фактический праздник, а на параздик+выходные.  
`isPreHoliday()` - предпраздничный день, может выпасть на выходной, который считается рабочим, поэтому isWorking вернёт `true`, а isHoliday - `false`  
`working()`, `holiday()`, `preHoliday()`, `noWorking()` - функции будут перебирать все даты день за днём, пока не найдут рабочий, выходной, нерабочий или предпраздничный день

Массив всех данных можно найти в json файле - **holidays.json**