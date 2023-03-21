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
<header>
    <?php if (isset($admin_panel) && $admin_panel) : ?>
        <div class="space-btwn">
            <h2>CeeMyStuff Admin Panel</h2>
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
$alert = '';
if ($_SESSION['Msg'] ?? false) {
    $alert .= '<p>'.$_SESSION['Msg'].'</p>';
    unset($_SESSION['Msg']);
}
if (isset($msg) && $msg>'') {
    $alert .= '<p>'.$msg.'</p>';
    unset($msg);
}
if ($alert) {
    echo '<article class="msg-alert">'.$alert.'</article>';
}
?>
    