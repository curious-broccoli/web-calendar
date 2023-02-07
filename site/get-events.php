<?php
require_once __DIR__ . "/../src/dbconnection.php";
require_once __DIR__ . "/../src/EventClass.php";
session_start();

// maybe set the hours to maximum/minimum hours of the day?
// or just cut off the hours if that works
function get_default_date(DateTimeImmutable $now, string $type) : string {
    $date_format = "Y-m-d\TH:i:s.v\Z";
    $range_start = "P2M"; // 2 months
    $range_end = "P12M"; // 12 months
    if ($type == "start_range") {
        $date = $now->sub(new DateInterval($range_start));
    }
    else {
        $date = $now->add(new DateInterval($range_end));
    }
    return $date->format($date_format);
}

// maybe make it so moderator can get events of all states?
/**
 * gets the appropriate ApprovalState for fetching events
 *
 * dependent on the user's permission
 * a request by a normal user returns ApprovalState::Approved
 * a request by a permitted users returns the ApprovalState equaling
 * the passed parameter or Approved if it fails
 */
function get_approval_state(PDO $dbh) : ApprovalState {
    $state = null;
    // if user isn't logged in
    if (!isset($_SESSION["userid"])) {
        $state = ApprovalState::Approved;
    }
    // if user is logged in
    else {
        $role = get_user_role($_SESSION["userid"], $dbh);
        // if a moderator passes an integer
        if (in_array($role, [Role::Approver, Role::Moderator]) && isset($_GET["state"]) && ctype_digit($_GET["state"])) {
            $state = ApprovalState::tryFrom($_GET["state"]) ?? ApprovalState::Approved;
        }
        else {
            $state = ApprovalState::Approved;
        }
    }
    return $state;
}

$now = new DateTimeImmutable("now");
// maybe check if passed value is valid date?
$start = $_GET["start"] ?? get_default_date($now, "start_range");
$end = $_GET["end"] ?? get_default_date($now, "end_range");
$approval_state = get_approval_state($dbh);
// if a parameter results in a query returning nothing that is fine
// e.g. when end is empty string

if ($approval_state === ApprovalState::Waiting) {
    // events that are waiting for approval need to be shown with their creator's username
    $sql = "SELECT u.name AS username, e.eventid, e.name, e.description,
    e.datetime_start, e.datetime_end, e.location,
    e.created_by, e.approved_by, e.event_series, e.last_change
    FROM event AS e
    JOIN user AS u
    ON e.created_by = u.userid
    WHERE e.approval_state = :approval_state
    AND e.datetime_start >= :start
    AND e.datetime_end <= :end
    ORDER BY e.datetime_start ASC;";
}
else {
    $sql = "SELECT eventid, name, description, datetime_start, datetime_end, location,
    created_by, approved_by, event_series, last_change
    FROM event
    WHERE approval_state = :approval_state
    AND datetime_start >= :start
    AND datetime_end <= :end
    ORDER BY datetime_start ASC;";
}
$stmt = $dbh->prepare($sql);
$parameters = array(":start" => $start, ":end" => $end, ":approval_state" => $approval_state->value);
$stmt->execute($parameters);

header("Content-Type: application/json");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>