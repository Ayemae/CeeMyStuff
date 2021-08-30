<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link  rel="stylesheet" href="admin.css">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-regular-straight/css/uicons-regular-straight.css'>
    <?php if ($page_title) :?>
        <title><?show($page_title);?></title>
    <?php endif; ?>
</head>
<body>
<header>
    <?php if ($admin_panel) : ?>
        <div class="space-btwn">
            <h2>Admin Panel</h2>
            <?php if ($loggedIn) :?>
            <form method="post">
                <button class="logout-btn" name="logout"><i class="fi fi-rs-power"></i> Logout</button>
            </form>
            <?php  endif; ?>
        </div>
        <?php include '_components/menu.inc.php';
    endif; ?>
</header>

<?php 
if (!$loggedIn && $admin_panel) {
    // kickOut();
    // exit();
}
if (isset($_SESSION['Msg'])) {
    echo $_SESSION['Msg'];
    unset($_SESSION['Msg']);
}
if (isset($msg)) {
    echo $msg;
}?>
    