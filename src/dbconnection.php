<?php

function create_table_user($dbh) {
    $dbh->query("CREATE TABLE IF NOT EXISTS user (
        userid INTEGER PRIMARY KEY,
        name TEXT UNIQUE NOT NULL,
        hash TEXT NOT NULL
        );");
}

$db_path = __DIR__ . "/../calendar.sqlite";
try {
    $dbh = new PDO("sqlite:$db_path");
    // enable foreign keys support
    $dbh->exec("PRAGMA foreign_keys = ON;");
    create_table_user($dbh);
} catch (PDOException $e) {
    die("Error!: " . $e->getMessage() . "<br/>");
}

?>