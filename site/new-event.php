<?php
$title = "Create new event";
$my_scripts = [
    ["name" => "form_timezone.js", "isModule" => false]
];
include __DIR__ . "/../src/top.php";
require_once __DIR__ . "/../src/flash_message.php";
require_once __DIR__ . "/../src/dbconnection.php";
require_once __DIR__ . "/../src/event_form.php";
session_start();
?>
<h2>New event</h2>
<?php
echo get_event_form($dbh, "create-event.php", true);

display_flash_message("create_event_error_message");

include __DIR__ . "/../src/bottom.php";
?>