<?php
$loggedIn = false;

function validateAdmin($id, $key) {
    if (!isset($key) || !$key) {
        session_unset();
        session_destroy();
        return false;
    } else {
     $conn = New SQLite3(dirname(__FILE__).'/../data/database.db');
     $qry = 'SELECT Curr_Sess_Key FROM Accounts WHERE Is_Admin = 1 LIMIT 1;';
     $result = $conn->prepare($qry)->execute();
     while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
         $storedKey = $row['Curr_Sess_Key'];
     }
     $currKey = hash("SHA256", $_SERVER['HTTP_USER_AGENT'].$id);
     if (!hash_equals($key, $currKey) || !hash_equals($storedKey, $currKey)) {
        session_unset();
        session_destroy();
        return false;
     }
     return true;
    }
}
if(session_id() <= ''){
    @session_start();
    if (@validateAdmin(session_id(), $_SESSION['Key'])) {
        $loggedIn=true;
    }
}