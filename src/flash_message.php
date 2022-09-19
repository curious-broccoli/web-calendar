<?php

function format_flash_message(string $message) {
    return "<br>" . $message . "<br>";
}

function display_flash_message(string $name) {
    if (!isset($_SESSION[$name])) {
        return;
    }

    $flash_message = $_SESSION[$name];
    unset($_SESSION[$name]);
    echo format_flash_message($flash_message);
}


?>