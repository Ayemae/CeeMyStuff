<?php 
include_once(dirname(__FILE__).'/../library/functions.php');
$page_title=null;$loggedIn=false;
$set = serializeSettings();
if(session_id() <= ''){
    @session_start();
    if (@validateAdmin(session_id(), $_SESSION['Key'])) {
        $loggedIn=true;
    }
}
if (!$loggedIn && $admin_panel) {
    kickOut();
    exit();
}
?>

