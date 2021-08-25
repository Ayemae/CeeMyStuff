<?php 
$root = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'], 2);
include_once $root.'/components/info-head.php';
$admin_panel = true;
$page_title = 'Admin Panel';
include $root.'/components/header.php';
?>

<main>

<?php if (!$loggedIn) {
include 'login.php';
}
var_dump($set);
?>

</main>

<?php
include $root.'/components/footer.php';