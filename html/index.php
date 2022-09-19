<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Calendar</title>
</head>
<body>
    <form action="login.php" method="post">
        Username:  <input type="text" name="username" /><br />
        Password: <input type="password" name="password" /><br />
        <input type="submit" name="submit" value="Login!" />
    </form>

    <?php
    require_once __DIR__ . "/../src/flash_message.php";
    session_start();

    display_flash_message("login_error_message");
    ?>

    <br>
    <form action="register.php" method="post">
        Username:  <input type="text" name="username" /><br />
        Password: <input type="password" name="password" /><br />
        <input type="submit" name="submit" value="Register!" />
    </form>

    <?php
    display_flash_message("register_error_message");
    ?>

</body>
</html>