<?php 
include_once $root.'/library/functions.php';
$set = serializeSettings();
$admin_panel=false;$loggedIn=false;
if(session_id() <= ''){
    @session_start();
    if (@validateAdmin(session_id(), $_SESSION['Key'])) {
        $loggedIn=true;
    }
}
?>

