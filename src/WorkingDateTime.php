<?php
/**
 * PHP 8.4
 * Created by PhpStorm.
 *
 * @author    : Oleh Boiko <support@mackrais.com> | <https://mackrais.com>
 * @license   MIT License
 * @copyright Copyright (c) 2016 - 2025, MackRais
 */

declare(strict_types=1);

namespace MackRais\DateTime;

use MackRais\DateTime\Exception\MaxAttemptsException;

final class WorkingDateTime
{
    private int $maxAttempts;
    private int $dayHourStart;
    private int $dayMinutesStart;
    private int $dayHourEnd;
    private int $dayMinutesEnd;

    /**
     * Gets name weekends day (days of the week).
     *
     * For example two days ['Sunday','Saturday']
     *
     * List all days ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
     *
     * @var array
     */
    private array $weekends = [];

    /**
     * Exception dates.
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
     * @var array
     */
    private array $exceptionDates = [];
    private int $years = 0;
    private int $months = 0;
    private int $days = 0;
    private int $hours = 0;
    private int $minutes = 0;
    private int $seconds = 0;
    private string $dateFrom;
    private bool $reverse = false;

    /**
     * WorkingDateTime constructor.
     */
    public function __construct()
    {
        $this->dateFrom = date('Y-m-d H:i:s');
        $this->dayHourStart = 6;
        $this->dayMinutesStart = 0;
        $this->dayHourEnd = 23;
        $this->dayMinutesEnd = 0;
        $this->maxAttempts = PHP_INT_MAX;
    }

    public function calculate(): \DateTime
    {
        $datetime = new \DateTime($this->dateFrom);
        $endOfDay = $this->getEndOfDay($datetime);
        $startOfDay = $this->getStartOfDay($datetime);

        $interval = $this->generateIntervalString();

        if ($this->reverse) {
            return $this->calculateReverse($datetime, $startOfDay, $endOfDay, $interval);
        }

        return $this->calculateForward($datetime, $startOfDay, $endOfDay, $interval);
    }

    /**
     * Calculates in reverse mode.
     */
    private function calculateReverse(\DateTime $datetime, \DateTime $startOfDay, \DateTime $endOfDay, string $interval): \DateTime
    {
        $datetime->sub(new \DateInterval($interval));

        if ($datetime < $startOfDay) {
            return $this->adjustReverseTime($datetime, $startOfDay, $endOfDay);
        }

        return $datetime;
    }

    /**
     * Calculates in forward mode.
     */
    private function calculateForward(\DateTime $datetime, \DateTime $startOfDay, \DateTime $endOfDay, string $interval): \DateTime
    {
        $datetime->add(new \DateInterval($interval));

        if ($datetime > $endOfDay) {
            return $this->adjustForwardTime($datetime, $endOfDay);
        }

        return $datetime;
    }

    /**
     * Adjusts time when moving to the previous working day in reverse mode.
     */
    private function adjustReverseTime(\DateTime $datetime, \DateTime $startOfDay, \DateTime $endOfDay): \DateTime
    {
        $seconds = $startOfDay->getTimestamp() - $datetime->getTimestamp();
        $attempts = 0;

        while ($attempts <= $this->maxAttempts) {
            $endOfDay->sub(new \DateInterval('PT24H')); // Перехід на попередній день
            $prevDay = $this->setEndOfDay($endOfDay);

            if ($this->isNonWorkingDay($prevDay)) {
                continue;
            }

            $tmpDate = $this->setStartOfDay(clone $prevDay);
            $prevDay->sub(new \DateInterval('PT' . abs($seconds) . 'S'));

            if ($prevDay < $tmpDate) {
                $seconds = $tmpDate->getTimestamp() - $prevDay->getTimestamp();
                $endOfDay = $this->setEndOfDay(clone $tmpDate);
            } else {
                return $endOfDay;
            }
            ++$attempts;
        }

        throw new MaxAttemptsException('Unable to adjust time');
    }

    /**
     * Adjusts time when carrying over to the next working day.
     */
    private function adjustForwardTime(\DateTime $datetime, \DateTime $endOfDay): \DateTime
    {
        $seconds = $datetime->getTimestamp() - $endOfDay->getTimestamp();
        $attempts = 0;

        while ($attempts <= $this->maxAttempts) {
            $endOfDay->add(new \DateInterval('PT24H'));
            $nextDay = $this->setStartOfDay($endOfDay);

            if ($this->isNonWorkingDay($nextDay)) {
                continue;
            }

            $tmpDate = $this->setEndOfDay(clone $nextDay);
            $nextDay->add(new \DateInterval('PT' . abs($seconds) . 'S'));

            if ($nextDay > $tmpDate) {
                $seconds = $nextDay->getTimestamp() - $tmpDate->getTimestamp();
                $endOfDay = $this->setStartOfDay(clone $tmpDate);
            } else {
                return $endOfDay;
            }
            ++$attempts;
        }

        throw new MaxAttemptsException('Unable to adjust time');
    }

    private function getStartOfDay(\DateTime $date): \DateTime
    {
        return (clone $date)->setTime($this->dayHourStart, $this->dayMinutesStart);
    }

    private function getEndOfDay(\DateTime $date): \DateTime
    {
        return (clone $date)->setTime($this->dayHourEnd, $this->dayMinutesEnd);
    }

    private function setEndOfDay(\DateTime $date): \DateTime
    {
        return $date->setTime($this->dayHourEnd, $this->dayMinutesEnd);
    }

    private function setStartOfDay(\DateTime $date): \DateTime
    {
        return $date->setTime($this->dayHourStart, $this->dayMinutesStart);
    }

    private function isNonWorkingDay(\DateTime $date): bool
    {
        return in_array($date->format('l'), $this->weekends, true) || $this->isExceptionDate($date);
    }

    public function setDateFrom(string $date): self
    {
        $this->dateFrom = $date;

        return $this;
    }

    public function setStartHourWorkingDay(int $hour): self
    {
        $this->dayHourStart = $hour;

        return $this;
    }

    public function setStartMinuteWorkingDay(int $minute): self
    {
        $this->dayMinutesStart = $minute;

        return $this;
    }

    public function setEndHourWorkingDay(int $hour): self
    {
        $this->dayHourEnd = $hour;

        return $this;
    }

    public function setEndMinuteWorkingDay(int $minute): self
    {
        $this->dayMinutesEnd = $minute;

        return $this;
    }

    public function setYears(int $years): self
    {
        $this->years = $years;

        return $this;
    }

    public function setMonths(int $months): self
    {
        $this->months = $months;

        return $this;
    }

    public function setDays(int $days): self
    {
        $this->days = $days;

        return $this;
    }

    public function setHours(int $hours): self
    {
        $this->hours = $hours;

        return $this;
    }

    public function setMinutes(int $minutes): self
    {
        $this->minutes = $minutes;

        return $this;
    }

    public function setSeconds(int $seconds): self
    {
        $this->seconds = $seconds;

        return $this;
    }

    public function asReverse(): self
    {
        $this->reverse = true;

        return $this;
    }

    public function setWeekends(array $days): self
    {
        $this->weekends = $days;

        return $this;
    }

    public function setExceptionDates(array $dates): self
    {
        $this->exceptionDates = $dates;

        return $this;
    }

    private function isExceptionDate(\DateTime $dateTime): bool
    {
        if (!empty($this->exceptionDates)) {
            $continue = false;
            foreach ($this->exceptionDates as $eDate) {
                $eMonthAndDay = \DateTime::createFromFormat('m-d', $eDate);
                $eFullDate = \DateTime::createFromFormat('Y-m-d', $eDate);
                if (!empty($eMonthAndDay) && $dateTime->format('md') === $eMonthAndDay->format('md')) {
                    $continue = true;
                }
                if (!empty($eFullDate) && $dateTime->format('Ymd') === $eFullDate->format('Ymd')) {
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

    private function generateIntervalString(): string
    {
        return "P{$this->years}Y{$this->months}M{$this->days}DT{$this->hours}H{$this->minutes}M{$this->seconds}S";
    }
}
