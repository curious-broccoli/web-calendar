<?php
require_once __DIR__ . "/../src/dbconnection.php";
$stmt = $dbh->query('SELECT * FROM user;');
var_dump($stmt->fetch());
//echo $_POST['username'];
//echo $_REQUEST['username'];
?>
