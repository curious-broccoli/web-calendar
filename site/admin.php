<?php
$title = "Moderator";
$my_scripts = [
    ["name" => "admin.js", "isModule" => true]
];
$pass_role = true;
include __DIR__ . "/../src/top.php";
require_once __DIR__ . "/../src/dbconnection.php";
require_once __DIR__ . "/../src/Role.php";
require_once __DIR__ . "/../src/EventFormClass.php";
session_start();

block_unauthorized([Role::Approver, Role::Moderator], $dbh);
$classes = "event-form moderator-event-form";
$form = new EventForm($dbh, "edit", "process-event.php", true, $classes);
?>

<div class="moderator-wrapper">
    <div id="unprocessed-container" class="unprocessed-events">
        <span id="state-error" class="hidden error"></span>
    </div>
    <div id="admin-form-wrapper" class="">
        <span id="form-error" class="hidden error"></span>
        <?= $form->getHtml() ?>
    </div>
</div>

<?php
include __DIR__ . "/../src/bottom.php";
?>