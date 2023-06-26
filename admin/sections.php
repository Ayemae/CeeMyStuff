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
// if ($sectID == 0 && $pageID == 0 && $task != false) {
//     $task = 'list';
// }
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
    case 'edit' :
        $sect = getSectInfo($sectID);
        if ($task == 'edit') {
            $create = false;
            $edit = true;
            if ($sect['Is_Reference']>0) {
                $isRef=true;
                $refSectIDs = explode(',', $sect['Ref_Sect_IDs']);
                $refSects = getRefSectsInfo($refSectIDs);
            } else {
                $refSects = array();
                $isRef=false;
            }
        } else {
            $create = true;
            $edit = false;
            if (isset($_GET['refsect'])) {
                $isRef = true;
                $refSectID = $_GET['refsect'];
                if (is_numeric($refSectID) && $refSectID>0) {
                    $refSectIDs = array(intval(filter_var($refSects, FILTER_SANITIZE_NUMBER_INT)));
                } else {
                    $refSectIDs = array();
                    $isRef=true;
                }
            } else {
                $refSectIDs = array();
                $isRef = false;
            }
        } 
        if ($isRef) {
            $sectList = getSectList(false, true);
            if (!isset($refSects) || !$refSects || count($refSects)<1) {
                for ($i=0;$i<count($sectList);$i++) {
                    if (in_array($sectList[$i]['ID'], $refSectIDs)) {
                        $refSects[] = array($sectList[$i]);
                    }
                }
            }
        }
        if ($edit && ($sect['Header_Img_Path'] ?? null)>'') {
            $imgExists = true;
        } else {
            $imgExists =false;
        }
        $pgList = getPageList($sect['Page_ID']);
        $sectFormats = getFormatList('section');
        $itemFormats = getFormatList();
        $viewItemFormats = getFormatList('view-item-page');
        $lightboxFormats = getFormatList('lightbox');
        include '_components/section-create-edit.inc.php';
        break;
    case 'list' :
        // do not break here, we want 'list' to inherit default
    default : 
        $sectList = getSectList(); 
        include '_components/section-list.inc.php';
        break;
    endswitch;
    ?>
</main>

<?php
include '_components/admin-footer.php';

