<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Admin Panel';
include '_components/admin-header.inc.php';
if (isset($_GET['sectid'])) {
    $sectID = filter_var($_GET['sectid'], FILTER_SANITIZE_NUMBER_INT);
    $sectInfo = getSectInfo($sectID);
} else {
    $sectID=0;
    $sectInfo = false;
}
if (isset($_GET['task'])) {
    $task = htmlspecialchars($_GET['task']);
} else {
    $task = false;
}
if (isset($_GET['type']) && (
    $_GET['type']=='image' || $_GET['type']=='embed'
)) {
    $type = ucfirst(htmlspecialchars($_GET['type']));
} else {
    $type = "Text";
}
if (isset($_GET['id'])) {
    $itemID = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
} else {
    $itemID = false;
}

$sectList = getSectList();
$formatList = getFormatList();
?>

<main>
    <?php if (isset($sectID) && $task === 'create') :
        $create = true;
        $edit = false;
        $item = null;
        $sectInfo = getSectInfo($sectID);
        include_once '_components/item-create-edit.inc.php';
    elseif (isset($sectID) && $task === 'edit') :
        $create = false;
        $edit = true;
        $item = getItem($itemID); 
        $sectInfo = getSectInfo($item['Sect_ID']);
        if (($item['Img_Path'] ?? null)>'') {
            $imgExists = true;
        } else {
            $imgExists =false;
        }
        $_SESSION['Item_Page_ID'] = $item['Page_ID'];
        include_once '_components/item-create-edit.inc.php';
    endif;?>


</main>

<?php
include '_components/admin-footer.php';