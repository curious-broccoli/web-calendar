<?php
$title = "Calendar";
$my_scripts = [
    ["name" => "calendar.js", "isModule" => true]
];
$hide_nav = true;
include __DIR__ . "/../src/top.php";
require_once __DIR__ . "/../src/dbconnection.php";

$format = "Y-m-d";
$today = date($format, strtotime("now"));
?>

<div class="calendar">
    <div id="calendar-header" class="calendar-header">
        <button class="date-picker-arrow" id="date-arrow-left" type="button"><</button>
        <button class="date-picker-arrow" id="date-arrow-right" type="button">></button>
        <input type="date" id="date-picker" value="<?=$today?>" min="2000-01-01">
        <div id="calendar-header-text"></div>
        <div id="view-tabs">
            <input type="radio" id="tab-list" name="view" value="list" />
            <label for="tab-list">List</label>
            <input type="radio" id="tab-day" name="view" value="day" />
            <label for="tab-day">Day</label>
            <input type="radio" id="tab-workweek" name="view" value="workweek" />
            <label for="tab-workweek">Workweek</label>
            <input type="radio" id="tab-week" name="view" value="week" />
            <label for="tab-week">Week</label>
            <input type="radio" id="tab-month" name="view" value="month" checked/>
            <label for="tab-month">Month</label>
            <input type="radio" id="tab-year" name="view" value="year" />
            <label for="tab-year">Year</label>
        </div>
    </div>
    <ol id="calendar-grid" class="calendar-grid">
    </ol>
</div>

<?php
include __DIR__ . "/../src/bottom.php";
?>