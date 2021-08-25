<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($admin_panel) : ?>
        <link  rel="stylesheet" href="admin.css">
    <?php endif; ?>
    <?php if ($page_title) :?>
        <title><?php echo $page_title;?></title>
    <?php endif; ?>
</head>
<body>
    <?php if ($page_title) :?>
        <h2><?php echo $page_title;?></h2>
    <?php endif; ?>

    <?php if ($loggedIn) :?>
        <form method="post">
            <button name="logout">Logout</button>
        </form>
    <?php  endif; ?>
<?php if (isset($msg)) {
    echo $msg;
}?>
    