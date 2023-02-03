<?php
$title = "Moderator";
$my_scripts = [
    ["name" => "admin.js", "isModule" => true]
];
include __DIR__ . "/../src/top.php";
require_once __DIR__ . "/../src/dbconnection.php";
require_once __DIR__ . "/../src/Role.php";
session_start();

block_unauthorized([Role::Approver, Role::Moderator], $dbh);
?>

<div id="unprocessed-container" class="unprocessed-events"></div>
<!-- FORM FOR EDITING HERE? -->

<?php
include __DIR__ . "/../src/bottom.php";
?>