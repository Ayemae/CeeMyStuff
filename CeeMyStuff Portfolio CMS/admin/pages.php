<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Admin Panel: Pages';
include '_components/admin-header.inc.php';
$isPage=true;
$isItem=$isSect=false;
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

// refractor later:
// $page;
// if (!empty($_POST)) {
//     $page = array(['']=>,)
// }
?>

<main>

<?php switch ($task) :
    case 'view' :
        include '_components/page-view.inc.php';
        break;
    case 'create' :
    case 'edit' :
        $page = getPage($pageID);
        if ($task=='create') {
            $create = true;
            $edit = false;
        } else {
            $create = false;
            $edit = true;
            $sectList = getPageSects($pageID);
        }
        if ($edit && ($page['Header_Img_Path'] ?? null)>'') {
            $imgExists = true;
        } else {
            $imgExists =false;
        }
        $formatList = getFormatList('page');
        include '_components/page-create-edit.inc.php';
        break;
    case 'list' :
        // do not break here, we want 'list' to inherit the default
    default : 
        $pgList = getPageList(); 
        include '_components/page-list.inc.php';
        break;
    endswitch;?>
</main>

<?php
include '_components/admin-footer.php';