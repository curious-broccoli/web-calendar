<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Calendar</title>
</head>
<body>
    <div class="calendar">
    <div class="calendar-header">Oktober 2017</div>
    <ol class="day-header">
    <?php
    require_once __DIR__ . "/../src/dbconnection.php";

    $seconds_in_day = 60 * 60 * 24;
    $weekdays = array(
        'mon' => - 3 * $seconds_in_day,
        'tue' => - 2 * $seconds_in_day,
        'wed' => - $seconds_in_day,
        'thu' => 0,
        'fri' => $seconds_in_day,
        'sat' => 2 * $seconds_in_day,
        'sun' => 3 * $seconds_in_day);
    $fmt = datefmt_create(
        'en_US',
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        'Europe/London',
        IntlDateFormatter::GREGORIAN,
        'eeee'
    );
    // get name of all days depending on locale and timezone
    foreach ($weekdays as $key => $day) {?>
        <li><?=datefmt_format($fmt, $day)?></li>    
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

    # first day is what weekday
    # how many days
    ?>
    </ol>
    </div>
    
</body>
</html>