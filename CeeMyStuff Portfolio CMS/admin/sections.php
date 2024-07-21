<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Admin Panel: Sections';
include '_components/admin-header.inc.php';
$isSect=true;
$isPage=$isItem=false;
if (isset($_GET['task'])) {
    $task = $_GET['task'];
} else {
    $task = false;
}
if (isset($_GET['sectid'])) {
    $sectID = cleanInt($_GET['sectid']);
} else {
    $sectID = 0;
}
if (isset($_GET['pageid'])) {
    $pageID = cleanInt($_GET['pageid']);
} else {
    $pageID = 0;
}
// if ($sectID == 0 && $pageID == 0 && $task != false) {
//     $task = 'list';
// }
?>

<main>

<?php switch ($task) :
    case 'create' :
    case 'edit' :
        $sect = getSectInfo($sectID);
        if ($task == 'edit') {
            $create = false;
            $edit = true;
            if (isset($sect['Is_Reference']) && $sect['Is_Reference']>0) {
                $isRef=true;
                $refSectIDs = explode(',', $sect['Ref_Sect_IDs']);
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
                    $refSectIDs = array(intval(filter_var($refSectID, FILTER_SANITIZE_NUMBER_INT)));
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
            if ($refSectIDs) {
                $refSects = getRefSectsInfo($refSectIDs);
            }
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
        $pgList = getPageList($sect['Page_ID'] ?? false);
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

