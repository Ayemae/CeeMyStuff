<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Items';
include '_components/admin-header.inc.php';
$isItem=true;
$isPage=$isSect=false;
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
    <? switch ($task) {
        case 'create':
        case 'edit':
            if ($task==='create') {
                $create = true;
                $edit = false;
                $item = null;
                $imgExists =false;
            } else {
                $create = false;
                $edit = true;
                $item = getItem($itemID); 
                $sectID=$item['Sect_ID'];
                $_SESSION['Item_Page_ID'] = $item['Page_ID'];
                if (($item['Img_Path'] ?? null)>'') {
                    $imgExists = true;
                } else {
                    $imgExists =false;
                }
            }
            $sectInfo = getSectInfo($sectID);
            include_once '_components/item-create-edit.inc.php';
            break;
        case 'list': 
        default:
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
            $items = getSectItems($sectID,0,null,null,$orderBy,$orderDir);
            include_once '_components/item-list.inc.php';
            break;
    }?>


</main>

<?php
include '_components/admin-footer.php';