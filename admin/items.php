<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Admin Panel';
include '_components/admin-header.inc.php';
if (isset($_GET['catid'])) {
    $catID = filter_var($_GET['catid'], FILTER_SANITIZE_NUMBER_INT);
    $catInfo = getCatInfo($catID);
} else {
    $catID="0";
    $catInfo = false;
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
?>

<main>
    <?php if (isset($catID) && $task === 'create') :
        $catList = getCatList(); 
        $formatList = getFormatList();
        include_once '_components/item-create.inc.php';
    elseif (isset($catID) && $task === 'edit') :
        $item = getItem($itemID); 
        $catInfo = getCatInfo($item['Cat_ID']);
        $formatList = getFormatList();
        include_once '_components/item-edit.inc.php';
    endif;?>


</main>

<?php
include '_components/footer.php';