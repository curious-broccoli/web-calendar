<?php
$title = "Calendar";
$my_script = "calendar.js";
include __DIR__ . "/../src/top.php";
require_once __DIR__ . "/../src/dbconnection.php";

$format = "Y-m-d";
$today = date($format, strtotime("now"));
?>

<div class="calendar">
    <div class="calendar-header">
        <input type="date" id="date-picker" name="selected_date" value="<?=$today?>" min="2000-01-01">
    </div>
    <ol class="grid-header">
    </ol>
    <ol class="grid">
    </ol>
</div>

<?php
include __DIR__ . "/../src/bottom.php";
?>