<?php

declare(strict_types=1);

namespace MackRais\DateTime\Tests;

use MackRais\DateTime\Exception\MaxAttemptsException;
use MackRais\DateTime\WorkingDateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WorkingDateTimeTest extends TestCase
{
    #[Test]
    public function itShouldIdentifyExceptionDatesCorrectly(): void
    {
        $workingDateTime = new WorkingDateTime();
        $workingDateTime
            ->setStartHourWorkingDay(8)
            ->setStartMinuteWorkingDay(0)
            ->setEndHourWorkingDay(17)
            ->setEndMinuteWorkingDay(0);

        $reflection = new \ReflectionClass($workingDateTime);
        $method = $reflection->getMethod('isExceptionDate');
        $method->setAccessible(true);

        $workingDateTime->setExceptionDates(['01-01', '2024-12-25']);

        $date1 = new \DateTime('2025-01-01'); // Should match "01-01"
        $date2 = new \DateTime('2024-12-25'); // Should match "2024-12-25"
        $date3 = new \DateTime('2024-06-15'); // Should NOT match

        $this->assertTrue($method->invokeArgs($workingDateTime, [$date1]));
        $this->assertTrue($method->invokeArgs($workingDateTime, [$date2]));
        $this->assertFalse($method->invokeArgs($workingDateTime, [$date3]));
    }

    #[Test]
    public function itShouldSkipWeekendWhenMovingForward(): void
    {
        $workingDateTime = new WorkingDateTime();
        $workingDateTime
            ->setStartHourWorkingDay(8)
            ->setStartMinuteWorkingDay(0)
            ->setEndHourWorkingDay(17)
            ->setEndMinuteWorkingDay(0);

        $reflection = new \ReflectionClass($workingDateTime);
        $method = $reflection->getMethod('adjustForwardTime');
        $method->setAccessible(true);

        $workingDateTime->setWeekends(['Saturday', 'Sunday']);

        $datetime = new \DateTime('2024-03-08 16:00:00'); // Friday
        $endOfDay = new \DateTime('2024-03-08 17:00:00');

        $result = $method->invokeArgs($workingDateTime, [$datetime, $endOfDay]);

        $this->assertSame('2024-03-11 09:00:00', $result->format('Y-m-d H:i:s'));
    }


    #[Test]
    public function itShouldSkipNonWorkingDayWhenReversingTime(): void
    {
        $workingDateTime = new WorkingDateTime();
        $workingDateTime
            ->setStartHourWorkingDay(8)
            ->setStartMinuteWorkingDay(0)
            ->setEndHourWorkingDay(17)
            ->setEndMinuteWorkingDay(0);

        $reflection = new \ReflectionClass($workingDateTime);
        $method = $reflection->getMethod('adjustReverseTime');
        $method->setAccessible(true);

        $workingDateTime->setWeekends(['Saturday', 'Sunday']);

        $datetime = new \DateTime('2024-03-11 08:00:00'); // Monday
        $startOfDay = new \DateTime('2024-03-11 08:00:00');
        $endOfDay = new \DateTime('2024-03-11 17:00:00');

        $result = $method->invokeArgs($workingDateTime, [$datetime, $startOfDay, $endOfDay]);

        $this->assertSame('2024-03-08 17:00:00', $result->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function itShouldReturnSameDateWhenReverseDoesNotCrossStartOfDay(): void
    {
        $workingDateTime = new WorkingDateTime();
        $reflection = new \ReflectionClass($workingDateTime);
        $method = $reflection->getMethod('calculateReverse');
        $method->setAccessible(true);

        $datetime = new \DateTime('2024-03-15 10:00:00');
        $startOfDay = new \DateTime('2024-03-15 08:00:00');
        $endOfDay = new \DateTime('2024-03-15 17:00:00');

        $result = $method->invokeArgs($workingDateTime, [$datetime, $startOfDay, $endOfDay, 'PT2H']);

        $this->assertSame('2024-03-15 08:00:00', $result->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function itShouldThrowExceptionWhenAdjustingForwardTimeExceedsAttempts(): void
    {
        $workingDateTime = new WorkingDateTime();

        $reflection = new \ReflectionClass($workingDateTime);
        $method = $reflection->getMethod('adjustForwardTime');
        $method->setAccessible(true);

        // Reduce maxAttempts for testing
        $this->setPrivateProperty($workingDateTime, 'maxAttempts', -1);

        $datetime = new \DateTime('2024-03-08 18:00:00'); // After working hours
        $endOfDay = new \DateTime('2024-03-08 17:00:00'); // End of working day

        $this->expectException(MaxAttemptsException::class);
        $this->expectExceptionMessage('Unable to adjust time');

        $method->invokeArgs($workingDateTime, [$datetime, $endOfDay]);
    }

    #[Test]
    public function itShouldThrowExceptionWhenAdjustingReverseTimeExceedsAttempts(): void
    {
        $workingDateTime = new WorkingDateTime();

        $reflection = new \ReflectionClass($workingDateTime);
        $method = $reflection->getMethod('adjustReverseTime');
        $method->setAccessible(true);

        // Reduce maxAttempts for testing
        $this->setPrivateProperty($workingDateTime, 'maxAttempts', -1);

        $datetime = new \DateTime('2024-03-08 06:00:00'); // Before working hours
        $startOfDay = new \DateTime('2024-03-08 08:00:00'); // Start of working day
        $endOfDay = new \DateTime('2024-03-08 17:00:00');

        $this->expectException(MaxAttemptsException::class);
        $this->expectExceptionMessage('Unable to adjust time');

        $method->invokeArgs($workingDateTime, [$datetime, $startOfDay, $endOfDay]);
    }

    /**
     * Helper function to modify private properties for testing.
     */
    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionClass($object);
        $propertyReflection = $reflection->getProperty($property);
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($object, $value);
    }

    #[Test]
    #[DataProvider('workingDateTimeProvider')]
    public function itShouldCalculateCorrectWorkingDate(
        string $dateFrom,
        int $startHour,
        int $startMinute,
        int $endHour,
        int $endMinute,
        int $years,
        int $months,
        int $days,
        int $hours,
        int $minutes,
        int $seconds,
        array $weekends,
        array $exceptionDates,
        bool $reverse,
        string $expectedDate,
    ): void {
        $workingDateTime = (new WorkingDateTime())
            ->setDateFrom($dateFrom)
            ->setStartHourWorkingDay($startHour)
            ->setStartMinuteWorkingDay($startMinute)
            ->setEndHourWorkingDay($endHour)
            ->setEndMinuteWorkingDay($endMinute)
            ->setYears($years)
            ->setMonths($months)
            ->setDays($days)
            ->setHours($hours)
            ->setMinutes($minutes)
            ->setSeconds($seconds)
            ->setWeekends($weekends)
            ->setExceptionDates($exceptionDates);

        if ($reverse) {
            $workingDateTime->asReverse();
        }

        $result = $workingDateTime->calculate()->format('Y-m-d H:i:s');

        $this->assertSame($expectedDate, $result, "Failed for dateFrom: {$dateFrom}");
    }

    public static function workingDateTimeProvider(): array
    {
        return [
            'simple addition within working hours' => [
                'dateFrom' => '2024-03-12 10:00:00',
                'startHour' => 8,
                'startMinute' => 0,
                'endHour' => 17,
                'endMinute' => 0,
                'years' => 0,
                'months' => 0,
                'days' => 0,
                'hours' => 5,
                'minutes' => 0,
                'seconds' => 0,
                'weekends' => ['Saturday', 'Sunday'],
                'exceptionDates' => [],
                'reverse' => false,
                'expectedDate' => '2024-03-12 15:00:00',
            ],

            'adding time beyond working hours' => [
                'dateFrom' => '2024-03-12 14:00:00',
                'startHour' => 8,
                'startMinute' => 0,
                'endHour' => 17,
                'endMinute' => 0,
                'years' => 0,
                'months' => 0,
                'days' => 0,
                'hours' => 5,
                'minutes' => 0,
                'seconds' => 0,
                'weekends' => ['Saturday', 'Sunday'],
                'exceptionDates' => [],
                'reverse' => false,
                'expectedDate' => '2024-03-13 10:00:00',
            ],

            'skip weekend' => [
                'dateFrom' => '2024-03-08 16:00:00',
                'startHour' => 8,
                'startMinute' => 0,
                'endHour' => 17,
                'endMinute' => 0,
                'years' => 0,
                'months' => 0,
                'days' => 0,
                'hours' => 3,
                'minutes' => 0,
                'seconds' => 0,
                'weekends' => ['Saturday', 'Sunday'],
                'exceptionDates' => [],
                'reverse' => false,
                'expectedDate' => '2024-03-11 10:00:00',
            ],

            'handle exception date' => [
                'dateFrom' => '2024-03-07 12:00:00',
                'startHour' => 8,
                'startMinute' => 0,
                'endHour' => 17,
                'endMinute' => 0,
                'years' => 0,
                'months' => 0,
                'days' => 1,
                'hours' => 4,
                'minutes' => 0,
                'seconds' => 0,
                'weekends' => ['Saturday', 'Sunday'],
                'exceptionDates' => ['03-08'],
                'reverse' => false,
                'expectedDate' => '2024-03-13 13:00:00',
            ],

            'cross year calculation' => [
                'dateFrom' => '2024-12-30 14:00:00',
                'startHour' => 8,
                'startMinute' => 0,
                'endHour' => 17,
                'endMinute' => 0,
                'years' => 0,
                'months' => 0,
                'days' => 3,
                'hours' => 2,
                'minutes' => 0,
                'seconds' => 0,
                'weekends' => ['Saturday', 'Sunday'],
                'exceptionDates' => ['01-01'],
                'reverse' => false,
                'expectedDate' => '2025-01-10 16:00:00',
            ],

            'reverse calculation' => [
                'dateFrom' => '2024-03-15 10:00:00',
                'startHour' => 8,
                'startMinute' => 0,
                'endHour' => 17,
                'endMinute' => 0,
                'years' => 0,
                'months' => 0,
                'days' => 1,
                'hours' => 3,
                'minutes' => 30,
                'seconds' => 0,
                'weekends' => ['Saturday', 'Sunday'],
                'exceptionDates' => [],
                'reverse' => true,
                'expectedDate' => '2024-03-12 09:30:00',
            ],

            'weekend in the middle (Wed, Thu)' => [
                'dateFrom' => '2024-03-11 15:00:00',
                'startHour' => 8,
                'startMinute' => 0,
                'endHour' => 17,
                'endMinute' => 0,
                'years' => 0,
                'months' => 0,
                'days' => 3,
                'hours' => 2,
                'minutes' => 0,
                'seconds' => 0,
                'weekends' => ['Wednesday', 'Thursday'],
                'exceptionDates' => [],
                'reverse' => false,
                'expectedDate' => '2024-03-23 17:00:00',
            ],

            'no weekends at all (continuous working week)' => [
                'dateFrom' => '2024-03-08 14:00:00',
                'startHour' => 6,
                'startMinute' => 30,
                'endHour' => 21,
                'endMinute' => 0,
                'years' => 0,
                'months' => 0,
                'days' => 2,
                'hours' => 5,
                'minutes' => 30,
                'seconds' => 0,
                'weekends' => [], // No weekends
                'exceptionDates' => [],
                'reverse' => false,
                'expectedDate' => '2024-03-12 09:30:00',
            ],

            'one week vacation (exception dates)' => [
                'dateFrom' => '2024-07-01 09:00:00',
                'startHour' => 8,
                'startMinute' => 0,
                'endHour' => 17,
                'endMinute' => 0,
                'years' => 0,
                'months' => 0,
                'days' => 2,
                'hours' => 4,
                'minutes' => 30,
                'seconds' => 0,
                'weekends' => ['Saturday', 'Sunday'],
                'exceptionDates' => ['07-02', '07-03', '07-04', '07-05', '07-06', '07-07'], // One week vacation
                'reverse' => false,
                'expectedDate' => '2024-07-12 16:30:00',
            ],

            'long range addition over multiple months' => [
                'dateFrom' => '2024-08-15 14:30:00',
                'startHour' => 9,
                'startMinute' => 0,
                'endHour' => 18,
                'endMinute' => 0,
                'years' => 0,
                'months' => 1,
                'days' => 5,
                'hours' => 7,
                'minutes' => 45,
                'seconds' => 0,
                'weekends' => ['Saturday', 'Sunday'],
                'exceptionDates' => [],
                'reverse' => false,
                'expectedDate' => '2024-12-30 13:15:00',
            ],

            'reverse calculation with midweek weekends' => [
                'dateFrom' => '2024-10-10 11:00:00',
                'startHour' => 8,
                'startMinute' => 0,
                'endHour' => 17,
                'endMinute' => 0,
                'years' => 0,
                'months' => 0,
                'days' => 2,
                'hours' => 6,
                'minutes' => 0,
                'seconds' => 0,
                'weekends' => ['Tuesday', 'Wednesday'], // Midweek weekends
                'exceptionDates' => [],
                'reverse' => true,
                'expectedDate' => '2024-09-30 11:00:00',
            ],

            'reverse calculation with multiple holidays' => [
                'dateFrom' => '2024-12-28 14:00:00',
                'startHour' => 7,
                'startMinute' => 0,
                'endHour' => 16,
                'endMinute' => 30,
                'years' => 0,
                'months' => 0,
                'days' => 4,
                'hours' => 3,
                'minutes' => 45,
                'seconds' => 0,
                'weekends' => ['Saturday', 'Sunday'],
                'exceptionDates' => ['12-30', '12-31', '01-01'],
                'reverse' => true,
                'expectedDate' => '2024-12-16 09:15:00',
            ],

            'working with leap year' => [
                'dateFrom' => '2024-02-28 12:00:00',
                'startHour' => 8,
                'startMinute' => 0,
                'endHour' => 17,
                'endMinute' => 0,
                'years' => 0,
                'months' => 0,
                'days' => 2,
                'hours' => 4,
                'minutes' => 0,
                'seconds' => 0,
                'weekends' => ['Saturday', 'Sunday'],
                'exceptionDates' => [],
                'reverse' => false,
                'expectedDate' => '2024-03-07 10:00:00',
            ],
        ];
    }
}
