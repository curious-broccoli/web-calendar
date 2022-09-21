<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar/login</title>
</head>
<body>
    
<?php

session_start();
unset($_SESSION["user"]);
header("Location: /index.php");

?>

</body>
</html>


