<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Admin Panel: Categories';
include '_components/admin-header.inc.php';
if (isset($_GET['task'])) {
    $task = $_GET['task'];
} else {
    $task = false;
}
if (isset($_GET['catid'])) {
    $catID = filter_var($_GET['catid'], FILTER_SANITIZE_NUMBER_INT);
} else {
    $catID = 0;
}
if (isset($_GET['pageid'])) {
    $pageID = filter_var($_GET['pageid'], FILTER_SANITIZE_NUMBER_INT);
} else {
    $pageID = 0;
}
?>

<main>

<?php switch ($task) :
    case 'view' :
        $items = getCatItems($catID);
        $cat = getCatInfo($catID);
        include '_components/category-view.inc.php';
        break;
    case 'create' :
        include '_components/category-create.inc.php';
        break;
    case 'edit' :
        if ($loggedIn) { echo 'logged in';}
        if ($admin_panel) { echo 'logged in';}
        $cat = getCatInfo($catID);
        $pgList = getPageList();
        include '_components/category-edit.inc.php';
        break;
    case 'list' :
        // do not break here, we want 'list' to inherit default
    default : 
        $catList = getCatList(); 
        include '_components/category-list.inc.php';
        break;
    endswitch;?>
</main>

<?php
include '../components/footer.php';