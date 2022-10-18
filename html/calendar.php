<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Calendar</title>
</head>
<body>
    <?php
    define('LOCALE', 'en_US');
    define('TIMEZONE', 'Europe/London');
    # get date (day, month, year) (later it can be set by user)
    $selected_date = new DateTimeImmutable("now");
    $date_formatter = new IntlDateFormatter(
        LOCALE,
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        TIMEZONE,
        IntlDateFormatter::GREGORIAN,
    );
    # first day is what weekday
    # how many days cal_days_in_month
    
    ?>
    <div class="calendar">
    <div class="calendar-header">
        <?php
        $date_formatter->setPattern('MMMM Y');
        echo $date_formatter->format($selected_date)
        ?>
    </div>
    <ol class="day-header">
    <?php
    require_once __DIR__ . "/../src/dbconnection.php";

    // get name of all days (later depending on locale and timezone)
    $seconds_in_day = 60 * 60 * 24;
    $weekdays = array(
        'mon' => - 3 * $seconds_in_day,
        'tue' => - 2 * $seconds_in_day,
        'wed' => - $seconds_in_day,
        'thu' => 0,
        'fri' => $seconds_in_day,
        'sat' => 2 * $seconds_in_day,
        'sun' => 3 * $seconds_in_day);
    $date_formatter->setPattern('eeee');
    foreach ($weekdays as $key => $day) {?>
        <li><?=$date_formatter->format($day)?></li>
    <?php
    }
    ?>
    </ol>
    <ol class="day-grid">
    <?php

    // get events for current view
    // do I need eventid?
    $sql = "SELECT name, description, datetime_start, datetime_end, location,
        created_by, approved_by, event_series
        FROM event
        WHERE approval_state = 0
        ORDER BY datetime_start;";
    //foreach ($dbh->query($sql, PDO::FETCH_ASSOC) as $row) {
    for ($i=0; $i < 35; $i++) { ?>
        <li><?=$i + 1?></li>
         <!-- <div><?=var_dump($row)?></div> -->
    <?php
    }
    
    ?>
    </ol>
    </div>
    
</body>
</html>