# PHP script Working Date Time

## Content
- [Installation](#installation)
- [How to use](#how-to-use)
- [Demos (examples)](https://mackrais.github.io/mr-upload-file-button/)
- [License](LICENSE.md)

## Installation



Either run

```
how install
```


## How to use

```php
           $workingDateTime = new WorkingDateTime();

           echo $workingDateTime
                ->setExceptionDates(['01-01']) // Exception date for example always New Year
                ->setWeekends(['Sunday', 'Saturday']) // Weekends days
                ->setDateFrom(date('Y-m-d H:i:s', strtotime('2019-02-01 10:10:20'))) // set date start from 
                ->setStartHourWorkingDay(6)
                ->setStartMinuteWorkingDay(30)
                ->setEndHourWorkingDay(14)
                ->setEndMinuteWorkingDay(30)
                ->setHours(12)
                ->setMinutes(30)
                ->calculate()
                ->format('Y-m-d H:i:s');
                
               // result 2019-02-05 06:40:20
                
           // Reverse date time
           $workingDateTime = new WorkingDateTime();

           echo $workingDateTime
                ->setExceptionDates(['01-01']) // Exception date for example always New Year
                ->setWeekends(['Sunday', 'Saturday']) // Weekends days
                ->setDateFrom(date('Y-m-d H:i:s', strtotime('2019-02-01 10:10:20'))) // set date start from 
                ->setStartHourWorkingDay(6)
                ->setStartMinuteWorkingDay(30)
                ->setEndHourWorkingDay(14)
                ->setEndMinuteWorkingDay(30)
                ->setHours(12)
                ->setMinutes(30)
                ->asReverse()
                ->calculate()
                ->format('Y-m-d H:i:s');

```

## License

**working-date-time-php** is released under the MIT License. See the bundled [`LICENSE.md`](LICENSE.md) for details.
