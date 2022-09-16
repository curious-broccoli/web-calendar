<?php
require_once __DIR__ . "/../src/dbconnection.php";

function isValidPassword($passwordCandidate, $name) {
    // maybe only return after all checks, even if one was already false
    $minLength = 8;
    $maxLength = 64;
    $length = strlen($passwordCandidate);
    if ($length < $minLength || $length > $maxLength) {
        echo "The password must be at least 8 and at most 64 characters long!\n";
        return false;
    }
    if (stripos($passwordCandidate, $name) !== false) { // how does it work with characters like ß (does it convert to lowercase or check both upper and lower case?)
        echo "The password must not contain the username!\n";
        return false;
    }
    if (preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $passwordCandidate) === 0) {
        echo "The password must have at least 1 lowercase letter, 1 uppercase letter and 1 digit!\n";
        return false;
    }
    return true;
}

function registerUser($dbh, $name, $passwordCandidate) {
    // check if name is valid
    if (preg_match("/^(?=.{4,20}$)(?!.*[._-]{2})[a-zA-Z0-9._-]+$/", $name) === 0) {
        echo "The username must be at least 4 and at most 20 characters long.
            It can contain special characters (.-_) but not two in a row!\n";
        return false;
    }
    // check if username is already used
    $stmt = $dbh->prepare("SELECT 1 FROM user WHERE name = :name;");
    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt->fetchColumn()) {
        echo "This username is already taken!\n";
        return false;
    }
    // check if password is valid
    if (!isValidPassword($passwordCandidate, $name)) {
        return false;
    }
    // register
    $hash = password_hash($passwordCandidate, PASSWORD_DEFAULT);
    $stmt = $dbh->prepare("INSERT INTO user (name, hash)
                            VALUES (:name, :hash);");
    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->bindParam(":hash", $hash, PDO::PARAM_STR);
    $stmt->execute();
    echo "Successfully registered!\n";
    return true;
}

registerUser($dbh, $_POST["username"], $_POST["password"]);



?>