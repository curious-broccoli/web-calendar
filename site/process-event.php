<?php
require_once __DIR__ . "/../src/dbconnection.php";
require_once __DIR__ . "/../src/Role.php";
require_once __DIR__ . "/../src/EventClass.php";
session_start();

/**
 * attempt to change approval or edit the event data
 *
 * if the last_change field from the moderator's request isn't the same
 * as in the database it returns false. this also prevents two moderators from
 * writing when the other just changed something
 */
function attempt_edit(PDO $dbh) : bool {
    // if mod A and B send a POST request at the same time,
    // A may manage to write first and then B fails to write
    // because the last_change time is not the same anymore
    $sql_change_state = "UPDATE
        event
    SET
        approval_state = :approval_state,
        approved_by = :approved_by,
        last_change = strftime('%Y-%m-%dT%H:%M:%S.000Z','now')
    WHERE
        eventid = :eventid
        AND last_change = :old_last_change;";

    $stmt = $dbh->prepare($sql_change_state);
    $new_state = $_POST["action"] === "approve"
        ? ApprovalState::Approved
        : ApprovalState::Rejected;
    $parameters = array(":approval_state" => $new_state->value,
                        ":approved_by" => $_SESSION["userid"],
                        ":eventid" => $_POST["eventid"],
                        ":old_last_change" => $_POST["last_change"]);
    echo $_POST["last_change"] ;

    $stmt->execute($parameters);
    // $stmt->rowCount() doesn't work with SQLite according
    // to the PDO docs but works for me
    return $stmt->rowCount() > 0 ? true : false;
}

$allowed_roles = [Role::Approver, Role::Moderator];
block_unauthorized($allowed_roles, $dbh);
// TODO
// if the event was edited and approved, edit the event in DB
// for that make a Event::fromDB method, a method to compare both events for equality and then edit if different?
// return 200 or error

if (!attempt_edit($dbh)) {
    http_response_code(409);
}


?>