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
            <h2>CeeMyStuff Admin Panel</h2>
            <?php if ($loggedIn) :?>
            <form method="post">
                <button class="logout-btn" name="logout"><i class="fi fi-rs-power"></i> Logout</button>
            </form>
            <?php  endif; ?>
        </div>
        <?php if ($_SESSION && $_SESSION['Key']) :?>
        <?php include '_components/admin-menu.inc.php';
        endif;
    endif; ?>
</header>

<?php 
if (isset($_SESSION['Msg'])) {
    echo '<article class="msg-alert"><p>'.$_SESSION['Msg'].'</p></article>';
    unset($_SESSION['Msg']);
}
if (isset($msg)) {
    echo '<article class="msg-alert"><p>'.$msg.'</p></article>';
}?>
    