<?php
require_once __DIR__ . "/../src/dbconnection.php";
require_once __DIR__ . "/../src/flash_message.php";
require_once __DIR__ . "/../src/Role.php";

function is_valid_password(string $passwordCandidate, string $name) {
    $minLength = 8;
    $maxLength = 64;
    $length = strlen($passwordCandidate);
    if ($length < $minLength || $length > $maxLength) {
        $_SESSION[FLASH_MESSAGE_NAME] = "The password must be at least 8 and at most 64 characters long!\n";
        return false;
    }
    if (stripos($passwordCandidate, $name) !== false) { // how does it work with characters like ß (does it convert to lowercase or check both upper and lower case?)
        $_SESSION[FLASH_MESSAGE_NAME] = "The password must not contain the username!\n";
        return false;
    }
    if (preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $passwordCandidate) === 0) {
        $_SESSION[FLASH_MESSAGE_NAME] = "The password must have at least 1 lowercase letter, 1 uppercase letter and 1 digit!\n";
        return false;
    }
    return true;
}

function register_user(PDO $dbh, string $name, string $passwordCandidate) {
    if ( empty($name) || empty($passwordCandidate)) {
        error_and_redirect("Please enter a username and password!");
    }

    // check if name is valid
    if (preg_match("/^(?=.{4,20}$)(?!.*[._-]{2})[a-zA-Z0-9._-]+$/", $name) === 0) {
        error_and_redirect("The username must be at least 4 and at most 20
            characters long. It can contain special characters (.-_) but not
            two in a row!\n");
    }

    // check if username is already used
    $stmt = $dbh->prepare("SELECT 1 FROM user WHERE name = :name;");
    $stmt->execute(array(":name" => $name));
    if ($stmt->fetchColumn()) {
        error_and_redirect("This username is already taken!\n");
    }

    // check if password is valid
    if (!is_valid_password($passwordCandidate, $name)) {
        header("Location: " . ERROR_REDIRECT_LOCATION);
        die();
    }

    // register
    $hash = password_hash($passwordCandidate, PASSWORD_DEFAULT);
    $stmt = $dbh->prepare("INSERT INTO user (name, hash, role)
                            VALUES (:name, :hash, :role);");
    $parameters = array(":name" => $name,
        ":hash" => $hash,
        ":role" => Role::Default->value);
    $stmt->execute($parameters);
    echo "Successfully registered!\n";
    // redirect to calendar
    // header(calendar.php)
}

define("FLASH_MESSAGE_NAME", "register_error_message");
define("ERROR_REDIRECT_LOCATION", "/index.php");
session_start();
if (isset($_SESSION["userid"])) {
    error_and_redirect("You are already logged in!");
}
register_user($dbh, $_POST["username"], $_POST["password"]);


?>
