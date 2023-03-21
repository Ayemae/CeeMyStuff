<?php
if (file_exists(dirname(__FILE__).'/_testing.inc.php')) {
    include(dirname(__FILE__).'/_testing.inc.php');
}

$db = dirname(__FILE__).'/../data/database.db';

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
                $row['Value'] = $row['Value'];
                break;
        }
        $settings[$row['Key']] = $row['Value'];
    }
    if (!$settings['has_max_img_dimns']) {$settings['max_img_dimns'] = false;}
    if (!$settings['has_max_upld_storage']) {$settings['max_upld_storage'] = false;}
    return $settings;
}
// run/get settings
$set = serializeSettings();
if (!isset($set['date_format']) || !$set['date_format']) {
    $set['date_format'] = "j F, Y g:i A";
}

$root = $_SERVER['DOCUMENT_ROOT'].$set['dir'];
$baseURL = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$set['dir'];
$route = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
$themePath = $root.'/themes/'.$set['theme'];
date_default_timezone_set($set['timezone']);

function show($input=false, $altInput=false) {
    if (is_array($input)) {
        $input= implode(', ', $input);
    } if (is_array($altInput)) {
        $altInput= implode(', ', $altInput);
    }
    if ($input || $input === 0 || $input === "0") {
        echo $input;
    } elseif ($altInput || $altInput === 0 || $altInput === "0") {
        echo $altInput;
    } else {
        return null;
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

function formCmp($input, $compare=null, $type="c", $strict=false) {
    switch ($type) {
        case ('s') : // 's' = 'select'
        $echo1 = 'selected';
        $echo2 = null;
        break;
        case ('sr') : // 'sr' = 'select-reverse'
            $echo1 = null;
            $echo2 = 'selected';
        break;
        case ('cr') : // 'cr' = 'checkbox-reverse'
            $echo1 = null;
            $echo2 = 'checked';
        break;
        case ('c') : // 'c' = 'checkbox'
            // inherit default
        default :
            $echo1 = 'checked';
            $echo2 = null;
            break;
    }
    if (($compare===null)
        &&
        (($strict===true && $input) 
            ||
            ($strict===false && ($input || $input === 0 || $input === "0")))
        ) {
        echo $echo1;
    } elseif (($strict && $input === $compare) ||
    (!$strict && $input == $compare)) {
        echo $echo1;
    } else {
        echo $echo2;
    }
}

function truncateTxt($txt,$at=140,$trail="...") {
    if (strlen($txt)>$at) {
        $cutOff = ($at - strlen($trail));
        $txt = substr($txt,0,$cutOff).$trail;
    }
    return $txt;
}

function cleanFileName($name) {
    $name = str_replace(" ","-",preg_replace("/[^A-Za-z0-9. \-_]/", '', stripHTML($name)));
    return $name;
  }

function cleanUpldName($path, $dir=null) {
    $name = str_replace('.'.pathinfo($path,PATHINFO_EXTENSION),"",$path);
    if ($dir) {
        $name = str_replace($dir, "", $name);
    }
    return $name;
  }

function paginateStrt ($pageNum, $itemsPerPage) {
    $lsStrtMultiple = $pageNum - 1;
    $lsStrt = $lsStrtMultiple * $itemsPerPage;
    return $lsStrt;
}

//email headers for no-reply email
$emailHeaders = 
            "MIME-Version: 1.0".PHP_EOL.
            "Content-Transfer-Encoding: 8bit".PHP_EOL.
            "Content-type: text/html; charset=iso-8859-1".PHP_EOL.
            "X-Priority: 3".PHP_EOL.
            "X-Mailer: PHP". phpversion() .PHP_EOL;


// deals with non-html text
function stripHTML($str, $incov=false, $nl2br=false, $trim=true){
    if ($nl2br) {$str = nl2br($str);}
    if ($incov) {$str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);}
    $str = htmlspecialchars($str);
    if ($trim) {$str = trim($str);}
    return $str;
}

// suggested by: https://stackoverflow.com/questions/7409512/new-line-to-paragraph-function
function nl2p($str) {
    if (!trim($str)) {
        return;
    }
    $html='';
    $lines = explode("\n", $str);
    $i = 1;
    foreach($lines AS $p) {
        if (trim($p)) {
            if (substr($p,0,1) != '<' && substr($p,-1) != '>') {
                $p = htmlspecialchars('<p>'.$p.'</p>');
            }
        } else {
            if ($i<count($lines)) {
                $p="<br/>";
            }
        }
        if ($i>1) {
            $p = "\n".$p;
        }
        $html .= $p;
        $i++;
    }
    return $html;
}

function cleanServerPost($post){
    if (is_array($post)) {
        foreach ($post AS $key=>&$val) {
            // get type from marked html name attributes
            $t = substr($key,0,2);
            switch ($t) {
                case 'n_': //number/integer
                    $val = intval(filter_var(trim($val), FILTER_SANITIZE_NUMBER_INT));
                    break;
                case 'm_': // markup text
                    $val = nl2p($val);
                    break;
                case 'b_': // blocks/code
                    $val = stripHTML($val, true);
                    break;
                default:
                    if (is_string($val)) {
                        $val = stripHTML($val);
                    } else if (is_array($val)) {
                        $val = array_map('stripHTML', $val);
                    }
                    break;
                }
            }
        }
    return $post;
}

// Snippet from PHP Share: http://www.phpshare.org
function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    return $bytes;
}

function getThemeList() {
    $themeList = array();
    $dirContent = scandir('../themes');
    foreach($dirContent AS &$folder) {
        if (file_exists('../themes/'.$folder.'/theme.css')) {
            $themeList[] = $folder;
        }
    }
    return $themeList;
}

function selectTheme($selected = '') {
    $themesArr = getThemeList();
        $inputSelect= '<select name="theme">';
        foreach ($themesArr AS &$theme) {
            $inputSelect .='<option value="'.$theme.'"';
            $inputSelect .= ($theme == $selected ? ' selected' : '');
            $inputSelect .= '>'.$theme.'</option>';
        } 
        $inputSelect.='</select>';
    return $inputSelect;
}

function getFormatList($key='items') {
    global $set;
    $formatList = array();
    // get universal formats from 'assets'
    if (is_dir('../assets/universal-formats/'.$key)) {
        $assetsDir = scandir('../assets/universal-formats/'.$key);
        foreach($assetsDir AS &$file) {
            if (substr($file, -4)==='.php') {
                $name = str_replace('.php','',$file);
                $path = '/assets/universal-formats/'.$key.'/'.$file;
                $format = array("Name"=>$name, "Path"=>$path, "From"=>"Universal");
                $formatList[] = $format;
            }
        }
    }
    // get assign theme's formats
    if ($set['theme'] && is_dir('../themes/'.$set['theme'].'/formats/'.$key)) {
        $themeDir = scandir('../themes/'.$set['theme'].'/formats/'.$key);
        foreach($themeDir AS &$file) {
            if (substr($file, -4)==='.php') {
                $name = str_replace('.php','',$file);
                $path = '/themes/'.$set['theme'].'/formats/'.$key.'/'.$file;
                $format = array("Name"=>$name, "Path"=>$path, "From"=>'Theme: '.$set['theme']);
                $formatList[] = $format;
            }
        }
    }
    return $formatList;
}

function pwCmp($pw1,$pw2){
    if ($pw1 === $pw2) {
        return hash("sha256", $pw1);
    } else {
        return false;
    }
}

if (isset($_POST['submit_credentials'])) {
    $msg= '';
    global $db; global $route;
    global $emailHeaders;
    if (isset($name) && $name) {
        $name = stripHTML($_POST['name']);
    } else {
        $name = 'Admin';
    }
    if ($_POST['email'] && $_POST['password'] && $_POST['password2']) {
        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $email = stripHTML(strtolower($_POST['email']));
        } else {
            $msg .= 'Please enter a valid email address.<br/>';
        }
        $password = pwCmp($_POST['password'],$_POST['password2']);
        if (strcasecmp($_POST['password'], $email) === 0) {
            $msg .= "Your password can't be the same as your email.<br/>";
        } else if ($password === false) {
            $msg .= 'Your password and password confirmation do not match.<br/>';
        }
    } else {
        $msg .= 'Please fill out the following fields.';
    }
    if ($password && $email) {
        $failed = false;
        $conn = new SQLite3($db);
        $dateQry = "UPDATE Accounts SET 
                        Username = :u_name, 
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
            // $nameQry = "UPDATE Settings SET 
            //             Value = :u_name
            //             WHERE Key = 'owner_name';";
            // $stmt = $conn->prepare($nameQry);
            // $stmt->bindValue(':u_name', $name, SQLITE3_TEXT);
            // $stmt->execute();
            
            $body = "Welcome to your new CeeMyStuff Site!<br/><br/>"; 
            $body .= "To activate your admin panel, click the following link:<br/>";
            $activateUrl = html_entity_decode($route."/index.php?key=$key");
            $body .=  '<a href="'.$activateUrl.'">'.$activateUrl.'</a>';
             if (mail($email, 'Validate your credentials', $body, $emailHeaders)) {
                $msg .= "<p>Thank you! A confirmation email has been sent to ".$email.". If it hasn't shown up after a few minutes, 
                check your spam or junk folder.</p>";
                $_POST = array();
             } else {
                $msg .= "<p>The email to validate your account failed to send. Please try again.</p>";
             }
             error_log('Activation link failsafe: '.$activateUrl);
             //FOR TESTING 
             //echo $body;
        } else {
            $msg = '<p>Credential submission failed. Please try again.</p>';
        }
    }
}

function validateEmail($key) {
    global $db;
    $adminValid = null; $emailValid = null; $unix = null; $expired = true; $newEmail = null; $bind = null;
    $conn = new SQLite3($db);
    $msg = 'Something went wrong. Please try again.';
    $qry = "SELECT Email_Valid FROM Accounts WHERE Is_Admin = 1 LIMIT 1;";
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $adminValid = $row['Email_Valid'];
    }
    if (!$adminValid) {
        $qryWhere = 'Is_Admin = 1';
    } elseif (isset($_SESSION['New_Email']) && $_SESSION['New_Email']>'') {
        $qryWhere = 'Curr_Sess_ID = ?';
        $bind = session_id();
        $newEmail = $_SESSION['New_Email'];
    } else {
        //// this is for later, if the capability for more than one account is ever added to the system
        $qryWhere = 'Activation_Key = ?';
        $bind = $key;
    }
    $qry = "SELECT Email_Valid, Activation_Timestamp, Activation_Key FROM Accounts WHERE $qryWhere LIMIT 1;";
    $stmt = $conn->prepare($qry);
    if ($bind) {
        $stmt->bindValue(1, $bind, SQLITE3_TEXT);
    }
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $emailValid = $row['Email_Valid'];
            $unix = $row['Activation_Timestamp'];
            $keyHash = $row['Activation_Key'];
    }
    if (!isset($keyHash) || !$keyHash) {
        $error = "The activation key was not found.";
        return $error;
    }
    // if it's been less than 15 minutes between the validation and when the email was sent
    if ((intval($unix) + (15*60)) > time()) {
        $expired = false;
    } else {
        $error = "This email activation key has expired. Please try again.";
        return $error;
    }
    if (!$expired && hash_equals(hash("sha256", $key), $keyHash)) {
        if (!$newEmail) {
            $updQry = "UPDATE Accounts SET Email_Valid = 1 WHERE Activation_Key = ? LIMIT 1;";
            $updStmt = $conn->prepare($updQry);
            $updStmt->bindValue(1,$keyHash,SQLITE3_TEXT);
            if ($updStmt->execute()) {
                // if install.php still exists and is writable...
                if(is_writable('install.php')){
                    // ...delete the install.php file
                    if (!unlink('install.php')) {
                        error_log("The 'install.php' file failed to delete.");
                    }
                }
                return true;
            } else {
                return $error;
            }
        } else {
            $updQry = "UPDATE Accounts SET Email = ? WHERE Curr_Sess_ID = ? LIMIT 1;";
            $updStmt = $conn->prepare($updQry);
            $updStmt->bindValue(1,$newEmail, SQLITE3_TEXT);
            $updStmt->bindValue(2,$sessID, SQLITE3_TEXT);
            if (!$updStmt->execute()) {
                return $error;
            } else {
                return true;
            }
        }
    } else {
        $error = 'Credentials do no match or were set incorrectly.';
        return $error;
    }
}

if (isset($_POST['send_password_reset'])) {
    $msg= '';
    global $db; global $route;
    $failed = false;
    $conn = new SQLite3($db);
    $email = filter_var($_POST['email'],FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $msg.="Invalid email.";
        return;
    }
    $getQry = "SELECT Email, Email_Valid FROM Accounts WHERE Email=:email LIMIT 1;";
    $stmt = $conn->prepare($getQry);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $email = $row['Email'];
            $valid = $row['Email_Valid'];
    } 
    if ($valid===1 && $email) {
        $updtQry = "UPDATE Accounts SET 
                    Activation_Timestamp = :time, 
                    Activation_Key = :key
                    WHERE Email = :email LIMIT 1";
        $stmt = $conn->prepare($updtQry);
        $time = time(); 
        $key = htmlspecialchars(bin2hex(random_bytes(22)));
        $stmt->bindValue(':time', $time, SQLITE3_INTEGER);
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        if (!$stmt->execute()) {
            $failed = true;
        }
        if ($failed === false) {
            $body = "To reset your password, click the following link:<br/>";
            $activateUrl = html_entity_decode($route."/account-settings.php?task=pw-reset&key=$key");
            $body .= '<a href="'.$activateUrl.'">'.$activateUrl.'</a>';
                if (mail($email, 'CeeMyStuff Password Reset', $body, $emailHeaders)) {
                    $msg .= "<p>An email with the link to reset your password has been sent. If it hasn't shown up after a few minutes, 
                    check your spam folder.</p>";
                    return;
                }
                //FOR TESTING 
                //echo $body.' <a href="'.$activateUrl.'">Link</a>';
        } else {
            $msg = 'Credential submission failed. Please try again.';
        }
    } else {
        $msg = 'The email for this account was never confirmed. Please <a href="'.$route.'">confirm your email here</a>.';
    }
}

function validatePWResetLink($key) {
    global $db;
    $expired = true;
    $key = htmlspecialchars($key);
    $conn = new SQLite3($db);
    $qry = "SELECT Activation_Timestamp FROM Accounts WHERE Activation_Key = :actkey LIMIT 1;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':actkey', $key, SQLITE3_INTEGER);
    $result = $stmt->execute();
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
    $key = htmlspecialchars($_POST['key']);
    if ($_POST['password'] && $_POST['password2']) {
        $password = pwCmp($_POST['password'],$_POST['password2']);
        if ($password === false) {
            $msg .= 'Your password and password confirmation do not match.<br/>';
            return ;
        }
    } else {
        $msg .= 'Please fill out the following fields.';
        return ;
    }
    if ($password) {
        $failed = false;
        $conn = new SQLite3($db);
        $time = time();
        $dateQry = "UPDATE Accounts SET 
                                Password = :pw, 
                                Activation_Key = NULL 
                            WHERE Activation_Key = :actkey;";
        $stmt = $conn->prepare($dateQry);
        $stmt->bindValue(':pw', $password, SQLITE3_TEXT);
        $stmt->bindValue(':actkey', $key, SQLITE3_TEXT);
        if (!$stmt->execute()) {
            $msg = "A database error occurred. Try again.";
            return ;
        } 
    } else {
        $msg = "New password failed to record. Try again.";
        return ;
    }
    $msg = 'Password has been reset! <a href="'.$set['dir'].'/admin">Login here.</a>';
}

function getPageLinks() {
    global $db;
    $conn = new SQLite3($db);
    $links = array();
    $qry = "SELECT Link FROM Pages;";
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $links[] = $row['Link'];
    }
    return $links;
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
            $lockedUntil = (time()+(60*8)); //lock for 8 minutes
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
        
        $msg .= '<br/><a href="'.$set['dir'].'/admin/account-settings.php?task=pw-reset">Click here if you forgot your password.</a>';
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

if (isset($_POST['change_password'])) {
    $msg= '';
    global $db;
    if ($_POST['password'] && $_POST['password2']) {
        $password = pwCmp($_POST['password'],$_POST['password2']);
        if ($password === false) {
            $msg .= 'Your password and password confirmation do not match.<br/>';
            return ;
        }
    } else {
        $msg .= 'Please fill out all of the following fields.';
        return ;
    }
    $oldPW = hash("sha256", $_POST['old_password']);
    $sessID = session_id();
    $conn = new SQLite3($db);
    $dateQry = "UPDATE Accounts SET 
                            Password = :pw
                        WHERE Password = :oldpw AND Curr_Sess_ID = :sessid;";
    $stmt = $conn->prepare($dateQry);
    $stmt->bindValue(':pw', $password, SQLITE3_TEXT);
    $stmt->bindValue(':oldpw', $oldPW, SQLITE3_TEXT);
    $stmt->bindValue(':sessid', $sessID, SQLITE3_TEXT);
    if (!$stmt->execute()) {
        $msg = "A database error occurred. Try again.";
        return ;
    } else if ($conn->changes()<1) {
        $msg = "Your current password is incorrect.";
        return ;
    }
    $msg = "Your password has been changed!";
}

if (isset($_POST['change_email'])) {
    global $db; global $emailHeaders; 
    $failed = false;
    $email = filter_var($_POST['new_email'], FILTER_VALIDATE_EMAIL);
    if ($email) {
        $_SESSION['New_Email'] = $email;
    } else {
        $msg = "<p>The email input is invalid. Check to make sure that it's written correctly, and try again.</p>";
        return $msg;
    }
    $conn = new SQLite3($db);
    $time = time();
    $key = bin2hex(random_bytes(22));
    $keyHash = hash("sha256", $key);
    $sessID = session_id();
    $qry = "UPDATE Accounts SET 
                    Activation_Timestamp = :time, 
                    Activation_Key = :key
                WHERE Curr_Sess_ID = :sessid
                LIMIT 1;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':time', $time, SQLITE3_INTEGER);
    $stmt->bindValue(':key', $keyHash, SQLITE3_TEXT);
    $stmt->bindValue(':sessid', $sessID, SQLITE3_TEXT);
    if (!$stmt->execute()) {
        $failed = true;
    }
    if ($failed === false) {
        $body = "To activate your new email address, click the following link:<br/>";
        $activateUrl = html_entity_decode($route."/index.php?key=$key");
        $body .=  '<a href="'.$activateUrl.'">'.$activateUrl.'</a>';
            if (mail($email, 'Validate your new email address', $body, $emailHeadegetrs)) {
                $msg = "<p>A confirmation email has been sent to your new email address! If it hasn't shown up within a few minutes, 
                check your spam or junk folder.</p>";
            } else {
                $msg = "<p>The email to validate your new email address failed to send. Please try again.</p>";
            }
            error_log('Email activation link failsafe: '.$activateUrl);
            ////FOR TESTING 
            //echo $body;
    } else {
        $msg = '<p>Something went wrong. Please try again.</p>';
        return $msg;
    }
}

function getPage($page, $key="id"){
    global $db;
    global $admin_panel;
    global $loggedIn;
    global $set;
    $conn = New SQLite3($db);
    if ($key === 'name') {
        $page = stripHTML($page);
        $where = "p.Name=?";
    } elseif ($key === 'link') {
        $page = stripHTML($page);
        $where = "p.Link=?";
    } else {
        $key = "id";
        $page = filter_var($page, FILTER_SANITIZE_NUMBER_INT);
        $where = "p.ID=?";
    }
    $qry = "SELECT p.*, 
            m.Img_Path AS Menu_Link_Img, m.Hidden AS Menu_Hidden, 
            COUNT(i.ID) AS Total_Items
    FROM Pages AS p
    LEFT JOIN Sections AS s ON p.ID=s.Page_ID
    LEFT JOIN Automenu AS m ON p.ID=m.Ref_ID
    LEFT JOIN  (SELECT ID, Sect_ID FROM Items".
    ($admin_panel===false ? ' WHERE Hidden=0 ' : '')
        .") AS i ON s.ID=i.Sect_ID
    WHERE ".$where.($admin_panel===false ? " AND p.Hidden=0" : '')." COLLATE NOCASE LIMIT 1;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1, $page, SQLITE3_TEXT);
    $result = $stmt->execute();
    $arr = array();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if ($row['Header_Img_Path']>'') {
            $row['Header_Img_Path'] = $set['dir'].$row['Header_Img_Path'];
        }
        $arr[] = $row;
    } 
    if (!is_null($arr[0]['ID'])) {
        return $arr[0];
    } else {
        $arr[0]['ID'] = 'Error';
        return $arr[0];
    }
}


function kickOut() {
    global $baseURL;
    header('Location: '.$baseURL.'/admin/');
    // if header fails, do it with Javascript instead:
        echo '<script>window.location.replace("'.$baseURL.'/admin/")</script>';
    exit();
}

if (isset($_POST['logout'])) {
    logout();
    kickOut();
};

function getPageList($sectPageID=false) {
    global $db;
    global $admin_panel;
    global $loggedIn;
    if ($sectPageID) {
        $sectPageID = filter_var($sectPageID, FILTER_SANITIZE_NUMBER_INT);
    }
    $conn = new SQLite3($db);
    $pageList = array();
    // TODO: Fix this, 'Can_Add_Sect' is inconsistent
    $qry = 'SELECT p.ID, p.Name, p.Link, p.Multi_Sect, s.Sect_Num,
    (CASE
    WHEN (p.Multi_Sect=0 AND s.Sect_Num >= 1)
    THEN 0
    ELSE 1
    END) AS Can_Add_Sect
    FROM Pages AS p
    LEFT JOIN (
    SELECT COUNT(ID) AS Sect_Num, Page_ID FROM Sections
    GROUP BY Page_ID
    ) AS s ON s.Page_ID = p.ID';
    if (!$admin_panel || !$loggedIn) {
        $qry .= ' WHERE p.Hidden=0';
    }
    $qry .= ';';
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $pageList[]=$row;
    } 
    return $pageList;
}

function getSectList($pageID=false) {
    global $db;
    global $admin_panel;
    global $loggedIn;
    $conn = new SQLite3($db);
    $sectList = array();
    $qry = 'SELECT s.ID, s.Page_ID, s.Name, 
    p.Name AS Page_Name, p.Link, s.Hidden 
    FROM Sections AS s 
    LEFT JOIN Pages AS p ON p.ID=s.Page_ID WHERE s.ID !=0 ';
    if ($pageID) {
        $pageID = filter_var($pageID, FILTER_SANITIZE_NUMBER_INT);
        $qry .= ' AND s.Page_ID='.$pageID;
    }
    if (!$admin_panel || !$loggedIn) {
        $qry .= ' AND s.Hidden=0';
    }
    $qry .= ' ORDER BY s.Page_ID, s.Name;';
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $sectList[]=$row;
    } 
    return $sectList;
}

function getPageSects($pageID) {
    global $db;
    global $admin_panel;
    global $loggedIn;
    global $set;
    $sectList = array();
    $pageID = filter_var($pageID, FILTER_SANITIZE_NUMBER_INT);
    $conn = new SQLite3($db);
    $qry = 'SELECT s.*, COUNT(i.Sect_ID) AS Total_Items
        FROM Sections AS s
        LEFT JOIN (SELECT Sect_ID FROM Items';    
        if (!$admin_panel || !$loggedIn) {
            $qry .= ' WHERE Hidden=0';
        }
        $qry .=') AS i ON s.ID=i.Sect_ID
        WHERE s.Page_ID = :id
        GROUP BY s.ID
        ORDER BY s.Page_Index_Order;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':id', $pageID, SQLITE3_INTEGER);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Text'] = html_entity_decode($row['Text']);
        $row['Item_Click_Area'] = explode(',',$row['Item_Click_Area']);
        if ($row['Header_Img_Path']>'') {
            $row['Header_Img_Path'] = $set['dir'].$row['Header_Img_Path'];
        }
        $sectList[]= $row;
    } 
    return $sectList;
}

function getSectInfo($id) {
    global $db; global $set;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $conn = new SQLite3($db);
    $qry = 'SELECT s.*, 
    p.ID AS Page_ID, p.Name AS Page_Name, p.Link AS Page_Link, p.Hidden as Page_Hidden
    FROM Sections AS s
    LEFT JOIN Pages AS p ON s.Page_ID=p.ID
    WHERE s.ID = :id LIMIT 1;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Text'] = htmlspecialchars_decode($row['Text']);
        $row['Item_Click_Area'] = explode(',',$row['Item_Click_Area']);
        if ($row['Header_Img_Path']>'') {
            $row['Header_Img_Path'] = $set['dir'].$row['Header_Img_Path'];
        }
        return $row;
    } 
}

function getSectItems($id, $pageNum=1,
            $orderBy='Date', $orderDir=false, 
            $paginate=false, $pgAfter=15) {
    global $db;global $set;global $admin_panel;global $loggedIn;
    $items = array();
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $conn = new SQLite3($db);
    $qry = "SELECT *";
    if ($admin_panel) {
        $qry .= ", CASE
        WHEN Publish_Timestamp>=strftime('%s','now')
        THEN 1
        ELSE 0
        END AS Queued ";
    }
    $qry .= " FROM Items WHERE Sect_ID = :sectid ";
    if (!$loggedIn || !$admin_panel) {
        $qry .= " AND Hidden=0 AND Publish_Timestamp<=strftime('%s','now')";
    }
    $qry .= " ORDER BY ";
    switch ($orderBy) {
        case 'Title':
            $qry .= 'Title';
            break;
        case 'Custom':
            $qry .= 'Sect_Index_Order';
            break;
        case 'Random':
            $qry .= 'RANDOM() ';
            break;
        case 'ID':
            $qry .= 'ID';
        break;
        case 'Date':
        default:
            $qry .= 'Publish_Timestamp';
            break;
    }
    if ($orderBy != 'Random') {
        if (intval($orderDir)===1) {
            $qry .= ' DESC ';
        } else {
            $qry .= ' ASC ';
        }
    }
    if ($paginate && !$admin_panel) {
        $pgAfter = filter_var($pgAfter, FILTER_SANITIZE_NUMBER_INT);
        $strt = paginateStrt($pageNum, $pgAfter);
        $qry .= ' LIMIT '.$strt.', '.$pgAfter;
    }
    $qry .= ';';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':sectid', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Text'] = htmlspecialchars_decode($row['Text']);
        // handle date w/timezone
        $date = new DateTime('@'.$row['Publish_Timestamp']);
        $date->setTimeZone(new DateTimeZone($set['timezone']));
        $row['Publish_Timestamp'] = $date->format('Y-m-d\TH:i');
        $row['Date'] = $date->format($set['date_format']);
        ///
        $row['File_Pres'] = '';
        if ($row['File_Path']) {
            $row['File_Pres'] = substr($row['File_Path'], 1, 3);
            $row['File_Path'] = substr($row['File_Path'],5);
        }
        $items[] = $row;
    } 
    return $items;
}
function getSectItemIDs($sectid, $orderBy='Date', $orderDir=false) {
    global $db;global $loggedIn;global $admin_panel;
    $itemIDs = array();
    $sectid = filter_var($sectid, FILTER_SANITIZE_NUMBER_INT);
    $conn = new SQLite3($db);
    $qry = "SELECT ID, Title";
    if ($admin_panel) {
        $qry .= ", CASE
        WHEN Publish_Timestamp>=strftime('%s','now')
        THEN 1
        ELSE 0
        END AS Queued ";
    }
    $qry .= " FROM Items WHERE Sect_ID = :sectid ";
    if (!$loggedIn || !$admin_panel) {
        $qry .= " AND Hidden=0 AND Publish_Timestamp<=strftime('%s','now')";
    }
    $qry .= " ORDER BY ";
    switch ($orderBy) {
        case 'Title':
            $qry .= 'Title';
            break;
        case 'Custom':
            $qry .= 'Sect_Index_Order';
            break;
        case 'Random':
            $qry .= 'RANDOM() ';
            break;
        case 'ID':
            $qry .= 'ID';
        break;
        case 'Date':
        default:
            $qry .= 'Publish_Timestamp';
            break;
    }
    if ($orderBy != 'Random') {
        if (intval($orderDir)===1) {
            $qry .= ' DESC ';
        } else {
            $qry .= ' ASC ';
        }
    }
    $qry .= ';';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':sectid', $sectid, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $i=1;
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Index'] = $i;
        $itemIDs[] = $row;
        $i++;
    } 
    return $itemIDs;
}

function getItem($id) {
    global $db;
    global $set;
    global $admin_panel;
    global $loggedIn;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $conn = new SQLite3($db);
    $qry = 'SELECT i.*, 
        s.Name AS Sect_Name, s.Hidden AS Sect_Hidden, 
        p.ID AS Page_ID, p.Name AS Page_Name, p.Link AS Page_Link, p.Hidden AS Page_Hidden
    FROM Items AS i 
    LEFT JOIN Sections AS s ON i.Sect_ID=s.ID
    LEFT JOIN Pages AS p ON s.Page_ID=p.ID
    WHERE i.ID = :itemid';
    if (!$admin_panel || !$loggedIn || (isset($view) && $view)) {
        $qry .= ' AND i.Hidden=0';
    }
    $qry .= ' LIMIT 1;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':itemid', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Text'] = htmlspecialchars_decode($row['Text']);
        // handle date w/timezone
        $date = new DateTime('@'.$row['Publish_Timestamp']);
        $date->setTimeZone(new DateTimeZone($set['timezone']));
        $row['Publish_Timestamp'] = $date->format('Y-m-d\TH:i');
        $row['Date'] = $date->format($set['date_format']);
        ///
        $row['File_Pres'] = '';
        if ($row['File_Path']) {
            $row['File_Pres'] = substr($row['File_Path'], 1, 3);
            $row['File_Path'] = substr($row['File_Path'],5);
        }
        return $row;
    } 
}


// Public-Facing page functions

function showTitle($setShowTitle, $title) {
    global $set;
    if (is_array($setShowTitle)) {
        $setShowTitle = $setShowTitle['Show_Item_Titles'];
    }
    if ($setShowTitle) {
        $title = '<h3 class="item-title">'.$title.'</h3>';
    } else {
        $title = '';
    }
    return $title;
}

function showText($setShowText, $text) {
    global $set;
    if (is_array($setShowText)) {
        $setShowText = $setShowText['Show_Item_Text'];
    }
    $text = htmlspecialchars_decode($text);
    switch ($setShowText) {
        case 1:
            $text=truncateTxt($text);
        case 2:
            $text = '<div class="item-text">'.$text.'</div>';
         break;
        default:
            $text = '';
    }
    return $text;
}

function showImage($setShowImg, $img=false, $thumb=false, $alt=false, $title=false) {
    global $set;
    if (is_array($setShowImg)) {
        $setShowImg = $setShowImg['Show_Item_Images'];
    }
    if (!$alt) {
        $alt= addslashes($title).' Image';
    }
    $dataAttr= 'data-thumbnail="'.$set['dir'].$thumb.'"';
    switch ($setShowImg) {
        case 0:
            $img='';
         break;
        case 1:
            if ($thumb) {
                $img = $thumb;
                $dataAttr= 'data-full-image="'.$set['dir'].$img.'"';
            }
        break;
        default:
            /* nuthin'. */
        break;
    }
    if ($img) {
        $img = '<img class="item-image" src="'.$set['dir'].$img.'" alt="'.$alt.'" '.$dataAttr.'>';
    }
    return $img;
}

function showFile($setShowFile, $filePath, $pres, $linkTxt=false) {
    global $set; global $route;
    if ($setShowFile && $filePath) {
        if (!$linkTxt) {
            $linkTxt = 'Click here';
        }
        switch ($pres) {
            case 'lnk':
                $file = '<a class="item-file link" href="'.$set['dir'].$filePath.'">'.$linkTxt.'</a>';
            break;
            case 'dld':
                $file = '<a class="item-file dnld" href="'.$set['dir'].$filePath.'" download>'.$linkTxt.'</a>';
            break;
            case 'txt':
            default:
                $file = $route.$filePath;
            break;
        }
        return $file;
    } else {
        return null;
    }
}

function className($str) {
    $str = strtolower(preg_replace("/[^A-Za-z0-9-]/",'',str_replace(' ','-',$str)));
    if (substr($str,0,1)==='-') {
        $str='dash'.$str;
    } else if (filter_var(substr($str,0,1), FILTER_SANITIZE_NUMBER_INT)){
        $str='num'.$str;
    }
    return $str;
}

function printSectPaginator($pgAfter, $pageNum, $itemTotal, $pageLink) {
    global $set;
    $last = ceil($itemTotal/$pgAfter);
    if ($itemTotal>$pgAfter) {
        $firstURL = $set['dir'].'/'.urlencode($pageLink).'/page/1';
        $backURL = $nextURL = '';
        $isFirst = $isLast = false;
        $lastURL = $set['dir'].'/'.urlencode($pageLink).'/page/'.$last;
    if ($pageNum>1) {
        $backURL = $set['dir'].'/'.urlencode($pageLink).'/page/'.($pageNum-1);
    } else {$isFirst = true;}
    if (($pgAfter*$pageNum)<$itemTotal) {
        $nextURL = $set['dir'].'/'.urlencode($pageLink).'/page/'.($pageNum+1);
    } else {$isLast = true;}
    $pageDropdown = '<select class="page-nav-submenu" name="page_nav_submenu" onchange="window.location.replace(this.value)">';
    for ($i=1;$i<=$last;$i++) {
        $pageDropdown .= '<option value="'.$set['dir'].'/'.urlencode($pageLink).'/page/'.$i.'" '.($i == $pageNum ? "selected" : null).'>'.$i.'</option>';
    }
    $pageDropdown .= '</select>';
    ob_start();
    include 'components/paginator.php';
    $output = ob_get_clean();
    return $output;
    }
}

function printItemPaginator($itemList, $currentID, $pageLink=false, $pageName=false) {
    global $set;
    $idList = array_column($itemList, 'ID');
    $itemTotal = count($idList);
    $lastID = $idList[($itemTotal-1)];
    $iLast = ($itemTotal-1);
    $iCurr = array_search($currentID, $idList);
    if ($itemTotal>$iCurr) {
        $firstURL = $set['dir'].'/view/'.$idList[0];
        $backURL = $nextURL = '';
        $isFirst = $isLast = false;
        $lastURL = $set['dir'].'/view/'.$lastID;
    if ($iCurr>0) {
        $backURL = $set['dir'].'/view/'.$idList[($iCurr-1)];
    } else {$isFirst = true;}
    if ($iCurr<$iLast) {
        $nextURL = $set['dir'].'/view/'.$idList[($iCurr+1)];
    } else {$isLast = true;}
    $pageDropdown = '<select class="page-nav-submenu" name="page_nav_submenu" onchange="window.location.replace(this.value)">';
    if ($pageLink!=false && $pageName!=false) {
        $pageDropdown .= '<option value="'.$set['dir'].'/'.$pageLink.'">Back to '.$pageName.'</option>';
    }
    for ($i=0;$i<$itemTotal;$i++) {
        $pageDropdown .= '<option value="'.$set['dir'].'/view/'.$idList[$i].'" '.($i == $iCurr ? "selected" : null).'>'.$itemList[$i]['Title'].'</option>';
    }
    $pageDropdown .= '</select>';
    ob_start();
    include 'components/paginator.php';
    $output = ob_get_clean();
    return $output;
    }
}

function addItemLinks ($areas, $id, $input, $place, $action, $sectID=false) {
    global $set;
    if (!is_array($areas)) {
        $areas = array($areas);
    }
    switch ($action) {
        case 1 :
            // direct to view page
            $aStrt = '<a class="item-link" href="'.$set['dir'].'/view/'.$id.'">';
            $aEnd = '</a>';
            break;
        case 2 :
            // lightbox
            if ($sectID===false) {
                $lightbox="on";
            } else {
                $lightbox="section-".$sectID;
            }
            $aStrt = '<a class="item-link" data-lightbox-link="'.$lightbox.'" href="'.$set['dir'].'/view/'.$id.'" >';
            $aEnd = '</a>';
            break;
        case 3 :
            // load view page in new window
            $aStrt = '<a class="item-link" href="'.$set['dir'].'/view/'.$id.'" target="_blank">';
            $aEnd = '</a>';
            break;
        case 0 :
            // no click action
        default : 
            $aStrt = $aEnd = '';
            break;
    }
    if (in_array($place, $areas)) {
        $input = $aStrt.$input.$aEnd;
    }
    return $input;
};

function printPageItems($itemList=false, $sect=false) {
    if (!$itemList || !is_array($itemList)) {
        return '<!-- No items were found in this section. -->';
    }
    if (!$sect) {
        $sect;
        $sect['Show_Item_Titles'] = 1;
        $sect['Show_Item_Text'] = 1;
        $sect['Show_Item_Images'] = 1;
    }
    global $set; global $root; global $themePath;
    $content = $itemContent = $jsContent = '';
    if ($sect['On_Click_Action']==2) { //lightbox
        $jsContent .= '<script type="text/javascript">
            var sect'.$sect['ID'].'Items = [];';
    }
    foreach($itemList AS $item){
        $id = $item['ID'];
        $srcImgFull = $srcImgThumb = $srcFilePath = '';
        if ($item['Img_Path']) {$srcImgFull = $set['dir'].$item['Img_Path'];}
        if ($item['Img_Thumb_Path']) {$srcImgThumb = $set['dir'].$item['Img_Thumb_Path'];}
        if ($item['File_Path']) {$srcFilePath = $set['dir'].$item['File_Path'];}
        $textFull = $item['Text'];
        $alt = addslashes($item['Img_Alt_Text']);
        $title = showTitle($sect['Show_Item_Titles'], $item['Title']);
        if ($title) {
            $title = addItemLinks($sect['Item_Click_Area'], $id, $title, 'Title', $sect['On_Click_Action'], $sect['ID']);
        }
        $text = showText($sect['Show_Item_Text'], $textFull);
        if ($text) {
            $text = addItemLinks($sect['Item_Click_Area'], $id, $text, 'Text', $sect['On_Click_Action'], $sect['ID']);
        }
        $image = showImage($sect['Show_Item_Images'], $item['Img_Path'], $item['Img_Thumb_Path'], $alt, $item['Title']);
        if ($image) {
            $image = addItemLinks($sect['Item_Click_Area'], $id, $image, 'Image', $sect['On_Click_Action'], $sect['ID']);
            $imageFull = '<img src="'.$srcImgFull.'" alt="'.($item['Img_Alt_Text'] ? $item['Img_Alt_Text'] : $item['Title']).'">';
        }
        $file = showFile($sect['Show_Item_Files'], $item['File_Path'], $item['File_Pres'], ($sect['Link_Text'] ?? 'Click here'));
        if ($file) {
            $file = addItemLinks($sect['Item_Click_Area'], $id, $file, 'File', $sect['On_Click_Action'], $sect['ID']);
        }
        $date = $item['Date'];
        $embed = htmlspecialchars_decode($item['Embed_HTML']);
        $class = className($item['Title']);
        $viewLink = '';
        $viewLink = addItemLinks($sect['Item_Click_Area'], $id, '', 'Link', $sect['On_Click_Action'], $sect['ID']);

        if ($item['Format'] <= '' && isset($sect['Default_Item_Format'])) {
            $item['Format'] = $sect['Default_Item_Format'];
        }

        $formatFile = $root.$item['Format'];
        $dataAttr = ($sect['On_Click_Action']==2 ? ' data-lightbox="section-'.$sect['ID'].'" ' : ' ' );
        $itemElem = '<div id="item_'.$id.'"'.$dataAttr.'class="item '.$class.'">';
        if ($item['Format'] && file_exists($formatFile)) {
            ob_start();
            include($formatFile);
            $itemContent .= ob_get_clean();
        } else {
            if ($title) {
                $itemContent .= '<h3 class="item-title">'.$title.'</h3>';
            }
            $itemContent .= '<!-- No valid item format assigned. -->';
            if ($image) {
                $itemContent .= $image;
            }
            if ($embed) {
                $itemContent .= $embed;
            }
            if ($text) {
                $itemContent .= $text;
            }
            if ($file) {
                $itemContent .= $file;
            }
            $itemContent .= $viewLink;
        }
        $itemContent = addItemLinks($sect['Item_Click_Area'], $id, $itemContent, 'All', $sect['On_Click_Action'], $sect['ID']);
        $itemContent = $itemElem.$itemContent.'</div>';
        if ($sect['On_Click_Action']==2) { //lightbox
            ob_start();
            include($root.$sect['Lightbox_Format']);
            $lbFormat = ob_get_clean();
            $jsContent .= 'const item'.$item['ID'].' = `'.$lbFormat.'`;
                sect'.$sect['ID'].'Items.push(item'.$item['ID'].');';
        }
        $content .= $itemContent;
        $itemContent = '';
    }unset($item);
    if ($sect['On_Click_Action']==2) { //lightbox
        $jsContent .= 'lbArrs.push(sect'.$sect['ID'].'Items);</script>';
        $content .= $jsContent;
    };
    return $content;
}

function printPageSects($sectList=false, $pageNum=1, $paginate=false, $pgAfter=15, $paginator='') {
    global $set; global $root; global $themePath;
    if (!$sectList) {
        return '<!-- There are no sections associated with this page. -->';
    } else if (!is_array($sectList)) {
        $sectList = array($sectList);
    }
    $hasLightbox = false;
    $content = '';
    foreach($sectList AS &$sect) {
        $id = $sect['ID'];
        $dataAttr = '';
        if ($sect['Show_Header_Img'] && $sect['Header_Img_Path']>'') {
            $image = '<img src="'.$sect['Header_Img_Path'].'" alt="'.$sect['Name'].' Header">';
         } else {
             $image = '';
         }
         if ($sect['Show_Title']) {
             $title = '<h2 class="sect-title">'.$sect['Name'].'</h2>';
         } else {
             $title = '';
         }
         $text = $sect['Text'];
        $class = className($sect['Name']);

        $itemList = getSectItems($sect['ID'],$pageNum,
                                $sect['Order_By'],$sect['Order_Dir'],
                                $paginate,$pgAfter);
                                
        $items_content = '<!-- No items found. -->';
        if ($itemList) {
            $items_content = printPageItems($itemList, $sect);
        } else {
            //if no items
            if ($paginate) {
                $items_content = "<div class='got-nothin'><!--There's nothing here!--></div>";
            }
        }

        $formatFile = $root.$sect['Format'];
        $hasLbClose = "false";
        $hasLbArrows = "false";
        if ($sect['On_Click_Action']==2) {
            $dataAttr .= ' data-sect-action="lightbox"';
            if ($sect['Paginate_Items'] > 0) {
                $pBool= 'true';
            } else {
                $pBool= 'false';
            }
            $dataAttr .= ' data-lb-paginate="'.$pBool.'"';
            $hasLightbox = true;
        }

        if ($sect['Format'] && file_exists($formatFile)) {
            ob_start();
            include($formatFile);
            $format = ob_get_clean();
            if ($hasLightbox===true) {
                if (strpos($format, ' id="lightbox-close"') || strpos($format, " id='lightbox-close'")) {
                    $hasLbClose="true";
                }
                if ((strpos($format, ' id="lb-back"') || strpos($format, " id='lb-back'"))
                &&
                (strpos($format, ' id="lb-next"') || strpos($format, " id='lb-next'"))) {
                    $hasLbArrows="true";
                }
            }
            $dataAttr .= ' data-lbformat-has-close="'.$hasLbClose.'" data-lbformat-has-arrows="'.$hasLbArrows.'"';
            $content .= '<section id="sect_'.$sect['ID'].'" class="section '.$class.'"'.$dataAttr.'>';
            $content .= $format;

        } else {
            $content .= '<!-- No valid section format assigned. -->';
            $content .= $image.$title.$text;
            $content .= $items_content;
        }
        $content .= '</section>';
        
    }unset($sect);
    if ($hasLightbox===true) {
        $content = '<script type="text/javascript">
                    var lbArrs = [];
                    </script>'.$content.'
                    <script src="components/_js/lightbox.js"></script>

            <div id="lightbox">
                <div class="lightbox-content lightbox-fade-in">
                <article id="lightbox-arrows" class="cms-default">
                            <button type="button" id="lb-back" class="lightbox-arrow left cms-default" tabindex="0"><i class="lb-back icon">◀</i><span>back</span></button>
                            <button type="button" id="lb-next" class="lightbox-arrow right cms-default" tabindex="0"><i class="lb-next icon">▶</i><span>next</span></button>
                            </article>
                <button type="button" id="lightbox-close" class="cms-default" tabindex="0"><span>close</span><i class="close icon">&times;</i></button>
                    <div id="lightbox-inner">
                                <!-- lightbox content goes here -->
                                </div>
                            </div>
                        </div>';
    }
    return $content;
}

function addMenuLinkType($typeCode, $refID, $extURL) {
    if ($typeCode==1 && !is_null($refID)) {
        return 'Page';
    } else if ($typeCode == 2 && !is_null($refID)) {
        return 'Section Index';
    } if ($typeCode == 8 && $extURL>'') {
        return 'Custom Link';
    } else if ($typeCode == 9) {
        return 'Heading';
    } else {
        return 'Error';
    }
}

function getMenu() {
    global $db;
    global $admin_panel;
    global $loggedIn;
    $conn = new SQLite3($db);
    $menu = array();
    $qry = "SELECT m.*, 
        p.Name AS Page_Name, p.Link, p.Hidden AS Page_Hidden
        FROM Automenu AS m
            LEFT JOIN Pages AS p ON m.Type_Code=1 AND p.ID=m.Ref_ID";
    if (!$admin_panel || !$loggedIn) {
        $qry .= " WHERE m.Hidden=0";
    }
    $qry .= " ORDER BY m.Index_Order;";
    $result = $conn->prepare($qry)->execute();
    $indexOrder=1;
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Index_Order'] = $indexOrder;
        $row['Link_Type'] = addMenuLinkType($row['Type_Code'], $row['Ref_ID'], $row['Ext_Url']);
        
        $menu[] = $row;
        $indexOrder++;
    }
    return $menu;
}
function getMenuItem($id) {
    global $db;global $set;
    $id=filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $conn = new SQLite3($db);
    $qry = "SELECT m.*, 
        p.Name AS Page_Name, p.Link, p.Hidden AS Page_Hidden
        FROM Automenu AS m
            LEFT JOIN Pages AS p ON m.Type_Code=1 AND p.ID=m.Ref_ID
            WHERE m.ID=? LIMIT 1;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1,$id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    if ($result) {
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $row['Link_Type'] = addMenuLinkType($row['Type_Code'], $row['Ref_ID'], $row['Ext_Url']);
            return $row;
        }
    }
}

function serializeMenu($subIndex=false) {
    global $db;
    global $set;
    global $root;
    $conn = new SQLite3($db);
    $isIndex = $indexOpen = false;
    if (!$subIndex) {
        $menuContent='<nav id="site-menu"> <ul class="automenu">';
    } else {
        $isIndex = true;
        $subIndex= urldecode($subIndex);
        $menuContent = '<ul class="automenu-submenu-index">';
    }
    $qry = "SELECT m.*, p.Name AS Page_Name, p.Link FROM Automenu AS m
            LEFT JOIN Pages AS p ON m.Type_Code=1 AND p.ID=m.Ref_ID 
            WHERE m.Hidden=0
            ORDER BY m.Index_Order;";
    $result = $conn->prepare($qry)->execute();
    $i=0;
    $inSubmenu = false;
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        // define type
        $row['Link_Type'] = addMenuLinkType($row['Type_Code'], $row['Ref_ID'], $row['Ext_Url']);

        if ($row['Submenu']>0 && !$inSubmenu) {
            $inSubmenu = true;
            if (!$indexOpen) {
                $menuContent .= '<ul class="automenu-submenu">';
            }
        } else if ($row['Submenu']<1 && $inSubmenu) {
            $inSubmenu = false;
            $menuContent .= '</ul>';
            if ($isIndex) {
                $indexOpen = false;
            }
        }
        if ($i>0) {
            $menuContent .= '</li></a>';
        }
        if ($row['Type_Code']==1 && !is_null($row['Ref_ID'])) {
            $linkText = $row['Page_Name'];
        } else {
            $linkText = $row['Link_Text'];
        }
        if ($isIndex && strcasecmp($linkText,$subIndex)===0) {
            $indexOpen=true;
            $menuContent .= '<h3 class="submenu-index-header">'.$linkText.'</h3>';
        }
        
        if ($set['menu_format'] === 'Images' && $row['Img_Path']>'' && file_exists($root.$row['Img_Path'])) {
            $link = '<img src="'.$set['dir'].$row['Img_Path'].'" alt="'.$row['Page_Name'].'" title="'.$row['Page_Name'].'">';
        } else {
            $link = $linkText;
        }
        $target='';
        switch ($row['Type_Code']) {
            case (1):
                $href= $set['dir'].'/'.$row['Link'];
            break;
            case (2) :
                $href= $row['Ext_Url'];
                $target=' target="_blank"';
            break;
            default :
                $href= $set['dir']."/menu-submenu-index/".urlencode(strtolower($link));
            break;
        }
        $linkContent = '<a class="automenu-link '.className($row['Link_Type']).' '.className($linkText).'"'.$target.' href="'.$href.'">
                    <li class="automenu-item '.className($row['Link_Type']).' '.className($linkText).'">'.$link;
        if (!$isIndex || ($inSubmenu && $indexOpen)) {
            $i++;
            $menuContent .= $linkContent;
        }
    }
    $menuContent .= '</li></a></ul></nav>';
    if (!$isIndex) {
        $menuContent .= '</nav>';
    }
    return $menuContent;
}

function printPage($page=1,$num=1,$singleItem=false, $menuIndex=false) {
    global $db; global $set; 
    global $root; global $route; global $themePath;
    $conn = New SQLite3($db);
    // if no 'page', go to the home page
    if (($page['ID'] ?? '') === 'Error') {
        $page404 = true;
        $page['ID']=1;
    }
    if ($page===false || $page===1) {
        $pgQry = "SELECT p.*, COUNT(i.ID) AS Total_Items
        FROM Pages AS p
        LEFT JOIN Sections AS s ON p.ID=s.Page_ID
        LEFT JOIN Items AS i ON s.ID=i.Sect_ID
        WHERE p.ID =1 LIMIT 1;";
        $result = $conn->prepare($pgQry)->execute();
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $page = $row;
        }
    }
    if ($page['Paginate']==1 && $page['Multi_Sect']==0 &&
    ($page['Paginate_After']<$page['Total_Items'])) {
        $paginator = printSectPaginator($page['Paginate_After'], $num, $page['Total_Items'], $page['Link']);
    } else {
        $paginator = '';
    }
    $id = $page['ID'];
    $class = className($page['Name']);
    if ($page['Show_Title']) {
        $title = '<h1 class="page-title">'.$page['Name'].'</h1>';
    } else {
        $title = '';
    }
    if ($page['Header_Img_Path'] && $page['Show_Header_Img']) {
        $image = '<img src="'.$page['Header_Img_Path'].'" alt="'.$page['Name'].' header"/>';
    } else {
        $image='';
    }
    if ($singleItem === true) {
        $item = getItem($num);
        $sectInfo = getSectInfo($item['Sect_ID']);
        $name = $item['Title']; 
        $pageName = ($item['Page_Hidden']<1 ? $item['Page_Name'] : false); 
        $metaText = truncateTxt($item['Text'], 300);
        $page['Format'] = $sectInfo['View_Item_Format'];
        $pageURL = ($item['Page_Hidden']<1 ? $set['dir'].'/'.$item['Page_Link'] : false);
        $item['Page_Link'] = ($item['Page_Hidden']<1 ? $item['Page_Link'] : false);
        $pageLink = ($item['Page_Hidden']<1 ? '<a class="back-to-page" href="'.$pageURL.'">Back to '.$item['Page_Name'].'</a>' : false);
        $paginator = '';
        if ($sectInfo['Paginate_Items']) {
            $itemList = getSectItemIDs($item['Sect_ID'], $sectInfo['Order_By'], $sectInfo['Order_Dir']);
            $paginator = printItemPaginator($itemList, $num, $item['Page_Link'], $pageName);
        }
    } elseif ($singleItem !== true && $menuIndex) {
        $metaText = $page['Meta_Text'];
        $name = $page['Name'];
    } else {
        $sectList = getPageSects($page['ID']);
        $section_content = printPageSects($sectList,$num,$page['Paginate'],$page['Paginate_After'],$paginator);
        $name = $page['Name'];
        $metaText = $page['Meta_Text'];
    }
    $faviconRel = $moIconRel = $content = '';
    $automenu = serializeMenu();
    if (($set['favicon'] ?? '') > '' && file_exists($root.$set['favicon_img'])) {
        $faviconRel = '<link rel="icon" href="'.$route.$set['favicon_img'].'">';
    }if (($set['mobile_icon'] ?? '')>'' && file_exists($root.$set['mobile_icon_img'])) {
        $moIconRel = '<link rel="apple-touch-icon" href="'.$route.$set['mobile_icon_img'].'">';
    }

    $admin_panel = false;
    include 'components/info-head.php';
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="'.$set['dir'].'/assets/css/CMSbasics.css">
        <link rel="stylesheet" href="'.$set['dir'].'/assets/css/lightbox.css">
        <link rel="stylesheet" href="'.$set['dir'].'/themes/'.$set['theme'].'/theme.css">
        <title>'.$set['site_name'].': '.$name.'</title>
        '.$faviconRel.$moIconRel.'
        <meta name="description" content="'.$metaText.'">
        <script type="text/javascript">'.PHP_EOL.'
            const subdir = "'.$set['dir'].'";'.PHP_EOL.'
        </script>
    </head>
    <body>';

    if ($noPage ?? null) {
        echo '<h1>Page Not Found</h1>';
    }

    $header = $themePath.'/header.php';
    $footer = $themePath.'/footer.php';
    $menu = $themePath.'/menu.php';
    $menuAuto = $root.'/components/site-menu-auto.php';
    $formatFile = $root.$page['Format'];
    $headerImg = '';
    // headerImg is not to be confused with the PAGE header image, referred to simply as '$image', above
    if ($set['header_img']>'' && file_exists($root.$set['header_img'])) {
        $headerImgSrc = $route.$set['header_img'];
        $headerImg = '<a href="'.$set['dir'].'/"><img src="'.$set['dir'].$set['header_img'].'" alt="'.$set['site_name'].'"></a>';
    }

    if ($singleItem === true) {
        // name vars for single-item view page
        $id = $item['ID'];
        $title = $item['Title'];
        $text =  $item['Text'];
        $image = ($item['Img_Path'] ? '<img src="'.$set['dir'].$item['Img_Path'].'" alt="'.($item['Img_Alt_Text'] ? $item['Img_Alt_Text'] : $item['Title']).'">' : null);
        $file = showFile(1, $item['File_Path'], $item['File_Pres'], ($sectInfo['Link_Text'] ?? 'Click here'));
        $date = $item['Date'];
        $embed = htmlspecialchars_decode($item['Embed_HTML']);
        $srcImgFull = $set['dir'].$item['Img_Path'];
        $srcImgThumb = $set['dir'].$item['Img_Thumb_Path'];
        $srcFilePath = $set['dir'].$item['File_Path'];
        $class = className($item['Title']);
        ///////
        ob_start();
        include($header);
        $content .= ob_get_clean();
        $content .= '<main id="view_item_'.$item['ID'].'" class="page '.$class.'">';
        if ($page['Format'] && file_exists($formatFile)) {
            ob_start();
            include($formatFile);
            $content .= ob_get_clean();
        } else {
            $content .= '<div id="item_'.$id.'" class="item '.$class.'">';
            if ($title) {
                $content .= '<h3 class="item-title">'.$title.'</h3>';
            }
            $content .= '<!-- No valid item view format assigned. -->';
            if ($image) {
                $content .= $image;
            }
            if ($embed) {
                $content .= $embed;
            }
            if ($text) {
                $content .= $text;
            }
            if ($file) {
                $content .= $file;
            }
            $content .= '</div>';
        }
        $content .= '</main>';
        ob_start();
        include($footer);
        $content .= ob_get_clean();
    } elseif ($menuIndex>'') {
        // menu submenu index page
        if ($page['Format'] && file_exists($formatFile)) {
            $title='';
            $section_content = serializeMenu($menuIndex);
            ob_start();
            include($formatFile);
            $content .= ob_get_clean();
        } else {
            ob_start();
            include($header);
            $content .= ob_get_clean();
            $content .= '<main id="menu-submenu-index" class="page menu-index">';
            $content .= serializeMenu($menuIndex);
            $content .= '</main>';
            ob_start();
            include($footer);
            $content .= ob_get_clean();
        }
    } else {
        if ($page['Format'] && file_exists($formatFile)) {
            ob_start();
            include($formatFile);
            $content .= ob_get_clean();
        } else {
            ob_start();
            include($header);
            $content .= ob_get_clean();
            $content .= '<main id="page_'.$page['ID'].'" class="page '.$class.'">';
            if ($page['Name'] && $page['Show_Title'] > 0) {
                $content .= '<h1 class="page-title">'.$page['Name'].'</h1>';
            }
            $content .= '<!-- No valid page format assigned. -->';
            $content .= $section_content;
            $content .= '</main>';
            ob_start();
            include($footer);
            $content .= ob_get_clean();
        }
    }

    echo $content;
    echo '</body>';
    include 'components/info-foot.php';
    echo '</html>';
}



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if (isset($loggedIn) && $loggedIn===true) {


function fetchSettings($arr=false) {
    global $db;
    $conn = new SQLite3($db);
    $settings = array();
    $qry = 'SELECT * FROM Settings WHERE';
    if ($arr) {
        $first = true;
        foreach ($arr AS $i) {
            $add = ' Field = ? ';
            if (!$first) {
                $add .= ' OR '.$add;
            }
            $qry .= $add;
            $first = false;
        } unset($i);
        $qry = ' AND ';
    }
    $qry .= ' Hidden<1 ORDER BY Index_Order;';
    $stmt = $conn->prepare($qry);
    if ($arr) {
        $index = 1;
        foreach ($arr AS &$i) {
            $stmt->bindValue($index, $i, SQLITE3_TEXT);
            $index++;
        } unset($i);
    }
    $result = $stmt->execute();
    if ($result) {
        $info = array();
        $display = array();
        $advanced = array();
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $row['Options'] = explode(', ', $row['Options']);
            switch ($row['Heading']) {
                case 'Display' :
                    $display[] = $row;
                break;
                case 'Advanced' :
                    $advanced[] = $row;
                break;
                case 'Info' :
                default :
                    $info[] = $row;
                break;
            }
        }
        $settings = array('Info' => $info, 'Display' => $display, 'Advanced' => $advanced);
    }
    return $settings;
}

function selectTimezone($selected = '') {
    $timezones = timezone_identifiers_list();
        $inputSelect= '<select name="timezone">';
        foreach ($timezones AS $key=>$row) {
            // note: 'key' is the numerical value of the timezone
            $inputSelect .='<option value="'.$row.'"';
            $inputSelect .= ($row == $selected ? ' selected' : '');
            $inputSelect .= '>'.$row.'</option>';
        }  // endwhile;
        $inputSelect.='</select>';
return $inputSelect;
}

if (isset($_POST['save_settings'])) {
    global $db;global $set;
    $conn = new SQLite3($db);
    $msg= "";
    $error = false;
    $_POST = array_map('stripHTML', $_POST);
    $setCmp = $set;
    foreach ($setCmp AS $key=>&$val) {
        if ($val===true) {$val="checked";};
    }
        require_once 'upload.php';
        $dir = '/assets/uploads/settings/';
        // header
        if (isset($_FILES['header_img']) && $_FILES['header_img']['name']>'') {
            $_POST['header_img'] = uploadImage ($dir, $_FILES['header_img'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage']);
        } elseif (isset($_POST['n_rmv_header_img']) && intval($_POST['n_rmv_header_img'])>0) {
            $_POST['header_img']='';
            unset($_POST['n_rmv_header_img']);
        } else {
            unset($_POST['header_img'],$setCmp['header_img'],$_POST['n_rmv_header_img']);
        }
        // mobile icon
        if (isset($_FILES['mobile_icon_img']) && $_FILES['mobile_icon_img']['name']>'') {
            $_POST['mobile_icon_img'] = uploadImage ($dir, $_FILES['mobile_icon_img'], 180, 180, false, false, false, $set['max_upld_storage']);
        } else if (intval($_POST['n_rmv_mobile_icon_img'])>0) {
            $_POST['mobile_icon_img']='';
            unset($_POST['n_rmv_mobile_icon_img']);
        } else {
            unset($_POST['mobile_icon_img'],$setCmp['mobile_icon_img'],$_POST['n_rmv_mobile_icon_img']);
        }
        // favicon
        if (isset($_FILES['favicon_img']) && $_FILES['favicon_img']['name']>'') {
            $_POST['favicon_img'] = uploadImage ($dir, $_FILES['favicon_img'], 16, 16, false, false, 'favicon_img', $set['max_upld_storage'],false,'gif');
        } else if (intval($_POST['n_rmv_favicon_img'])>0) {
            $_POST['favicon_img']='';
            unset($_POST['n_rmv_favicon_img']);
        } else {
            unset($_POST['favicon_img'],$setCmp['favicon_img'],$_POST['n_rmv_favicon_img']);
        }
    $changes = array_diff($_POST, $setCmp);
    foreach ($changes AS $key=>&$val) {
        // sanitize inputs
        switch ($key) {
            case ('dir') :
                $replace = array($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_SCHEME'].'://');
                $val = str_ireplace($replace, '', $val);
                if (substr($val, -1)==='/') {
                    $val=substr($val,0,-1);
                }
                if ($val !== '') {
                    if (substr($val,0,1) !== '/') {
                        $val = '/'.$val;
                    }
                    if (!is_dir($_SERVER['DOCUMENT_ROOT'].$val)) {
                        $msg .= "<div class='red'>Subdirectory folder is invalid. Check to see if it is correct and try again.</div>";
                        $error = true;
                    }
                }
            break;
            case ('c_year'):
                if ($val!='' && ($val<1000 || $val>date("Y") || !is_numeric($val))) {
                    $val='';
                    $msg .= "<div class='red'>Invalid inital copyright year entry.</div>";
                }
            break;
            case ('max_img_dimns'):
            case ('max_upld_storage'):
                if ($val>50000) {
                    $val = 50000;
                    $prop = ($key=="max_upld_storage" ? 'Max Upload Storage (in megabytes)' : 'Max Image Dimensions (in pixels)');
                    $msg .= "<div class='red'>...If you're going to set your level for '".$prop."' that high, you may as well just turn the setting off. 
                    We dropped it down to '50,000'.</div>";
                }
            break;
            default:
                if (substr($key,-4)!=='_img' && strlen($val)>85) {
                    $val = substr($val, 0, 85);
                    $msg .= "<div class='red'>One of your entries was too long. We shortened it to 85 characters.</div>";
                }
            break; 
        }
        $qry = "UPDATE Settings SET Value = :val WHERE Key = :key;";
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(':val',$val,SQLITE3_TEXT);
        $stmt->bindValue(':key',$key,SQLITE3_TEXT);
        if (!$stmt->execute()) {
            $error=true;
        }
    }
    if (!$error) {
        $msg .= "Changes were saved at ".date('d F, Y h:i:s').'.';
    } else {
        $msg .= "<div class='red'>There was a problem saving your changes. Check your inputs and try again.</div>";
    }
}

function createPage($cPost) {
    global $db;
    global $set;
    $conn = new SQLite3($db);
    $_SESSION['Msg'] = '';
    $qry = 'INSERT INTO Pages (Name,Link,Meta_Text,Header_Img_Path,Show_Title,Show_Header_Img,Multi_Sect,Paginate,Paginate_After,Format,Hidden) 
    VALUES (:name,:link,:text,:img,:showtitle,:showimg,:multi,:pg,:pga,:format,:hide);';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':name',$cPost['name'], SQLITE3_TEXT);
    $stmt->bindValue(':link',$cPost['link'], SQLITE3_TEXT);
    $stmt->bindValue(':text',$cPost['meta_text'], SQLITE3_TEXT);
    $stmt->bindValue(':img',$cPost['img_path'], SQLITE3_TEXT);
    $stmt->bindValue(':showtitle',$cPost['n_show_title'], SQLITE3_INTEGER);
    $stmt->bindValue(':showimg',$cPost['n_show_header_img'], SQLITE3_INTEGER);
    $stmt->bindValue(':multi',$cPost['n_multi_sect'], SQLITE3_INTEGER);
    $stmt->bindValue(':pg',$cPost['n_paginate'], SQLITE3_INTEGER);
    $stmt->bindValue(':pga',$cPost['n_paginate_after'], SQLITE3_INTEGER);
    $stmt->bindValue(':format',$cPost['format'], SQLITE3_TEXT);
    $stmt->bindValue(':hide',$cPost['n_hidden'], SQLITE3_INTEGER);
    if (!$stmt->execute()) {
        return false;
    } else {
        $pageID = $conn->lastInsertRowID();
        $sectQry = 'INSERT INTO Sections (Name,Page_ID,Page_Index_Order,Show_Title) 
            VALUES (?,?,1,?);';
        $sectName= $cPost['name'].' Content';
        $sectStmt = $conn->prepare($sectQry);
        $sectStmt->bindValue(1,$sectName, SQLITE3_TEXT);
        $sectStmt->bindValue(2,$pageID, SQLITE3_INTEGER);
        $sectStmt->bindValue(3,$cPost['n_multi_sect'], SQLITE3_INTEGER);
        if (!$sectStmt->execute()) {
            $_SESSION['Msg'] .= "<br/>There was an error creating a new content section for this page. <a href='".$set['dir']."/admin/sections.php?task=create'>Click here to create one manually.</a>";
            return false;
        } else {
            $sectID = $conn->lastInsertRowID();
            $_SESSION['Msg'] .= "New Page created!
            <br/><a href='".$set['dir']."/admin/sections.php?task=edit&sectid=".$sectID."'>Go here to edit its content settings</a>, 
            or <a href='".$set['dir']."/admin/sections.php?task=view&sectid=".$sectID."'>here to add an item to this page's content</a>.";
            
            $menuQry = 'INSERT INTO Automenu (Type_Code, Ref_ID, Hidden'.($cPost['menu_img_path'] ? ', Img_Path' : null).') 
            VALUES (1,?,?'.($cPost['menu_img_path'] ? ', ?' : null).');';
            $menuStmt = $conn->prepare($menuQry);
            $menuStmt->bindValue(1,$pageID, SQLITE3_INTEGER);
            $stmt->bindValue(2,$cPost['n_hidden'], SQLITE3_INTEGER);
            if ($cPost['menu_img_path']) {
                $menuStmt->bindValue(2,$cPost['menu_img_path'], SQLITE3_TEXT);
            }
            if (!$menuStmt->execute()) {
                $_SESSION['Msg'] .= "<br/>There was an error creating a link for this page in the automenu.";
                return false;
            } else {
                return true;}
        }
    }
}

function editPage($cPost,$updtMHidden=false,$sectArr=false) {
    global $db;
    $conn = new SQLite3($db);
    $_SESSION['Msg'] = '';
    $qry = 'UPDATE Pages 
    SET Name=:name,Link=:link,Meta_Text=:text,Header_Img_Path=:img,Show_Title=:showtitle,Show_Header_Img=:showimg,Multi_Sect=:multi,Paginate=:pg,Paginate_After=:pga,Format=:format,Hidden=:hide
    WHERE ID=:id;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':name',$cPost['name'], SQLITE3_TEXT);
    $stmt->bindValue(':link',$cPost['link'], SQLITE3_TEXT);
    $stmt->bindValue(':text',$cPost['meta_text'], SQLITE3_TEXT);
    $stmt->bindValue(':img',$cPost['img_path'], SQLITE3_TEXT);
    $stmt->bindValue(':showtitle',$cPost['n_show_title'], SQLITE3_INTEGER);
    $stmt->bindValue(':showimg',$cPost['n_show_header_img'], SQLITE3_INTEGER);
    $stmt->bindValue(':multi',$cPost['n_multi_sect'], SQLITE3_INTEGER);
    $stmt->bindValue(':pg',$cPost['n_paginate'], SQLITE3_INTEGER);
    $stmt->bindValue(':pga',$cPost['n_paginate_after'], SQLITE3_INTEGER);
    $stmt->bindValue(':format',$cPost['format'], SQLITE3_TEXT);
    $stmt->bindValue(':hide',$cPost['n_hidden'], SQLITE3_INTEGER);
    $stmt->bindValue(':id',$cPost['n_page_id'], SQLITE3_INTEGER);
    if (!$stmt->execute()) {
        return false;
    } else {
        if ($cPost['menu_img_path']!== false || $updtMHidden !== false) {
            $qry = "UPDATE Automenu SET Hidden=:hid".($cPost['menu_img_path'] !== false ? ', Img_Path=:img' : '')." WHERE Type_Code=1 AND Ref_ID=:id";
            $stmt = $conn->prepare($qry);
            $stmt->bindValue(':hid',$cPost['n_menu_hidden'], SQLITE3_INTEGER);
            if ($cPost['menu_img_path'] != false) {
                $stmt->bindValue(':img',$cPost['menu_img_path'], SQLITE3_TEXT);
            }
            $stmt->bindValue(':id',$cPost['n_page_id'], SQLITE3_INTEGER);
            if (!$stmt->execute()) {
                return false;
            }
            // sort sections by the order of their inputs
            if ($sectArr && is_array($sectArr)) {
                usort($sectArr, function ($a, $b) {
                    return $a['n_index'] <=> $b['n_index'];
                });
                $error = false;
                if ($cPost['n_multi_sect']==1) {
                    $indexOrder=0;
                    $qry = "UPDATE Sections 
                    SET  Page_Index_Order=:inorder WHERE ID=:sectid";
                    foreach ($sectArr AS &$sect) {
                        if ($error) { // if 'error' was set to 'true' in the previous loop...
                            $_SESSION['Msg'] .= "<br/>There was an error saving your section order changes. Please try again.";
                            return;
                        }
                        $siPost = cleanServerPost($sect);
                        $indexOrder = ($indexOrder+1);
                        $stmt = $conn->prepare($qry);
                        $stmt->bindValue(':inorder',$indexOrder, SQLITE3_INTEGER);
                        $stmt->bindValue(':sectid',$siPost['n_sect_id'], SQLITE3_INTEGER);
                        if (!$stmt->execute()) {
                            $_SESSION['Msg'] .= "<br/>An error occurred while saving the Section index order.";
                        }
                    }
                } else {
                    $sectID = filter_var($sectArr[0]['n_sect_id'], FILTER_SANITIZE_NUMBER_INT);
                    $qry = "UPDATE Sections 
                    SET  Page_ID=NULL WHERE Page_ID=:pageid AND ID!=:sectid";
                    $stmt = $conn->prepare($qry);
                    $stmt->bindValue(':sectid',$sectID, SQLITE3_INTEGER);
                    $stmt->bindValue(':pageid',$cPost['n_page_id'], SQLITE3_INTEGER);
                    if (!$stmt->execute()) {
                        $_SESSION['Msg'] .= "<br/>An error occurred while divorcing extra Sections from this page.";
                    }
                }
            }
        }
    }
    return true;
}

function createSection($cPost, $clickAreas, $imgPath) {
    global $db;
    $conn = new SQLite3($db);
    $qry = 'INSERT INTO Sections (Name, Page_ID, Text, Header_Img_Path, Show_Title, Show_Header_Img, 
    Show_Item_Images, Show_Item_Titles, Show_Item_Text, Order_By, Order_Dir, Auto_Thumbs, Thumb_Size, 
    Thumb_Size_Axis, Format, Default_Item_Format, Show_Item_Files, Default_File_Link_Text, Item_Click_Area, 
    On_Click_Action, View_Item_Format, Lightbox_Format, Paginate_Items, Hidden) 
    VALUES (:name, :pageid, :text, :imgpath, :showtitle, :showimg, :showitemimgs, :showitemtitles, 
    :showitemtext, :orderby, :orderdir, :autothumbs, :thumbsize, :thumbsizea, :format, :itemformat, 
    :showfiles, :filelinktext, :clickarea, :clickaction, :viformat, :lbformat, :paginate, :hidden);';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':name',$cPost['name'], SQLITE3_TEXT);
    $stmt->bindValue(':pageid',$cPost['n_page_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':text',$cPost['text'], SQLITE3_TEXT);
    $stmt->bindValue(':imgpath',$imgPath, SQLITE3_TEXT);
    $stmt->bindValue(':showtitle',$cPost['n_show_title'], SQLITE3_INTEGER);
    $stmt->bindValue(':showimg',$cPost['n_show_header_img'], SQLITE3_INTEGER);
    $stmt->bindValue(':showitemimgs',$cPost['n_show_images'], SQLITE3_INTEGER);
    $stmt->bindValue(':showitemtitles',$cPost['n_show_titles'], SQLITE3_INTEGER);
    $stmt->bindValue(':showitemtext',$cPost['n_show_text'], SQLITE3_INTEGER);
    $stmt->bindValue(':orderby',$cPost['order_by'], SQLITE3_TEXT);
    $stmt->bindValue(':orderdir',$cPost['n_order_dir'], SQLITE3_INTEGER);
    $stmt->bindValue(':autothumbs',$cPost['n_create_thumbs'], SQLITE3_INTEGER);
    $stmt->bindValue(':thumbsize',$cPost['n_thumb_size'], SQLITE3_INTEGER);
    $stmt->bindValue(':thumbsizea',$cPost['n_thumb_axis'], SQLITE3_INTEGER);
    $stmt->bindValue(':format',$cPost['format'], SQLITE3_TEXT);
    $stmt->bindValue(':itemformat',$cPost['item_format'], SQLITE3_TEXT);
    $stmt->bindValue(':showfiles',$cPost['n_show_files'], SQLITE3_INTEGER);
    $stmt->bindValue(':filelinktext',$cPost['file_link_text'], SQLITE3_TEXT);
    $stmt->bindValue(':clickarea',$clickAreas, SQLITE3_TEXT);
    $stmt->bindValue(':clickaction',$cPost['n_onclick_action'], SQLITE3_INTEGER);
    $stmt->bindValue(':viformat',$cPost['view_item_format'], SQLITE3_TEXT);
    $stmt->bindValue(':lbformat',$cPost['lightbox_format'], SQLITE3_TEXT);
    $stmt->bindValue(':paginate',$cPost['n_paginate_items'], SQLITE3_INTEGER);
    $stmt->bindValue(':hidden',$cPost['n_hidden'], SQLITE3_INTEGER);
    return $stmt->execute();
}

function editSection($cPost, $clickAreas, $imgPath) {
    global $db;
    $conn = new SQLite3($db);
    $qry = 'UPDATE Sections SET 
        Name=:name, Text=:text, Header_Img_Path=:imgpath, Show_Title=:showtitle, Show_Header_Img=:showimg, 
        Show_Item_Images=:showitemimgs, Show_Item_Titles=:showitemtitles, Show_Item_Text=:showitemtext, 
        Order_By=:orderby, Order_Dir=:orderdir, Auto_Thumbs=:autothumbs, Thumb_Size=:thumbsize, Thumb_Size_Axis=:thumbsizea, 
        Format=:format, Default_Item_Format=:itemformat, View_Item_Format=:viformat, Page_ID=:pageid, 
        Show_Item_Files=:showfiles, Default_File_Link_Text=:filelinktext, Item_Click_Area=:clickarea, On_Click_Action=:clickaction, 
        Lightbox_Format=:lbformat, Paginate_Items=:paginate, Hidden=:hidden
    WHERE ID=:id;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':name',$cPost['name'], SQLITE3_TEXT);
    $stmt->bindValue(':pageid',$cPost['n_page_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':text',$cPost['text'], SQLITE3_TEXT);
    $stmt->bindValue(':imgpath',$imgPath, SQLITE3_TEXT);
    $stmt->bindValue(':showtitle',$cPost['n_show_title'], SQLITE3_INTEGER);
    $stmt->bindValue(':showimg',$cPost['n_show_header_img'], SQLITE3_INTEGER);
    $stmt->bindValue(':showitemimgs',$cPost['n_show_images'], SQLITE3_INTEGER);
    $stmt->bindValue(':showitemtitles',$cPost['n_show_titles'], SQLITE3_INTEGER);
    $stmt->bindValue(':showitemtext',$cPost['n_show_text'], SQLITE3_INTEGER);
    $stmt->bindValue(':orderby',$cPost['order_by'], SQLITE3_TEXT);
    $stmt->bindValue(':orderdir',$cPost['n_order_dir'], SQLITE3_INTEGER);
    $stmt->bindValue(':autothumbs',$cPost['n_create_thumbs'], SQLITE3_INTEGER);
    $stmt->bindValue(':thumbsize',$cPost['n_thumb_size'], SQLITE3_INTEGER);
    $stmt->bindValue(':thumbsizea',$cPost['n_thumb_axis'], SQLITE3_INTEGER);
    $stmt->bindValue(':format',$cPost['format'], SQLITE3_TEXT);
    $stmt->bindValue(':itemformat',$cPost['item_format'], SQLITE3_TEXT);
    $stmt->bindValue(':showfiles',$cPost['n_show_files'], SQLITE3_INTEGER);
    $stmt->bindValue(':filelinktext',$cPost['file_link_text'], SQLITE3_TEXT);
    $stmt->bindValue(':clickarea',$clickAreas, SQLITE3_TEXT);
    $stmt->bindValue(':clickaction',$cPost['n_onclick_action'], SQLITE3_INTEGER);
    $stmt->bindValue(':viformat',$cPost['view_item_format'], SQLITE3_TEXT);
    $stmt->bindValue(':lbformat',$cPost['lightbox_format'], SQLITE3_TEXT);
    $stmt->bindValue(':paginate',$cPost['n_paginate_items'], SQLITE3_INTEGER);
    $stmt->bindValue(':hidden',$cPost['n_hidden'], SQLITE3_INTEGER);
    $stmt->bindValue('id',$cPost['n_sect_id'], SQLITE3_INTEGER);
    return $stmt->execute();
}

function createItem($cPost) {
    global $db;
    $conn = new SQLite3($db);
    $qry = "INSERT INTO Items (Sect_ID,Title,Img_Path,Img_Alt_Text,File_Path,File_Link_Text,
    Embed_HTML,Img_Thumb_Path,Text,Publish_Timestamp,Hidden,Format)
    VALUES (:sectid,:title,:img,:alttext,:file,:linktext,:embed,:imgthumb,:text,:ts,:hide,:format);";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':sectid',$cPost['n_sect_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':title',$cPost['title'], SQLITE3_TEXT);
    $stmt->bindValue(':img',$cPost['img_path'], SQLITE3_TEXT);
    $stmt->bindValue(':alttext',$cPost['img_alt_text'], SQLITE3_TEXT);
    $stmt->bindValue(':file',$cPost['file_path'], SQLITE3_TEXT);
    $stmt->bindValue(':linktext',($cPost['file_link_text'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':embed',$cPost['b_embed'], SQLITE3_TEXT);
    $stmt->bindValue(':imgthumb',$cPost['thumb_path'], SQLITE3_TEXT);
    $stmt->bindValue(':text',$cPost['m_text'], SQLITE3_TEXT);
    $stmt->bindValue(':ts',$cPost['publish_datetime'], SQLITE3_INTEGER);
    $stmt->bindValue(':hide',$cPost['n_hidden'], SQLITE3_INTEGER);
    $stmt->bindValue(':format',$cPost['format'], SQLITE3_TEXT);
    return $stmt->execute();
}

function editItem($cPost) {
    global $db;
    $conn = new SQLite3($db);
    $qry = "UPDATE Items 
    SET Sect_ID=:sectid,Title=:title,Publish_Timestamp=:ts,Text=:text,Img_Path=:img, Img_Alt_Text=:alttext,
    File_Path=:file, File_Link_Text=:linktext, Embed_HTML=:embed,Img_Thumb_Path=:imgthumb,Format=:format,Hidden=:hide
    WHERE ID=:id;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':sectid',$cPost['n_sect_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':title',$cPost['title'], SQLITE3_TEXT);
    $stmt->bindValue(':ts',$cPost['publish_datetime'], SQLITE3_INTEGER);
    $stmt->bindValue(':text',$cPost['m_text'], SQLITE3_TEXT);
    $stmt->bindValue(':img',$cPost['img_path'], SQLITE3_TEXT);
    $stmt->bindValue(':alttext',$cPost['img_alt_text'], SQLITE3_TEXT);
    $stmt->bindValue(':file',$cPost['file_path'], SQLITE3_TEXT);
    $stmt->bindValue(':linktext',($cPost['file_link_text'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':embed',$cPost['b_embed'], SQLITE3_TEXT);
    $stmt->bindValue(':imgthumb',$cPost['thumb_path'], SQLITE3_TEXT);
    $stmt->bindValue(':hide',$cPost['n_hidden'], SQLITE3_INTEGER);
    $stmt->bindValue(':format',$cPost['format'], SQLITE3_TEXT);
    $stmt->bindValue(':id',$cPost['n_item_id'], SQLITE3_INTEGER);
    return $stmt->execute();
}

// page create/edit...
if (isset($_POST['create_page']) || isset($_POST['edit_page'])) {
    if (isset($_POST['create_page'])) {
        $newPage = true;
    } else {
        $newPage = false;
    }
    global $set;
    $msg = '';
    $error = false;
    if (isset($_POST['sect'])){
        $sectArr = $_POST['sect'];
        unset($_POST['sect']);
    } else {
        $sectArr=false;
    }
    $cPost = cleanServerPost($_POST);
    $imgUpld = ($_FILES['header_img_upload']['name'] ?? false);
    $storedImg = ($cPost['stored_header_img'] ?? null);
    $imgPath = $storedImg;
    $menuImgUpld = ($_FILES['menu_img_upload']['name'] ?? false);
    $rmvMenuImg = ($cPost['n_rmv_menu_img'] ?? 0);
    $menuImgPath = false;
    $storedPageName = ($cPost['name_stored'] ?? '');
    $cPost['link'] = strtolower(preg_replace("/[^A-Za-z0-9-]/",'',str_replace(' ','-',$cPost['name'])));
    if (strcasecmp($cPost['name'], $storedPageName)!=0) {
        $pageLinks = getPageLinks();
        if (in_array($cPost['link'],$pageLinks)) {$cPost['link'] .= '_';}
    }
    if ($imgUpld || $menuImgUpld) {
        require_once 'upload.php';
        if ($imgUpld) {
            $dir = '/assets/uploads/page-headers/';
            $imgPath = uploadImage($dir, $_FILES['header_img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage']);
        }
        if ($menuImgUpld || $rmvMenuImg) {
            if ($menuImgUpld) {
                $dir = '/assets/uploads/menu/';
                $menuImgUpld = cleanFileName($menuImgUpld);
                $menuImgPath = uploadImage ($dir, $_FILES['menu_img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage']);
            } else if ($rmvMenuImg) {
                $menuImgPath = '';
            }
        }
    }
    if (!$newPage) {
        if ($cPost['n_hidden']==1 && $cPost['n_menu_hidden']==0) {
            $updtMHidden = true;
            $cPost['n_menu_hidden'] = 1;
        } else {
            $updtMHidden = false;
        }
    }
    $cPost['img_path'] = $imgPath;
    $cPost['menu_img_path'] = $menuImgPath;
    if ($newPage) {
        $exec = createPage($cPost);
    } else {
        $exec = editPage($cPost,$updtMHidden,$sectArr);
    }
    if (!$exec) {
        $msg .= "Changes failed to save. Please try again.";
    } else {
        $msg .="Changes to '".$cPost['name']."' were saved at ".date('d F, Y h:i:s').'.';
    }
}


if (isset($_POST['delete_page'])) {
    global $db;
    $conn = new SQLite3($db);
    $pageID = filter_var($_POST['n_page_id'], FILTER_SANITIZE_NUMBER_INT);
    if ($pageID<2) {
        $msg="You cannot delete essential pages, such as home or the error page.";
        return;
    }
    $moveSectQry = 'UPDATE Sections SET Page_ID=null WHERE Page_ID=?;';
    $sectStmt = $conn->prepare($moveSectQry);
    $sectStmt->bindValue(1,$pageID, SQLITE3_INTEGER);
    if ($sectStmt->execute()) {
        $qry = 'DELETE FROM Pages WHERE ID=?;';
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(1,$pageID, SQLITE3_INTEGER);
        if ($stmt->execute() && $conn->changes()>0) {
            $msg="Page deleted!";
            $menuQry = 'DELETE FROM Automenu WHERE Type_Code=1 AND Ref_ID=?;';
            $menuStmt = $conn->prepare($menuQry);
            $menuStmt->bindValue(1,$pageID, SQLITE3_INTEGER);
            $menuStmt->execute();
        } else {
            $msg="Page failed to delete. Please try again.";
        }
    } else {
        $msg="Page failed to delete. Please try again.";
    }
}

// create/edit section
if (isset($_POST['create_section']) || isset($_POST['edit_section'])) {
    if (isset($_POST['create_section'])) {
        $newSection = true;
    } else {
        $newSection = false;
    }
    global $set;
    $imgUpld = ($_FILES['header_img_upload']['name'] ?? null);
    $cPost = cleanServerPost($_POST);
    if (!$newSection && $cPost['n_sect_id']===0) {
        $msg="You cannot make changes to the 'Orphaned Items' section.";
        return;
    }
    if ($cPost['n_page_id'] <= '') {
        $cPost['n_page_id'] = null;
    }
    // in case I decide whether Section Text gets cleaned up as code or markup text should be toggable...
    if ($cPost['m_text'] ?? null) {
        $cPost['text'] = $cPost['m_text'];
    } else {
        $cPost['text'] = $cPost['b_text'];
    }
    //
    $storedImg = ($cPost['header_img_stored'] ?? false);
    if ($imgUpld) {
        $dir = '/assets/uploads/sect-headers/';
        require_once 'upload.php';
        $imgPath = uploadImage($dir, $_FILES['header_img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage'], $storedImg);
    } else if ($storedImg) {
        $imgPath = $storedImg;
    } else {
        $imgPath = null;
    }
    if ($cPost['file_link_text'] === "") {
        $cPost['file_link_text'] = "Click here";
    }
    $clickAreas = cleanServerPost($cPost['item_click_area']);
    if (in_array('All',$clickAreas)) {
        $clickAreas = array('All');
    }
    $clickAreas= implode(',',$clickAreas);
    if ($newSection) {
        $exec = createSection($cPost, $clickAreas, $imgPath);
    } else {
        $exec = editSection($cPost, $clickAreas, $imgPath);
    }
    if (!$exec) {
        $msg="Changes failed to save. Please try again.";
    } else {
        $msg="Section settings were saved at ".date('d F, Y h:i:s').'.';
    }
}

if (isset($_POST['delete_section'])) {
    global $db;
    $conn = new SQLite3($db);
    $sectID = intval(filter_var($_POST['n_sect_id'], FILTER_SANITIZE_NUMBER_INT));
    if ($sectID===0) {
        $msg="You cannot delete the 'Orphaned Items' section.";
        return;
    }
    $moveItemsQry = 'UPDATE Items SET Sect_ID=0 WHERE Sect_ID=?;';
    $itemStmt = $conn->prepare($moveItemsQry);
    $itemStmt->bindValue(1,$sectID, SQLITE3_INTEGER);
    if ($itemStmt->execute()) {
        $qry = 'DELETE FROM Sections WHERE ID=?;';
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(1,$sectID, SQLITE3_INTEGER);
        if ($stmt->execute()) {
            $msg="Section deleted!";
        } else {
            $msg="Section failed to delete. Please try again.";
        }
    } else {
        $msg="Section failed to delete. Please try again.";
    }
}

// item create/item
if (isset($_POST['create_item']) || isset($_POST['edit_item'])) {
    if (isset($_POST['create_item'])) {
        $newItem=true;
    } else {
        $newItem=false;
    }
    global $set;
    $cPost = cleanServerPost($_POST, true);
    $msg ="";
    // sort out what's going on with files...
    $newImgUpld=false;
    $imgUpld = ($_FILES['img_upload']['name'] ?? false);
    $thumbUpld = ($_FILES['thumb_upload']['name'] ?? false);
    $fileUpld = ($_FILES['file_upload']['name'] ?? false);
    if ($cPost['sect_create_thumbnail']==1 || 
        ($cPost['sect_create_thumbnail']==0 && $cPost['item_create_thumbnail']==1)) {
        $autoThumb = true;
    } else {
        $autoThumb = false;
    }
    if ($cPost['sect_create_thumbnail']==1) {
        $cPost['n_thumb_size'] = $cPost['n_sect_thumb_size'];
        $cPost['n_thumb_size_axis'] = $cPost['n_sect_thumb_size_axis'];
    } else {
        $cPost['n_thumb_size'] = $cPost['n_item_thumb_size'];
        $cPost['n_thumb_size_axis'] = $cPost['n_item_thumb_size_axis'];
    }
    if ($autoThumb==false && $thumbUpld==false) {
        $createThumb = false;
    } else {
        $createThumb = true;
    }
    // check if we need any file uploads...
    if ($imgUpld || $thumbUpld || $fileUpld || $createThumb) {
        require_once 'upload.php';
    }
    $dir = '/assets/uploads/items/section-'.$cPost['n_sect_id'].'/';
    // image upload
    $imgStored = ($cPost['img_stored'] ?? false);
    if ($imgUpld) {
        $imgName = cleanUpldName($imgUpld);
        $imgPath = uploadImage($dir, $_FILES['img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage'], $imgStored);
        if (!$imgPath) {
            $msg .= 'Image upload failed. Please try again. ';
            return;
        } else {
            $newImgUpld=true;
        }
    } else {
        if (!$newItem && $imgStored) {
            $imgName = cleanUpldName($imgStored, $dir);
            $imgPath = $imgStored;
        } else {
            $imgPath = null;
        }
    }
    // image thumbnail
    $thumbStored = ($cPost['thumb_stored'] ?? false);
    if (($newImgUpld && $createThumb) || ($imgPath && $createThumb)) {
        if ($thumbUpld) {
            $imgThumbPath = uploadImage($dir, $_FILES['thumb_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, $imgName.'_thumb', $set['max_upld_storage'],$thumbStored);
            if (!$imgThumbPath) {
                $msg .= 'Thumbnail image upload failed. Please try again. ';
                return;
            }
        } else {
            if ($imgUpld) {
                $oriImg = $imgUpld;
            } else {
                $oriImg = $imgStored;
            }
            $newH = false;
            $newW = $cPost['n_thumb_size'];
            if ($cPost['n_thumb_size_axis'] === 1) {
                $newH = $cPost['n_thumb_size'];
                $newW = false;
            }
            $imgThumbPath = mkThumb($dir, $oriImg, $imgPath, $newW, $newH);
        }
    } else if (($cPost['n_rmv_thumb_img'] ?? 0)>0) {
        $imgThumbPath = '';
    } else {
        $imgThumbPath = ($cPost['stored_thumb_img'] ?? false);
    }
    // file upload
    $fileStored = ($cPost['file_stored'] ?? false);
    if ($fileUpld) {
        $filePath = uploadFile ($dir, $_FILES['file_upload'], false, $set['max_upld_storage'], $fileStored);
        if (!$filePath) {
            $msg .= 'Image upload failed. Please try again. ';
            return;
        }
    } else {
        if ($fileStored) {
            $filePath = $fileStored;
        } else {
            $filePath = null;
        }
    }
    if ($filePath) {
        $filePath= '['.substr($cPost['file_pres'], 0, 3).']'.$filePath;
    }
    if ($cPost['publish_datetime']) {
        $cPost['publish_datetime'] = strtotime($cPost['publish_datetime']);
    } else {
        $cPost['publish_datetime'] = time();
    }
    if ($cPost['b_embed'] <= '') {
        $cPost['b_embed'] = null;
    }
    if ($cPost['format'] <= '') {
        $cPost['format'] = null;
    }
    $cPost['img_path'] = $imgPath;
    $cPost['thumb_path'] = $imgThumbPath;
    $cPost['file_path'] = $filePath;
    if ($newItem) {
        $exec = createItem($cPost);
    } else {
        $exec = editItem($cPost);
    }
    if ($exec) {
        $msg.="Changes were saved at ".date('d F, Y h:i:s').'.';
    } else {
        $msg.="Changes did not save. Please try again.";
    }
}

if (isset($_POST['delete_item'])) {
    global $db;
    $conn = new SQLite3($db);
    $itemID = filter_var($_POST['n_item_id'], FILTER_SANITIZE_NUMBER_INT);
    $qry = 'DELETE FROM Items WHERE ID=?;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1,$itemID, SQLITE3_INTEGER);
    if ($stmt->execute()) {
        $msg="Item deleted!";
    } else {
        $msg="Item failed to delete. Please try again.";
    }
}


if (isset($_POST['save_item_order'])) {
    global $db;
    $conn = new SQLite3($db);
    $msg="";
    usort($_POST["item"], function ($a, $b) {
        return $a['n_index_order'] <=> $b['n_index_order'];
    });
    $indexOrder=0;
    $error = false;
    $qry = "UPDATE Items SET Sect_Index_Order=:num WHERE ID=:itemid";
    foreach ($_POST["item"] AS &$item) {
        if ($error) {
            $msg.=" There was an error saving your item order changes. Please try again.";
            return;
        }
        $cPost = cleanServerPost($item);
        $indexOrder = ($indexOrder+1);
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(':num',$indexOrder, SQLITE3_INTEGER);
        $stmt->bindValue(':itemid',$cPost['n_item_id'], SQLITE3_INTEGER);
        if (!$stmt->execute()) {
            $error = true;
        }
    }
    if (!$error) {
        $msg.="Changes were saved at ".date('d F, Y h:i:s').".";
    } else {
        $msg.=" There was an error saving your item order changes. Please try again.";
    }
}

if (isset($_POST['save_menu'])) {
    global $db;
    $conn = new SQLite3($db);
    $msg="";
    usort($_POST["option"], function ($a, $b) {
        return $a['n_index'] <=> $b['n_index'];
    });
    $indexOrder=0;
    $error = false;
    $qry = "UPDATE Automenu 
        SET  Index_Order=:inorder, Submenu=:insub, Hidden=:hidden
            WHERE ID=:id";
    foreach ($_POST["option"] AS &$opt) {
        if ($error) {
            $msg.=" There was an error saving your menu changes. Please try again.";
            return;
        }
        $cPost = cleanServerPost($opt);
        if (!isset($cPost['n_submenu']) || $indexOrder===0) {
            if ($indexOrder===0 && ($cPost['n_submenu'] ?? 0)>0) {
                $msg .="<div class='red'>Menu items in the first position cannot be in a submenu.</div>";
            }
            $cPost['n_submenu'] = 0;
        }
        if ((isset($cPost['n_page_hidden']) && $cPost['n_page_hidden']==1) && $cPost['n_hidden']==0) {
            $cPost['n_hidden']=1;
            $msg .="<div class='red'>The '".$cPost['name']."' page is hidden and cannot be shown on the menu.
            To change this setting, 
                <a href='".$set['dir']."/admin/pages.php?task=edit&id=".$cPost['n_page_id']."'>edit its page settings here</a>.</div>";
        }
        $indexOrder = ($indexOrder+1);
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(':inorder',$indexOrder, SQLITE3_INTEGER);
        $stmt->bindValue(':insub',$cPost['n_submenu'], SQLITE3_INTEGER);
        $stmt->bindValue(':hidden',$cPost['n_hidden'], SQLITE3_INTEGER);
        $stmt->bindValue(':id',$cPost['n_menu_id'], SQLITE3_INTEGER);
        if (!$stmt->execute()) {
            $error = true;
        }
    }
    if (!$error) {
        $msg.="Changes were saved at ".date('d F, Y h:i:s').'.';
    } else {
        $msg.=" There was an error saving your menu changes. Please try again.";
    }
}

if (isset($_POST['menu_add_item'])) {
    global $db; global $set;
    $conn = new SQLite3($db);
    $msg="";
    $error = false;
    require_once 'upload.php';
    $dir = '/assets/uploads/menu/';
    if (isset($_FILES['link_img']) && $_FILES['link_img']['name']>'') {
        $imgPath = uploadImage ($dir, $_FILES['link_img'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage']);
    } else {
        $imgPath = null;
    }
    $qry = 'INSERT INTO Automenu 
        (Type_Code, Link_Text, Ext_Url'.($imgPath ? ', Img_Path' : null).') 
    VALUES (:type,:text,:exturl'.($imgPath ? ',:img' : null).')';
        $_POST['n_type_code'] = $_SESSION['MenuItemType'];
        unset($_SESSION['MenuItemType']);
        $cPost = cleanServerPost($_POST);
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(':type',$cPost['n_type_code'], SQLITE3_INTEGER);
        $stmt->bindValue(':text',$cPost['link_text'], SQLITE3_TEXT);
        $stmt->bindValue(':exturl',$cPost['ext_url'], SQLITE3_TEXT);
        if ($imgPath) {
            $stmt->bindValue(':img',$imgPath, SQLITE3_TEXT);
        }
        if (!$stmt->execute()) {
            $error = true;
        }
    if (!$error) {
        $msg.="Changes were saved at ".date('d F, Y h:i:s').'.';
    } else {
        $msg.=" There was an error saving your menu changes. Please try again.";
    }
}

if (isset($_POST['menu_edit_item'])) {
    global $db; global $set;
    $conn = new SQLite3($db);
    $msg="";
    $cPost = cleanServerPost($_POST);
        if ($cPost['n_type_code']==8 && (isset($cPost['ext_url']) && !$cPost['ext_url'])) {
            $cPost['ext_url']='/';
        } else {
            $cPost['ext_url']=null;
        }
    $dir = '/assets/uploads/menu/';
    if (isset($_FILES['link_img']) && $_FILES['link_img']['name']>'') {
        require_once 'upload.php';
        $imgPath = uploadImage ($dir, $_FILES['link_img'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage']);
    } else if (isset($cPost['link_img_stored']) && $cPost['link_img_stored']>'') {
        $imgPath = $cPost['link_img_stored'];
    } else {
        $imgPath = null;
    }
    $qry = 'UPDATE Automenu
            SET Link_Text=:text'.($cPost['ext_url'] ? ', Ext_Url=:exturl' : null).($imgPath ? ', Img_Path=:img' : null).'
            WHERE ID=:id';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':text',$cPost['link_text'], SQLITE3_TEXT);
    if ($cPost['ext_url']) {
        $stmt->bindValue(':exturl',$cPost['ext_url'], SQLITE3_TEXT);
    }
    if ($imgPath) {
        $stmt->bindValue(':img',$imgPath, SQLITE3_TEXT);
    }
    $stmt->bindValue(':id',$cPost['n_menu_id'], SQLITE3_INTEGER);
    if (!$stmt->execute()) {
        $msg.=" There was an error saving your menu changes. Please try again.";
    } else {
        $msg.="Changes were saved at ".date('d F, Y h:i:s').'.';
    }
}

if (isset($_POST['delete_menu_item'])) {
    global $db; global $set;
    $conn = new SQLite3($db);
    $id = filter_var($_POST['n_menu_id'], FILTER_SANITIZE_NUMBER_INT);
    $msg="";
    if ($_SESSION['MenuItemType'] < 8) {
        $msg .= "You can only delete custom links and headings via the automenu.";
        return false;
    } else {
        unset($_SESSION['MenuItemType']);
    }
    $qry = "DELETE FROM Automenu WHERE ID=? AND (Type_Code>3 AND Ref_ID IS NULL);";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1,$id, SQLITE3_INTEGER);
    if (!$stmt->execute() || $conn->changes()<1) {
        $msg.=" There was an error deleting this menu item. Please try again.";
    } else {
        $msg.="Menu item deleted at ".date('d F, Y h:i:s').'.';
    }
}

} //end 'if $loggedIn'