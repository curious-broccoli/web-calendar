
<?php
require_once __DIR__ . "/../src/dbconnection.php";

//change this to be all events from past two month from selected date till all events in one year
//change approval_state depending on what user sent the request
$sql = "SELECT eventid, name, description, datetime_start, datetime_end, location,
    created_by, approved_by, event_series
    FROM event
    WHERE approval_state = 0
    ORDER BY datetime_start;";
$stmt = $dbh->prepare($sql);
$stmt->execute();
header("Content-Type: application/json");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>