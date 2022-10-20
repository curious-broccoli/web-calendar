<?php
$title = 'Calendar login';
include __DIR__ . "/../src/top.php";
require_once __DIR__ . "/../src/flash_message.php";
session_start();
if (isset($_SESSION["userid"])) {?>
    <span>logged in with userid <?=$_SESSION["userid"]?></span>
<?php
}
?>

<h2>Login</h2>
<form action="login.php" method="post">
    Username:  <input type="text" name="username" required autofocus /><br />
    Password: <input type="password" name="password" required /><br />
    <input type="submit" name="submit" value="Login!" />
</form>

<?php
display_flash_message("login_error_message");
?>

<br>
<h2>Register</h2>
<form action="register.php" method="post">
    Username:  <input type="text" name="username" required /><br />
    Password: <input type="password" name="password" required /><br />
    <input type="submit" name="submit" value="Register!" />
</form>

<?php
display_flash_message("register_error_message");

include __DIR__ . "/../src/bottom.php";
?>
