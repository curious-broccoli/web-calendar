<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="styles/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
    <script defer src="scripts/purify.min.js"></script>
    <?php
    foreach ($my_scripts as $key => $script) { ?>
        <script <?= $script["isModule"] == true ? 'type="module"' : "defer" ?> src=<?= "scripts/" . $script["name"] ?>></script>
    <?php
    } ?>
    <noscript>Please enable JavaScript!</noscript>
    <title><?= $title ?></title>
</head>

<body>
    <?php
    if (!isset($hide_nav)) { ?>
        <nav>
            <ul class="nav-bar">
                <li><a href="/">Login</a></li>
                <li><a href="/new-event.php">Create event</a></li>
                <li><a href="/calendar.php">Calendar</a></li>
                <li><a href="/logout.php">Logout</a></li>
                <li><a href="/admin.php">Moderator</a></li>
            </ul>
        </nav>
    <?php } ?>