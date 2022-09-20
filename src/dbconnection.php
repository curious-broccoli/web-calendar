<?php

function create_table_user(PDO $dbh) {
    $dbh->query("CREATE TABLE IF NOT EXISTS user (
        userid INTEGER PRIMARY KEY,
        name TEXT UNIQUE NOT NULL,
        hash TEXT NOT NULL
        );");
}

function insert_guest_user(PDO $dbh) {
    $userid = 1;
    $name = "guest";
    // this should prevent anyone from logging in as user
    $hash = "ßßßßßßßßßßßßßßßßßßßßßßß";
    $dbh->query("INSERT INTO user (userid, name, hash) VALUES (
        $userid, '$name', '$hash');");
}

$db_path = __DIR__ . "/../calendar.sqlite";
try {
    $dbh = new PDO("sqlite:$db_path");
    // enable foreign keys support
    $dbh->exec("PRAGMA foreign_keys = ON;");
    create_table_user($dbh);
    insert_guest_user($dbh);
} catch (PDOException $e) {
    die("Error!: " . $e->getMessage() . "<br/>");
}

?>