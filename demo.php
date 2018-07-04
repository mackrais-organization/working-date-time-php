<?php
/**
 * PHP 7.0
 * Created by PhpStorm.
 *
 * @author    : Oleh Boiko <mackrais@gmail.com> | <http://mackrais.com>
 * @license   MIT License
 * @copyright Copyright (c) 2016 - 2018, MackRais
 */

require_once 'WorkingDateTime.php';

// create new object
$workingDateTime = new \mackrais\datetime\WorkingDateTime();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>MackRais WorkingDateTime.php script </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css"
          integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
</head>

<body>

<div class="container-fluid" style="padding: 20px">

    <h3>Demo 1</h3>


    <div class="row">
        <div class="col-lg-6">
            We need to get the date and time for sending the email to the support service, after <strong>5
                hours</strong>
            from some
            action such as (cancellation of the reservation). !(During working hours)
            Working hours from <strong>08:00 to 17:00</strong> . If the time does not reach today, we must send the next
            day.
            For an example, let's take the current date and time as <strong>2018-07-04 16:00:00</strong>
        </div>

        <div class="col-lg-3 ">
            <h5>Result execute script: </h5>
            <div class="alert alert-secondary">
                <?= $workingDateTime
                    ->setStartHourWorkingDay(8)
                    ->setEndHourWorkingDay(17)
                    ->setDateFrom('2018-07-04 16:00:00')
                    ->setHours(5)
                    ->calculate()
                    ->format('Y-m-d H:i:s');
                ?>
            </div>

        </div>

        <div class="col-lg-3">
            <h5>PHP Code: </h5>
            <code>
                $workingDateTime = new WorkingDateTime();
                <br>
                echo  $workingDateTime  <br>
                ->setStartHourWorkingDay(8)  <br>
                ->setEndHourWorkingDay(17)  <br>
                ->setDateFrom('2018-07-04 16:00:00')  <br>
                ->setHours(5)  <br>
                ->calculate()  <br>
                ->format('Y-m-d H:i:s');  <br>
            </code>
        </div>


    </div>


</div>


</body>

</html>


