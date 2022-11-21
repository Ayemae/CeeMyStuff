<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($page_title) :?>
        <title><?show($page_title);?></title>
    <?php endif; ?>
</head>
<body>

<header>
    <h1><?=$set['site_name']?></h1>
<? include 'components/site-menu.php';?>
</header>

<?php 
if (isset($_SESSION['Msg'])) {
    echo $_SESSION['Msg'];
    unset($_SESSION['Msg']);
}
if (isset($msg)) {
    echo $msg;
}?>
    