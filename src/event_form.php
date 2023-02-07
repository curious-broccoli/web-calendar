<?php

function get_event_form(PDO $dbh, string $action, bool $showCaptcha) : string {
    ob_start(); ?>
    <form action="<?=$action?>" method="post">
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
    <?php
    if (!isset($_SESSION["userid"]) && $showCaptcha) { ?>
        Put security question or captcha here later.<br />
        <?php } ?>
    <input type="submit" name="submit" value="Submit!" />
    </form>
<?php
    return ob_get_clean();
}

?>