# Производственный календарь

Список рабочих, праздничных, выходных и нерабочих дней, в соответствии с производственным календарём Российской Федерации 2013-2021гг  
Данные предоставлены сайтом http://www.consultant.ru/

## Начало использования

Для начала использования, добавте пакет в ваш проект
`composer require webmasterskaya\ProductionCalendar`

И загрузите нужный класс
```php
use webmasterskaya\ProductionCalendar\Calendar;
use webmasterskaya\ProductionCalendar\Updater;
```

Массив всех данных можно найти в json файле - **holidays.json**

## Описание методов класса Calendar

### Проверка конкретной даты

Является ли день рабочим
```php
Calendar::isWorking($date, $weekend = [6, 0]); //bool
```

Является ли день праздничным
> Метод проверяет не на фактический праздник, а на параздик+выходные
```php
Calendar::isHoliday($date = null); //bool
```

Является ли день предпраздничным
> Обычно, предпразднечные рабочие дни сокращены на 1 час
> Предпраздничный день, может выпасть на выходной, который считается рабочим, поэтому isWorking вернёт `true`, а isHoliday - `false`
```php
Calendar::isPreHoliday($date); //bool
```

Является ли день не рабочим
> В 2020 году появилось новое определение "Нерабочий день"
> Нерабочие дни введены в соответствии с Указами Президента РФ от 25.03.2020 N 206, от 02.04.2020 N 239, от 28.04.2020 N 294, от 29.05.2020 N 345, от 23.04.2021 N 242
```php
Calendar::isNoWorking($date); //bool
```

### Поиск по дате

Найти ближайший рабочий день, за указанной датой.
```php
Calendar::find($date = null)->working()->format($format = null); //string
```

Найти ближайший выходной день, за указанной датой.
```php
Calendar::find($date = null)->holiday()->format($format = null); //string
```

Найти ближайший предпразднечный день, за указанной датой.
```php
Calendar::find($date = null)->preHoliday()->format($format = null); //string
```

Найти ближайший нерабочий день, за указанной датой.
```php
Calendar::find($date = null)->noWorking()->format($format = null); //string
```

### Получить список дат

Полуить список всех рабочих дней в году
```php
Calendar::getWorkingsByYear($year); //array
```

Полуить список всех выходных дней в году
```php
Calendar::getHolidaysByYear($year); //array
```

Полуить список всех предпраздничных дней в году
```php
Calendar::getPreHolidaysByYear($year); //array
```

Полуить список всех нерабочих дней в году
```php
Calendar::getNoWorkingByYear($year); //array
```

Полуить список всех выходных дней за указанный промежуток дат
```php
Calendar::getHolidaysListByInterval($date_from, $date_to, $format = null); //array
```

## Описание методов класса Updater

Обновить данные за указанный год 
```php
Updater::update($year = null); //void
```

Обновить все данные, начиная с 2013 года и до текущего
```php
Updater::updateAll(); //void
```

## Обновление данных

Для обновления данных необходимо выполнить cli скрипт parser.php
```shell
php -f parser.php # обновит данные за текущий год
php -f parser.php 2020 # обновит данные за указанный год
php -f parser.php all # обновит все данные за промежуток с 2013 по текущий год 
```