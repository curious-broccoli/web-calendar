<?php
require_once __DIR__ . "/../src/dbconnection.php";
require_once __DIR__ . "/../src/Role.php";
require_once __DIR__ . "/../src/EventClass.php";
require_once __DIR__ . "/../src/error.php";
session_start();
header('Content-Type: application/json');

function get_edit_parameters(Event $event): array {
    $parameters = array(
        ":name" => $event->getName(),
        ":location" => $event->getLocation(),
        ":start" => $event->getStart(),
        ":end" => $event->getEnd(),
        ":description" => $event->getDescription(),
        ":series" => $event->getSeriesId(),
        ":eventid" => $_POST["eventid"],
        ":old_last_change" => $_POST["last_change"]
    );

    if ($_POST["action"] === "edit-approve") {
        $parameters[":approval_state"] = ApprovalState::Approved->value;
        $parameters[":approved_by"] = $_SESSION["userid"];
    }
    return $parameters;
}

function get_edit_sql() : string {
    $sql_set = [
        "name = :name",
        "location = :location",
        "datetime_start = :start",
        "datetime_end = :end",
        "description = :description",
        "event_series = :series",
        "last_change = strftime('%Y-%m-%dT%H:%M:%S.000Z','now')"
    ];

    if ($_POST["action"] === "edit-approve") {
        array_push(
            $sql_set,
            "approval_state = :approval_state",
            "approved_by = :approved_by"
        );
    }

    $sql = "UPDATE event SET " . implode(", ", $sql_set) .
        " WHERE
            eventid = :eventid
            AND last_change = :old_last_change;";
    return $sql;
}

function attempt_edit(PDO $dbh, Event $edited_event) : bool {
    $sql = get_edit_sql();
    $params = get_edit_parameters($edited_event);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($params);

    return $stmt->rowCount();
}

function handle_edit_request(PDO $dbh) : void {
    try {
        $edited_event = new Event($dbh, $_POST["last_change"]);
    } catch (Exception $e) {
        quit([
            "action" => $_POST["action"],
            "eventid" => $_POST["eventid"],
            "error" => $e->getMessage()
        ]);
    } catch (Error $e) {
        quit([
            "action" => $_POST["action"],
            "eventid" => $_POST["eventid"],
            "error" => "Event data caused an unexpected error."
        ]);
    }

    $success = attempt_edit($dbh, $edited_event);
    if ($success) {
        quit([
            "action" => $_POST["action"],
            "eventid" => $_POST["eventid"]
        ]);
    } else {
        quit([
            "action" => $_POST["action"],
            "eventid" => $_POST["eventid"],
            "error" => "Failed to change out-of-date event's values. Please refresh the data."
        ]);
    }
}

/**
 * attempt to change approval
 *
 * if the last_change field from the moderator's request isn't the same
 * as in the database it returns false. this also prevents two moderators from
 * writing when the other just changed something
 */
function attempt_state_change(PDO $dbh) : bool {
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
    if ($_POST["action"] === "approve" || $_POST["action"] === "edit-approve") {
        $new_state = ApprovalState::Approved;
    } elseif ($_POST["action"] === "reject") {
        $new_state = ApprovalState::Rejected;
    }
    $parameters = array(
        ":approval_state" => $new_state->value,
        ":approved_by" => $_SESSION["userid"],
        ":eventid" => $_POST["eventid"],
        ":old_last_change" => $_POST["last_change"]);
    $stmt->execute($parameters);
    // $stmt->rowCount() doesn't work with SQLite according
    // to the PDO docs but works for me
    return $stmt->rowCount();
}

function handle_state_request(PDO $dbh) : void {
    // I don't need to check if last_change is valid format because either the SQL
    // or the EventClass constructor for editing will already do that
    if (attempt_state_change($dbh)) {
        quit([
            "action" => $_POST["action"],
            "eventid" => $_POST["eventid"]
        ]);
    } else {
        quit([
            "action" => $_POST["action"],
            "eventid" => $_POST["eventid"],
            "error" => "Failed to change out-of-date event's state. Please refresh the data."
        ]);
    }
}

function check_missing_params() : void {
    // parameters from edit form will later be checked by trying to create a new event
    $required_params = ["action", "eventid", "last_change"];
    foreach ($required_params as $param) {
        if (!isset($_POST[$param])) {
            quit([
                "action" => $_POST["action"],
                "error" => "Missing parameter " . $param . " ."
            ]);
        }
    }
}

function process_request(PDO $dbh) : void {
    check_missing_params();

    if ($_POST["action"] === "approve" || $_POST["action"] === "reject") {
        handle_state_request($dbh);
    } elseif ($_POST["action"] === "edit" || $_POST["action"] === "edit-approve") {
        $role = get_user_role($_SESSION["userid"], $dbh);
        if ($role === Role::Moderator) {
            handle_edit_request($dbh);
        } elseif ($_POST["action"] === "edit-approve") {
            handle_state_request($dbh);
        } else {
            quit([
                "action" => $_POST["action"],
                "error" => "Insufficient permissions for editing."
            ]);
        }
    } else {
        quit([
            "action" => $_POST["action"],
            "error" => "Invalid action parameter."
        ]);
    }
}

$allowed_roles = [Role::Approver, Role::Moderator];
block_unauthorized($allowed_roles, $dbh);

process_request($dbh);

echo json_encode([
    "action" => $_POST["action"],
    "error" => "Something went wrong. Please refresh the data.",
    "eventid" => $_POST["eventid"]]);

?>