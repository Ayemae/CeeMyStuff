<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Items';
include '_components/admin-header.inc.php';
$isItem=true;
$isPage=$isSect=false;
if (isset($_GET['sectid']) && $_GET['sectid']!=='null' && $_GET['sectid'] !== '') {
    $sectID = filter_var($_GET['sectid'], FILTER_SANITIZE_NUMBER_INT);
    $sectInfo = getSectInfo($sectID);
} else {
    $sectID=null;
    $sectInfo = false;
}
if (isset($_GET['pageid'])) {
    $pageID = cleanInt($_GET['pageid']);
} else {
    $pageID=null;
}
if (isset($_GET['task'])) {
    $task = htmlspecialchars($_GET['task']);
} else {
    $task = false;
}
if (isset($_GET['view'])) {
    $view = htmlspecialchars($_GET['view']);
} else {
    $view=false;
}
if (isset($_GET['id'])) {
    $itemID = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
} else {
    $itemID = null;
}

?>

<main>
    <? switch ($task) {        
        case 'create':
        case 'edit':
            $sectList = getSectList();
            $formatList = getFormatList();
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
            switch ($sectInfo['Thumb_Size_Axis']) {
                case 3:
                    $thumbAxis='longest axis';
                    break;
                case 2:
                    $thumbAxis='shortest axis';
                    break;
                case 1:
                    $thumbAxis='height';
                    break;
                case 0:
                default:
                    $thumbAxis='width';
                    break;
            }
            include_once '_components/item-create-edit.inc.php';
            break;
        case 'view-selection' :
            if (isset($_GET['bulk-edit'])) {
                $bulkTask = strtolower(htmlspecialchars($_GET['bulk-edit']));
            } else {
                $bulkTask=false;
            }
            if (isset($_SESSION['items_selected'])) {
                $items = getSelectedItems($_SESSION['items_selected']);
                $itemPrevs = fetchSettings('preview_item_content', 'Type, Value')['Value'];   
            } else {
                $items=false;
            }
            switch ($bulkTask) {
                case 'add-tags':
                    $taskTitle='Add Tags to Items';
                    break;
                case 'clear-tags':
                    $taskTitle='Clear Tags From Items';
                    break;
                case 'toggle-hide':
                    $taskTitle='Toggle Item Hidden State';
                    break;
                case 'delete':
                    $taskTitle='Delete All Selected Items';
                case 'move':
                default:
                    $taskTitle='Move Items to Section';
                    $sectList = getSectList();
                    break;
            }
            include_once '_components/item-selection-list.inc.php';
            break;
        case 'list':
        default:
            if (isset($_SESSION['items_selected'])) {
                unset($_SESSION['items_selected']);
            }
            $itemPrevs = fetchSettings('preview_item_content', 'Type, Value')['Value'];               
            $customOrder = false ;
            if (validID($sectID)) {
                $sect = getSectInfo($sectID);
                $fullIndex=false;
            } else {
                $sect= array('ID'=>null,'Order_By'=>'Title', 'Order_Dir'=>0);
                $fullIndex=true;
            }
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
            if (validID($pageID)) {
                $sectID=getPageSects($pageID,'page',true);
            }
            $items = getItems($sectID,0,null,null,$orderBy,$orderDir);
            include_once '_components/item-list.inc.php';
            break;
    }?>


</main>

<?php
include '_components/admin-footer.php';