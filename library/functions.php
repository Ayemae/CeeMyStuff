<?php

$showErrors = 1;

if ($showErrors) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(-1);
}

$root = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'], 2);
$baseURL = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
$route = $baseURL.dirname($_SERVER['PHP_SELF']);

$db = $root.'/data/database.db';

function show($input) {
    if ($input) {
        echo $input;
    } else {
        return;
    }
}
function showID($input, $rplc="_") {
    if ($input) {
        // replace spaces
        $input = str_replace(' ',$rplc,strtolower($input));
        echo $input;
    } else {
        return;
    }
}

//email headers for no-reply email
$emailHeaders = 
            "MIME-Version: 1.0".PHP_EOL.
            "Content-Transfer-Encoding: 8bit".PHP_EOL.
            "Content-type: text/html; charset=iso-8859-1".PHP_EOL.
            "X-Priority: 3".PHP_EOL.
            "X-Mailer: PHP". phpversion() .PHP_EOL;


// deals with non-html text
function stripHTML($str, $nl2br=false, $trim=true){
    if ($nl2br) {$str = nl2br($str);}
    $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    $str = htmlspecialchars($str);
    if ($trim) {$str = trim($str);}
    return $str;
}


if (isset($_POST['submit_credentials'])) {

    $msg= '';
    global $db; global $route; 
    //global $emailHeaders;
    $name = stripHTML($_POST['name']);
    if ($_POST['email'] && $_POST['password'] && $_POST['password2']) {
        $passw = $_POST['password'];
        $passw2 = $_POST['password2'];
        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $email = stripHTML(strtolower($_POST['email']));
        } else {
            $msg .= 'Please enter a valid email address.<br/>';
        }
        if ($passw == $email) {
            $msg .= "Your password can't be the same as your email.<br/>";
        } else if ($passw === $passw2) {
            $password = hash("sha256", $passw);
        } else {
            $msg .= 'Your password and password confirmation do not match.<br/>';
        }
    } else {
        $msg .= 'Please fill out the following fields.';
    }
    if ($password && $email) {
        $failed = false;
        $conn = new SQLite3($db);
        $dateQry = "UPDATE Accounts SET 
                        Name = :u_name, 
                        Email = :email, 
                        Activation_Timestamp = :time, 
                        Activation_Key = :key,
                        Password = :pw
                    WHERE Is_Admin = 1;";
        $stmt = $conn->prepare($dateQry);
        $time = time();
        $key = bin2hex(random_bytes(22));
        $keyHash = hash("sha256", $key);
        $stmt->bindValue(':u_name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':time', $time, SQLITE3_INTEGER);
        $stmt->bindValue(':key', $keyHash, SQLITE3_TEXT);
        $stmt->bindValue(':pw', $password, SQLITE3_TEXT);
        if (!$stmt->execute()) {
            $failed = true;
        }
        if ($failed === false) {
            $body = "Welcome to your new CeeMyStuff Site!<br/><br/>"; 
            $body .= "To activate your admin panel, click the following link:<br/>";
            $activateUrl = html_entity_decode($route."/account.php?key=$key");
            $body .=  '<a href="'.$activateUrl.'">'.$activateUrl.'</a>';
             if (mail($email, 'Validate your credentials', $body, $emailHeaders)) {
                $msg .= "<p>Thank you! A confirmation email has been sent. If it hasn't shown up after a few minutes, 
                check your spam folder.</p>";
                $_POST = array();
             } else {
                $msg .= "<p>The email to validate your account failed to send. Please try again.</p>";
             }
             //FOR TESTING 
             echo $emailHeaders.'<br/><br/>'.$body;
        } else {
            $msg = '<p>Credential submission failed. Please try again.</p>';
        }
    }
}

function validateEmail($key) {
    global $db;
    $ehash = null; $email = null; $unix = null; $expired = true;
    $conn = new SQLite3($db);
    $qry = "SELECT Email, Activation_Timestamp, Activation_Key FROM Accounts WHERE Is_Admin = 1 LIMIT 1;";
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $email = $row['Email'];
            $unix = $row['Activation_Timestamp'];
            $keyHash = $row['Activation_Key'];
    } 
    // if it's been less than 15 minutes between the validation and when the email was sent
    if ((intval($unix) + (15*60)) > time()) {
        $expired = false;
    } else {
        echo "This activation email has expired. Please try again.";
        return false;
    }
    if (!$expired && hash_equals(hash("sha256", $key), $keyHash)) {
        $updQry = "UPDATE Accounts SET Email_Valid = 1 WHERE Is_Admin = 1;";
        if ($conn->prepare($updQry)->execute()) {
            $msg = 'All set!';
            if(is_writable($root.'/admin/install.php')){
                //Delete the file
                unlink($root.'/admin/install.php');
            }
            return true;
        } else {
            echo 'Something went wrong. Please try again.';
            return false;
        }
    } else {
        echo 'Credentials do no match or were set incorrectly.';
        return false;
    }
}

if (isset($_POST['send_password_reset'])) {
    $msg= '';
    global $db; global $route;
    $failed = false;
    $conn = new SQLite3($db);
    $getQry = "SELECT Email, Email_Valid FROM Accounts WHERE Is_Admin = 1 LIMIT 1;";
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $email = $row['Email'];
            $valid = $row['Email_Valid'];
    } 
    if ($valid===1 && $email) {
        $updtQry = "UPDATE Accounts SET 
                    Activation_Timestamp = :time, 
                    Activation_Key = :key
                    WHERE Is_Admin = 1 LIMIT 1";
        $stmt = $conn->prepare($updtQry);
        $time = time();
        $key = bin2hex(random_bytes(22));
        $stmt->bindValue(':time', $time, SQLITE3_INTEGER);
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        if (!$stmt->execute()) {
            $failed = true;
        }
        if ($failed === false) {
            $body = "To reset your admin password, click the following link:<br/>";
            $activateUrl = html_entity_decode($route."/account.php?key=$key");
            $body .= '<a href="'.$activateUrl.'">'.$activateUrl.'</a>';
                mail($email, 'CeeMyStuff Password Reset', $body, $emailHeaders);
                $msg .= "<p>An email with the link to reset your password has been sent. If it hasn't shown up after a few minutes, 
                check your spam folder.</p>";
                //FOR TESTING 
                //echo $body.' <a href="'.$activateUrl.'">Link</a>';
        } else {
            $msg = 'Credential submission failed. Please try again.';
        }
    } else {
        $msg = 'The email for this account was never confirmed. Please <a href="'.$route.'/account.php">confirm your email here</a>.';
    }
}

function validatePWResetLink($key) {
    global $db;
    $expired = true;
    $conn = new SQLite3($db);
    $qry = "SELECT Activation_Key, Activation_Timestamp FROM Accounts WHERE Is_Admin = 1 LIMIT 1;";
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $storedKey = $row['Activation_Key'];
        $unix = $row['Activation_Timestamp'];
    }
    $keyHash = hash("sha256", $key);
    if ((intval($unix) + (15*60)) > time()) {
        $expired = false;
    } else {
        echo "This activation key has expired. Please try again.";
        return false;
    }
    if (!$expired && hash_equals($storedKey, $keyHash)) {
        return true;
    } else {
        return false;
    }
}

if (isset($_POST['reset_password'])) {
    $msg= '';
    global $db;
    if ($_POST['password'] && $_POST['password2']) {
        $passw = $_POST['password'];
        $passw2 = $_POST['password2'];
        if ($passw === $passw2) {
            $password = hash("sha256", $passw);
        } else {
            $msg .= 'Your password and password confirmation do not match.<br/>';
        }
        //TODO: stop password from being email?
    } else {
        $msg .= 'Please fill out the following fields.';
    }
    if ($password) {
        $failed = false;
        $conn = new SQLite3($db);
        $time = time();
        $dateQry = "UPDATE Accounts SET 
                                Password = :pw, 
                                Activation_Key = NULL 
                            WHERE Is_Admin = 1;";
        $stmt = $conn->prepare($dateQry);
        $stmt->bindValue(':pw', $password, SQLITE3_TEXT);
        if (!$stmt->execute()) {
            $msg = "A database error occurred. Try again.";
            return false;
        } 
            } else {
                $msg = "New password failed to record. Try again.";
                return false;
            }
        if ($failed === false) {
            $msg = 'Password has been reset!';
        } else {
            $msg = 'Credential submission failed. Please try again.';
        }
}

function serializeSettings() {
    global $db;
    $conn = new SQLite3($db);
    $settings = array();
    $qry = 'SELECT Key, Value, Type FROM Settings;';
    $stmt = $conn->prepare($qry);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        switch ($row['Type']) {
            case 'checkbox':
                switch ($row['Value']) {
                    case "checked":
                        $row['Value'] = true;
                        break;
                    default: 
                    $row['Value'] = false;
                }
            break;
            case 'number':
                if (is_numeric($row['Value'])) {
                    $row['Value'] = intval($row['Value']);
                }
                break;
            case 'select':
                $row['Value'] = strtolower($row['Value']);
                break;
        }
        $settings[$row['Key']] = $row['Value'];
    }
    return $settings;
}

if (isset($_POST['login'])) {
    global $db;
    $conn = new SQLite3($db);
    $msg = '';
    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $email = stripHTML(strtolower($_POST['email']));
    }
    $password = hash('sha256', $_POST['password']);
    $qry= "SELECT Email, Password, Curr_Sess_ID, Login_Attempts, Locked_Until FROM Accounts WHERE Is_Admin = 1 LIMIT 1;";
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $strdEmail = $row['Email'];
        $strdPw = $row['Password'];
        $strdSID = $row['Curr_Sess_ID'];
        $loginAttempts = $row['Login_Attempts'];
        $lockedUntil = $row['Locked_Until'];
    }
    if (time() > $lockedUntil && 
    ($email === $strdEmail && hash_equals($strdPw, $password))) {
        if (session_id()) {
            session_commit();
        }
        session_start();
        $sessID = session_id();
        $sessKey = hash("SHA256", $_SERVER['HTTP_USER_AGENT'].$sessID);
        session_commit();
        if ($strdSID) {
            session_id($strdSID);
            session_start();
            session_destroy();
            session_commit();
        }
        session_id($sessID);
        session_start();
        $_SESSION['Key'] = $sessKey;
        session_commit();
        
        //record new session 
        $updtQry= "UPDATE Accounts SET 
                    Curr_Sess_ID = :sessid, 
                    Curr_Sess_Key = :sesskey,
                    Login_Attempts = 0,
                    Locked_Until = 0
                WHERE Is_Admin = 1;";
        $stmt = $conn->prepare($updtQry);
        $stmt->bindValue(':sessid', $sessID, SQLITE3_TEXT);
        $stmt->bindValue(':sesskey', $sessKey, SQLITE3_TEXT);
        $stmt->execute();

        $msg .= 'Logged in!';
        return true;
    } else {
        $msg .= 'Your email or password did not match.';
        $loginAttempts = $loginAttempts+1;
        $updtQry= "UPDATE Accounts SET Login_Attempts = :la";
        if ($loginAttempts >= 2) {
            $lockedUntil = (time()+(60*8));
            $updtQry .= ", Locked_Until = :lu ";
            $msg .= '<br/>Too many failed login attempts. Try again in a few minutes.';
        } else {
            $msg .= '<br/>Login Attempts: '.$loginAttempts;
        }
        $updtQry .= " WHERE Is_Admin = 1;";
        $stmt = $conn->prepare($updtQry);
        $stmt->bindValue(':la', $loginAttempts, SQLITE3_INTEGER);
        if ($lockedUntil>0) {
            $stmt->bindValue(':lu', $lockedUntil, SQLITE3_INTEGER);
        }
        $stmt->execute();
        
        return false;
    }
}

function logout() {
    global $db;
    ob_start();
    if(session_id() <= ''){
        session_start();
    }
    $conn = new SQLite3($db);
    $qry= "UPDATE Accounts SET Curr_Sess_ID = null, Curr_Sess_Key = null WHERE Is_Admin = 1;";
    $conn->prepare($qry)->execute();
    $_SESSION = array();
    session_unset();
    session_destroy();
    session_write_close();
    //setcookie(session_name(),'',0,'/');
}


function kickOut() {
    header('Location: '.$baseURL);
    // if header fails, do it with Javascript instead:
        echo '<script>window.location.replace("'.$baseURL.'")</script>';
    exit();
}

if (isset($_POST['logout'])) {
    logout();
    kickOut();
};


function validateAdmin($id, $key) {
    if (!isset($key) || !$key) {
        session_unset();
        session_destroy();
        return false;
    } else {
     global $db;
     $conn = NEW SQLite3($db);
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


function fetchSettings($arr=false) {
    global $db;
    $conn = new SQLite3($db);
    $settings = array();
    $qry = 'SELECT Field, Key, Value, Type, Options FROM Settings';
    if ($arr) {
        $qry .= ' WHERE ';
        $first = true;
        foreach ($arr AS $i) {
            $add = ' Field = ? ';
            if (!$first) {
                $add .= ' OR '.$add;
            }
            $qry .= $add;
            $first = false;
        } unset($i);
    }
    $qry .= ' ORDER BY Index_Order;';
    $stmt = $conn->prepare($qry);
    if ($arr) {
        $index = 1;
        foreach ($arr AS &$i) {
            $stmt->bindValue($index, $i, SQLITE3_TEXT);
            $index++;
        } unset($i);
    }
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Options'] = explode(', ', $row['Options']);
        $settings[] = $row;
    }
    return $settings;
}



if (isset($_POST['save_settings'])) {
    global $db;
    $conn = new SQLite3($db);
    $_POST = array_map('stripHTML', $_POST);
    foreach ($_POST AS $key=>$val) {
        $qry = "UPDATE Settings SET Value = :val WHERE Key = :key;";
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(':val',$val,SQLITE3_TEXT);
        $stmt->bindValue(':key',$key,SQLITE3_TEXT);
        $stmt->execute();
    }
    $msg= "Saved!";
}

if (isset($_POST['create_item'])) {
    global $db;
    $conn = new SQLite3($db);
    $set = serializeSettings();
    $_POST = array_map('stripHTML', $_POST);
    require_once 'imgUpload';
    $dir = $root.'/assets/uploads/items/';
    if (!$set['has_max_img_dimns']) {$set['max_img_dimns'] = false;}
    if (!$set['has_max_storage']) {$set['max_storage'] = false;}
    $imgPath = uploadImage ($dir, $_FILES['image_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, $_POST['Title'], $set['max_storage']);
}
