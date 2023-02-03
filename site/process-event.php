<?php
require_once __DIR__ . "/../src/dbconnection.php";
require_once __DIR__ . "/../src/Role.php";
require_once __DIR__ . "/../src/EventClass.php";
session_start();
// TODO
// reject or approve the event if it was still in waiting state
// (to make sure two moderators' decision doesn't clash)
// if the event was edited and approved, edit the event in DB
// for that make a Event::fromDB method, a method to compare both events for equality and then edit if different?
// return 200 or error

block_unauthorized([Role::Approver, Role::Moderator], $dbh);

// updates only if status = ApprovalState::Waiting
$sql = "UPDATE event
SET approval_state = :approval_state
WHERE
    eventid = :eventid
AND
    approval_state = 0;";
$stmt = $dbh->prepare($sql);
// should I do an else check if it is really "rejected" ?
$new_state = $_POST["action"] === "approve" ? ApprovalState::Approved : ApprovalState::Rejected;
$parameters = array(":approval_state" => $new_state->value, ":eventid" => $_POST["eventid"]);
$stmt->execute($parameters);




?>