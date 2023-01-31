<?php
require_once __DIR__ . "/../src/Role.php";

function create_table_user(PDO $dbh) {
    $dbh->query("CREATE TABLE IF NOT EXISTS user (
        userid INTEGER PRIMARY KEY,
        name TEXT UNIQUE NOT NULL,
        hash TEXT NOT NULL,
        role INTEGER,
        FOREIGN KEY (role) REFERENCES role(roleid)
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
        approved_by INTEGER,
        datetime_creation TEXT NOT NULL,
        event_series INTEGER,
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

function create_table_role(PDO $dbh) {
    $dbh->query("CREATE TABLE IF NOT EXISTS role (
        roleid INTEGER PRIMARY KEY,
        name TEXT UNIQUE NOT NULL
        );");
    $roles = [
        Role::Default->value => "default",
        Role::Approver->value => "approver",
        Role::Moderator->value => "moderator"
    ];
    foreach ($roles as $key => $value) {
        $dbh->query("INSERT OR IGNORE INTO role (roleid, name) VALUES (
        $key, '$value'
        );");
    }
}

function insert_guest_user(PDO $dbh) {
    $userid = 1;
    $name = "guest";
    // this should prevent anyone from logging in as user 'guest'
    $hash = "ßßßßßßßßßßßßßßßßßßßßßßß";
    $dbh->query("INSERT OR IGNORE INTO user (userid, name, hash, role) VALUES (
            $userid, '$name', '$hash', null);");
}

$db_path = __DIR__ . "/../database/calendar.sqlite";
try {
    // TODO: check if (!$dbh) ?
    $dbh = new PDO("sqlite:$db_path");
    // enable foreign keys support
    $dbh->exec("PRAGMA foreign_keys = ON;");
    //all this stuff needs to be removed later and only be run once for a new database
    create_table_user($dbh);
    insert_guest_user($dbh);
    create_table_event($dbh);
    create_table_series($dbh);
    create_table_role($dbh);
} catch (PDOException $e) {
    // printing detailed error to user would be bad, change this later?
    die("Error!: " . $e->getMessage() . "<br/>");
}

?>