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
if (isset($_GET['id'])) {
    $pageID = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
} else {
    $pageID = 0;
}
?>

<main>

<?php switch ($task) :
    case 'view' :
        include '_components/page-view.inc.php';
        break;
    case 'create' :
        $formatList = getFormatList('page');
        include '_components/page-create.inc.php';
        break;
    case 'edit' :
        $page = getPage($pageID);
        $formatList = getFormatList('page');
        include '_components/page-edit.inc.php';
        break;
    case 'list' :
        // do not break here, we want default to inherit 'list'
    default : 
        $pgList = getPageList(); 
        include '_components/page-list.inc.php';
        break;
    endswitch;?>
</main>

<?php
include '_components/admin-footer.php';