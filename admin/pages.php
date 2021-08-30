<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Admin Panel: Pages';
include '_components/admin-header.inc.php';
if (isset($_GET['task'])) {
    $task = $_GET['task'];
} else {
    $task = false;
}
if (isset($_GET['pageid'])) {
    $pageID = $_GET['pageid'];
} else {
    $pageID = 0;
}
?>

<main>

<?php switch ($task) :
    case 'view' :
        $items = getCatItems($catID);
        include '_components/page-view.inc.php';
        break;
    case 'create' :
        include '_components/page-create.inc.php';
        break;
    case 'edit' :
        $cat = getCatInfo($catID);
        include '_components/page-edit.inc.php';
        break;
    case 'list' :
        // do not break here, we want 'list' to inherit default
    default : 
        //$pgList = getPageList(); 
        include '_components/page-list.inc.php';
        break;
    endswitch;?>
</main>

<?php
include '../components/footer.php';