<?php
$title = 'Create new event';
include __DIR__ . "/../src/top.php";
require_once __DIR__ . "/../src/flash_message.php";
require_once __DIR__ . "/../src/dbconnection.php";
session_start();

$format = "Y-m-d H:00";
$datetime_next_hour = date($format,strtotime("now + 1 hour "));
$datetime_next_next_hour = date($format,strtotime($datetime_next_hour . "+ 1 hour "));
$next_split = explode(" ", $datetime_next_hour);
$next_next_split = explode(" ", $datetime_next_next_hour);
?>
<h2>New event</h2>
<form action="create_event.php" method="post">
    <input type="text" name="name" placeholder="Event name" required autofocus /><br />
    <input type="text" name="location" placeholder="Location" required /><br />
    Start<br />
    <input type="date" name="date_start" required value="<?=$next_split[0]?>"/>
    <input type="time" name="time_start" required value="<?=$next_split[1]?>"/><br />
    End<br />
    <input type="date" name="date_end" required value="<?=$next_next_split[0]?>"/>
    <input type="time" name="time_end" required value="<?=$next_next_split[1]?>"/><br />
    <textarea name="description" rows="5" cols="31" placeholder="Description"></textarea><br />
    <select name="series">
        <option value="">Event series</option>
        <?php
        $series = $dbh->query("SELECT seriesid, name FROM event_series;");
        foreach ($series as $row) {?>
            <option value="<?=$row["seriesid"]?>"><?=$row["name"]?></option>
        <?php
        }
        ?>
    </select><br />
    Put security question here later.<br />
    <input type="submit" name="submit" value="Submit!" />
</form>
<?php
display_flash_message("create_event_error_message");

include __DIR__ . "/../src/bottom.php";
?>
