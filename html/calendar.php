<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Calendar</title>
</head>
<body>
    <div class="grid">
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
        <div><?=datefmt_format($fmt, $day)?></div>
    <?php
    }

    // do I need eventid?
    $sql = "SELECT name, description, datetime_start, datetime_end, location,
        created_by, approved_by, event_series
        FROM event
        WHERE approval_state = 0
        ORDER BY datetime_start;";
    //foreach ($dbh->query($sql, PDO::FETCH_ASSOC) as $row) {
    for ($i=0; $i < 35; $i++) { ?>
        <div><?=$i + 1?></div>    
         <!-- <div><?=var_dump($row)?></div> -->
    <?php
    }

    # is leap year?
    # what day is the 1st of the month?
    ?>
        
    </div>
    
</body>
</html>