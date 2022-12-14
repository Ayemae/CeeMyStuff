<?php
$showErrors = 0;
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
// run/get settings
$set = serializeSettings();
if (!isset($set['date_format']) || !$set['date_format']) {
    $set['date_format'] = "j F, Y g:i A";
}

$root = $_SERVER['DOCUMENT_ROOT'].$set['dir'];
$baseURL = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$set['dir'];
$route = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);

function show($input=false) {
    if ($input || $input === 0 || $input === "0") {
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
function truncateTxt($txt,$at=140,$trail="...") {
    if (strlen($txt)>$at) {
        $cutOff = ($at - strlen($trail));
        $txt = substr($txt,0,$cutOff).$trail;
    }
    return $txt;
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
                case 'n_':
                    $val = intval(filter_var($val, FILTER_SANITIZE_NUMBER_INT));
                    break;
                case 'b_':
                    // might treat these ('blocks') differently than regular text if I find a need to in the future.
                    //break;
                default:
                    $val = nl2br($val);
                    $val = iconv('UTF-8', 'ASCII//TRANSLIT', $val);
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
            // note: 'key' is the numerical value of the timezone
            $inputSelect .='<option value="'.$theme.'"';
            $inputSelect .= ($theme == $selected ? ' selected' : '');
            $inputSelect .= '>'.$theme.'</option>';
        }  // endwhile;
        $inputSelect.='</select>';
    return $inputSelect;
}

function getFormatList($key='item') {
    global $root;
    global $set;
    $formatList = array();
    $dirContent = scandir('../themes/'.$set['theme'].'/formats/'.$key);
    // echo $root.'/formats/'.$key.'/*.php';
    // $formatList = glob($root.'/formats/'.$key.'/*.php', GLOB_ONLYDIR);
    foreach($dirContent AS &$file) {
        if (substr($file, -4)==='.php') {
            $file = str_replace('.php','',$file);
            $formatList[] = $file;
        }
    }
    return $formatList;
}

if (isset($_POST['submit_credentials'])) {
    $msg= '';
    global $db; global $route;
    global $emailHeaders;
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
                $msg .= "<p>Thank you! A confirmation email has been sent. If it hasn't shown up after a few minutes, 
                check your spam folder.</p>";
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
            $activateUrl = html_entity_decode($route."/pw-reset.php?key=$key");
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
        $passw = $_POST['password'];
        $passw2 = $_POST['password2'];
        if ($passw === $passw2) {
            $password = hash("sha256", $passw);
        } else {
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
        if ($failed === false) {
            $msg = 'Password has been reset! <a href="'.$set['dir'].'/admin">Login here.</a>';
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
        
        $msg .= '<br/><a href="'.$set['dir'].'/admin/pw-reset.php">Click here if you forgot your password.</a>';
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

function getPage($page, $key="id"){
    global $db;
    global $admin_panel;
    global $loggedIn;
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
    $qry = "SELECT p.*, COUNT(i.ID) AS Total_Items
    FROM Pages AS p
    LEFT JOIN Categories AS c ON p.ID=c.Page_ID
    LEFT JOIN  (SELECT ID, Cat_ID FROM Items";
    if (!$admin_panel || !$loggedIn) {
        $qry .= ' WHERE Hidden=0 ';
    }
    $qry .= ") AS i ON c.ID=i.Cat_ID
    WHERE ".$where." COLLATE NOCASE LIMIT 1;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1, $page, SQLITE3_TEXT);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        return $row;
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

function getPageList() {
    global $db;
    global $admin_panel;
    global $loggedIn;
    $conn = new SQLite3($db);
    $pageList = array();
    $qry = 'SELECT p.ID, p.Name, p.Multi_Cat, c.Cat_Num,
    (CASE
    WHEN (p.Multi_Cat=0 AND c.Cat_Num >= 1)
    THEN 0
    ELSE 1
    END) AS Can_Add_Cat
    FROM Pages AS p
    LEFT JOIN (
    SELECT COUNT(ID) AS Cat_Num, Page_ID FROM Categories 
    GROUP BY Page_ID
    ) AS c ON c.Page_ID = p.ID';
    if (!$admin_panel || !$loggedIn) {
        $qry .= ' WHERE Hidden=0';
    }
    $qry .= ';';
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $pageList[]=$row;
    } 
    return $pageList;
}

function getCatList($pageID=false) {
    global $db;
    global $admin_panel;
    global $loggedIn;
    $conn = new SQLite3($db);
    $catList = array();
    $qry = 'SELECT c.ID, c.Page_ID, c.Name, p.Name AS Page_Name, c.Hidden 
    FROM Categories AS c
    LEFT JOIN Pages AS p ON p.ID=c.Page_ID WHERE c.ID !=0 ';
    if ($pageID || $pageID === 0 || $pageID === "0") {
        $pageID = filter_var($pageID, FILTER_SANITIZE_NUMBER_INT);
        $qry .= ' AND c.Page_ID='.$pageID;
    }
    if (!$admin_panel || !$loggedIn) {
        $qry .= ' AND c.Hidden=0';
    }
    $qry .= ' ORDER BY c.Name;';
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $catList[]=$row;
    } 
    return $catList;
}

function getPageCats($pageID) {
    global $db;
    global $admin_panel;
    global $loggedIn;
    $catList = array();
    $pageID = filter_var($pageID, FILTER_SANITIZE_NUMBER_INT);
    $conn = new SQLite3($db);
    $qry = 'SELECT c.*, COUNT(i.ID) AS Total_Items
            FROM Categories AS c
            LEFT JOIN (SELECT ID, Cat_ID FROM Items';
    if (!$admin_panel || !$loggedIn) {
        $qry .= ' WHERE Hidden=0 ';
    }
    $qry .= ') AS i ON c.ID=i.Cat_ID
            WHERE c.Page_ID = :id;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':id', $pageID, SQLITE3_INTEGER);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Text'] = html_entity_decode($row['Text']);
        $catList[]= $row;
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
        $row['Text'] = html_entity_decode($row['Text']);
        return $row;
    } 
}

function getCatItems($id, $pageNum=1,
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
    $qry .= " FROM Items WHERE Cat_ID = :catid ";
    if (!$loggedIn || !$admin_panel) {
        $qry .= " AND Hidden=0 AND Publish_Timestamp<=strftime('%s','now')";
    }
    $qry .= " ORDER BY ";
    switch ($orderBy) {
        case 'Title':
            $qry .= 'Title';
            break;
        case 'Custom':
            $qry .= 'Cat_Index_Order';
            break;
        case 'Random':
            $qry .= 'RANDOM() ';
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
    $stmt->bindValue(':catid', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Text'] = htmlspecialchars_decode($row['Text']);
        $dt = new DateTime('@'.$row['Publish_Timestamp']);
        $dt->setTimeZone(new DateTimeZone($set['timezone']));
        $row['Publish_Timestamp'] = $dt->format('Y-m-d\TH:i');
        $row['Date'] = $dt->format($set['date_format']);
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
        $row['Text'] = htmlspecialchars_decode($row['Text']);
        $dt = new DateTime('@'.$row['Publish_Timestamp']);
        $dt->setTimeZone(new DateTimeZone($set['timezone']));
        $row['Publish_Timestamp'] = $dt->format('Y-m-d\TH:i');
        $row['Date'] = $dt->format($set['date_format']);
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

function showImage($setShowImg, $img=false, $thumb=false, $title=false) {
    global $set;
    if (is_array($setShowImg)) {
        $setShowImg = $setShowImg['Show_Item_Images'];
    }
    switch ($setShowImg) {
        case 0:
            $img='';
         break;
        case 1:
            if ($thumb) {
                $img = $thumb;
            }
    }
    if ($img) {
        $img = '<img class="item-image" src="'.$set['dir'].$img.'" alt="'.$title.' Image">';
    }
    return $img;
}

function className($str) {
    return strtolower(preg_replace("/[^A-Za-z-]/",'',str_replace(' ','-',$str)));
}

function printPaginator($pgAfter, $pageNum, $itemTotal, $pageLink) {
    global $set;
    if ($itemTotal>$pgAfter) {
        $firstURL = $set['dir'].'/'.urlencode($pageLink).'/page/1';
        $backURL = '';
        $nextURL = '';
        $lastURL = $set['dir'].'/'.urlencode($pageLink).'/page/'.ceil($itemTotal/$pgAfter);
    if ($pageNum>1) {
        $backURL = $set['dir'].'/'.urlencode($pageLink).'/page/'.($pageNum-1);
    }
    if (($pgAfter*$pageNum)<$itemTotal) {
        $nextURL = $set['dir'].'/'.urlencode($pageLink).'/page/'.($pageNum+1);
    }
    ob_start();
    include 'components/paginator.php';
    $output = ob_get_clean();
    return $output;
    }
}

function printPageItems($itemList=false, $cat=false) {
    if (!$itemList || !is_array($itemList)) {
        return '<!-- There are no items in this category. -->';
    }
    if (!$cat) {
        $cat;
        $cat['Show_Item_Titles'] = 1;
        $cat['Show_Item_Text'] = 1;
        $cat['Show_Item_Images'] = 1;
    }
    global $set;
    $content = '';
    foreach($itemList AS $item){
        $id = $item['ID'];
        $title = showTitle($cat['Show_Item_Titles'], $item['Title']);
        $text = showText($cat['Show_Item_Text'], $item['Text']);
        $image = showImage($cat['Show_Item_Images'], $item['Img_Path'], $item['Img_Thumb_Path'], $item['Title']);
        $date = $item['Date'];
        $embed = $item['Embed_HTML'];
        $srcImgFull = $set['dir'].$item['Img_Path'];
        $srcImgThumb = $set['dir'].$item['Img_Thumb_Path'];
        $class = className($item['Title']);
        if ($item['Format'] && file_exists('formats/item/'.$item['Format'].'.php')) {
            ob_start();
            include 'formats/item/'.$item['Format'].'.php';
            $content .= ob_get_clean();
        } else {
            $content .= '<div id="item_'.$id.'" class="item '.$class.'">';
            if ($title) {
                $content .= '<h3 class="item-title">'.$title.'</h3>';
            }
            $content .= '<!-- No valid item format assigned. -->';
            if ($image) {
                $content .= $image;
            }
            if ($embed) {
                $content .= $embed;
            }
            if ($text) {
                $content .= $text;
            }
            $content .= '</div>';
        }
    }unset($item);
    return $content;
}

function printPageCats($catList=false, $pageNum=1, $paginate=false, $pgAfter=15, $paginator='') {
    if (!$catList) {
        return '<!-- There are no categories associated with this page. -->';
    } else if (!is_array($catList)) {
        $catList = array($catList);
    }
    global $set;
    $content = '';
    foreach($catList AS $cat) {
        $id = $cat['ID'];
        if ($cat['Show_Header_Img'] && $cat['Header_Img_Path']) {
            $image = '<img src="'.$set['dir'].$cat['Header_Img_Path'].'" alt="'.$cat['Name'].' Header">';
         } else {
             $image = '';
         }
         if ($cat['Show_Title']) {
             $title = '<h2 class="cat-title">'.$cat['Name'].'</h2>';
         } else {
             $title = '';
         }
         $text = $cat['Text'];
        $class = className($cat['Name']);

        $itemList = getCatItems($cat['ID'],$pageNum,
                                $cat['Order_By'],$cat['Order_Dir'],
                                $paginate,$pgAfter);
        if ($itemList) {
            $items_content = printPageItems($itemList, $cat);
        } else {
            //if no items
            if ($paginate) {
                $items_content = "<div class='got-nothin'><i>There's nothing here!</i></div>";
            }
        }

        if ($cat['Format'] && file_exists('formats/category/'.$cat['Format'].'.php')) {
            ob_start();
            include 'formats/category/'.$cat['Format'].'.php';
            $content .= ob_get_clean();
        } else {
            $content .= '<section id="cat_'.$cat['ID'].'" class="category '.$class.'">';
            $content .= '<!-- No valid category format assigned. -->';
            $content .= $image.$title.$text;
            $content .= $items_content;
            $content .= '</section>';
        }
        
    }unset($cat);
    return $content;
}

function getMenu() {
    global $db;
    global $admin_panel;
    global $loggedIn;
    $conn = new SQLite3($db);
    $menu = array();
    $qry = "SELECT m.*, p.Name AS Page_Name, p.Link FROM Menu_Options AS m
            LEFT JOIN Pages AS p ON p.ID=m.Page_ID";
    if (!$admin_panel || !$loggedIn) {
        $qry .= " WHERE m.Hidden=0";
    }
    $qry .= " ORDER BY m.Index_Order;";
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $menu[] = $row;
    }
    return $menu;
}

function serializeMenu() {
    global $db;
    global $set;
    $conn = new SQLite3($db);
    $menu = array();
    $qry = "SELECT m.*, p.Name AS Page_Name, p.Link FROM Menu_Options AS m
            LEFT JOIN Pages AS p ON p.ID=m.Page_ID 
            WHERE m.Hidden=0 
            ORDER BY m.Index_Order;";
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if ($set['menu_format'] === 'Images') {
            $a = '<img src="'.$set['dir'].$row['Img_Path'].'" alt="'.$row['Page_Name'].'" title="'.$row['Page_Name'].'">';
        } else {
            $a = $row['Page_Name'];
        }
        if ($row['Outgoing_Link']) {
            $href= $row['Outgoing_Link'];
        } else {
            $href= $set['dir'].'/'.$row['Link'];
        }
        $menuLink = '<a href="'.$href.'">'.$a.'</a>';
        $menu[] = $menuLink;
    }
    return $menu;
}

function printPage($page=false,$pageNum=1) {
    global $db; global $set; 
    global $root;
    $conn = New SQLite3($db);
    // if no 'page', go to the home page
    if (!$page) {
        $pgQry = "SELECT p.*, COUNT(i.ID) AS Total_Items
        FROM Pages AS p
        LEFT JOIN Categories AS c ON p.ID=c.Page_ID
        LEFT JOIN Items AS i ON c.ID=i.Cat_ID
        WHERE p.ID =0 LIMIT 1;";
        $result = $conn->prepare($pgQry)->execute();
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $page = $row;
        }
    }
    if ($page['Paginate']==1 && $page['Multi_Cat']==0 &&
    ($page['Paginate_After']<$page['Total_Items'])) {
        $paginator = printPaginator($page['Paginate_After'], $pageNum, $page['Total_Items'], $page['Link']);
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
    $catList = getPageCats($page['ID']);
    $category_content = printPageCats($catList,$pageNum,$page['Paginate'],$page['Paginate_After'],$paginator);
    $content = '';
    $menu = serializeMenu();
    $themePath = $root.'/themes/'.$set['theme'];

    $admin_panel = false;
    include 'components/info-head.php';
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="'.$set['dir'].'/assets/css/CMSreset.css">
        <link rel="stylesheet" href="'.$set['dir'].'/themes/'.$set['theme'].'/theme.css">
        <title>'.$set['site_name'].': '.$page['Name'].'</title>
        <meta name="description" content="'.$page['Meta_Text'].'">
    </head>
    <body>';

    // not likely to be used on the client side, but just in case...
    if (isset($_SESSION['Msg'])) {
        echo $_SESSION['Msg'];
        unset($_SESSION['Msg']);
    } if (isset($msg)) {
        echo $msg;
    }
    // ***

    $header = $themePath.'/header.php';
    $footer = $themePath.'/footer.php';
    $siteMenuAuto = $root.'/components/site-menu-auto.php';
    $siteMenuManual = $root.'/components/site-menu-manual.php';

    if ($page['Format'] && file_exists($themePath.'/formats/page/'.$page['Format'].'.php')) {
        ob_start();
        include $themePath.'/formats/page/'.$page['Format'].'.php';
        $content .= ob_get_clean();
    } else {
        $content .= '<main id="page_'.$page['ID'].'" class="page '.$class.'">';
        if ($page['Name'] && $page['Show_Title'] > 0) {
            $content .= '<h1 class="page-title">'.$page['Name'].'</h1>';
        }
        $content .= '<!-- No valid page format assigned. -->';
        $content .= $category_content;
        $content .= '</main>';
    }

    echo $content;
    echo '</body>
    </html>';
}



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if (isset($loggedIn) && $loggedIn===true) {


function fetchSettings($arr=false) {
    global $db;
    $conn = new SQLite3($db);
    $settings = array();
    $qry = 'SELECT * FROM Settings';
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


if (isset($_POST['create_page'])) {
    global $db;
    global $set;
    $conn = new SQLite3($db);
    $cPost = cleanServerPost($_POST);
    $link = strtolower(preg_replace("/[^A-Za-z0-9-]/",'',str_replace(' ','-',$cPost['name'])));
    require_once 'imgUpload.php';
    $dir = '/assets/uploads/cat-headers/';
    if (!$set['has_max_img_dimns']) {$set['max_img_dimns'] = false;}
    if (!$set['has_max_upld_storage']) {$set['max_upld_storage'] = false;}
    if ($_FILES['header_img_upload']['name']) {
        //sanitize img name
        $_FILES['header_img_upload']['name'] = stripHTML($_FILES['header_img_upload']['name']);
        $_FILES['header_img_upload']['name'] = str_replace(" ","-",preg_replace("/[^A-Za-z0-9. \-_]/", '', $_FILES['header_img_upload']['name']));
        $imgPath = uploadImage ($dir, $_FILES['header_img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage']);
    } else {
        $imgPath = null;
    }
    $qry = 'INSERT INTO Pages (Name,Link,Meta_Text,Header_Img_Path,Show_Title,Show_Header_Img,Multi_Cat,Paginate,Paginate_After,Format,Hidden) 
    VALUES (:name,:link,:text,:img,:showtitle,:showimg,:multi,:pg,:pga,:format,:hide);';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':name',$cPost['name'], SQLITE3_TEXT);
    $stmt->bindValue(':link',$link, SQLITE3_TEXT);
    $stmt->bindValue(':text',$cPost['meta_text'], SQLITE3_TEXT);
    $stmt->bindValue(':img',$imgPath, SQLITE3_TEXT);
    $stmt->bindValue(':showtitle',$cPost['n_show_title'], SQLITE3_INTEGER);
    $stmt->bindValue(':showimg',$cPost['n_show_header_img'], SQLITE3_INTEGER);
    $stmt->bindValue(':multi',$cPost['n_multi_cat'], SQLITE3_INTEGER);
    $stmt->bindValue(':pg',$cPost['n_paginate'], SQLITE3_INTEGER);
    $stmt->bindValue(':pga',$cPost['n_paginate_after'], SQLITE3_INTEGER);
    $stmt->bindValue(':format',$cPost['format'], SQLITE3_TEXT);
    $stmt->bindValue(':hide',$cPost['n_hidden'], SQLITE3_INTEGER);
    if ($stmt->execute()) {
        $pageID = $conn->lastInsertRowID();
        $catQry = 'INSERT INTO Categories (Name,Page_ID,Page_Index_Order) 
            VALUES (?,?,1);';
        $catName= $cPost['name'].' Content';
        $catStmt = $conn->prepare($catQry);
        $catStmt->bindValue(1,$catName, SQLITE3_TEXT);
        $catStmt->bindValue(2,$pageID, SQLITE3_INTEGER);
        $catStmt->execute();

        // TODO: save menu img path to menu table
        if ($_FILES['menu_img_upload']['name']) {
            //sanitize img name
            $_FILES['menu_img_upload']['name'] = stripHTML($_FILES['menu_img_upload']['name']);
            $_FILES['menu_img_upload']['name'] = str_replace(" ","-",preg_replace("/[^A-Za-z0-9. \-_]/", '', $_FILES['menu_img_upload']['name']));
            $menuImgPath = uploadImage ($dir, $_FILES['menu_img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage']);
        } else {
            $menuImgPath = null;
        }
        $msg="New page created!";
    } else {
        $msg="Page creation failed. Please try again.";
    }
}

if (isset($_POST['edit_page'])) {
    global $db;
    global $set;
    $conn = new SQLite3($db);
    $cPost = cleanServerPost($_POST);
    $link = strtolower(preg_replace("/[^A-Za-z0-9-]/",'',str_replace(' ','-',$cPost['name'])));
    require_once 'imgUpload.php';
    $dir = '/assets/uploads/cat-headers/';
    if (!$set['has_max_img_dimns']) {$set['max_img_dimns'] = false;}
    if (!$set['has_max_upld_storage']) {$set['max_upld_storage'] = false;}
    if ($_FILES['header_img_upload']['name']) {
        //sanitize img name
        $_FILES['header_img_upload']['name'] = stripHTML($_FILES['header_img_upload']['name']);
        $_FILES['header_img_upload']['name'] = str_replace(" ","-",preg_replace("/[^A-Za-z0-9. \-_]/", '', $_FILES['header_img_upload']['name']));
        $imgPath = uploadImage ($dir, $_FILES['header_img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage']);
    } else {
        if ($cPost['stored_header_img']) {
            $imgPath = $cPost['stored_header_img'];
        } else {
            $imgPath = null;
        }
    }
    $qry = 'UPDATE Pages 
    SET Name=:name,Link=:link,Meta_Text=:text,Header_Img_Path=:img,Show_Title=:showtitle,Show_Header_Img=:showimg,Multi_Cat=:multi,Paginate=:pg,Paginate_After=:pga,Format=:format,Hidden=:hide
    WHERE ID=:id;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':name',$cPost['name'], SQLITE3_TEXT);
    $stmt->bindValue(':link',$link, SQLITE3_TEXT);
    $stmt->bindValue(':text',$cPost['meta_text'], SQLITE3_TEXT);
    $stmt->bindValue(':img',$imgPath, SQLITE3_TEXT);
    $stmt->bindValue(':showtitle',$cPost['n_show_title'], SQLITE3_INTEGER);
    $stmt->bindValue(':showimg',$cPost['n_show_header_img'], SQLITE3_INTEGER);
    $stmt->bindValue(':multi',$cPost['n_multi_cat'], SQLITE3_INTEGER);
    $stmt->bindValue(':pg',$cPost['n_paginate'], SQLITE3_INTEGER);
    $stmt->bindValue(':pga',$cPost['n_paginate_after'], SQLITE3_INTEGER);
    $stmt->bindValue(':format',$cPost['format'], SQLITE3_TEXT);
    $stmt->bindValue(':hide',$cPost['n_hidden'], SQLITE3_INTEGER);
    $stmt->bindValue(':id',$cPost['n_page_id'], SQLITE3_INTEGER);
    if ($stmt->execute()) {

        // TODO: save menu img path to menu table
        if ($_FILES['menu_img_upload']['name']) {
            //sanitize img name
            $_FILES['menu_img_upload']['name'] = stripHTML($_FILES['menu_img_upload']['name']);
            $_FILES['menu_img_upload']['name'] = str_replace(" ","-",preg_replace("/[^A-Za-z0-9. \-_]/", '', $_FILES['menu_img_upload']['name']));
            $menuImgPath = uploadImage ($dir, $_FILES['menu_img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage']);
        } else {
            if ($cPost['stored_menu_img']) {
                $menuImgPath = $cPost['stored_menu_img'];
            } else {
                $menuImgPath = null;
            }
        }

        $msg="Changes to '".$cPost['name']."' have been saved!";
    } else {
        $msg="Changes failed to save. Please try again.";
    }
}


if (isset($_POST['delete_page'])) {
    global $db;
    $conn = new SQLite3($db);
    $pageID = filter_var($_POST['n_page_id'], FILTER_SANITIZE_NUMBER_INT);
    if ($pageID==0) {
        $msg="You cannot delete your homepage.";
        return;
    }
    $moveCatQry = 'UPDATE Categories SET Page_ID=null WHERE Page_ID=?;';
    $catStmt = $conn->prepare($moveCatQry);
    $catStmt->bindValue(1,$pageID, SQLITE3_INTEGER);
    if ($catStmt->execute()) {
        $qry = 'DELETE FROM Pages WHERE ID=?;';
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(1,$pageID, SQLITE3_INTEGER);
        if ($stmt->execute()) {
            $msg="Page deleted!";
        } else {
            $msg="Page failed to delete. Please try again.";
        }
    } else {
        $msg="Page failed to delete. Please try again.";
    }
}


if (isset($_POST['create_category'])) {
    global $db;
    global $set;
    $conn = new SQLite3($db);
    $cPost = cleanServerPost($_POST);
    require_once 'imgUpload.php';
    $dir = '/assets/uploads/cat-headers/';
    if (!$set['has_max_img_dimns']) {$set['max_img_dimns'] = false;}
    if (!$set['has_max_upld_storage']) {$set['max_upld_storage'] = false;}
    if ($_FILES['header_img_upload']['name']) {
        //sanitize img name
        $_FILES['header_img_upload']['name'] = stripHTML($_FILES['header_img_upload']['name']);
        $_FILES['header_img_upload']['name'] = str_replace(" ","-",preg_replace("/[^A-Za-z0-9. \-_]/", '', $_FILES['img_upload']['name']));
        $imgPath = uploadImage ($dir, $_FILES['header_img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage']);
    }
    $qry = 'INSERT INTO Categories (Name,Page_ID,Text,Header_Img_Path,Show_Title,Show_Header_Img,Show_Item_Images,Show_Item_Titles,Show_Item_Text,Order_By,Auto_Thumbs,Thumb_Size,Thumb_Size_Axis,Format,Hidden) 
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1,$cPost['name'], SQLITE3_TEXT);
    $stmt->bindValue(2,$cPost['n_page_id'], SQLITE3_INTEGER);
    $stmt->bindValue(3,$cPost['b_text'], SQLITE3_TEXT);
    $stmt->bindValue(4,$imgPath, SQLITE3_TEXT);
    $stmt->bindValue(5,$cPost['n_show_title'], SQLITE3_INTEGER);
    $stmt->bindValue(6,$cPost['n_show_header_img'], SQLITE3_INTEGER);
    $stmt->bindValue(7,$cPost['n_show_images'], SQLITE3_INTEGER);
    $stmt->bindValue(8,$cPost['n_show_titles'], SQLITE3_INTEGER);
    $stmt->bindValue(9,$cPost['n_show_text'], SQLITE3_INTEGER);
    $stmt->bindValue(10,$cPost['order_by'], SQLITE3_TEXT);
    $stmt->bindValue(11,$cPost['n_create_thumbs'], SQLITE3_INTEGER);
    $stmt->bindValue(12,$cPost['n_thumb_size'], SQLITE3_INTEGER);
    $stmt->bindValue(13,$cPost['n_thumb_axis'], SQLITE3_INTEGER);
    $stmt->bindValue(14,$cPost['format'], SQLITE3_TEXT);
    $stmt->bindValue(15,$cPost['n_hidden'], SQLITE3_INTEGER);
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
    if (!$set['has_max_upld_storage']) {$set['max_upld_storage'] = false;}
    if ($_FILES['header_img_upload']['name']) {
        require_once 'imgUpload.php';
        //sanitize img name
        $_FILES['header_img_upload']['name'] = stripHTML($_FILES['header_img_upload']['name']);
        $_FILES['header_img_upload']['name'] = str_replace(" ","-",preg_replace("/[^A-Za-z0-9. \-_]/", '', $_FILES['header_img_upload']['name']));
        if (!$cPost['header_img_stored']) {
            $cPost['header_img_stored'] = false;
        }
        $imgPath = uploadImage ($dir, $_FILES['header_img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage'], $cPost['header_img_stored']);
    } else if ($cPost['header_img_stored']) {
        $imgPath = $cPost['header_img_stored'];
    } else {
        $imgPath = null;
    }
    $qry = 'UPDATE Categories SET 
        Name=?,Text=?,
        Header_Img_Path=?,Show_Title=?,Show_Header_Img=?,Show_Item_Images=?,
        Show_Item_Titles=?,Show_Item_Text=?,Order_By=?,Auto_Thumbs=?,
        Thumb_Size=?,Thumb_Size_Axis=?,Format=?,Hidden=?,Page_ID=?
    WHERE ID=?;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1,$cPost['name'], SQLITE3_TEXT);
    $stmt->bindValue(2,$cPost['b_text'], SQLITE3_TEXT);
    $stmt->bindValue(3,$imgPath, SQLITE3_TEXT);
    $stmt->bindValue(4,$cPost['n_show_title'], SQLITE3_INTEGER);
    $stmt->bindValue(5,$cPost['n_show_header_img'], SQLITE3_INTEGER);
    $stmt->bindValue(6,$cPost['n_show_images'], SQLITE3_INTEGER);
    $stmt->bindValue(7,$cPost['n_show_titles'], SQLITE3_INTEGER);
    $stmt->bindValue(8,$cPost['n_show_text'], SQLITE3_INTEGER);
    $stmt->bindValue(9,$cPost['order_by'], SQLITE3_TEXT);
    $stmt->bindValue(10,$cPost['n_create_thumbs'], SQLITE3_INTEGER);
    $stmt->bindValue(11,$cPost['n_thumb_size'], SQLITE3_INTEGER);
    $stmt->bindValue(12,$cPost['n_thumb_axis'], SQLITE3_INTEGER);
    $stmt->bindValue(13,$cPost['format'], SQLITE3_TEXT);
    $stmt->bindValue(14,$cPost['n_hidden'], SQLITE3_INTEGER);
    $stmt->bindValue(15,$cPost['n_page_id'], SQLITE3_INTEGER);
    $stmt->bindValue(16,$cPost['n_cat_id'], SQLITE3_INTEGER);
    if ($stmt->execute()) {
        $msg="Category setting changes saved!";
    } else {
        $msg="Changes failed to save. Please try again.";
    }
}

if (isset($_POST['delete_category'])) {
    global $db;
    $conn = new SQLite3($db);
    $catID = filter_var($_POST['n_cat_id'], FILTER_SANITIZE_NUMBER_INT);
    if ($pageID==0) {
        $msg="You cannot delete a non-category.";
        return;
    }
    $moveItemsQry = 'UPDATE Items SET Cat_ID=0 WHERE Cat_ID=?;';
    $itemStmt = $conn->prepare($moveItemsQry);
    $itemStmt->bindValue(1,$catID, SQLITE3_INTEGER);
    if ($itemStmt->execute()) {
        $qry = 'DELETE FROM Categories WHERE ID=?;';
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(1,$catID, SQLITE3_INTEGER);
        if ($stmt->execute()) {
            $msg="Category deleted!";
        } else {
            $msg="Category failed to delete. Please try again.";
        }
    } else {
        $msg="Category failed to delete. Please try again.";
    }
}


if (isset($_POST['create_item'])) {
    global $db;
    global $set;
    $conn = new SQLite3($db);
    $cPost = cleanServerPost($_POST);
    $type = $cPost['type'];
    $dir = '/assets/uploads/items/';
    if (!$set['has_max_img_dimns']) {$set['max_img_dimns'] = false;}
    if (!$set['has_max_upld_storage']) {$set['max_upld_storage'] = false;}
    if ($type==='Image' && $_FILES['img_upload']['name']) {
        require_once 'imgUpload.php';
        //sanitize img name
        $_FILES['img_upload']['name'] = stripHTML($_FILES['img_upload']['name']);
        $_FILES['img_upload']['name'] = str_replace(" ","-",preg_replace("/[^A-Za-z0-9. \-_]/", '', $_FILES['img_upload']['name']));
        $imgName = str_replace(pathinfo($_FILES['img_upload']['name'],PATHINFO_EXTENSION),"",$_FILES['img_upload']['name']);
        $imgPath = uploadImage ($dir, $_FILES['img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage']);
        if (!$imgPath) {
            $msg = 'Image upload failed. Please try again.';
        }
        if ($_FILES['thumb_upload']['name']) {
            $imgThumbPath = uploadImage ($dir, $_FILES['thumb_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, $imgName.'_thumb', $set['max_upld_storage']);
            if (!$imgThumbPath) {
                $msg = 'Thumbnail image upload failed. Please try again.';
            }
        } else if ($cPost['create_thumbnail']) {
            $newH = false;
            $newW = $cPost['n_thumb_size'];
            if ($cPost['n_thumb_size_axis'] === 1) {
                $newH = $cPost['n_thumb_size'];
                $newW = false;
            }
            $imgThumbPath = mkThumb($dir, $_FILES['img_upload']['name'], $imgPath, $newW, $newH);
        } else {
            $imgThumbPath = null;
        }
    }
    if ($cPost['publish_datetime']) {
        $pdtUnix = strtotime($cPost['publish_datetime']);
    } else {
        $pdtUnix = time();
    }
    $qry = "INSERT INTO Items (Cat_ID,Title,";
    if ($type==='Image') {
        $qry .= "Type,Img_Path,";
    } elseif ($type==='Embed') {
        $qry .= "Type,Embed_HTML,";
    }
    $qry .= "Img_Thumb_Path,Text,Publish_Timestamp,Hidden,Format)
    VALUES (:catid,:title,";
    if ($type==='Image') {
        $qry .= ":type,:img,";
    } elseif ($type==='Embed') {
        $qry .= ":type,:embed,";
    }
    $qry .= ":imgthumb,:text,:ts,:hide,:format);";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':catid',$cPost['n_cat_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':title',$cPost['title'], SQLITE3_TEXT);
    if ($type==='Image') {
        $stmt->bindValue(':type', $type, SQLITE3_TEXT);
        $stmt->bindValue(':img',$imgPath, SQLITE3_TEXT);
    } elseif ($type==='Embed') {
        $stmt->bindValue(':type', $type, SQLITE3_TEXT);
        $stmt->bindValue(':embed',$cPost['b_embed'], SQLITE3_TEXT);
    }
    $stmt->bindValue(':imgthumb',$imgThumbPath, SQLITE3_TEXT);
    $stmt->bindValue(':text',$cPost['b_text'], SQLITE3_TEXT);
    $stmt->bindValue(':ts',$pdtUnix, SQLITE3_INTEGER);
    $stmt->bindValue(':hide',$cPost['n_hidden'], SQLITE3_INTEGER);
    $stmt->bindValue(':format',$cPost['format'], SQLITE3_TEXT);
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
    $type = $cPost['type'];
    $dir = '/assets/uploads/items/';
    $msg ="";
    if (!$set['has_max_img_dimns']) {$set['max_img_dimns'] = false;}
    if (!$set['has_max_upld_storage']) {$set['max_upld_storage'] = false;}
    require_once 'imgUpload.php';
    $imgName = str_replace($dir, "", str_replace('.'.pathinfo($cPost['img_stored'],PATHINFO_EXTENSION),"",$cPost['img_stored']));
    if ($type==='Image' && $_FILES['img_upload']['name']) {
        //sanitize img name
        $_FILES['img_upload']['name'] = stripHTML($_FILES['img_upload']['name']);
        $_FILES['img_upload']['name'] = str_replace(" ","-",preg_replace("/[^A-Za-z0-9. \-_]/", '', $_FILES['img_upload']['name']));
        $imgName = str_replace(pathinfo($_FILES['img_upload']['name'],PATHINFO_EXTENSION),"",$_FILES['img_upload']['name']);
        if (!$cPost['img_stored']) {
            $cPost['img_stored'] = false;
        } else {
            $cPost['img_stored'] = stripHTML($cPost['img_stored']);
        }
        $imgPath = uploadImage ($dir, $_FILES['img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage'], $cPost['img_stored']);
        if (!$imgPath) {
            $msg .= 'Image upload failed. Please try again.';
            return;
        }
    } else {
        if ($cPost['img_stored']) {
            $imgPath = $cPost['img_stored'];
        } else {
            $imgPath = null;
        }
    }
    if ($_FILES['thumb_upload']['name'] || $cPost['create_thumbnail']) {
        if ($_FILES['thumb_upload']['name']) {
            $imgThumbPath = uploadImage ($dir, $_FILES['thumb_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, $imgName.'_thumb', $set['max_upld_storage'],$cPost['thumb_stored']);
            if (!$imgThumbPath) {
                $msg .= 'Thumbnail image upload failed. Please try again.';
                return;
            }
        } else {
            if ($_FILES['img_upload']['name']) {
                $oriImg = $_FILES['img_upload']['name'];
            } else {
                $oriImg = $cPost['img_stored'];
            }
            $newH = false;
            $newW = $cPost['n_thumb_size'];
            if ($cPost['n_thumb_size_axis'] === 1) {
                $newH = $cPost['n_thumb_size'];
                $newW = false;
            }
            $imgThumbPath = mkThumb($dir, $oriImg, $imgPath, $newW, $newH);
        }
    } else {
        if (isset($cPost['thumb_stored'])) {
            $imgThumbPath = $cPost['thumb_stored'];
        } else {
            $imgThumbPath = null;
        }
    }
    if ($cPost['publish_datetime']) {
        $pdtUnix = strtotime($cPost['publish_datetime']);
    } else {
        $pdtUnix = time();
    }
    $qry = "UPDATE Items 
            SET Cat_ID=:catid,Title=:title,";
    if ($type==='Image') {
        $qry .="Type=:type,Img_Path=:img,";
    } elseif($type==='Embed') {
        $qry .="Type=:type,Embed_HTML=:embed,";
    }
    $qry .= "Img_Thumb_Path=:imgthumb,Text=:text,Publish_Timestamp=:ts,Format=:format,Hidden=:hide
            WHERE ID=:id;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':catid',$cPost['n_cat_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':title',$cPost['title'], SQLITE3_TEXT);
    if ($type==='Image') {
        $stmt->bindValue(':type', $type, SQLITE3_TEXT);
        $stmt->bindValue(':img',$imgPath, SQLITE3_TEXT);
    } elseif ($type==='Embed') {
        $stmt->bindValue(':type', $type, SQLITE3_TEXT);
        $stmt->bindValue(':embed',$cPost['b_embed'], SQLITE3_TEXT);
    }
    $stmt->bindValue(':imgthumb',$imgThumbPath, SQLITE3_TEXT);
    $stmt->bindValue(':text',$cPost['b_text'], SQLITE3_TEXT);
    $stmt->bindValue(':ts',$pdtUnix, SQLITE3_INTEGER);
    $stmt->bindValue(':hide',$cPost['n_hidden'], SQLITE3_INTEGER);
    $stmt->bindValue(':format',$cPost['format'], SQLITE3_TEXT);
    $stmt->bindValue(':id',$cPost['n_item_id'], SQLITE3_INTEGER);
    if ($stmt->execute()) {
        $msg.="Changes saved!";
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

if (isset($_POST['save_menu'])) {
    global $db;
    $conn = new SQLite3($db);
    $msg="";
    usort($_POST["option"], function ($a, $b) {
        return $a['n_index'] <=> $b['n_index'];
    });
    $indexOrder=0;
    $error = false;
    $qry = "UPDATE Menu_Options 
        SET  Index_Order=:inorder, Outgoing_Link=:link, In_Dropdown=:indrop, Hidden=:hidden
            WHERE Page_ID=:pageid";
    foreach ($_POST["option"] AS &$opt) {
        if ($error) {
            $msg.=" There was an error saving your menu changes. Please try again.";
            return;
        }
        $cPost = cleanServerPost($opt);
        if (!isset($cPost['n_dropdown'])) {
            $cPost['n_dropdown'] = 0;
        }
        $indexOrder = ($indexOrder+1);
        $cPost['n_index'] = $indexOrder;
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(':inorder',$cPost['n_index'], SQLITE3_INTEGER);
        $stmt->bindValue(':link',$cPost['link'], SQLITE3_TEXT);
        $stmt->bindValue(':indrop',$cPost['n_dropdown'], SQLITE3_INTEGER);
        $stmt->bindValue(':hidden',$cPost['n_hidden'], SQLITE3_INTEGER);
        $stmt->bindValue(':pageid',$cPost['n_page_id'], SQLITE3_INTEGER);
        if (!$stmt->execute()) {
            $error = true;
        }
    }
    if (!$error) {
        $msg.="Changes saved!";
    } else {
        $msg.=" There was an error saving your menu changes. Please try again.";
    }
}

} //end 'if $loggedIn'