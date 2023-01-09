<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Admin Panel: Sections';
include '_components/admin-header.inc.php';
if (isset($_GET['task'])) {
    $task = $_GET['task'];
} else {
    $task = false;
}
if (isset($_GET['sectid'])) {
    $sectID = filter_var($_GET['sectid'], FILTER_SANITIZE_NUMBER_INT);
} else {
    $sectID = 0;
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
        $customOrder = false;
        $sect = getSectInfo($sectID);
        if (isset($_GET['order'])) {
            $orderBy = $_GET['order'];
        } elseif ($sect['Order_By']) {
            $orderBy = $sect['Order_By'];
        } else {
            $orderBy = false;
        }if (isset($_GET['orderdir'])) {
            $orderDir = $_GET['orderdir'];
        } elseif ($sect['Order_Dir']) {
            $orderDir = $sect['Order_Dir'];
        } else {
            $orderDir = false;
        }
        $items = getSectItems($sectID,0,$orderBy,$orderDir);
        include '_components/section-view.inc.php';
        break;
    case 'create' :
        $sectFormats = getFormatList('section');
        $itemFormats = getFormatList();
        include '_components/section-create.inc.php';
        break;
    case 'edit' :
        $sectFormats = getFormatList('section');
        $itemFormats = getFormatList();
        $sect = getSectInfo($sectID);
        $pgList = getPageList($sect['Page_ID']);
        include '_components/section-edit.inc.php';
        break;
    case 'list' :
        // do not break here, we want 'list' to inherit default
    default : 
        $sectList = getSectList(); 
        include '_components/section-list.inc.php';
        break;
    endswitch;?>
</main>

<?php
include '_components/admin-footer.php';
