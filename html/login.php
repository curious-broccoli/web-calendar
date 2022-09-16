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

function loginUser($dbh, $name, $password) {
    // check if name and password fit
    $stmt = $dbh->prepare("SELECT hash FROM user WHERE name = :name;");
    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->execute();
    $hash = $stmt->fetchColumn();
    if ($hash == false) {
        echo "No account with this username exists!\n";
        return false;
    }
    if (password_verify($password, $hash)) {
        echo "Successfully logged in!\n";
        return true;
    }
    else {
        echo "The password does not match the account!\n";
        return false;
    }
}

loginUser($dbh, $_POST["username"], $_POST["password"]);



?>

</body>
</html>


