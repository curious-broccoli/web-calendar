<?php
$title = "Calendar";
$my_script = "calendar.js";
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
    </div>
    <ol id="calendar-grid" class="calendar-grid">
    </ol>
</div>

<?php
include __DIR__ . "/../src/bottom.php";
?>