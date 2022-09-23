<?php

function error_and_redirect($message) {
    // uses define() constants
    $_SESSION[FLASH_MESSAGE_NAME] = $message;
    header("Location: " . ERROR_REDIRECT_LOCATION);
    die();
}

function format_flash_message(string $message) {
    return sprintf('<span class="error ">%s</div>',
        $message
    );
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