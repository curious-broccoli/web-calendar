<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar/login</title>
</head>
<body>
    
<?php
require_once __DIR__ . "/../src/dbconnection.php";

function login_user(PDO $dbh, string $name, string $password) {
    if ( empty($name) || empty($password)) {
        $_SESSION[FLASH_MESSAGE_NAME] = "Please enter a username and password!";
        header("Location: " . ERROR_REDIRECT_LOCATION);
        die();        
    }

    // check if name and password fit
    $stmt = $dbh->prepare("SELECT userid, hash FROM user WHERE name = :name;");
    $stmt->execute(array(":name" => $name));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result == false) {
        $_SESSION[FLASH_MESSAGE_NAME] = "No account with this username exists!\n";
        header("Location: " . ERROR_REDIRECT_LOCATION);
        die();
    }
    if (password_verify($password, $result["hash"])) {
        // redirect to calendar later on
        // header(calendar.php);
        $_SESSION["userid"] = $result["userid"];
        echo "Successfully logged in!\n";
    }
    else {
        $_SESSION[FLASH_MESSAGE_NAME] = "The password does not match the account!\n";
        header("Location: " . ERROR_REDIRECT_LOCATION);
        die();
    }
}

define("FLASH_MESSAGE_NAME", "login_error_message");
define("ERROR_REDIRECT_LOCATION", "/index.php");
session_start();
if (isset($_SESSION["userid"])) {
    $_SESSION[FLASH_MESSAGE_NAME] = "You are already logged in!";
    header("Location: " . ERROR_REDIRECT_LOCATION);
    die();
}
login_user($dbh, $_POST["username"], $_POST["password"]);



?>

</body>
</html>


