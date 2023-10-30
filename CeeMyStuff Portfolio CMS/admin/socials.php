<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Social Media';
include '_components/admin-header.inc.php';

if (isset($_GET['task'])) {
    $task = $_GET['task'];
} else {
    $task = false;
}
if (isset($_GET['id'])) {
    $smID = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
} else {
    $smID = 0;
}
?>


<main>

<? switch ($task) {
    case "create":
    case "edit":
        if ($task==='create') {
            $create = true;
            $edit = false;
        } else {
            $create = false;
            $edit = true;
            $social = getSocials($smID);
        }
        include '_components/socials-add-edit.inc.php';
    break;
    case "list":
    default:
        $socials = getSocials();
        $rss = fetchSettings(array('has_rss'));
        include '_components/socials-list.inc.php';
    break;
}
?>

</main>

<?php
include '_components/admin-footer.php';