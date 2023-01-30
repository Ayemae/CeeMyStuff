<?php 
$admin_panel = true;
$loginArea = true;
include '../components/info-head.php';
$page_title = 'Admin Panel';
include '_components/admin-header.inc.php';

if (isset($_GET['key']) && $_GET['key']>'') {
    $key = filter_var($_GET['key'], FILTER_SANITIZE_STRING);
} else {
    $key = null;
}
?>

<main>

<?php 
if (!is_null($key)) : 

    $validation = validateEmail($key);
    if (!$validation || is_string($validation)) : ?>
         <h2>Email validation failed.</h2><p><?=$validation?></p>;
   <? else : ?>
        <h2>Email verified. Login below!</h2>
    <?php 
    include('_components/login.inc.php');
    endif;?>

   <?php else : 
        if (!$loggedIn) :
            include('_components/login.inc.php');?>
        <? else : 
        $sectList = getSectList();
        $pageList = getPageList();
            include('_components/shortcuts.inc.php');?>
        <? endif;

endif; ?>

</main>

<?php
include '_components/admin-footer.php';