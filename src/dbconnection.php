<?php

function create_table_user(PDO $dbh) {
    $dbh->query("CREATE TABLE IF NOT EXISTS user (
        userid INTEGER PRIMARY KEY,
        name TEXT UNIQUE NOT NULL,
        hash TEXT NOT NULL
        );");
}

function create_table_event(PDO $dbh) {
    $dbh->query("CREATE TABLE IF NOT EXISTS event (
        eventid INTEGER PRIMARY KEY,
        name TEXT NOT NULL,
        description TEXT NOT NULL,
        datetime_start TEXT NOT NULL,
        datetime_end TEXT NOT NULL,
        location TEXT NOT NULL,
        created_by INTEGER NOT NULL,
        approval_state INTEGER NOT NULL,
        approved_by INT,
        event_series INT,
        FOREIGN KEY (created_by) REFERENCES user(userid),
        FOREIGN KEY (approved_by) REFERENCES user(userid),
        FOREIGN KEY (event_series) REFERENCES event_series(seriesid)
        );");
}

function create_table_series(PDO $dbh) {
    $dbh->query("CREATE TABLE IF NOT EXISTS event_series (
        seriesid INTEGER PRIMARY KEY,
        name TEXT UNIQUE NOT NULL
        );");
}

function insert_guest_user(PDO $dbh) {
    $userid = 1;
    $name = "guest";
    // this should prevent anyone from logging in as user
    $hash = "ßßßßßßßßßßßßßßßßßßßßßßß";
    try {
        $dbh->query("INSERT INTO user (userid, name, hash) VALUES (
            $userid, '$name', '$hash');");
    } catch (PDOException) {
        // so I don't have to test if "guest" user already exists
    }
}

$db_path = __DIR__ . "/../calendar.sqlite";
try {
    $dbh = new PDO("sqlite:$db_path");
    // enable foreign keys support
    $dbh->exec("PRAGMA foreign_keys = ON;");
    create_table_user($dbh);
    insert_guest_user($dbh);
    create_table_event($dbh);
    create_table_series($dbh);
} catch (PDOException $e) {
    // printing detailed error to user would be bad, change this later?
    die("Error!: " . $e->getMessage() . "<br/>");
}

?>