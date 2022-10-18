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
    require_once __DIR__ . "/../src/dbconnection.php";
    define('LOCALE', 'en_US');
    define('TIMEZONE', 'Europe/London');    
    // get date (day, month, year is probably needed)(later it can be set by user)
    // get selected datetime, everything but year and month stripped
    // maybe try except around the date(), depends how the date is selected
    $selected_date = new DateTimeImmutable(date('o-m'));
    $date_formatter = new IntlDateFormatter(
        LOCALE,
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        TIMEZONE,
        IntlDateFormatter::GREGORIAN,
    );
    
    ?>
    <div class="calendar">
    <div class="calendar-header">
        <?php
        // get name of month and year
        $date_formatter->setPattern('MMMM Y');
        echo $date_formatter->format($selected_date)
        ?>
    </div>
    <ol class="day-header">
    <?php
    // get name of all days
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
    // here the number of needed columns should be minimum and not fixed
    $total_days_displayed = 7 * 6;
    $number_of_days = intval($selected_date->format('t'));
    //$number_of_today = intval($selected_date->format('j'));
    $first_day_on = intval($selected_date->format('N'));
    //for today coloring it will also need to check if month and year is the same
    for ($i=0; $i < $first_day_on - 1; $i++) { 
        echo '<li class="month-prev">prev</li>';
    }
    for ($i=1; $i <= $number_of_days; $i++) {
            echo '<li>' , $i , '</li>';
        
        // if ($i == $number_of_today) {
        //     echo '<li id="today">' ,  $i , '</li>';
        // }       
    }
    for ($i=$number_of_days + $first_day_on - 1; $i < $total_days_displayed; $i++) { 
        echo '<li class="month-next">next</li>';
    }
    
    // get events for current view
    // do I need eventid?
    $sql = "SELECT name, description, datetime_start, datetime_end, location,
        created_by, approved_by, event_series
        FROM event
        WHERE approval_state = 0
        ORDER BY datetime_start;";
    //foreach ($dbh->query($sql, PDO::FETCH_ASSOC) as $row) {
        /*<!-- <div><?=var_dump($row)?></div> -->*/
    ?>
    </ol>
    </div>
    
</body>
</html>