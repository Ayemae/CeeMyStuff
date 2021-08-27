<?php 
$root = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'], 2);
include_once $root.'/components/info-head.php';
$admin_panel = true;
$page_title = 'Admin Panel: Categories';
include $root.'/components/header.php';
if (!$loggedIn && $admin_panel) {
    // kickOut();
    // exit();
}
if (isset($_GET['task'])) {
    $task = $_GET['task'];
} else {
    $task = false;
}
?>

<main>

<?php if ($task==='create') :
    include '_components/category-create.inc.php';
else : 
    include '_components/category-list.inc.php';
endif;?>
</main>

<?php
include $root.'/components/footer.php';