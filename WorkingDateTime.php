<?php
/**
 * PHP 7.0
 * Created by PhpStorm.
 *
 * @author    : Oleh Boiko <support@mackrais.com> | <https://mackrais.com>
 * @license   MIT License
 * @copyright Copyright (c) 2016 - 2018, MackRais
 */

namespace common\components;

class WorkingDateTime
{
    /**
     * @var int
     */
    protected $_dayHourStart = 6;

    /**
     * @var int
     */
    protected $_dayMinutesStart = 0;

    /**
     * @var int
     */
    protected $_dayHourEnd = 23;

    /**
     * @var int
     */
    protected $_dayMinutesEnd = 0;

    /**
     * Gets name weekends day (days of the week)
     *
     * For example two days ['Sunday','Saturday']
     *
     * List all days ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
     *
     * @var array
     */
    protected $_weekends = [];

    /**
     * Exception dates
     *
     * Use two formats:
     *
     * MM-DD - month and day
     * YYYY-MM-DD - full data
     *
     * For example Each year for the new year is weekend
     * [ '01-01' ]
     *
     * Weekend once a time (for example Easter) because in next year it`s new date
     * [ '2018-04-08' ]
     *
     *
     * @var array
     */
    protected $_exceptionDates = [];

    /**
     * @var int
     */
    protected $_years = 0;

    /**
     * @var int
     */
    protected $_months = 0;

    /**
     * @var int
     */
    protected $_days = 0;

    /**
     * @var int
     */
    protected $_hours = 0;

    /**
     * @var int
     */
    protected $_minutes = 0;

    /**
     * @var int
     */
    protected $_seconds = 0;

    /**
     * @var string
     */
    protected $_dateFrom;

    /**
     * @var bool
     */
    protected $_reverse = false;


    /**
     * WorkingDateTime constructor.
     */
    public function __construct()
    {
        $this->_dateFrom = date('Y-m-d H:i:s');
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    function calculate(): \DateTime
    {
        try {
            $datetime = new \DateTime($this->_dateFrom);
            $endOfDay = clone $datetime;
            $endOfDay->setTime($this->_dayHourEnd, $this->_dayMinutesEnd); //set end of working day time
            $interval = $this->generateIntervalString();
            if($this->_reverse){
                $datetime->sub(new \DateInterval($interval));
            }else{
                $datetime->add(new \DateInterval($interval));
            }
            if ($datetime > $endOfDay) {
                $seconds = $datetime->getTimestamp() - $endOfDay->getTimestamp();
                while (true) {
                    $endOfDay->add(new \DateInterval('PT24H'));
                    $nextDay = $endOfDay->setTime($this->_dayHourStart, $this->_dayMinutesStart);
                    $isExceptionDate = $this->isExceptionDate($nextDay);
                    if (in_array($nextDay->format('l'), $this->_weekends) || $isExceptionDate) {
                        continue;
                    } else {
                        $tmpDate = clone $nextDay;
                        $tmpDate->setTime($this->_dayHourEnd, $this->_dayMinutesEnd);
                        $nextDay->add(new \DateInterval('PT' . $seconds . 'S'));
                        if ($nextDay > $tmpDate) {
                            $seconds = $nextDay->getTimestamp() - $tmpDate->getTimestamp();
                            $endOfDay = clone $tmpDate;
                            $endOfDay->setTime($this->_dayHourStart, $this->_dayMinutesStart);
                        } else {
                            return $endOfDay;
                        }
                    }
                }
            }
            return $datetime;
        } catch (\Throwable $e) {
            return new \DateTime();
        }
    }

    /**
     * @param string $date
     *
     * @return self
     */
    public function setDateFrom(string $date): self
    {
        $this->_dateFrom = $date;
        return $this;
    }

    /**
     * @param int $hour
     *
     * @return self
     */
    public function setStartHourWorkingDay(int $hour): self
    {
        $this->_dayHourStart = $hour;
        return $this;
    }

    /**
     * @param int $minute
     *
     * @return self
     */
    public function setStartMinuteWorkingDay(int $minute): self
    {
        $this->_dayMinutesStart = $minute;
        return $this;
    }

    public function setEndHourWorkingDay(int $hour): self
    {
        $this->_dayHourEnd = $hour;
        return $this;
    }

    /**
     * @param int $minute
     *
     * @return self
     */
    public function setEndMinuteWorkingDay(int $minute): self
    {
        $this->_dayMinutesEnd = $minute;
        return $this;
    }

    /**
     * @param int $years
     *
     * @return self
     */
    public function setYears(int $years): self
    {
        $this->_years = $years;
        return $this;
    }

    /**
     * @param int $months
     *
     * @return self
     */
    public function setMonths(int $months): self
    {
        $this->_months = $months;
        return $this;
    }

    /**
     * @param int $days
     *
     * @return self
     */
    public function setDays(int $days): self
    {
        $this->_days = $days;
        return $this;
    }

    /**
     * @param int $hours
     *
     * @return self
     */
    public function setHours(int $hours): self
    {
        $this->_hours = $hours;
        return $this;
    }

    /**
     * @param int $minutes
     *
     * @return self
     */
    public function setMinutes(int $minutes): self
    {
        $this->_minutes = $minutes;
        return $this;
    }

    /**
     * @param int $seconds
     *
     * @return self
     */
    public function setSeconds(int $seconds): self
    {
        $this->_seconds = $seconds;
        return $this;
    }

    /**
     * @return $this
     */
    public function asReverse(): self{
        $this->_reverse = true;
        return $this;
    }

    /**
     * @see WorkingDateTime::$_exceptionDates
     *
     * @param \DateTime $dateTime
     *
     * @return bool
     */
    protected function isExceptionDate(\DateTime $dateTime): bool
    {
        if (!empty($this->_exceptionDates)) {
            $continue = false;
            foreach ($this->_exceptionDates as $eDate) {
                $eMonthAndDay = \DateTime::createFromFormat('m-d', $eDate);
                $eFullDate = \DateTime::createFromFormat('Y-m-d', $eDate);
                if (!empty($eMonthAndDay) && $dateTime->format('md') == $eMonthAndDay->format('md')) {
                    $continue = true;
                }
                if (!empty($eFullDate) && $dateTime->format('Ymd') == $eFullDate->format('Ymd')) {
                    $continue = true;
                }
                if ($continue) {
                    break;
                }
            }
            return $continue;
        }
        return false;
    }

    /**
     * @return string
     */
    protected function generateIntervalString()
    {
        return "P{$this->_years}Y{$this->_months}M{$this->_days}DT{$this->_hours}H{$this->_minutes}M{$this->_seconds}S";
    }
}
