# PHP script Working Date Time

[![Scrutinizer Quality Score](https://img.shields.io/scrutinizer/g/mackrais-organization/working-date-time-php.svg?style=flat-square)](https://scrutinizer-ci.com/g/mackrais-organization/working-date-time-php/)
[![Build Status](https://img.shields.io/github/actions/workflow/status/mackrais-organization/working-date-time-php/ci.yaml?branch=master&style=flat-square)](https://github.com/mackrais-organization/working-date-time-php/actions?query=workflow%3ACI+branch%3Amaster+)
[![CodeCov](https://img.shields.io/codecov/c/github/mackrais-organization/working-date-time-php.svg?style=flat-square)](https://codecov.io/github/mackrais-organization/working-date-time-php)
[![StyleCI](https://styleci.io/repos/139721416/shield?style=flat-square)](https://styleci.io/repos/139721416)
[![Gitter](https://img.shields.io/badge/gitter-join%20chat-brightgreen.svg?style=flat-square)](https://gitter.im/mackrais-organization/working-date-time-php)

## Overview
The **Working Date Time** PHP library allows you to perform calculations on working hours and business days while skipping weekends and holidays. This can be useful for scheduling tasks, processing business logic that adheres to working hours, and handling delays due to non-working days.

### Features
- Supports custom **start and end working hours**
- Skips **weekends and exception dates (holidays)**
- Supports both **forward and reverse calculations**
- Allows adding days, hours, minutes within **working time only**
- Handles **cross-year transitions** while respecting work schedules
- Efficiently processes **work duration across multiple days**
- Ideal for **workforce scheduling, deadline calculations, and automated task execution**

## 📌 Table of Contents
- [Installation](#installation)
- [How to Use](#how-to-use)
- [Examples](#examples)
    - [Default Usage](docs/example_1_default.md)
    - [Reverse Calculation](docs/example_1_reverse.md)
- [Algorithm Explanation](#algorithm-explanation)
- [License](LICENSE.md)

## 🛠 Installation

You can install this package via Composer:
```sh
composer require mackrais/working-date-time
```

Alternatively, you can manually include it in your project.

## 🚀 How to Use

```php
$workingDateTime = new WorkingDateTime();

// Forward calculation example
$calculatedDate = $workingDateTime
    ->setExceptionDates(['01-01']) // New Year as an exception date
    ->setWeekends(['Sunday', 'Saturday']) // Weekends are Saturday and Sunday
    ->setDateFrom('2024-03-12 10:00:00') // Start date
    ->setStartHourWorkingDay(8)
    ->setStartMinuteWorkingDay(0)
    ->setEndHourWorkingDay(17)
    ->setEndMinuteWorkingDay(0)
    ->setDays(2) // Add 2 working days
    ->setHours(4) // Add 4 working hours
    ->calculate()
    ->format('Y-m-d H:i:s');

// Output: 2024-03-14 14:00:00
```

## 🔄 Reverse Calculation Example
```php
$workingDateTime = new WorkingDateTime();

// Reverse calculation example (subtracting time)
$calculatedDate = $workingDateTime
    ->setExceptionDates(['01-01']) // New Year as an exception date
    ->setWeekends(['Sunday', 'Saturday']) // Weekends
    ->setDateFrom('2024-03-15 10:00:00') // Start date
    ->setStartHourWorkingDay(8)
    ->setStartMinuteWorkingDay(0)
    ->setEndHourWorkingDay(17)
    ->setEndMinuteWorkingDay(0)
    ->setDays(1) // Subtract 1 working day
    ->setHours(3) // Subtract 3 working hours
    ->setMinutes(30) // Subtract 30 minutes
    ->asReverse() // Reverse mode
    ->calculate()
    ->format('Y-m-d H:i:s');

// Output: 2024-03-12 09:30:00
```

## 📚 Algorithm Explanation
The script calculates working time while considering working hours, weekends, and exception dates. The steps are as follows:

### 1️⃣ **Initialize Time Variables**
- Parse the given `dateFrom` and extract hours, minutes, and seconds.
- Determine `startOfDay` and `endOfDay` based on working hours.

### 2️⃣ **Apply Additions or Subtractions**
- If adding time, increment the date by the provided days/hours while skipping weekends and holidays.
- If subtracting time (reverse mode), decrement accordingly while respecting work hours.

### 3️⃣ **Handle Working Hour Limits**
- If the added/subtracted time crosses the `endOfDay`, carry over to the next/previous working day.
- If the computed time lands on a weekend or exception date, shift forward or backward accordingly.

### 4️⃣ **Edge Case Handling**
- **Cross-Year Transitions**: If the date range includes New Year or other holidays, the script ensures calculations account for them.
- **Multiple Non-Working Days**: If several holidays or weekends occur consecutively, the script intelligently skips them.

## 🏆 Use Cases
- **Task Scheduling**: Automate scheduling tasks that should only execute during business hours.
- **Deadline Calculations**: Compute deadlines that exclude weekends and holidays.
- **Workforce Management**: Calculate shift times while considering off days and public holidays.
- **Financial Calculations**: Determine transaction processing dates based on business hours.

## 📜 License
**working-date-time-php** is released under the MIT License. See the bundled [`LICENSE.md`](LICENSE.md) for details.

