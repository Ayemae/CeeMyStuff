<?php
$loggedIn = false;

function validateAdmin($id, $key) {
    if (!isset($key) || !$key) {
        session_unset();
        session_destroy();
        return false;
    } else {
     $conn = New SQLite3(dirname(__FILE__).'/../data/database.db');
     $qry = 'SELECT `Curr_Sess_Key`,`Permissions`,`Username` FROM `Accounts` WHERE `Curr_Sess_ID`=:id LIMIT 1;';
     $stmt = $conn->prepare($qry);
     $stmt->bindValue(':id',$id,SQLITE3_TEXT);
     $result = $stmt->execute();
     if ($result) {
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $accPerms = $row['Permissions'];
            $accName = $row['Username'];
            $storedKey = $row['Curr_Sess_Key'];
        }
     } else {
        $accPerms=($_SESSION['Permissions'] ?? 0);
        $accName=($_SESSION['Username'] ?? 0);
        $storedKey = false;
     }
     $stmt->close();
     $currKey = hash("SHA256", $_SERVER['HTTP_USER_AGENT'].$id);
     if ((!hash_equals((string)$key, (string)$currKey) || !hash_equals((string)$storedKey, (string)$currKey))) {
        session_unset();
        session_destroy();
        return false;
        $msg = "Invalid session.";
     }
     if ($accPerms!==$_SESSION['Permissions']) {
        $_SESSION['Permissions']=$accPerms;
     }
     if ($accName!==$_SESSION['Username']) {
        $_SESSION['Username']=$accName;
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