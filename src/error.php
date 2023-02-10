<?php

function quit(array $response) : void {
    // content type should be set to json in the calling script
    echo json_encode($response);
    die();
}

function error_and_redirect(string $message) : void {
    // uses define() constants
    $_SESSION[FLASH_MESSAGE_NAME] = $message;
    header("Location: " . ERROR_REDIRECT_LOCATION);
    die();
}

function format_flash_message(string $message) : string {
    return sprintf('<span class="error ">%s</div>',
        $message
    );
}

function display_flash_message(string $name) : void {
    if (!isset($_SESSION[$name])) {
        return;
    }

    $flash_message = $_SESSION[$name];
    unset($_SESSION[$name]);
    echo format_flash_message($flash_message);
}


?>