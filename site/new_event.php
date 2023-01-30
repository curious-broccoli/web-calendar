<?php
$title = "Create new event";
$my_script = "form_timezone.js";
include __DIR__ . "/../src/top.php";
require_once __DIR__ . "/../src/flash_message.php";
require_once __DIR__ . "/../src/dbconnection.php";
session_start();
?>
<h2>New event</h2>
<form action="create_event.php" method="post">
    <input type="text" name="name" placeholder="Event name" required autofocus /><br />
    <input type="text" name="location" placeholder="Location" required /><br />
    Start<br />
    <input type="date" id="date_start" name="date_start" required />
    <input type="time" id="time_start" name="time_start" required /><br />
    End<br />
    <input type="date" id="date_end" name="date_end" required />
    <input type="time" id="time_end" name="time_end" required /><br />
    <textarea name="description" rows="5" cols="31" placeholder="Description"></textarea><br />
    <select name="series">
        <option value="">Event series</option>
        <?php
        $series = $dbh->query("SELECT seriesid, name FROM event_series;");
        foreach ($series as $row) { ?>
            <option value="<?= $row["seriesid"] ?>"><?= $row["name"] ?></option>
        <?php
        }
        ?>
    </select><br />
    Put security question or captcha here later.<br />
    <input type="submit" name="submit" value="Submit!" />
</form>
<?php
display_flash_message("create_event_error_message");

include __DIR__ . "/../src/bottom.php";
?>