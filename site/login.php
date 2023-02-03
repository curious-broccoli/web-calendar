<?php
require_once __DIR__ . "/../src/dbconnection.php";
require_once __DIR__ . "/../src/flash_message.php";

function login_user(PDO $dbh, string $name, string $password) {
    if ( empty($name) || empty($password)) {
        error_and_redirect("Please enter a username and password!");
    }

    // check if name and password fit
    $stmt = $dbh->prepare("SELECT userid, hash FROM user WHERE name = :name;");
    $stmt->execute(array(":name" => $name));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result == false) {
        error_and_redirect("No account with this username exists!\n");
    }
    if (password_verify($password, $result["hash"])) {
        // redirect to calendar later on
        // or maybe admin view if permitted
        header("Location: /index.php");
        $_SESSION["userid"] = $result["userid"];
        echo "Successfully logged in!\n";
    }
    else {
        error_and_redirect("The password does not match the account!\n");
    }
}

define("FLASH_MESSAGE_NAME", "login_error_message");
define("ERROR_REDIRECT_LOCATION", "/index.php");
session_start();
if (isset($_SESSION["userid"])) {
    error_and_redirect("You are already logged in!");
}
login_user($dbh, $_POST["username"], $_POST["password"]);

?>