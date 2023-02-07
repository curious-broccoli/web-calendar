<?php
$title = "Moderator";
$my_scripts = [
    ["name" => "admin.js", "isModule" => true]
];
include __DIR__ . "/../src/top.php";
require_once __DIR__ . "/../src/dbconnection.php";
require_once __DIR__ . "/../src/Role.php";
require_once __DIR__ . "/../src/EventFormClass.php";
session_start();

block_unauthorized([Role::Approver, Role::Moderator], $dbh);
$classes = "event-form moderator-event-form";
$form = new EventForm($dbh, "edit", "create-event.php", true, $classes);
?>

<div class="moderator-wrapper">
    <div id="unprocessed-container" class="unprocessed-events">
    </div>
    <div id="admin-event-form">
        <?=$form->getHtml()?>
    </div>
</div>

<?php
include __DIR__ . "/../src/bottom.php";
?>