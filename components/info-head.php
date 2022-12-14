<?php 
if (!isset($admin_panel)) {$admin_panel = false;}
include_once(dirname(__FILE__).'/../library/validateAdmin.php');
if (!isset($loggedIn)) {$loggedIn = false;}
include_once(dirname(__FILE__).'/../library/functions.php');
if (!isset($set)) {$set = serializeSettings();}
if (!$loggedIn && $admin_panel && !$loginArea) {
    error_log('Permissions invalid; kicking client out of admin area.');
    kickOut();
    exit();
}
$page_title=null;
?>