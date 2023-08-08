<?php
if (file_exists(dirname(__FILE__).'/_testing.inc.php')) {
    include(dirname(__FILE__).'/_testing.inc.php');
}

$db = dirname(__FILE__).'/../data/database.db';

function serializeSettings() {
    global $db;
    $conn = new SQLite3($db);
    $settings = array();
    $qry = 'SELECT `Key`, `Value`, `Type` FROM `Settings`;';
    $stmt = $conn->prepare($qry);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        switch ($row['Type']) {
            case 'checkbox':
                if ($row['Value']==="checked") {
                    $row['Value'] = true;
                } else {
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

function show($input=null, $altInput=null) {
    if (is_array($input)) {
        $input= implode(', ', $input);
    } if (is_array($altInput)) {
        $altInput= implode(', ', $altInput);
    }
    if ($input===true) {
        $input='true';
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

// deals with non-html text
function stripHTML($str, $incov=false, $nl2br=false, $trim=true){
    if ($nl2br) {$str = nl2br($str);}
    if ($incov) {$str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);}
    $str = htmlspecialchars($str);
    if ($trim) {$str = trim($str);}
    return $str;
}

// removes HTML tags and their contents
// suggested by https://www.php.net/manual/en/function.strip-tags.php
function destroyHTML($str, $tags='<a><p><b><i><strong><em><u><del><span>', $invert=false, $destroyEmptyLine=false) {
    preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
    $tags = array_unique($tags[1]);
    if ((!$invert ? !in_array('img', $tags) : in_array('img', $tags))) {
        $str = preg_replace("/<img[^>]+\>/i", "", $str); 
    }
    if ((!$invert ? !in_array('input', $tags) : in_array('input', $tags))) {
        $str = preg_replace("/<input[^>]+\>/i", "", $str); 
    }
    if ((!$invert ? !in_array('br', $tags) : in_array('br', $tags))) {
        $str = preg_replace("/<b[^>]+\>/i", "\n", $str); 
    }
    if(is_array($tags) && count($tags) > 0) {
      if($invert == FALSE) {
        return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $str);
      }
      else {
        return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $str);
      }
    }
    elseif($invert == false) {
      return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $str);
    }
    return $str;
  }

  //from https://gist.github.com/JayWood/348752b568ecd63ae5ce
  function closeHTML($html) {
    preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
    $openedtags = $result[1];
    preg_match_all('#</([a-z]+)>#iU', $html, $result);

    $closedtags = $result[1];
    $len_opened = count($openedtags);

    if (count($closedtags) == $len_opened) {
        return $html;
    }
    $openedtags = array_reverse($openedtags);
    for ($i=0; $i < $len_opened; $i++) {
        if (!in_array($openedtags[$i], $closedtags)) {
            $html .= '</'.$openedtags[$i].'>';
        } else {
            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
    }
    return $html;
}

function truncateTxt($txt,$at=140,$trail="...") {
    if ($txt) {
        $txt = destroyHTML($txt);
        $txt = preg_replace("/<p[^>]*><\\/p[^>]*>/", '', $txt); 
        if (strlen($txt)>$at) {
            $cutOff = ($at - strlen($trail));
            $txt = substr($txt,0,$cutOff).$trail;
        }
        return $txt;
    }
    else {
        return null;
    }
}

function cleanRequest($rmvQry=true) {
    global $set;
    $request = $_SERVER['REQUEST_URI'];
    if (!$request) {
        return false;
    }
    if ($set['dir']>'') {
        $request = str_replace($set['dir'],'',$request);
    }
    $reqLen = strlen($request);
    if (substr($request,0,1)==='/') {
        $request = substr($request,1);;
    }
    if (!$request) {
        return false;
    }
    $slashPos = strpos($request,'/');
    if ($slashPos!=false) {
        $request = substr($request,0,$slashPos);
    }
    if ($rmvQry) {
        $qryPos=strpos($request,'?');
        if ($qryPos!=false) {
            $request = substr($request,0,$qryPos);
        }
    }
    if ($request) {
        return strtolower($request);
    } else {
        return false;
    }
}

function cleanInt($num, $abs=false){
    $num = intval(filter_var($num, FILTER_SANITIZE_NUMBER_INT));
    if ($abs) {
        return abs($num);
    } else {
        return $num;
    }
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

function paginateStrt ($pageNum, $itemsPerPage=null) {
    if (!$itemsPerPage) {
        return 1;
    } else {
        $lsStrtMultiple = (int)$pageNum - 1;
        $lsStrt = $lsStrtMultiple * $itemsPerPage;
        return $lsStrt;
    }
}

function strDecode($str=null, $mode='specialChars') {
    if ($str) {
        switch ($mode) {
            case 'entities' : 
            case 'entity' : 
                return html_entity_decode($str);
                break;
            case 'url' :
                return urldecode($str);
                break;
            case 'specialChars' :
            default : 
            return htmlspecialchars_decode($str);
            break;
        }
    } else {
        return null;
    }
}

//email headers for no-reply email
$emailHeaders = 
            "MIME-Version: 1.0".PHP_EOL.
            "Content-Transfer-Encoding: 8bit".PHP_EOL.
            "Content-type: text/html; charset=iso-8859-1".PHP_EOL.
            "X-Priority: 3".PHP_EOL.
            "X-Mailer: PHP". phpversion() .PHP_EOL;


// automatically add paragraphs/breaks
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
                    $val = closeHTML(nl2p($val));
                    break;
                case 'b_': // blocks/code
                    $val = stripHTML($val, true);
                    break;
                default:
                    if (is_string($val)) {
                        $val = stripHTML($val);
                    } else if (is_array($val)) {
                        $val = cleanServerPost($val);
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

function prepTags($tags, $input=true, $clean=false) {
    $output = ($input ? false : true);
    $cTags = array();
    if (!is_null($tags)) {
        if (!is_array($tags)) {
            $tags = explode(',',(string)$tags);
        }
        foreach ($tags AS &$tag) {
            $tag = trim($tag);
            if ($output && $clean) {
                strDecode($tag);
            } else if ($clean) {
                htmlspecialchars($tag);
            }
            if ($tag>'') {
                $cTags[] = $tag;
            }
        }unset($tag);
        if ($input) {
            $cTags = implode(',',$cTags);
        }
        return $cTags;
    }
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

function getPage($page, $key="id", $tag=null){
    global $db;
    global $admin_panel;
    global $loggedIn;
    global $set;
    $iWhere = ($admin_panel===false ? ' WHERE Hidden=0 ' : '');
    $iID=1;
    $conn = New SQLite3($db);
    if ($key === 'name') {
        $page = stripHTML($page);
        $where = "p.`Name`=?";
    } elseif ($key === 'link') {
        $page = stripHTML($page);
        $where = "p.`Link`=?";
    } else {
        $key = "id";
        $page = cleanInt($page);
        $where = "p.`ID`=?";
    }
    if ($tag) {
        $tag = stripHTML($tag);
        if ($tag) {
            $tag = '%,'.strtoupper($tag).',%';
            if ($iWhere) {
                $iWhere .= "AND";
            } else {
                $iWhere .= " WHERE";
            }
            $iWhere .= " UPPER(`Tags`) LIKE ? ESCAPE '\' ";
            $iID=2;
        }
    }
    $qry = "SELECT p.*, 
            m.Img_Path AS Menu_Link_Img, m.Hidden AS Menu_Hidden, 
            COUNT(i.ID) AS Total_Items
    FROM Pages AS p
    LEFT JOIN Sections AS s ON p.ID=s.Page_ID
    LEFT JOIN Automenu AS m ON p.ID=m.Ref_ID
    LEFT JOIN  (SELECT ID, Sect_ID FROM Items".$iWhere.") AS i ON s.ID=i.Sect_ID
    WHERE ".$where.($admin_panel===false ? " AND p.Hidden=0" : '')." COLLATE NOCASE LIMIT 1;";
    $stmt = $conn->prepare($qry);
    if ($tag) {
        $stmt->bindValue(1, $tag, SQLITE3_TEXT);
    }
    $stmt->bindValue($iID, $page, SQLITE3_TEXT);
    $result = $stmt->execute();
    $arr = array();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        // if ($row['Header_Img_Path']>'') {
        //     $row['Header_Img_Path'] = $set['dir'].$row['Header_Img_Path'];
        // }
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

function getSectList($pageID=false, $excludeRefSects=false) {
    global $db;
    global $admin_panel;
    global $loggedIn;
    $conn = new SQLite3($db);
    $sectList = array();
    $qry = 'SELECT s.ID, s.Page_ID, s.Name, s.Is_Reference,
    r.Ref_Sect_IDs,
    p.Name AS Page_Name, p.Link, s.Hidden 
    FROM Sections AS s 
    LEFT JOIN Reference_Sections AS r ON s.ID=r.Sect_ID
    LEFT JOIN Pages AS p ON p.ID=s.Page_ID 
    WHERE s.ID>0 ';
    if ($pageID) {
        $pageID = filter_var($pageID, FILTER_SANITIZE_NUMBER_INT);
        $qry .= ' AND s.Page_ID='.$pageID;
    }
    if ($excludeRefSects) {
        $qry .= ' AND s.Is_Reference=0';
    }
    if (!($admin_panel && $loggedIn)) {
        $qry .= ' AND s.Hidden=0';
    }
    $qry .= ' ORDER BY s.Page_ID, s.Name;';
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Ref_Count'] = 0;
        if (!is_null($row['Ref_Sect_IDs'])) {
            if (is_string($row['Ref_Sect_IDs'])) {
                $row['Ref_Sect_IDs'] = explode(',',$row['Ref_Sect_IDs']);
                $row['Ref_Count'] = count($row['Ref_Sect_IDs']);
            } elseif (is_numeric($row['Ref_Sect_IDs'])) {
                $row['Ref_Sect_IDs'] = array($row['Ref_Sect_IDs']);
                $row['Ref_Count'] = 1;
            } else {
                $row['Ref_Sect_IDs'] = array();
            }
        }
        $row['Ref_Sects'] = array();
        $sectList[]=$row;
    } 
    foreach ($sectList AS &$sect) {
        if (is_array($sect['Ref_Sect_IDs']) && $sect['Ref_Count']>0) {
            for ($i=0;$i<$sect['Ref_Count'];$i++) {
                foreach ($sectList AS $sect2) {
                    if ($sect2['ID']==$sect['Ref_Sect_IDs'][$i]) {
                        $name=$sect2['Name'];
                        break;
                    }
                }
                if (isset($name)) {
                    $sect['Ref_Sects'][] = array(
                        "ID"=>$sect['Ref_Sect_IDs'][$i],
                        "Name"=>$name);
                }
            }
        }
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
    $qry = 'SELECT s.*, COUNT(i.Sect_ID) AS Total_Items,
        r.Ref_Sect_IDs, r.Item_Limit, 
        r.Date_Cutoff_On, r.Date_Cutoff, r.Date_Cutoff_Dir,
        r.Tag_Filter_On, r.Tag_Filter_List, r.Tag_Filter_Mode
        FROM `Sections` AS s
        LEFT JOIN `Reference_Sections` AS r ON s.ID=r.Sect_ID
        LEFT JOIN (SELECT Sect_ID FROM Items';    
        if (!($admin_panel && $loggedIn)) {
            $qry .= ' WHERE Hidden=0';
        }
        $qry .=') AS i ON s.ID=i.Sect_ID
        WHERE s.Page_ID = :id';
        if (!($admin_panel && $loggedIn)) {
            $qry .= ' AND s.Hidden=0';
        }
        $qry .=' GROUP BY s.ID
        ORDER BY s.Page_Index_Order;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':id', $pageID, SQLITE3_INTEGER);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Text'] = strDecode($row['Text']);
        $row['Item_Click_Area'] = explode(',',$row['Item_Click_Area']);
        $sectList[]= $row;
    } 
    $stmt->close();
    return $sectList;
}

function getSectInfo($id) {
    global $db;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $conn = new SQLite3($db);
    $qry = 'SELECT s.*, 
    r.Ref_Sect_IDs, r.Item_Limit, r.Date_Cutoff_On, r.Date_Cutoff, r.Date_Cutoff_Dir,
        r.Tag_Filter_On, r.Tag_Filter_List, r.Tag_Filter_Mode,
    p.ID AS Page_ID, p.Name AS Page_Name, p.Link AS Page_Link, p.Hidden as Page_Hidden
    FROM Sections AS s
    LEFT JOIN Reference_Sections AS r ON s.ID=r.Sect_ID
    LEFT JOIN Pages AS p ON s.Page_ID=p.ID
    WHERE s.ID = :id LIMIT 1;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Text'] = strDecode($row['Text']);
        $row['Date_Cutoff_Mode'] = (substr($row['Date_Cutoff'],0,3)==='[2]' ? 2 : 1);
        $row['Date_Cutoff'] = substr($row['Date_Cutoff'],3);
        if ($row['Date_Cutoff_Mode']===2) {
            $dateAgo = explode(' ',$row['Date_Cutoff']);
            $row['Date_Number'] = $dateAgo[0];
            $row['Date_Unit'] = $dateAgo[1];
            $row['Date_Cutoff'] = date('Y-m-d',strtotime('-'.$row['Date_Cutoff']));
        } else {
            $row['Date_Number'] = null;
            $row['Date_Unit'] = null;
        }
        $row['Item_Click_Area'] = explode(',',$row['Item_Click_Area']);
        $stmt->close();
        return $row;
    } 
}

function getRefSectsInfo($ids, $pageInfo=false) {
    global $db;
    $sects = array();
    if (is_string($ids)){
        $ids = explode(',',$ids);
    } elseif (!is_array($ids)) {
        $ids = array($ids);
    }
    $idsLen = count($ids);
    for ($i=0;$i<$idsLen;$i++) {
        $ids[$i] = filter_var($ids[$i], FILTER_SANITIZE_NUMBER_INT);
    }
    $conn = new SQLite3($db);
    $qry = 'SELECT s.`ID`, s.`Name`, s.`Page_ID`'.($pageInfo ? ', p.`Name` AS Page_Name, p.`Link`' : null).' FROM `Sections` AS s ';
    if ($pageInfo) {
        $qry .= 'LEFT JOIN `Pages` AS p ON s.`Page_ID`=p.`ID` ';
    }
    $qry .= ' WHERE ';
    for ($i=0;$i<$idsLen;$i++) {
        $qry .= ' `ID` = ? ';
        if (($i+1)<$idsLen) {
            $qry .='OR';
        }
    }
    $stmt = $conn->prepare($qry);
    for ($i=0;$i<$idsLen;$i++) {
        $stmt->bindValue(($i+1), $ids[$i], SQLITE3_INTEGER);
    }
    $result = $stmt->execute();
    if ($result) {
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $sects[] = $row;
        } 
    } else {
        return false;
    }
    $stmt->close();
    return $sects;
}

function itemQryOrder($orderBy="Date", $orderDir=false, $table=null) {
    if ($table) {
        $table .= ".";
    }
    switch ($orderBy) {
        case 'Custom':
            $orderBy = 'Sect_Index_Order';
            break;
        case 'Random':
            $orderBy = 'RANDOM()';
            break;
        case 'ID':
        case 'Title':
            // do nothing
            break;
        case 'Date':
        default:
            $orderBy = 'Publish_Timestamp';
            break;
    }
    if ($orderBy === 'RANDOM()') {
        $orderDir = "";
    } else {
        if ($orderDir==1 || $orderDir==='DESC') {
            $orderDir = ' DESC';
        } else {
            $orderDir = ' ASC';
        }
    }
    $qryStr = " ORDER BY ".$table.$orderBy.$orderDir;
    return $qryStr;
}

function findItemInPages($id, $pgAfter=null, $orderBy='Date', $orderDir=false, $moreInfo=false) {
    if ((!$pgAfter && !$moreInfo) || $id=='New') {
        return 1;
    }
    global $db;global $admin_panel;global $loggedIn;
    $conn = new SQLite3($db);
    $qryOrderBy = itemQryOrder($orderBy,$orderDir);
    $qry = "SELECT *
    FROM (
      SELECT i.*,";
      if ($admin_panel && $moreInfo) {
        $qry .= " CASE
        WHEN i.Publish_Timestamp>=strftime('%s','now')
        THEN 1
        ELSE 0
        END AS Queued, ";
    }
      " ROW_NUMBER() OVER (
      PARTITION BY i.Sect_ID, (i.ID=:id OR i.Hidden=0)
      ".$qryOrderBy.") as Position";
    if ($moreInfo) {
        $qry .= ", COUNT() OVER (
            PARTITION BY i.Sect_ID, i.Hidden=0) as Total_Items,
            s.Page_ID";
    }
    if ((!$loggedIn || !$admin_panel) || $moreInfo) {
        $qry .= ", i.Hidden AS Item_Hidden, 
        s.Hidden AS Sect_Hidden, p.Hidden AS Page_Hidden";
    }
      $qry .= " FROM Items AS i ";
    if ((!$loggedIn || !$admin_panel) || $moreInfo) {
        $qry .= "LEFT JOIN Sections AS s ON s.ID = i.Sect_ID
      LEFT JOIN Pages AS p ON s.Page_ID = p.ID";
    }
    $qry .= ") WHERE ID = :id";
    if (!$loggedIn || !$admin_panel) {
        $qry .= " AND Item_Hidden=0 AND Sect_Hidden=0 AND Page_Hidden=0 AND i.Publish_Timestamp<=strftime('%s','now')";
    }
    $qry .= " LIMIT 1;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    while($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $position = intval($row['Position']);
        $pageNum = ($pgAfter ? ceil($position / $pgAfter) : 1);
        if ($moreInfo) {
            $itemInfo = ["ID" => $row['ID'], "Title" => $row['Title'], "Sect_ID" => $row['Sect_ID'], "Position" => $row['Position'], 
            "Sect_Total_Items" => $row['Total_Items'], "Page_Num" => $pageNum, "Order_By" => $orderBy, "Order_Dir" => ($orderDir ?? 0), 
            "Queued" => $row["Queued"], "Hidden" => $row["Item_Hidden"], "Sect_Hidden" => $row["Sect_Hidden"], "Page_Hidden" => $row["Page_Hidden"]];
        } else {
            $itemInfo = $pageNum;
        }
    }
    return $itemInfo;
}

function getSectItems($sect, $pageNum=1, $pgAfter=null, $tag=null) {
    global $db;global $set;global $admin_panel;global $loggedIn;
    $tagFilter=$tagIndex=false;
    if (!is_array($sect)) {
        $sectID = (int)$sect;
        $isRef= 0;
        $orderBy = 'Date';
        $orderDir = 1;
    } else {
        $sectID = $sect['ID'];
        $isRef=$sect['Is_Reference'];
        $orderBy = $sect['Order_By'];
        $orderDir = $sect['Order_Dir'];
    }
    if ($tag && is_string($tag)) {
        $tagIndex=true;
        $sect['Tag_Filter_List'] = array(stripHTML($tag));
        $sect['Tag_Filter_On']=1;
        $sect['Tag_Filter_Mode']=2;
    }
    $conn = new SQLite3($db);
    $items = array();
    if ($isRef) {
        if ($sect['Ref_Sect_IDs'] && !is_array($sect['Ref_Sect_IDs']) && is_string($sect['Ref_Sect_IDs'])) {
            $ids = explode(',',$sect['Ref_Sect_IDs']);
        } elseif (is_array($sect['Ref_Sect_IDs'])) {
            $ids = $sect['Ref_Sect_IDs'];
        } else {
            $ids = null;
        }
        if (is_array($ids)) {
            $idsLen = count($ids);
        } else {
            $idsLen = 0;
        }
        for ($i=0;$i<$idsLen;$i++) {
            $ids[$i] = filter_var($ids[$i], FILTER_SANITIZE_NUMBER_INT);
        }
    } else {
        $ids = array(filter_var($sectID, FILTER_SANITIZE_NUMBER_INT));
        $idsLen = 1;
    }
    $qryOrderBy = itemQryOrder($orderBy,$orderDir);
    if ($pgAfter && !$admin_panel) {
        if (isset($sect['Item_Limit']) && $sect['Item_Limit']<$pgAfter) {
            $lim = $sect['Item_Limit'];
        } else {
            $lim=$pgAfter;
        }
        $lim = filter_var($lim, FILTER_SANITIZE_NUMBER_INT);
        $strt = paginateStrt($pageNum, $lim);
        $qryLimit = ' LIMIT '.$strt.', '.$lim;
    } elseif ($sect['Item_Limit'] ?? null) {
        $sect['Item_Limit'] = filter_var($sect['Item_Limit'] , FILTER_SANITIZE_NUMBER_INT);
        $qryLimit=" LIMIT ".$sect['Item_Limit'];
    } else {
        $qryLimit="";
    }
    $qry = "SELECT i.*,";
    if ($admin_panel && $loggedIn) {
        $qry .= " CASE
        WHEN Publish_Timestamp>=strftime('%s','now')
        THEN 1
        ELSE 0
        END AS Queued,";
    }
    $qry .= " s.`Page_ID`, p.`Link` AS Page_Link FROM `Items` AS i 
    LEFT JOIN `Sections` AS s ON i.`Sect_ID` = s.`ID`
    LEFT JOIN `Pages` AS p ON s.`Page_ID`=p.`ID`
    WHERE (";
    for ($i=0;$i<$idsLen;$i++) {
        $qry .= " Sect_ID = ? ";
        if (($i+1)<$idsLen) {
            $qry .= "OR";
        }
    }
    $qry .= " ) ";
    if ($isRef && $sect['Date_Cutoff_On'] && $sect['Date_Cutoff']) {
        $qry .= " AND Publish_Timestamp";
        $dateMode = substr($sect['Date_Cutoff'],0,3);
        $date = substr($sect['Date_Cutoff'], 3);
        if ($dateMode ==='[2]') {
            $date = date('Y-m-d',strtotime("-".$date));
        }
        $date = new DateTime($date);
        $date->setTimeZone(new DateTimeZone($set['timezone']));
        $date = $date->format('Y-m-d');
        if ($sect['Date_Cutoff_Dir']==0) {
            $qry .= "<=";
        } else {
            $qry .= ">=";
        }
        $qry .= "strftime('%s','".$date."') ";
    }
    if (($tagIndex || $isRef) && $sect['Tag_Filter_On'] && $sect['Tag_Filter_List']) {
        $tagFilter=true;
        if (!is_array($sect['Tag_Filter_List'])) {
            $sect['Tag_Filter_List']=prepTags($sect['Tag_Filter_List'], false);
        } 
        $tagListLen = count($sect['Tag_Filter_List']);
        if ($sect['Tag_Filter_Mode']==1 || $sect['Tag_Filter_Mode']==2) {
            // for 'Includes items with tags'
            $qryAdd = " UPPER(i.Tags) LIKE ? ESCAPE '\' ";
        } else {
            // for 'Excludes items with tags'
            $qryAdd = " UPPER(i.Tags) NOT LIKE ? ESCAPE '\' ";
        }
        $tagIndex=1;
        foreach ($sect['Tag_Filter_List'] AS $tag) {
            if ($tagIndex===1) {
                $qry .= ' AND (';
            } else {
                if ($sect['Tag_Filter_Mode']<2) {
                    // for 'Include items with ANY of these tags' and 'Exclude items with ALL of these tags'
                    $qry .= 'OR';
                } else {
                    // for 'Include items with ALL of these tags' and 'Exclude items with ANY of these tags'
                    $qry .= 'AND';
                }
            }
            $qry .= $qryAdd;
            if ($tagIndex===$tagListLen) {
                $qry .= ') ';
            }
            $tagIndex++;
        }
    }
    if (!$loggedIn || !$admin_panel) {
        $qry .= " AND i.Hidden=0 ";
        if (!$sect['Date_Cutoff_On'] || $sect['Date_Cutoff_Dir']==1 || $date>date('Y-m-d\TH:i')) {
            $qry .= " AND Publish_Timestamp<=strftime('%s','now') ";
        }
    }
    $qry .= $qryOrderBy.$qryLimit.";";
    $stmt = $conn->prepare($qry);
    $index=1;
    foreach ($ids AS $id) {
        $stmt->bindValue($index, $id, SQLITE3_INTEGER);
        $index++;
    }
    if ($tagFilter) {
        foreach ($sect['Tag_Filter_List'] AS $tag) {
            $search = '%,'.strtoupper($tag).',%';
            $stmt->bindValue($index, $search, SQLITE3_TEXT);
            $index++;
        }
    }
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Text'] = strDecode($row['Text']);
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
    $qryOrderBy = itemQryOrder($orderBy,$orderDir);
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
    $qry .= $qryOrderBy.';';
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
        $row['Text'] = strDecode($row['Text']);
        $row['Embed'] = strDecode($row['Embed_HTML']);
        // handle date w/timezone
        $date = new DateTime('@'.$row['Publish_Timestamp']);
        $date->setTimeZone(new DateTimeZone($set['timezone']));
        $row['Publish_Timestamp'] = $date->format('Y-m-d\TH:i');
        $row['Date'] = $date->format($set['date_format']);
        $row['Tags'] = prepTags($row['Tags'],false,true);
        ///
        $row['File_Pres'] = '';
        if ($row['File_Path']) {
            $row['File_Pres'] = substr($row['File_Path'], 1, 3);
            $row['File_Path'] = substr($row['File_Path'],5);
        }
        return $row;
    } 
}

function getSocials($smID=null) {
    global $db;
    global $admin_panel; global $loggedIn;
    if (!is_null($smID) && is_numeric($smID)) {
        $smID=filter_var($smID, FILTER_SANITIZE_NUMBER_INT);
        $single = true;
    } else {
        $socials = array();
        $single = false;
    }
    $conn = new SQLite3($db);
    $qry = 'SELECT * FROM `Social_Media`'; 
    if ($single) {
        $qry .= ' WHERE `ID`=? LIMIT 1'; 
    } else {
        $qry .= (!($loggedIn && $admin_panel) ? ' WHERE `Hidden`<1 ' : null).' ORDER BY `Index_Order` ASC'; 
    }
    $qry .= ';';
    $stmt = $conn->prepare($qry);
    if ($single) {
        $stmt->bindValue(1,$smID,SQLITE3_INTEGER);
    }
    $result = $stmt->execute();
    if ($result) {
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($single) {
                $row['Link_Name'] = addslashes($row['Link_Name']);
                $stmt->close();
                return $row;
            } else {
                $row['External'] = true;
                if (substr($row['URL'],0,1)==='/') {
                    global $baseURL;
                    $row['URL'] = $baseURL.$row['URL'];
                    $row['External'] = false;
                }
                $socials[] = $row;
            }
        }
    } else {
        return false;
    }
    $stmt->close();
    return $socials;
}

function printRSS() {
    global $set;
    if (!$set['has_rss']) {
        exit;
    }
    global $db; global $baseURL;
    $conn = new SQLite3($db);
    $qry = 'SELECT * FROM `Reference_Sections` WHERE `Sect_ID` = 0 LIMIT 1';
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['Ref_Count'] = 0;
        if (!is_null($row['Ref_Sect_IDs'])) {
            if (is_string($row['Ref_Sect_IDs'])) {
                $row['Ref_Sect_IDs'] = explode(',',$row['Ref_Sect_IDs']);
                $row['Ref_Count'] = count($row['Ref_Sect_IDs']);
            } elseif (is_numeric($row['Ref_Sect_IDs'])) {
                $row['Ref_Sect_IDs'] = array($row['Ref_Sect_IDs']);
                $row['Ref_Count'] = 1;
            } else {
                $row['Ref_Sect_IDs'] = array();
            }
        }
        $settings=$row;
        $settings['ID']=null;
        $settings['Is_Reference']=1;
        $settings['Order_By']='Date';
        $settings['Order_Dir']=1;
    } 
    $rssItems = getSectItems($settings);

    $output = "<?xml version='1.0' encoding='utf-8'?>
    <rss version='2.0'>
    <channel>
    <title>".$set['site_name'].": RSS Feed</title>
    <link>".$baseURL."</link>
    <description>".$set['site_name']."'s RSS Feed</description>
    <language>en-us</language>";
    foreach ($rssItems AS $item) {
        $output .= "<item>
        <title>".$item['Title']."</title>
        <link>".$baseURL."/view/".$item['ID']."</link>
        <guid>Item ID#: ".$item['ID']."</guid>
        <pubDate>".date("D, d M Y H:i:s T", strtotime($item['Publish_Timestamp']))."</pubDate>
        <description><![CDATA[".$item['Text']."]]></description>
        </item>";
    } unset($item);
    return $output.'</channel></rss>';
}


// Public-Facing page functions

function showTitle($setShowTitle, $title) {
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

function showDate($setShowDate, $date) {
    if (is_array($setShowDate)) {
        $setShowDate = $setShowDate['Show_Item_Titles'];
    }
    if (!$setShowDate) {
        $date = '';
    }
    return $date;
}

function showText($setShowText, $text, $truncAt=140) {
    if (is_array($setShowText)) {
        $setShowText = $setShowText['Show_Item_Text'];
    }
    $text = strDecode($text);
    switch ($setShowText) {
        case 1:
            $text=truncateTxt($text, $truncAt);
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
    global $set; global $route; global $root;
    if ($setShowFile && $filePath) {
        if (!$linkTxt) {
            $linkTxt = 'Click here';
        }
        $localFile = $set['dir'].$filePath;
        $globalFile = $route.$filePath;
        switch ($pres) {
            case 'lnk':
                $file = '<a class="item-file link" href="'.$localFile.'">'.$linkTxt.'</a>';
            break;
            case 'dld':
                $file = '<a class="item-file dnld" href="'.$localFile.'" download>'.$linkTxt.'</a>';
            break;
            case 'aud':
                $mime = mime_content_type($root.$filePath);
                $file = '<audio class="item-file aud-player" controls>
                            <source src="'.$globalFile.'" type="'.$mime.'">
                            Your browser does not support this audio player. 
                            <a href="'.$localFile.'" target="_blank">Click here to open the file instead.</a>
                        </audio>';
            break;
            case 'vid':
                $mime = mime_content_type($root.$filePath);
                $file = '<video class="item-file vid-player" controls>
                            <source src="'.$globalFile.'" type="'.$mime.'">
                            Your browser does not support this video player. 
                            <a href="'.$localFile.'" target="_blank">Click here to open the file instead.</a>
                        </video>';
            break;
            case 'txt':
            default:
                $file = $globalFile;
            break;
        }
        return $file;
    } else {
        return null;
    }
}

function className($input) {
    if (is_array($input)) {
        $input=implode('-',$input);
    } elseif (is_int($input)) {
        $input = strval($input);
    }
    if (is_string($input)) {
            $input = strtolower(preg_replace("/[^A-Za-z0-9-]/",'',str_replace(' ','-',$input)));
        if (is_numeric($input) || filter_var(substr($input,0,1), FILTER_SANITIZE_NUMBER_INT)) {
            $input='num'.(string)$input;
        } else if (substr($input,0,1)==='-') {
            $input='dash'.$input;
        }
    }
    return $input;
}

function printSectPaginator($pgAfter, $pageNum, $itemTotal, $pageLink) {
    global $set;
    if ($_GET['tag'] ?? null) {
        $tag = '?tag='.stripHTML(urldecode($_GET['tag']));
    } else {
        $tag='';
    }
    //prep
    $pgAfter = cleanInt($pgAfter);
    $itemTotal = cleanInt($itemTotal);
    $pageNum = cleanInt($pageNum);
    if (!$pageNum) {
        $pageNum=1;
    }
    $last = ceil($itemTotal/$pgAfter);
    //
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
        $pageDropdown .= '<option value="'.$set['dir'].'/'.urlencode($pageLink).'/page/'.$i.$tag.'" '.($i == $pageNum ? "selected" : null).'>'.$i.'</option>';
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
        if ((!$input && $input!=0) && $action>0) {
            $input='View';
        }
        $input = $aStrt.$input.$aEnd;
    }
    return $input;
};

// serialize tags (serializeTags)
function serializeItemTags($tags, $pageLink, $tagDisplay=2, $spacer=null, $spacerOnEnds=0) {
    global $set;
    $path = $_SERVER['REQUEST_URI'];
    if (!$tagDisplay) {
        return '';
    } else if ($tagDisplay===2) {
        $tagLinks=true;
    } else {
        $tagLinks=false;
    }
    if (!is_array($tags)) {
        $tags = prepTags($tags, false);
    }
    $tagsLen = count($tags);
    $aEnd = ($tagLinks ? '</a>' : '');
    $tagOutput = '<ul class="item-tag-list">';
    if ($spacerOnEnds===1 || $spacerOnEnds>2) {
        $tagOutput .= $spacer;
    }
    $i = 0;
    foreach ($tags AS $tag) {
        $tagOutput.= "<li class='item-tag'>";
        if ($tagLinks) {
            $tagOutput.= '<a href="'.$set['dir'].'/'.urlencode($pageLink).'?tag='.urlencode($tag).'">';
        }
        $tagOutput.= $tag.$aEnd.'</li>';
        $i++;
        if ($i<$tagsLen && $spacer) {
            $tagOutput.= $spacer;
        }
    } unset($tag);
    if ($spacerOnEnds>1) {
        $tagOutput .= $spacer;
    }
    $tagOutput .= '</ul>';
    return $tagOutput;
}

function printPageItems($itemList=false, $sect=false) {
    if (!$itemList || !is_array($itemList)) {
        return '<!-- No items were found in this section. -->';
    }
    if (!$sect) {
        $sect;
        $sect['Show_Item_Titles'] = 1;
        $sect['Show_Item_Text'] = 1;
        $sect['Show_Item_Images'] = 1;
        $sect['Show_Item_Tags'] = 2;
        $sect['Tag_List_Spacer'] = NULL;
        $sect['Tag_Spacer_On_Ends'] = 0;
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
        $text = showText($sect['Show_Item_Text'], $textFull, ($sect['Truncate_Text_At'] ?? 140));
        if ($text) {
            $text = closeHTML(addItemLinks($sect['Item_Click_Area'], $id, $text, 'Text', $sect['On_Click_Action'], $sect['ID']));
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
        $embed = $item['Embed_HTML'];
        if ($embed) {
            $embed = closeHTML(strDecode($embed));
        }
        $tags = serializeItemTags($item['Tags'],$item['Page_Link'],$sect['Show_Item_Tags'],$sect['Tag_List_Spacer'], $sect['Tag_Spacer_On_Ends']);
        $date = showDate($sect['Show_Item_Dates'],$item['Date']);
        $class = className($item['Title']);
        $viewLink = '';
        if ($sect['On_Click_Action'] && (in_array('Link',$sect['Item_Click_Area']) || $sect['Item_Click_Area']=='Link')) {
            $viewLink = addItemLinks($sect['Item_Click_Area'], $id, $sect['Default_Item_Link_Text'], 'Link', $sect['On_Click_Action'], $sect['ID']);
        }

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
                $itemContent .= closeHTML($embed);
            }
            if ($text) {
                $itemContent .= closeHTML($text);
            }
            if ($file) {
                $itemContent .= $file;
            }
            $itemContent = closeHTML($itemContent);
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
        
        $content .= closeHTML($itemContent);
        $itemContent = '';
    }unset($item);
    if ($sect['On_Click_Action']==2) { //lightbox
        $jsContent .= 'lbArrs.push(sect'.$sect['ID'].'Items);</script>';
        $content .= $jsContent;
    };
    return $content;
}

function printPageSects($sectList=false, $pageNum=1, $pgAfter=null, $paginator='', $tag=null, $itemPrvw=false, $pageInfo=null) {
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
            $image = '<img src="'.$set['dir'].$sect['Header_Img_Path'].'" alt="'.$sect['Name'].' Header">';
            $srcImgFull = $set['dir'].$sect['Header_Img_Path'];
         } else {
             $image = '';
             $srcImgFull = '';
         }
         if ($sect['Show_Title']) {
             $title = '<h2 class="sect-title">'.$sect['Name'].'</h2>';
         } else {
             $title = '';
         }
         $text = $sect['Text'];
         if (trim($text)==='' || $text===null) {
            $text='';
         }
        $class = className($sect['Name']);
        $itemList = getSectItems($sect,$pageNum,$pgAfter,($tag ?? false));
        if ($itemPrvw) {
            if ($itemPrvw['ID'] != "New") {
                foreach ($itemList AS &$item) {
                    if ($item['ID'] == $itemPrvw['ID']) {
                        $item = $itemPrvw;
                    }
                }
            } else {
                if (!$sect['Order_Dir']) {
                    if (count($itemList) >= $pgAfter) {
                        $itemList = array($itemPrvw);
                    } else {
                        $itemList[] = $itemPrvw;
                    }
                } else {
                    $itemList = array_unshift(array_pop($itemList), $itemPrvw);
                }
            }
        }
                                
        $items_content = '<!-- No items found. -->';
        if ($itemList) {
            $items_content = printPageItems($itemList, $sect);
        } else {
            //if no items
            if ($pgAfter) {
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

function serializeSocials() {
    global $set;
    $socials=getSocials();
    $output = '<article class="cms-socials">
    <ul class="cms-socials-list">';
    foreach ($socials AS $social) {
        $id = ($social['ID'] ? $social['ID'] : 'rss');
        $linkText = htmlspecialchars_decode($social['Link_Text']);
        $output .= '<a id="social_'.$id.'" href="'.$social['URL'].'" title="'.$social['Link_Name'].'"';
        if ($social['External']) {
            $output.= ' target="_blank"';
        }
        $output.= '><li class="social-'.$id.'">'.$linkText.'</li></a>';
    } unset($social);
    $output .= '</ul>
    </article>';
    return $output;
}

function previewPrep($area, $page=null, $content=null) {
    global $_POST; global $set;
    $cPost = cleanServerPost($_POST);
    if ($area!='page') {
        if (!$page) {
            $page=array();
        } 
        // else if (!is_array($page) && is_numeric($page)) {
        //     $page = getPage($page);
        // }
    }
    switch ($area) {
        case ('item') :
            if ($cPost['publish_datetime']) {
                $cPost['publish_datetime'] = strtotime($cPost['publish_datetime']);
            } else {
                $cPost['publish_datetime'] = time();
            }

            $content['ID'] = ($cPost['n_item_id'] ?? "New");
            $content['Sect_ID'] = $cPost['n_sect_id'];
            $content['Title'] = $cPost['title'];
            $date = new DateTime('@'.$cPost['publish_datetime']);
                $date->setTimeZone(new DateTimeZone($set['timezone']));
            $content['Publish_Timestamp'] = $date->format('Y-m-d\TH:i');
            $content['Date'] = $date->format($set['date_format']);
            $content['Text'] = strDecode($cPost['m_text']);
            $content['Img_Path'] = ($cPost['img_preview'] ?? $cPost['img_stored']);
            $content['Img_Alt_Text'] = $cPost['img_alt_text'];
            $content['File_Path'] = ($cPost['file_path'] ?? null);
            $content['File_Pres'] = $cPost['file_pres'];
            $content['File_Link_Text'] = ($cPost['file_link_text'] ?? null);
            $content['Embed_HTML'] = strDecode($cPost['b_embed']);
            $content['Img_Thumb_Path'] = ($cPost['thumb_preview'] ?? $cPost['stored_thumb_img']);
            $content['Hidden'] = $cPost['n_hidden'];
            $content['Format'] = $cPost['format'];
            $content['Tags'] = $cPost['tags'];
        break;
        case ('section') :
            $content['ID'] = ($cPost['n_sect_id'] ?? "New");
            $content['Page_ID'] = ($cPost['n_page_id'] ?? "New");
            $content['Name'] = $cPost['name'];
            $content['Text'] = strDecode($cPost['b_text']);
            $content['Header_Img_Path'] = ($cPost['header_img_preview'] ?? $cPost['stored_header_img']);
            $content['Show_Title'] = $cPost['n_show_title'];
            $content['Show_Header_Img'] = $cPost['n_show_header_img'];
            $content['Show_Item_Images'] = $cPost['n_show_images'];
            $content['Show_Item_Titles'] = $cPost['n_show_titles'];
            $content['Show_Item_Text'] = $cPost['n_show_text'];
            $content['Order_By'] = $cPost['order_by'];
            $content['Order_Dir'] = $cPost['n_order_dir'];
            $content['Auto_Thumbs'] = $cPost['n_create_thumbs'];
            $content['Thumb_Size'] = $cPost['n_thumb_size'];
            $content['Thumb_Size_Axis'] = $cPost['n_thumb_axis'];
            $content['Format'] = $cPost['format'];
            $content['Default_Item_Format'] = $cPost['item_format'];
            $content['Show_Item_Files'] = $cPost['n_show_files'];
            $content['Default_File_Link_Text'] = $cPost['file_link_text'];
            $content['Item_Click_Area'] = implode(',',$cPost['item_click_area']);
            $content['On_Click_Action'] = $cPost['n_onclick_action'];
            $content['View_Item_Format'] = $cPost['view_item_format'];
            $content['Lightbox_Format'] = $cPost['lightbox_format'];
            $content['Paginate_Items'] = $cPost['n_paginate_items'];
            $content['Hidden'] = $cPost['n_hidden'];
        break;
        case ('page-sections') :
            if (is_array($content) && $cPost['n_multi_sect'] && isset($cPost['sect'])){
                for ($i=0;$i<count($content);$i++) {
                    foreach ($cPost['sect'] AS $sect) {
                        if ($content[$i]['ID']==$sect['n_sect_id']) {
                            $content[$i]['Page_Index_Order'] = $sect['n_index'];
                        }
                    }
                }
            usort($content, function ($a, $b) {
                return $a['Page_Index_Order'] <=> $b['Page_Index_Order'];
            });
        }
        break;
        case ('page') :
        default:
        $content['ID'] = ($cPost['n_page_id'] ?? "New");
        $content['Name'] = $cPost['name'];
        $content['Link'] = ($cPost['link'] ?? '/');
        $content['Meta_Text'] = $cPost['meta_text'];
        $content['Header_Img_Path'] = ($cPost['header_img_preview'] ?? $cPost['stored_header_img']);
        $content['Show_Title'] = $cPost['n_show_title'];
        $content['Show_Header_Img'] = $cPost['n_show_header_img'];
        $content['Multi_Sect'] = $cPost['n_multi_sect'];
        $content['Paginate'] = $cPost['n_paginate'];
        $content['Paginate_After'] = ($cPost['n_paginate_after'] ?? 5);
        $content['Format'] = $cPost['format'];
        $content['Hidden'] = $cPost['n_hidden'];
        $content['Total_Items'] = $cPost['n_paginate_after'];
        break;
    }
    return $content;
}

function printPage($page=1,$pKey=1,$pageType=null,$method=null) {
    global $db; global $set; 
    global $root; global $route; global $themePath;
    $menuIndex = $singleItem = $isItemPrvw = $previewArea = $tagIndex = false;
    switch ($pageType) {
        case 'item' :
            $singleItem = true;
        break;
        case 'submenu' :
            $menuIndex = true;
            $page = array();
            $page['Name'] = urldecode($pKey);
        break;
        case 'preview' :
            $previewArea = $pKey;
            $prvwContent = ($previewArea !='page' ? $page : null);
            $isItemPrvw = ($previewArea=='item' ? $prvwContent['ID'] : false);
            $pKey=1;
        break;
        case 'page' :
        case 'default' :
        default :
            $pageType=null;
        break;
    }
    $tag = $tagHeader = null;
    if ($_GET['tag'] ?? null) {
        $tag = stripHTML(urldecode($_GET['tag']));
        if ($tag) {
            $tagIndex = true;
            $tag_header = "<div class='tag-index-header'>Tag Index: '".$tag."'</div>";
        }
    }
    $conn = New SQLite3($db);
    // if no 'page', go to the home page
    // NOTE: If the page isn't a home page or preview, it is fetched on index.php.
    if ((isset($page['ID']) && $page['ID'] === 'Error') || !isset($page['ID'])) {
        $page404 = true;
        $page = 1;
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
    } else if ($previewArea && $previewArea != 'page') {
        $pageID= ($prvwContent['Page_ID'] ?? $_SESSION['Item_Page_ID']);
        $page = getPage($pageID);
    }
    $id = $page['ID'];
    $class = className($page['Name'] ?? '');
    if ((isset($page['Show_Title']) && $page['Show_Title']) && isset($page['Name'])) {
        $title = '<h1 class="page-title">'.$page['Name'].'</h1>';
    } else {
        $title = '';
    }
    if (($page['Header_Img_Path'] ?? null) && ($page['Show_Header_Img'] ?? null)) {
        $image = '<img src="'.$set['dir'].$page['Header_Img_Path'].'" alt="'.$page['Name'].' header"/>';
        $image_path = $set['dir'].$page['Header_Img_Path'];
    } else {
        $image='';
        $image_path='';
    }
    $paginator = '';
    if ($singleItem === true) {
        $item = getItem($pKey);
        $sectInfo = getSectInfo($item['Sect_ID']);
        $name = $item['Title']; 
        $pageName = ($item['Page_Hidden']<1 ? $item['Page_Name'] : false); 
        $meta_text = truncateTxt($item['Text'], 300);
        $page['Format'] = $sectInfo['View_Item_Format'];
        $pageURL = ($item['Page_Hidden']<1 ? $set['dir'].'/'.$item['Page_Link'] : false);
        $item['Page_Link'] = ($item['Page_Hidden']<1 ? $item['Page_Link'] : false);
        $pageLink = ($item['Page_Hidden']<1 ? '<a class="back-to-page" href="'.$pageURL.'">Back to '.$item['Page_Name'].'</a>' : false);
        if ($sectInfo['Paginate_Items']) {
            $itemList = getSectItemIDs($item['Sect_ID'], $sectInfo['Order_By'], $sectInfo['Order_Dir']);
            $paginator = printItemPaginator($itemList, $pKey, $item['Page_Link'], $pageName);
        }
    } elseif ($singleItem !== true && $menuIndex) {
        $meta_text = ($page['Meta_Text'] ?? null);
        $name = ($page['Name'] ?? null);
    } else {
            $sectList = ($page['ID'] != "New" ? getPageSects($page['ID']) : null);
        if ($previewArea=='page' && $page['Multi_Sect']) {
            $sectList = previewPrep('page-sections', $page, $sectList);
        } else if ($previewArea=='section') {
            foreach ($sectList AS &$sect) {
                if (($sect['ID'] && $prvwContent['ID'] != "New") && ($sect['ID'] == $prvwContent['ID'])) {
                    $sect = $prvwContent;
                    unset($prvwContent);
                }
            }
        }
        $pgAfter = (($page['Paginate'] ?? null) ? $page['Paginate_After'] : null);
        $pageNum = (($isItemPrvw && $pgAfter) ? findItemInPages($prvwContent['ID'], $pgAfter, $sect['Order_By'], $sect['Order_By']) : $pKey);
        if (($page['Paginate'] ?? null)==1 && $page['Multi_Sect']==0 &&
        ($page['Paginate_After']<$page['Total_Items'])) {
            $totalItems = $page['Total_Items'];
            if ($previewArea === 'item' && $prvwContent['ID']=="New") {
                $totalItems++;
                if (intval($sect['Order_Dir'])===1) {
                    $pageNum = $totalItems;
                }
            }
            $paginator = printSectPaginator($page['Paginate_After'], $pageNum, $totalItems, $page['Link']);
        } else {
            $paginator = '';
        }
        $section_content = printPageSects($sectList,$pageNum,$pgAfter,$paginator,($tagIndex ? $tag : null),($isItemPrvw ? $prvwContent : false),($isItemPrvw ? $page : null));
        $name = $page['Name'];
        $meta_text = $page['Meta_Text'];
    }
    $faviconRel = $moIconRel = $content = '';
    $automenu = serializeMenu();
    $socials_list = serializeSocials();
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
        <meta name="description" content="'.$meta_text.'">
        <script type="text/javascript">'.PHP_EOL.'
            const subdir = "'.$set['dir'].'";'.PHP_EOL.'
        </script>
    </head>
    <body>';
    if ($previewArea) {
        $prvwAreaTxt = " <em>".ucfirst($previewArea)."</em>"; 
        $prvwAreaTxt = ($previewArea=='item' ? "n".$prvwAreaTxt : $prvwAreaTxt);
        $prvwMsg= "This is a".$prvwAreaTxt." preview.";
        if ($isItemPrvw) {
            $prvwMsg .= " Item previews may not always be 100% accurate.";
        }
        echo '<div class="cms-alert-msg"><p>'.$prvwMsg.'</p></div>';
    }

    if ($noPage ?? null) {
        echo '<h1>Page Not Found</h1>';
    }

    $header = $themePath.'/header.php';
    $footer = $themePath.'/footer.php';
    $menu = $themePath.'/menu.php';
    $menuAuto = $root.'/components/site-menu-auto.php';
    if (isset($page['Format'])) {
        $formatFile = $root.$page['Format'];
    }
    $headerImg = '';
    // headerImg is not to be confused with the PAGE header image, referred to simply as '$image', above
    if ($set['header_img']>'' && file_exists($root.$set['header_img'])) {
        $headerImgSrc = $route.$set['header_img'];
        $headerImg = '<a href="'.$set['dir'].'/"><img src="'.$set['dir'].$set['header_img'].'" alt="'.$set['site_name'].'"></a>';
    }

    if ($singleItem === true) {
        // name vars for single-item view page
        $id = $item['ID'];
        $title = '<h3 class="item-view item-title">'.$item['Title'].'</h3>';
        $text =  '<div class="item-view item-text">'.$item['Text'].'</div>';
        $image = ($item['Img_Path'] ? '<img src="'.$set['dir'].$item['Img_Path'].'" alt="'.($item['Img_Alt_Text'] ? $item['Img_Alt_Text'] : $item['Title']).'">' : null);
        $file = showFile(1, $item['File_Path'], $item['File_Pres'], ($sectInfo['Link_Text'] ?? 'Click here'));
        $date = '<div class="item-view item-date">'.$item['Date'].'</div>';
        $embed = strDecode($item['Embed_HTML']);
        $srcImgFull = $set['dir'].$item['Img_Path'];
        $srcImgThumb = $set['dir'].$item['Img_Thumb_Path'];
        $srcFilePath = $set['dir'].$item['File_Path'];
        $class = className($item['Title']);
        ///////
        ob_start();
        include($header);
        $content .= ob_get_clean();
        // NOTE: $page['Format'] has been rewritten to the single item format.
        if ($page['Format'] && file_exists($formatFile)) {
            ob_start();
            include($formatFile);
            $content .= ob_get_clean();
        } else {
            $content .= '<main id="view_item_'.$item['ID'].'" class="page '.$class.'">
            <div id="item_'.$id.'" class="item '.$class.'">';
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
            $content .= '</div></main>';
        }
        ob_start();
        include($footer);
        $content .= ob_get_clean();
    } elseif ($menuIndex===true) {
        // menu submenu index page
        if (isset($page['Format']) && $page['Format'] && file_exists($formatFile)) {
            $title='';
            $section_content = serializeMenu($pKey);
            ob_start();
            include($formatFile);
            $content .= ob_get_clean();
        } else {
            ob_start();
            include($header);
            $content .= ob_get_clean();
            $content .= '<main id="menu-submenu-index" class="page menu-index">';
            $content .= serializeMenu($pKey);
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


function fetchSettings($selectArr=false) {
    global $db;
    $conn = new SQLite3($db);
    $settings = array();
    $qry = 'SELECT * FROM `Settings` WHERE';
    if ($selectArr) {
        $selectsLen = count($selectArr);
        $first = true;
        foreach ($selectArr AS $i) {
            $add = ' `Key` = ? ';
            if (!$first) {
                $add .= ' OR '.$add;
            }
            $qry .= $add;
            $first = false;
        } unset($i);
        if ($selectsLen>1) {
            $qry .= ' AND ';
        }
    } else {
        $qry .= ' `Hidden`<1';
    }
    $qry .= ' ORDER BY `Index_Order`;';
    $stmt = $conn->prepare($qry);
    if ($selectArr) {
        $index = 1;
        foreach ($selectArr AS &$i) {
            $stmt->bindValue($index, $i, SQLITE3_TEXT);
            $index++;
        } unset($i);
    }
    $result = $stmt->execute();
    if ($result) {
        if (!$selectArr) {
            $info = array();
            $display = array();
            $advanced = array();
        }
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if (!$selectArr) {
                if ($row['Options']) {
                    $row['Options'] = explode(', ', $row['Options']);
                }
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
            if (!$selectArr) {
                $settings = array('Info' => $info, 'Display' => $display, 'Advanced' => $advanced);
            } else if ($selectsLen===1) {
                return $row;
            } else {
                $settings[] = $row;
            }
        }
        return $settings;
    } else {
        $msg = "An error occurred while trying to fetch your settings. Please try again.";
    }
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
    } unset($val);
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
        } else if (intval(($_POST['n_rmv_mobile_icon_img'] ?? null))>0) {
            $_POST['mobile_icon_img']='';
            unset($_POST['n_rmv_mobile_icon_img']);
        } else {
            unset($_POST['mobile_icon_img'],$setCmp['mobile_icon_img'],$_POST['n_rmv_mobile_icon_img']);
        }
        // favicon
        if (isset($_FILES['favicon_img']) && $_FILES['favicon_img']['name']>'') {
            $_POST['favicon_img'] = uploadImage ($dir, $_FILES['favicon_img'], 16, 16, false, false, 'favicon_img', $set['max_upld_storage'],false,'gif');
        } else if (intval($_POST['n_rmv_favicon_img'] ?? null)>0) {
            $_POST['favicon_img']='';
            unset($_POST['n_rmv_favicon_img']);
        } else {
            unset($_POST['favicon_img'],$setCmp['favicon_img'],$_POST['n_rmv_favicon_img']);
        }
    $postLen = count($_POST); $cmpCount = count($setCmp);
    $setLen = ($postLen>$cmpCount ? $postLen : $cmpCount);
    $changes = array();
    unset($_POST['advanced_sets'],$_POST['save_settings']);
    foreach ($_POST AS $key=>$val) {
        if (isset($setCmp[$key]) && $setCmp[$key]!=$val) {
            $changes[$key] = $val;
        }
    }unset($key,$val);
    if (count($changes)>0) {
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
                        It was lowered to '50,000'.</div>";
                    }
                break;
                default:
                    if (substr($key,-4)!=='_img' && strlen($val)>85) {
                        $val = substr($val, 0, 85);
                        $msg .= "<div class='red'>One of your entries was too long. It was shortened to 85 characters.</div>";
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
        } unset($val);
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
                $menuStmt->bindValue(3,$cPost['menu_img_path'], SQLITE3_TEXT);
            }
            if (!$menuStmt->execute()) {
                $_SESSION['Msg'] .= "<br/>There was an error creating a link for this page in the automenu.";
                return false;
            } else {
                $automenu = serializeMenu();
                return $pageID;}
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
    return true;
}

function updateRefSects($cPost, $new=false) {
    global $db;
    $conn = new Sqlite3($db);
    if ($new) {
        $qry = "INSERT INTO `Reference_Sections` (`Ref_Sect_IDs`, 
        `Date_Cutoff_On`, `Date_Cutoff`, `Date_Cutoff_Dir`,
        `Tag_Filter_On`, `Tag_Filter_List`, `Tag_Filter_Mode`,
        `Item_Limit`, `Sect_ID`) VALUES (:sectlist, 
        :datecutoffon, :datecutoff, :datecutoffdir, 
        :tagson, :tags, :tagsdir, :itemlimit, :id);";
    } else {
        $qry = "UPDATE `Reference_Sections` SET `Ref_Sect_IDs`=:sectlist, 
        `Date_Cutoff_On`=:datecutoffon, `Date_Cutoff`=:datecutoff, `Date_Cutoff_Dir`=:datecutoffdir,
        `Tag_Filter_On`=:tagson, `Tag_Filter_List`=:tags, `Tag_Filter_Mode`=:tagsdir,
        `Item_Limit`=:itemlimit WHERE `Sect_ID`=:id;";
    }
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':sectlist',$cPost['ref_sect_list'], SQLITE3_TEXT);
    $stmt->bindValue(':datecutoffon',$cPost['n_date_cutoff_on'], SQLITE3_INTEGER);
    $stmt->bindValue(':datecutoff',$cPost['date_cutoff'], SQLITE3_TEXT);
    $stmt->bindValue(':datecutoffdir',$cPost['n_date_cutoff_dir'], SQLITE3_INTEGER);
    $stmt->bindValue(':tagson',$cPost['n_tag_filter_on'], SQLITE3_INTEGER);
    $stmt->bindValue(':tags',$cPost['tag_filter_list'], SQLITE3_TEXT);
    $stmt->bindValue(':tagsdir',$cPost['n_tag_filter_mode'], SQLITE3_INTEGER);
    $stmt->bindValue(':itemlimit',$cPost['n_item_limit'], SQLITE3_INTEGER);
    $stmt->bindValue(':id',$cPost['n_sect_id'], SQLITE3_INTEGER);
    return $stmt->execute();
}

function createSection($cPost) {
    global $db;
    $conn = new SQLite3($db);
    $qry = 'INSERT INTO Sections (`Name`, `Page_ID`, `Text`, `Header_Img_Path`, `Show_Title`, `Show_Header_Img`, `Is_Reference`,
    `Show_Item_Images`, `Show_Item_Titles`, `Show_Item_Dates`, `Show_Item_Text`, `Order_By`, `Order_Dir`, `Auto_Thumbs`, `Thumb_Size`, 
    `Thumb_Size_Axis`, `Format`, `Default_Item_Format`, `Show_Item_Files`, `Default_File_Link_Text`, `Show_Item_Tags`, `Tag_List_Spacer`,
    `Tag_Spacer_On_Ends`, `Item_Click_Area`, `On_Click_Action`, `View_Item_Format`, `Lightbox_Format`, `Paginate_Items`, `Truncate_Text_At`, 
    `Default_Item_Link_Text`, `Hidden`) 
    VALUES (:name, :pageid, :text, :imgpath, :showtitle, :showimg, :isref, :showitemimgs, :showitemtitles, 
    :showitemdates, :showitemtext, :orderby, :orderdir, :autothumbs, :thumbsize, :thumbsizea, :format, :itemformat, 
    :showfiles, :filelinktext, :showtags, :tagspacer, :spaceronends,
    :clickarea, :clickaction, :viformat, :lbformat, :paginate, :trunctxtat, :itemlinktext, :hidden);';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':name',$cPost['name'], SQLITE3_TEXT);
    $stmt->bindValue(':pageid',$cPost['n_page_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':text',$cPost['text'], SQLITE3_TEXT);
    $stmt->bindValue(':imgpath',$cPost['img_path'], SQLITE3_TEXT);
    $stmt->bindValue(':showtitle',$cPost['n_show_title'], SQLITE3_INTEGER);
    $stmt->bindValue(':showimg',$cPost['n_show_header_img'], SQLITE3_INTEGER);
    $stmt->bindValue(':isref',$cPost['is_reference'], SQLITE3_INTEGER);
    $stmt->bindValue(':showitemimgs',$cPost['n_show_images'], SQLITE3_INTEGER);
    $stmt->bindValue(':showitemtitles',$cPost['n_show_titles'], SQLITE3_INTEGER);
    $stmt->bindValue(':showitemdates',$cPost['n_show_dates'], SQLITE3_INTEGER);
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
    $stmt->bindValue(':showtags',$cPost['n_show_tags'], SQLITE3_INTEGER);
    $stmt->bindValue(':tagspacer',$cPost['tag_spacer'], SQLITE3_TEXT);
    $stmt->bindValue(':spaceronends',$cPost['n_spacer_on_ends'], SQLITE3_INTEGER);
    $stmt->bindValue(':clickarea',$cPost['click_areas'], SQLITE3_TEXT);
    $stmt->bindValue(':clickaction',$cPost['n_onclick_action'], SQLITE3_INTEGER);
    $stmt->bindValue(':viformat',$cPost['view_item_format'], SQLITE3_TEXT);
    $stmt->bindValue(':lbformat',$cPost['lightbox_format'], SQLITE3_TEXT);
    $stmt->bindValue(':paginate',$cPost['n_paginate_items'], SQLITE3_INTEGER);
    $stmt->bindValue(':trunctxtat',$cPost['n_truncate_at'], SQLITE3_INTEGER);
    $stmt->bindValue(':itemlinktext',$cPost['item_link_text'], SQLITE3_TEXT);
    $stmt->bindValue(':hidden',$cPost['n_hidden'], SQLITE3_INTEGER);
    if ($stmt->execute()) {
        $sectID = $conn->lastInsertRowID();
        $cPost['n_sect_id'] = $sectID;
        if ($cPost['is_reference'] && $sectID) {
            updateRefSects($cPost, true);
        }
        return $sectID;
    } else {
        return false;
    }
}

function editSection($cPost) {
    global $db;
    $conn = new SQLite3($db);
    $qry = 'UPDATE Sections SET 
        `Name`=:name, `Text`=:text, `Header_Img_Path`=:imgpath, `Show_Title`=:showtitle, `Show_Header_Img`=:showimg, 
        `Show_Item_Images`=:showitemimgs, `Show_Item_Titles`=:showitemtitles, `Show_Item_Dates`=:showitemdates, `Show_Item_Text`=:showitemtext, 
        `Order_By`=:orderby, `Order_Dir`=:orderdir, `Auto_Thumbs`=:autothumbs, `Thumb_Size`=:thumbsize, `Thumb_Size_Axis`=:thumbsizea, 
        `Format`=:format, `Default_Item_Format`=:itemformat, `View_Item_Format`=:viformat, `Page_ID`=:pageid, 
        `Show_Item_Files`=:showfiles, `Default_File_Link_Text`=:filelinktext, `Show_Item_Tags`=:showtags, `Tag_List_Spacer`=:tagspacer,
        `Tag_Spacer_On_Ends`=:spaceronends, `Item_Click_Area`=:clickarea, `On_Click_Action`=:clickaction, 
        `Lightbox_Format`=:lbformat, `Paginate_Items`=:paginate, `Truncate_Text_At`=:trunctxtat, `Default_Item_Link_Text`=:itemlinktext, 
        `Hidden`=:hidden
    WHERE ID=:id;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':name',$cPost['name'], SQLITE3_TEXT);
    $stmt->bindValue(':pageid',$cPost['n_page_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':text',$cPost['text'], SQLITE3_TEXT);
    $stmt->bindValue(':imgpath',$cPost['img_path'], SQLITE3_TEXT);
    $stmt->bindValue(':showtitle',$cPost['n_show_title'], SQLITE3_INTEGER);
    $stmt->bindValue(':showimg',$cPost['n_show_header_img'], SQLITE3_INTEGER);
    $stmt->bindValue(':showitemimgs',$cPost['n_show_images'], SQLITE3_INTEGER);
    $stmt->bindValue(':showitemtitles',$cPost['n_show_titles'], SQLITE3_INTEGER);
    $stmt->bindValue(':showitemdates',$cPost['n_show_dates'], SQLITE3_INTEGER);
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
    $stmt->bindValue(':showtags',$cPost['n_show_tags'], SQLITE3_INTEGER);
    $stmt->bindValue(':tagspacer',$cPost['tag_spacer'], SQLITE3_TEXT);
    $stmt->bindValue(':spaceronends',$cPost['n_spacer_on_ends'], SQLITE3_INTEGER);
    $stmt->bindValue(':clickarea',$cPost['click_areas'], SQLITE3_TEXT);
    $stmt->bindValue(':clickaction',$cPost['n_onclick_action'], SQLITE3_INTEGER);
    $stmt->bindValue(':viformat',($cPost['view_item_format'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':lbformat',$cPost['lightbox_format'], SQLITE3_TEXT);
    $stmt->bindValue(':paginate',$cPost['n_paginate_items'], SQLITE3_INTEGER);
    $stmt->bindValue(':trunctxtat',$cPost['n_truncate_at'], SQLITE3_INTEGER);
    $stmt->bindValue(':itemlinktext',$cPost['item_link_text'], SQLITE3_TEXT);
    $stmt->bindValue(':hidden',$cPost['n_hidden'], SQLITE3_INTEGER);
    $stmt->bindValue('id',$cPost['n_sect_id'], SQLITE3_INTEGER);
    if ($stmt->execute()) {
        if ($cPost['is_reference']) {
            updateRefSects($cPost, false);
        }
        if ($cPost['n_sect_id']>0) {
            return $cPost['n_sect_id'];
        } else {
            // if the edit was to section defaults
            return true;
        }
    } else {
        return false;
    }
}

function createItem($cPost) {
    global $db;
    $conn = new SQLite3($db);
    $qry = "INSERT INTO Items (Sect_ID,Title,Img_Path,Img_Alt_Text,File_Path,File_Link_Text,
    Embed_HTML,Img_Thumb_Path,Text,Publish_Timestamp,Tags,Hidden,Format)
    VALUES (:sectid,:title,:img,:alttext,:file,:linktext,:embed,:imgthumb,:text,:ts,:tags,:hide,:format);";
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
    $stmt->bindValue(':tags',$cPost['tags'], SQLITE3_TEXT);
    $stmt->bindValue(':format',$cPost['format'], SQLITE3_TEXT);
    return $stmt->execute();
}

function editItem($cPost) {
    global $db;
    $conn = new SQLite3($db);
    $qry = "UPDATE Items 
    SET Sect_ID=:sectid,Title=:title,Publish_Timestamp=:ts,Text=:text,Img_Path=:img, Img_Alt_Text=:alttext,
    File_Path=:file, File_Link_Text=:linktext, Embed_HTML=:embed,Img_Thumb_Path=:imgthumb,Format=:format,
    Tags=:tags,Hidden=:hide
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
    $stmt->bindValue(':tags',$cPost['tags'], SQLITE3_TEXT);
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
        $msg .= "Changes to this page failed to save. Please try again.";
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
    global $set;
    if (isset($_POST['create_section'])) {
        $newSection = true;
    } else {
        $newSection = false;
    }
    $imgUpld = ($_FILES['header_img_upload']['name'] ?? null);
    // preserve tag spacer so it doesn't get trimmed in the regular sanitizing process
    $tagSpacer = stripHTML($_POST['tag_spacer'], false, false, false);
    $cPost = cleanServerPost($_POST);
    $cPost['tag_spacer'] = $tagSpacer;
    if (isset($_POST['is_reference'])) {
        $cPost['is_reference']=1;
        if (isset($cPost['ref_sect']) && is_array($cPost['ref_sect'])) {
            $cPost['ref_sect_list'] = implode(',',$cPost['ref_sect']);
            $isRef=true;
        }
        $cPost['n_create_thumbs'] = $cPost['n_thumb_size'] = $cPost['n_thumb_axis'] = 0;
        if ($cPost['n_date_cutoff_mode']==2) {
            $dateCutoff = $cPost['n_date_number'].' '.$cPost['date_unit'];
            $cPost['n_date_cutoff_mode']=2;
        } else {
            $cPost['n_date_cutoff_mode']==1;
            $dateCutoff = $cPost['date_cutoff_strict'];
        }
        $cPost['date_cutoff'] = '['.$cPost['n_date_cutoff_mode'].']'.$dateCutoff;
        if ($cPost['tag_filter_list']) {
            $cPost['tag_filter_list'] = prepTags($cPost['tag_filter_list'], true, true);
        }
    } else {
        $cPost['is_reference']=0;
        $isRef=false;
    }
    if (!$newSection && $cPost['n_sect_id']===0) {
        // section default/orphaned items
        $cPost['name'] = 'Section Default';
        $cPost['n_hidden'] = 0;
        $cPost['n_page_id'] = null;
        $cPost['b_text'] = null;
    }
    if ($cPost['n_page_id'] <= '') {
        $cPost['n_page_id'] = null;
    }
    if (isset($cPost['n_item_limit']) && isset($cPost['item_limit_on'])) {
        if (!$cPost['n_item_limit'] || !$cPost['item_limit_on']) {
            $cPost['n_item_limit'] = null;
        } else {
            $cPost['n_item_limit'] = intval(filter_var($cPost['n_item_limit'], FILTER_SANITIZE_NUMBER_INT));
        }
    }
    // in case I decide whether Section Text gets cleaned up as code or markup text should be toggable...
    if ($cPost['m_text'] ?? null) {
        $cPost['text'] = $cPost['m_text'];
    } else {
        $cPost['text'] = $cPost['b_text'];
    }
    $cPost['text'] = closeHTML($cPost['text']);
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
    if ($cPost['item_link_text'] === "") {
        $cPost['item_link_text'] = "View";
    }
    if ($cPost['item_click_area']) {
        $clickAreas = cleanServerPost($cPost['item_click_area']);
        if (in_array('All',$clickAreas)) {
            $clickAreas = array('All');
        }
    } else {
        $clickAreas = array('All');
    }
    $clickAreas= implode(',',$clickAreas);
    $cPost['img_path'] = $imgPath;
    $cPost['click_areas'] = $clickAreas;
    //if ($isRef)
    if ($newSection) {
        $exec = createSection($cPost);
    } else {
        $exec = editSection($cPost);
    }
    if (!$exec) {
        $msg="Changes to this sectionfailed to save. Please try again.";
    } else {
        $msg="Section settings were saved at ".date('d F, Y h:i:s').'.';
    }
}

if (isset($_POST['delete_section'])) {
    global $db;
    $conn = new SQLite3($db);
    $sectID = intval(filter_var($_POST['n_sect_id'], FILTER_SANITIZE_NUMBER_INT));
    if ($sectID===0) {
        $msg="You cannot delete the default section.";
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
    // surround prepTags with commas to make searching with wildcards easier
    $cPost['tags'] = ','.prepTags($cPost['tags']).',';

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
    $cPost = cleanServerPost($_POST);
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

if (isset($_POST['add_new_social']) || isset($_POST['edit_social'])) {
    global $db; global $set;
    $new=false;
    $action='edit';
    if (isset($_POST['add_new_social'])) {
        $new=true;
        $action='add';
    }
    $msg='';
    $cPost = cleanServerPost($_POST);
    $cPost['icon_path']=null;
    $iconUpld = ($_FILES['icon_img_upload']['name'] ?? false);
    if ($iconUpld) {
        require_once 'upload.php';
        $dir = '/assets/uploads/socials/';
        $cPost['icon_path'] = uploadImage($dir, $_FILES['icon_img_upload'], $set['max_img_dimns'], $set['max_img_dimns'], true, true, false, $set['max_upld_storage']);
    } else if (isset($cPost['icon_img_stored']) && $cPost['icon_img_stored']>'') {
        $cPost['icon_path'] = $cPost['icon_img_stored'];
    }
    $conn = new SQLite3($db);
    if ($new) {
        $qry = "INSERT INTO `Social_Media` (`Link_Name`,`Link_Text`,`Icon`,`URL`,`Hidden`) VALUES (:linkname,:linktext,:icon,:url,:hide);";
    } else {
        $qry = "UPDATE `Social_Media` SET `Link_Name`=:linkname,`Link_Text`=:linktext,`Icon`=:icon,`URL`=:url,`Hidden`=:hide WHERE `ID`=:id;";
    }
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':linkname',$cPost['link_name'],SQLITE3_TEXT);
    $stmt->bindValue(':linktext',$cPost['link_text'],SQLITE3_TEXT);
    $stmt->bindValue(':icon',$cPost['icon_path'],SQLITE3_TEXT);
    $stmt->bindValue(':url',$cPost['url'],SQLITE3_TEXT);
    $stmt->bindValue(':hide',$cPost['n_hidden'],SQLITE3_INTEGER);
    if (!$new) {
        $stmt->bindValue(':id',$cPost['n_link_id'],SQLITE3_INTEGER);
    }
    if (!$stmt->execute() || $conn->changes()<1) {
        $msg.=" There was an error ".$action."ing this social media link. Please try again.";
    } else {
        $msg.="Social media link ".$action."ed at ".date('d F, Y h:i:s').'.';
    }
}

if (isset($_POST['delete_social'])) {
    global $db;
    $cPost = cleanServerPost($_POST);
    if ((int)$cPost['n_link_id']===0) {
        $msg='You cannot delete the site RSS link. Try hiding it, or disabling your RSS Feed.';
        return;
    }
    $qry = "DELETE FROM `Social_Media` WHERE `ID`=:id;";
    $conn = new SQLite3($db);
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':id',$cPost['n_link_id'],SQLITE3_INTEGER);
    if (!$stmt->execute() || $conn->changes()<1) {
        $msg=" There was an error deleting this social media link. Please try again.";
    } else {
        $msg="Social media link deleted at ".date('d F, Y h:i:s').'.';
    }
}

if (isset($_POST['save_socials_list'])) {
    global $db;
    $conn = new SQLite3($db);
    $msg="";
    usort($_POST["option"], function ($a, $b) {
        return $a['n_index'] <=> $b['n_index'];
    });
    $indexOrder=0;
    $error = false;
    $qry = "UPDATE `Social_Media` 
        SET  Index_Order=:inorder, Hidden=:hidden
            WHERE ID=:id";
    foreach ($_POST["option"] AS &$opt) {
        if ($error) {
            $msg.=" There was an error saving the changes to your social media links. Please try again.";
            return;
        }
        $cPost = cleanServerPost($opt);
        $indexOrder = ($indexOrder+1);
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(':inorder',$indexOrder, SQLITE3_INTEGER);
        $stmt->bindValue(':hidden',$cPost['n_hidden'], SQLITE3_INTEGER);
        $stmt->bindValue(':id',$cPost['n_link_id'], SQLITE3_INTEGER);
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

if (isset($_POST['edit_rss'])) {
    global $db;
    $cPost = cleanServerPost($_POST);
    $cPost['n_sect_id'] = 0;
    if (isset($cPost['ref_sect']) && is_array($cPost['ref_sect'])) {
        $cPost['ref_sect_list'] = implode(',',$cPost['ref_sect']);
    }
    if ($cPost['n_date_cutoff_mode']==2) {
        $dateCutoff = $cPost['n_date_number'].' '.$cPost['date_unit'];
        $cPost['n_date_cutoff_mode']=2;
    } else {
        $cPost['n_date_cutoff_mode']==1;
        $dateCutoff = $cPost['date_cutoff_strict'];
    }
    $cPost['date_cutoff'] = '['.$cPost['n_date_cutoff_mode'].']'.$dateCutoff;
    if ($cPost['tag_filter_list']) {
        $cPost['tag_filter_list'] = prepTags($cPost['tag_filter_list'], true, true);
    }
    if (isset($cPost['n_item_limit']) && isset($cPost['item_limit_on'])) {
        if (!$cPost['n_item_limit'] || !$cPost['item_limit_on']) {
            $cPost['n_item_limit'] = null;
        } else {
            $cPost['n_item_limit'] = intval(filter_var($cPost['n_item_limit'], FILTER_SANITIZE_NUMBER_INT));
        }
    }
    if (updateRefSects($cPost)) {
        $msg = 'RSS Feed settings saved successfully at '.date('d F, Y h:i:s').'.';
    } else {
        $msg = 'There was an error saving the changes to your RSS Feed settings. Please try again later.';
    }
}

} //end 'if $loggedIn'