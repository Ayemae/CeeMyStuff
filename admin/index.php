<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Admin Panel';
include '../components/header.php';
?>

<main>

<?php if (!$loggedIn) {
include 'login.php';
}
var_dump($set);
?>

</main>

<?php
include '../components/footer.php';