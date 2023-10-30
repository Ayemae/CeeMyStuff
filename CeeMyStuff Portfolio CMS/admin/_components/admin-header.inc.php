<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link  rel="stylesheet" href="<?=$baseURL?>/admin/admin.css">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-regular-straight/css/uicons-regular-straight.css'>
    <?php if ($page_title) :?>
        <title><?show($page_title);?></title>
    <?php endif; ?>
    <script type="text/javascript">
        const subdir = "<?=$set['dir']?>";
    </script>
</head>
<body>
<header class="admin-header">
    <?php if (isset($admin_panel) && $admin_panel) : ?>
        <div class="space-btwn">
            <h2>CeeMyStuff Admin Panel<?=($loggedIn ? "<span class='admin-username'> : ".$user['Name']."</span>" : null)?></h2>
            <?php if ($loggedIn) :?>
            <form method="post">
                <button class="logout-btn" name="logout"><i class="fi fi-rs-power"></i> Logout</button>
            </form>
            <?php  endif; ?>
        </div>
        <?php if ($_SESSION && $_SESSION['Key']) :?>
        <?php include $root.'/admin/_components/admin-menu.inc.php';
        endif;
    endif; ?>
</header>

<?php 
$fdbkMsg = '';
if ($_SESSION['Msg'] ?? false) {
    $fdbkMsg .= $_SESSION['Msg'];
    unset($_SESSION['Msg']);
}
if (isset($msg) && $msg>'') {
    $fdbkMsg .= $msg;
    unset($msg);
}
if ($fdbkMsg) {
    echo $fdbkMsg;
}
?>
    