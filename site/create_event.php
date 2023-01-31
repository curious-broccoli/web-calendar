<?php
require_once __DIR__ . "/../src/dbconnection.php";
require_once __DIR__ . "/../src/flash_message.php";
require_once __DIR__ . "/../src/EventClass.php";

define("FLASH_MESSAGE_NAME", "create_event_error_message");
define("ERROR_REDIRECT_LOCATION", "/new_event.php");

session_start();
try {
    $event = new Event($dbh);
} catch (Exception $e) {    
    error_and_redirect($e->getMessage());
}
catch (Error $e) {
    // TODO: don't give details to user
    //error_and_redirect("Fatal error!");
    error_and_redirect("Fatal error: " . $e->getMessage());
}
insertEventInDb($dbh, $event);

?>