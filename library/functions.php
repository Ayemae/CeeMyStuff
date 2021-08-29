<?php
$showErrors = 1;
if ($showErrors) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(-1);
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
                $row['Value'] = strtolower($row['Value']);
                break;
        }
        $settings[$row['Key']] = $row['Value'];
    }
    return $settings;
}

$set = serializeSettings();

$root = $_SERVER['DOCUMENT_ROOT'].$set['dir'];
$baseURL = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$set['dir'];
$route = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);

function show($input=false) {
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


function cleanServerPost($post){
    if (is_array($post)) {
        foreach ($post AS $key=>&$val) {
            // get type from marked html name attributes
            $t = substr($key,0,2);
            $val = trim($val);
            switch ($t) {
                case 'b_':
                    $val = nl2br($val);
                    $val = iconv('UTF-8', 'ASCII//TRANSLIT', $val);
                    $val = htmlentities($val);
                    break;
                case 'n_':
                    $val = filter_var($val, FILTER_SANITIZE_NUMBER_INT);
                    break;
                default:
                $val = htmlspecialchars($val);
                break;
            }
            return $post;
        }
    }
}

// Snippet from PHP Share: http://www.phpshare.org
function formatSizeUnits($bytes)
{
    if ($bytes >= 1073741824)
    {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    }
    elseif ($bytes >= 1048576)
    {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    }
    elseif ($bytes >= 1024)
    {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    }
    elseif ($bytes > 1)
    {
        $bytes = $bytes . ' bytes';
    }
    elseif ($bytes == 1)
    {
        $bytes = $bytes . ' byte';
    }
    else
    {
        $bytes = '0 bytes';
    }

    return $bytes;
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
             //echo $body;
        } else {
            $msg = '<p>Credential submission failed. Please try again.</p>';
        }
    }
}

function validateEmail($key) {
    global $db;
    $ehash = null; $email = null; $unix = null; $expired = true;
    $conn = new SQLite3($db);
    $msg = '';
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
        $error .= "This activation email has expired. Please try again.";
        return $error;
    }
    if (!$expired && hash_equals(hash("sha256", $key), $keyHash)) {
        $updQry = "UPDATE Accounts SET Email_Valid = 1 WHERE Is_Admin = 1;";
        if ($conn->prepare($updQry)->execute()) {
            if(is_writable('install.php')){
                //Delete the file
                if (!unlink('install.php')) {
                    error_log('install.php failed to delete.');
                }
            }
            return true;
        } else {
            $error .= 'Something went wrong. Please try again.';
            return $error;
        }
    } else {
        $error .= 'Credentials do no match or were set incorrectly.';
        return $error;
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

function getPage($page){
    global $db;
    $conn = New SQLite3($db);
    $page = stripHTML($page);
    $qry = "SELECT * FROM Pages WHERE Page_Name=:request LIMIT 1;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':request', $page, SQLITE3_TEXT);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        return $row;
    }
}

function printPage($request) {
    global $db;
    $conn = New SQLite3($db);
    $page = stripHTML($request);
    $qry = "SELECT * FROM Pages WHERE Page_Name=:request LIMIT 1;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':request', $request, SQLITE3_TEXT);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $request = $row;
    }
    $admin_panel = false;
    $page_title = $page['Page_Header'];
    include_once 'components/info-head.php';
    include_once 'components/header.php';
    echo '<h1>'.$page['Page_Header'].'</h1>';
    include_once 'components/footer.php';
}


function kickOut() {
    global $baseURL;
    header('Location: '.$baseURL);
    // if header fails, do it with Javascript instead:
        echo '<script>window.location.replace("'.$baseURL.'")</script>';
    exit();
}

if (isset($_POST['logout'])) {
    logout();
    kickOut();
};



function getCatList() {
    global $db;
    global $admin_area;
    global $loggedIn;
    $conn = new SQLite3($db);
    $catList = array();
    $qry = 'SELECT ID, Name FROM Categories';
    if (!$admin_area || !$loggedIn) {
        $qry .= ' WHERE Hidden=0';
    }
    $qry .= ';';
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $catList[]=$row;
    } 
    return $catList;
}

function getCatInfo($id) {
    global $db;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $conn = new SQLite3($db);
    $qry = 'SELECT * FROM Categories WHERE ID = :id LIMIT 1;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Blurb'] = html_entity_decode($row['Blurb']);
        return $row;
    } 
}

function getCatItems($id) {
    global $db;global $set;global $admin_panel;global $loggedIn;
    $items = array();
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $conn = new SQLite3($db);
    $qry = 'SELECT * FROM Items WHERE Cat_ID = :catid ';
    if (!$loggedIn || !$admin_panel) {
        $qry .= ' AND Hidden=0';
    }
    $qry .= ';';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':catid', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Caption'] = html_entity_decode($row['Caption']);
        $row['Img_Path'] = $set['dir'].$row['Img_Path'];
        $row['Img_Thumb_Path'] = $set['dir'].$row['Img_Thumb_Path'];
        $items[] = $row;
    } 
    return $items;
}

function getItem($id) {
    global $db;
    global $set;
    global $admin_panel;
    global $loggedIn;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $conn = new SQLite3($db);
    $qry = 'SELECT * FROM Items WHERE ID = :itemid';
    if (!$admin_panel || !$loggedIn) {
        $qry .= ' AND Hidden=0';
    }
    $qry .= ' LIMIT 1;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':itemid', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Caption'] = html_entity_decode($row['Caption']);
        $row['Img_Path'] = $row['Img_Path'];
        $row['Img_Thumb_Path'] = $row['Img_Thumb_Path'];
        $dt = new DateTime('@'.$row['Publish_Timestamp']);
        $dt->setTimeZone(new DateTimeZone($set['timezone']));
        $row['Publish_Timestamp'] = $dt->format('Y-m-d\TH:i');
        return $row;
    } 
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if ($loggedIn) {


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

if (isset($_POST['create_category'])) {
    global $db;
    global $set;
    $conn = new SQLite3($db);
    $cPost = cleanServerPost($_POST);
    require_once 'imgUpload.php';
    $dir = '/assets/uploads/cat-headers/';
    if (!$set['has_max_img_dimns']) {$set['max_img_dimns'] = false;}
    if (!$set['has_max_img_storage']) {$set['max_img_storage'] = false;}
    if ($_FILES['header_img_upload']['name']) {
        //sanitize img name
        $_FILES['header_img_upload']['name'] = stripHTML($_FILES['header_img_upload']['name']);
        $_FILES['header_img_upload']['name'] = str_replace(" ","-",preg_replace("/[^A-Za-z0-9. \-_]/", '', $_FILES['img_upload']['name']));
        $imgPath = uploadImage ($dir, $_FILES['header_img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_img_storage']);
    }
    $qry = 'INSERT INTO Categories (Name,Blurb,Header_Img_Path,Show_Images,Show_Titles,Show_Captions,Order_By,Auto_Thumbs,Thumb_Size,Thumb_Size_Axis,Hidden,Format_ID) 
    VALUES (?,?,?,?,?,?,?,?,?,?,?);';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1,$cPost['name'], SQLITE3_TEXT);
    $stmt->bindValue(2,$cPost['b_blurb'], SQLITE3_TEXT);
    $stmt->bindValue(3,$imgPath, SQLITE3_TEXT);
    $stmt->bindValue(4,$cPost['n_show_images'], SQLITE3_INTEGER);
    $stmt->bindValue(5,$cPost['n_show_titles'], SQLITE3_INTEGER);
    $stmt->bindValue(6,$cPost['n_show_captions'], SQLITE3_INTEGER);
    $stmt->bindValue(7,$cPost['order_by'], SQLITE3_TEXT);
    $stmt->bindValue(8,$cPost['n_create_thumbs'], SQLITE3_INTEGER);
    $stmt->bindValue(9,$cPost['n_thumb_size'], SQLITE3_INTEGER);
    $stmt->bindValue(10,$cPost['n_thumb_axis'], SQLITE3_INTEGER);
    $stmt->bindValue(11,$cPost['n_hidden'], SQLITE3_INTEGER);
    $stmt->bindValue(12,$cPost['n_format_id'], SQLITE3_INTEGER);
    if ($stmt->execute()) {
        $msg="New category created!";
    } else {
        $msg="Category creation failed. Please try again.";
    }
}

if (isset($_POST['edit_category'])) {
    global $db;
    global $set;
    $conn = new SQLite3($db);
    $cPost = cleanServerPost($_POST);
    $dir = '/assets/uploads/cat-headers/';
    if (!$set['has_max_img_dimns']) {$set['max_img_dimns'] = false;}
    if (!$set['has_max_img_storage']) {$set['max_img_storage'] = false;}
    if ($_FILES['header_img_upload']['name']) {
        require_once 'imgUpload.php';
        //sanitize img name
        $_FILES['header_img_upload']['name'] = stripHTML($_FILES['header_img_upload']['name']);
        $_FILES['header_img_upload']['name'] = str_replace(" ","-",preg_replace("/[^A-Za-z0-9. \-_]/", '', $_FILES['img_upload']['name']));
        if (!$cPost['header_img_stored']) {
            $cPost['header_img_stored'] = false;
        }
        $imgPath = uploadImage ($dir, $_FILES['header_img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_img_storage'], $cPost['header_img_stored']);
    } else if ($cPost['header_img_stored']) {
        $imgPath = $cPost['header_img_stored'];
    } else {
        $imgPath = null;
    }
    $qry = 'UPDATE Categories 
    SET Name=?,Blurb=?,Header_Img_Path=?,Show_Images=?,Show_Titles=?,Show_Captions=?,Order_By=?,Auto_Thumbs=?,Thumb_Size=?,Thumb_Size_Axis=?,Hidden=?,Format_ID=?
    WHERE ID=?;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1,$cPost['name'], SQLITE3_TEXT);
    $stmt->bindValue(2,$cPost['b_blurb'], SQLITE3_TEXT);
    $stmt->bindValue(3,$imgPath, SQLITE3_TEXT);
    $stmt->bindValue(4,$cPost['n_show_images'], SQLITE3_INTEGER);
    $stmt->bindValue(5,$cPost['n_show_titles'], SQLITE3_INTEGER);
    $stmt->bindValue(6,$cPost['n_show_captions'], SQLITE3_INTEGER);
    $stmt->bindValue(7,$cPost['order_by'], SQLITE3_TEXT);
    $stmt->bindValue(8,$cPost['n_create_thumbs'], SQLITE3_INTEGER);
    $stmt->bindValue(9,$cPost['n_thumb_size'], SQLITE3_INTEGER);
    $stmt->bindValue(10,$cPost['n_thumb_axis'], SQLITE3_INTEGER);
    $stmt->bindValue(11,$cPost['n_hidden'], SQLITE3_INTEGER);
    $stmt->bindValue(12,$cPost['n_format_id'], SQLITE3_INTEGER);
    $stmt->bindValue(13,$cPost['n_cat_id'], SQLITE3_INTEGER);
    if ($stmt->execute()) {
        $msg="Category setting changes saved!";
    } else {
        $msg="Changes failed to save. Please try again.";
    }
}


if (isset($_POST['create_item'])) {
    global $db;
    global $set;
    $conn = new SQLite3($db);
    $cPost = cleanServerPost($_POST);
    $dir = '/assets/uploads/items/';
    if (!$set['has_max_img_dimns']) {$set['max_img_dimns'] = false;}
    if (!$set['has_max_img_storage']) {$set['max_img_storage'] = false;}
    if ($_FILES['img_upload']['name']) {
        require_once 'imgUpload.php';
        //sanitize img name
        $_FILES['img_upload']['name'] = stripHTML($_FILES['img_upload']['name']);
        $_FILES['img_upload']['name'] = str_replace(" ","-",preg_replace("/[^A-Za-z0-9. \-_]/", '', $_FILES['img_upload']['name']));
        $imgPath = uploadImage ($dir, $_FILES['img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_img_storage']);
        if (!$imgPath) {
            $msg = 'Image upload failed. Please try again.';
            return;
        }
        if ($cPost['create_thumbnail']) {
            $newH = false;
            $newW = $cPost['n_thumb_size'];
            if ($cPost['n_thumb_size_axis'] === 1) {
                $newH = $cPost['n_thumb_size'];
                $newW = false;
            }
            $imgThumbPath = mkThumb($dir, $_FILES['img_upload']['name'], $imgPath, $newW, $newH);
        }
    }
    if ($cPost['publish_datetime']) {
        $pdtUnix = strtotime($cPost['publish_datetime']);
    } else {
        $pdtUnix = time();
    }
    $qry = "INSERT INTO Items (Cat_ID,Title,Img_Path,Img_Thumb_Path,Caption,Publish_Timestamp,Format_ID,Hidden)
    VALUES (?,?,?,?,?,?,?,?);";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1,$cPost['n_cat_id'], SQLITE3_INTEGER);
    $stmt->bindValue(2,$cPost['title'], SQLITE3_TEXT);
    $stmt->bindValue(3,$imgPath, SQLITE3_TEXT);
    $stmt->bindValue(4,$imgThumbPath, SQLITE3_TEXT);
    $stmt->bindValue(5,$cPost['b_caption'], SQLITE3_TEXT);
    $stmt->bindValue(6,$pdtUnix, SQLITE3_INTEGER);
    $stmt->bindValue(7,$cPost['n_format_id'], SQLITE3_INTEGER);
    $stmt->bindValue(8,$cPost['n_hidden'], SQLITE3_INTEGER);
    if ($stmt->execute()) {
        $msg="New item created!";
    } else {
        $msg="Item creation failed. Please try again.";
    }
}

if (isset($_POST['edit_item'])) {
    global $db;
    global $set;
    $conn = new SQLite3($db);
    $cPost = cleanServerPost($_POST);
    $dir = '/assets/uploads/items/';
    if (!$set['has_max_img_dimns']) {$set['max_img_dimns'] = false;}
    if (!$set['has_max_img_storage']) {$set['max_img_storage'] = false;}
    if ($_FILES['img_upload']['name']) {
        require_once 'imgUpload.php';
        //sanitize img name
        $_FILES['img_upload']['name'] = stripHTML($_FILES['img_upload']['name']);
        $_FILES['img_upload']['name'] = str_replace(" ","-",preg_replace("/[^A-Za-z0-9. \-_]/", '', $_FILES['img_upload']['name']));
        if (!$cPost['img_stored']) {
            $cPost['img_stored'] = false;
        } else {
            $cPost['img_stored'] = stripHTML($cPost['img_stored']);
        }
        $imgPath = uploadImage ($dir, $_FILES['img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_img_storage'], $cPost['img_stored']);
        if (!$imgPath) {
            $msg = 'Image upload failed. Please try again.';
            return;
        }
        if ($cPost['create_thumbnail']) {
            $newH = false;
            $newW = $cPost['n_thumb_size'];
            if ($cPost['n_thumb_size_axis'] === 1) {
                $newH = $cPost['n_thumb_size'];
                $newW = false;
            }
            $imgThumbPath = mkThumb($dir, $_FILES['img_upload']['name'], $imgPath, $newW, $newH);
        }
    } else {
        if ($cPost['img_stored']) {
            $imgPath = $cPost['img_stored'];
        } else {
            $imgPath = null;
        }
        if ($cPost['thumb_stored']) {
            $imgThumbPath = $cPost['thumb_stored'];
        } else {
            $imgPath = null;
        }
    }
    if ($cPost['publish_datetime']) {
        $pdtUnix = strtotime($cPost['publish_datetime']);
    } else {
        $pdtUnix = time();
    }
    $qry = "UPDATE Items 
            SET Cat_ID=?,Title=?,Img_Path=?,Img_Thumb_Path=?,Caption=?,Publish_Timestamp=?,Format_ID=?,Hidden=?
            WHERE ID=?;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1,$cPost['n_cat_id'], SQLITE3_INTEGER);
    $stmt->bindValue(2,$cPost['title'], SQLITE3_TEXT);
    $stmt->bindValue(3,$imgPath, SQLITE3_TEXT);
    $stmt->bindValue(4,$imgThumbPath, SQLITE3_TEXT);
    $stmt->bindValue(5,$cPost['b_caption'], SQLITE3_TEXT);
    $stmt->bindValue(6,$pdtUnix, SQLITE3_INTEGER);
    $stmt->bindValue(7,$cPost['n_format_id'], SQLITE3_INTEGER);
    $stmt->bindValue(8,$cPost['n_hidden'], SQLITE3_INTEGER);
    $stmt->bindValue(9,$cPost['n_item_id'], SQLITE3_INTEGER);
    if ($stmt->execute()) {
        $msg="Changes saved!";
    } else {
        $msg="Changes did not save. Please try again.";
    }
}

} //end 'if $loggedIn'