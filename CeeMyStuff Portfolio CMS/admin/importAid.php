<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Test';
include '_components/admin-header.inc.php';
$sectID = 2;
    $importDir = $_SERVER['DOCUMENT_ROOT'].'/varethane/gallery';
    $targetDir = $root.'/assets/uploads/items/section-'.$sectID.'/';
    $fileArr = array_slice(scandir($importDir),2);
    $results = bulkCreateItems($fileArr, $sectID, $importDir, $targetDir);
    echoIfTesting($results);
?>

<main>


</main>

<?php
include '_components/admin-footer.php';