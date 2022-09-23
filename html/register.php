<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar/register</title>
</head>
<body>
<?php
require_once __DIR__ . "/../src/dbconnection.php";

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
        $_SESSION[FLASH_MESSAGE_NAME] = "Please enter a username and password!";
        header("Location: " . ERROR_REDIRECT_LOCATION);
        die();
    }

    // check if name is valid
    if (preg_match("/^(?=.{4,20}$)(?!.*[._-]{2})[a-zA-Z0-9._-]+$/", $name) === 0) {
        $_SESSION[FLASH_MESSAGE_NAME] = "The username must be at least 4 and at most 20
            characters long. It can contain special characters (.-_) but not two in a row!\n";
        header("Location: " . ERROR_REDIRECT_LOCATION);
        die();
    }

    // check if username is already used
    $stmt = $dbh->prepare("SELECT 1 FROM user WHERE name = :name;");
    $stmt->execute(array(":name" => $name));
    if ($stmt->fetchColumn()) {
        $_SESSION[FLASH_MESSAGE_NAME] = "This username is already taken!\n";
        header("Location: " . ERROR_REDIRECT_LOCATION);
        die();
    }

    // check if password is valid
    if (!is_valid_password($passwordCandidate, $name)) {
        header("Location: " . ERROR_REDIRECT_LOCATION);
        die();
    }
    
    // register
    $hash = password_hash($passwordCandidate, PASSWORD_DEFAULT);
    $stmt = $dbh->prepare("INSERT INTO user (name, hash)
                            VALUES (:name, :hash);");
    $stmt->execute(array(":name" => $name, ":hash" => $hash));
    echo "Successfully registered!\n";
    // redirect to calendar
    // header(calendar.php)
}

define("FLASH_MESSAGE_NAME", "register_error_message");
define("ERROR_REDIRECT_LOCATION", "/index.php");
session_start();
if (isset($_SESSION["userid"])) {
    $_SESSION[FLASH_MESSAGE_NAME] = "You are already logged in!";
    header("Location: " . ERROR_REDIRECT_LOCATION);
    die();
}
register_user($dbh, $_POST["username"], $_POST["password"]);



?>

</body>
</html>
