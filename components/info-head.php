<?php 
include_once(dirname(__FILE__).'/../library/validateAdmin.php');
include_once(dirname(__FILE__).'/../library/functions.php');
if (!isset($set)) {$set = serializeSettings();}
if (!$loggedIn && $admin_panel) {
    error_log('Permissions invalid; kicking client out of admin area.');
    kickOut();
    exit();
}
$page_title=null;
?>

