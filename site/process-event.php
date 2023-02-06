<?php
require_once __DIR__ . "/../src/dbconnection.php";
require_once __DIR__ . "/../src/Role.php";
require_once __DIR__ . "/../src/EventClass.php";
session_start();

$allowed_roles = [Role::Approver, Role::Moderator];
block_unauthorized($allowed_roles, $dbh);
// TODO
// if the event was edited and approved, edit the event in DB
// for that make a Event::fromDB method, a method to compare both events for equality and then edit if different?
// return 200 or error



$sql_change_state = "UPDATE
    event
SET
    approval_state = :approval_state,
    approved_by = :approved_by
WHERE
    eventid = :eventid;";
$stmt = $dbh->prepare($sql_change_state);
$new_state = $_POST["action"] === "approve" ? ApprovalState::Approved : ApprovalState::Rejected;
$parameters = array(":approval_state" => $new_state->value, ":eventid" => $_POST["eventid"], ":approved_by" => $_SESSION["userid"]);
$stmt->execute($parameters);

?>