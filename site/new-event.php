<?php
$title = "Create new event";
$my_scripts = [
    ["name" => "form_timezone.js", "isModule" => true]
];
include __DIR__ . "/../src/top.php";
require_once __DIR__ . "/../src/error.php";
require_once __DIR__ . "/../src/dbconnection.php";
require_once __DIR__ . "/../src/EventFormClass.php";
session_start();
?>
<h2>New event</h2>
<?php
$classes = "event-form create-event-form";
//echo get_event_form($dbh, "create-event.php", true, $classes);
$form = new EventForm($dbh, "create", "create-event.php", true, $classes);
echo $form->getHtml();

display_flash_message("create_event_error_message");

include __DIR__ . "/../src/bottom.php";
?>