<?php
$testing = false;
if (file_exists(dirname(__FILE__).'/_testing.inc.php')) {
    include(dirname(__FILE__).'/_testing.inc.php');
    $testing=true;
}


$db = dirname(__FILE__).'/../data/database.db';

function serializeSettings($globalOnly=true) {
    global $db;
    $conn = new SQLite3($db);
    $settings = array();
    $qry = 'SELECT `Key`, `Value`, `Type` FROM `Settings`';
    if ($globalOnly) {
        $qry .= ' WHERE  `Global`>0;';
    }
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
            case 'checklist':
                $row['Value']=explode(',',$row['Value']);
            break;
            case 'number':
                $row['Value'] = cleanInt($row['Value']);
            break;
            case 'select':
            default:
                // do nothing
            break;
        }
        $settings[$row['Key']] = $row['Value'];
    }
    if (trim($settings['dir']) === '/' || !trim($settings['dir'])) {$settings['dir'] = '';}
    if (!$settings['has_max_img_dimns']) {$settings['max_img_dimns'] = false;}
    if (!$settings['has_max_upld_storage']) {$settings['max_upld_storage'] = false;}
    $settings['standard_permissions']=1;
    $settings['full_permissions']=9;
    return $settings;
}
// run/get settings
$set = serializeSettings();
if (!isset($set['date_format']) || !$set['date_format']) {
    $set['date_format'] = "j F, Y g:i A";
}

// get user info
// NOTE: '$loggedIn' is set in 'validateAdmin.php'
$sessID = ($_SESSION['ID'] ?? null);
$sessKey = ($_SESSION['Key'] ?? null);
$user = array("ID"=>null, "Name"=>null, "Permissions"=>null);
$user['ID'] = ($_SESSION['UserID'] ?? null);
$user['Name'] = ($_SESSION['Username'] ?? null);
$user['Permissions'] = ($_SESSION['Permissions'] ?? 0);
date_default_timezone_set($set['timezone'] ?? 'America/New_York');

function isMasterAccount($permissions=null,$onlyID=null) {
    global $set;
    if (is_null($permissions)) {
        $permissions=($_SESSION['Permissions'] ?? 0);
    }
    if ($permissions>=$set['full_permissions']) {
        if ($onlyID) {
            if ($onlyID===($_SESSION['UserID'] ?? 0)) {
                return true;
            } else if ($onlyID!=($_SESSION['UserID'] ?? 0)) {
                return false;
            }
        }
        return true;
    } else {
        return false;
    }
}



////// get server/path info

// server root + subdir
$root = $_SERVER['DOCUMENT_ROOT'].($set['dir'] ?? null);

// base URL for site
$baseURL = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].($set['dir'] ?? null);

// current folder 
$route = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);

// current URL without query params 
$location = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];

// current URL
$currentURL = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

// base theme URL
$themePath = $root.'/themes/'.$set['theme'];

//////



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

function validID($input){
    if (is_numeric($input) && $input>=0 && !is_float($input)) {
        return true;
    } else {
        return false;
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
    if (!is_string($str)) {
        if (is_array($str)) {
            $str=implode(', ',$str);
        }
        $str=(string)$str;
        error_log('Non-string variable input into function "stripHTML". Variable has been converted to string.');
    }
    if ($nl2br) {$str = nl2br($str);}
    if ($incov) {$str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);}
    $str = htmlspecialchars($str);
    if ($trim) {$str = trim($str);}
    return $str;
}

// removes HTML tags and their contents
// suggested by https://www.php.net/manual/en/function.strip-tags.php
function destroyHTML($str, $exceptions='<a><p><b><i><strong><em><u><del><span><h1><h2><h3><h4><h5><h6>', $invert=false) {
    if ($str) {
        preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($exceptions), $exceptions);
        $exceptions = array_unique($exceptions[1]);
        if ((!$invert ? !in_array('img', $exceptions) : in_array('img', $exceptions))) {
            $str = preg_replace("/<img[^>]+\>/i", "", $str); 
        }
        if ((!$invert ? !in_array('input', $exceptions) : in_array('input', $exceptions))) {
            $str = preg_replace("/<input[^>]+\>/i", "", $str); 
        }
        if ((!$invert ? !in_array('br', $exceptions) : in_array('br', $exceptions))) {
            $str = preg_replace("/<b[^>]+\>/i", "\n", $str); 
        }
        if(is_array($exceptions) && count($exceptions) > 0) {
          if($invert == FALSE) {
            return preg_replace('@<(?!(?:'. implode('|', $exceptions) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $str);
          }
          else {
            return preg_replace('@<('. implode('|', $exceptions) .')\b.*?>.*?</\1>@si', '', $str);
          }
        }
        elseif($invert == false) {
          return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $str);
        }
        return $str;
    }
  }

  // closes any open HTML tags
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

function truncateTxt($txt,$at=240,$trail="...",$noHTML=true) {
    if ($txt) {
        if ($noHTML) {
            //clear and replace HTML
            $txt = strip_tags(str_replace(array('</p>','<br>','<br/>',), PHP_EOL, destroyHTML($txt)));
        }
        if ($at==="Custom" || $at==="custom") {
            $at = strpos($txt,'<!--truncate-->');
            if (strlen($txt)>$at) {
                $cutOff = ($at - strlen($trail));
                $txt = trim(substr($txt,0,$cutOff)).$trail;
            }
        } elseif (is_numeric($at) && intval($at)>0) {
            //$txt = preg_replace("/<p[^>]*><\\/p[^>]*>/", '', $txt); 
            $totalNoTags = strlen(strip_tags($txt));
            $tempTxt=substr($txt,0,($at+25));
            $strLenWithTags = strlen($tempTxt);
            $strLenNoTags=strlen(strip_tags($tempTxt));
            if ($totalNoTags>$at) {
                $posOffset = ($strLenWithTags-$strLenNoTags);
                $at = ($posOffset+$at);
                $cutOff = ($at - strlen($trail));
                $tail=floor($cutOff/4);
                $cutOff = ($cutOff-$tail);
                $tail=strip_tags(substr($txt,$cutOff,$tail));
                $txt = substr($txt,0,$cutOff).$tail;
                $txt = trim($txt).$trail;
            }
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

function cleanInt($num, $abs=false, $mkInt=true){
    $num = filter_var($num, FILTER_SANITIZE_NUMBER_INT);
    if (($mkInt || $abs) && !is_int($num)) {
        $num = intval($num);
    }
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

function fileExt($fileName,$strToLower=true) {
    $ext = pathinfo(basename($fileName),PATHINFO_EXTENSION);
    if ($strToLower) {
        $ext = strtolower($ext);
    }
    return $ext;
}

function cleanUpldName($path, $dir=null) {
    // returns the name of the file without the extension
    $name = str_replace('.'.fileExt($path,false),"",$path);
    if ($dir) {
        $name = str_replace($dir, "", $name);
    }
    return $name;
  }

function insertFilenameTag($filename, $tag, $buffer='_', $forUpload=false) {
    $fileTypeOG = fileExt($filename,false);
    if (strtolower($fileTypeOG) == 'jpeg') {
        $fileTypeNew = 'jpg';
    } else {
        $fileTypeNew = strtolower($fileTypeOG);
    }
    if ($forUpload===true) {
        $newName = cleanFileName($filename); 
    } else {
        $newName = stripHTML($filename);
    }
    if ($buffer) {
        $tag = $buffer.$tag;
    }
    $newName = str_replace('.'.$fileTypeOG, $tag.".".$fileTypeNew, $newName);
    return $newName;
}

function fdbkMsg($type=0,$msg=null,$header=null,$addslashes=false){
    if ($msg) {
        if ($addslashes) {
            $msg = addslashes($msg);
        }
        $msg = trim($msg);
    }
    switch ($type) {
        case 1:
        case "success":
            $typeClass=" success";
            if (!$header) {$header="Success!";}
            break;
        case 2:
        case "caution":
            $typeClass=" caution";
            if (!$header) {$header="Caution";}
            break;
        case 3:
        case "neutral":
            $typeClass=" neutral";
            break;
        case 0:
        case 'error':
        default:
            $typeClass=" error";
            if (!$header) {$header="Error";}
            break;
    }
    $output = '
    <div class="fdbk-msg'.$typeClass.'">
    '.($header ? '<h2>'.$header.'</h2>' : '').'
    '.($msg ? ' <div class="fdbk-txt">'.htmlspecialchars_decode(nl2p($msg)).'</div>' : '').'
    </div>';
    return $output;
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

function timestampToDate($ts=null,$format=null) {
    global $set; $isUnix=true;
    if (!$ts) {
        $ts = time();
    } else if (!is_numeric($ts) && is_string($ts)) {
        $isUnix=false;
        $ts = strtotime($ts);
    }
    if ($format==="form") {
        $format = 'Y-m-d\TH:i';
    } else if (!$format || !is_string($format)) {
        $format = $set['date_format'];
    }
    // handle date w/timezone
    try {
        $date = new DateTime('@'.$ts);
    } catch (Exception $e) {
        return $ts;
    }
    $date->setTimeZone(new DateTimeZone($set['timezone']));
    return $date->format($format);
}

function failedConstrait($sqlite3) {
    if ($sqlite3->lastErrorCode()==19) {
        return true;
    } else {
        return false;
    }
}
function echoIfTesting($input,$tag=null,$echoNow=true,$onlyID=false){
    global $testing;
    if (($onlyID && $onlyID!=$_SESSION['UserID']) || !isMasterAccount()) {
        return;
    }
    if ($tag===null || $tag===false) {
        $tag='Testing &mdash;';
    }
    if ($testing) {
        if (is_null($input)) {
            $input = "[null]";
        } elseif (is_array($input)) {
            $output = '';
            $inputLen = count($input);
            $i=1;
            foreach($input AS $key=>$val) {
                if (is_null($val)) {
                    $val = "[null]";
                } elseif (is_bool($val)) {
                    $val = ($val ? '[true]' : '[false]');
                } elseif (is_string($val)){
                    $val = "'".$val."'";
                } else if (is_array($val)) {
                    $val="Array=>[".echoIfTesting($val,'',false).']';
                }
                $output.= '<b>'.$key.':</b> '.$val.'<br/>';
            }
            $output = '<b><i>'.$tag.' </i></b> <br/>'.$output;
            if ($echoNow) {
                echo $output;
            }
            return $output;
        } else {
            if (is_bool($input)) {
                $input = ($input ? '[true]' : '[false]');
            } else if (is_string($input)){
                $input = "'".$input."'";
            }
            $output = $tag.': '.$input.'<br/>';
            if ($echoNow) {
                echo $output;
            }
            return $output;
        }
    }
}

function jsConsoleLog($input, $echoNow=true) {
    $output = '<script type="text/javascript">
    console.log("'.addslashes((string)$input).'");
    </script>';
    if ($echoNow) {
        echo $output;
    } else {
        return $output;
    }
}

if (!file_exists(dirname(__FILE__).'/PHPMailer') || !is_dir(dirname(__FILE__).'/PHPMailer')) {
    $usingPHPMailer = false;
} else {
    $usingPHPMailer = true;

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
}

//email headers for no-reply email
$emailHeaders = 
            "MIME-Version: 1.0\r\n".
            "Content-Transfer-Encoding: 8bit\r\n".
            "Content-type: text/html; charset=iso-8859-1\r\n".
            "X-Priority: 3\r\n".
            "X-Mailer: PHP".phpversion()."\r\n";

// permitted image filetypes
$validImgTypes = array('jpg','jpeg','png','gif','webp');


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
                    $val = cleanInt(trim($val));
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

function separateUploads($files, $filesLen=null) {
    if (is_null($filesLen)) {
        $filesLen = count($files['name']);
    }
    $keys = array_keys($files);
    $keysLen = count($keys);
    $output = array();
    if ($filesLen) {
        for ($f=0;$f<$filesLen;$f++) {
            for ($k=0;$k<$keysLen;$k++) {
                $output[$f][$keys[$k]] = $files[$keys[$k]][$f];
            }
        }
        return $output;
    } else {
        return null;
    }
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
            // if 'input', return as string rather than array
            $cTags = implode(',',$cTags);
        }
        return $cTags;
    }
}

function addToUnixTime($unixTS,$time=15,$unit="minutes") {
    if (!is_int($unixTS)) {
        $unixTS = intval($unixTS);
    }
    if (!is_int($time)) {
        $time = intval($time);
    }
    switch ($unit) {
        case 'seconds':
            $secs = 1;
            break;
        case 'hours':
            $secs = 3600;
            break;
        case 'days':
            $secs = 86400;
            break;
        case 'minutes':
        default:
            $secs = 60;
        break;
    }
    return ($unixTS+($time*$secs));
}

function pwCmp($pw1,$pw2,$hash=true){
    if (((string)$pw1 && (string)$pw2) && ($pw1 === $pw2)) {
        if ($hash) {
            return hash("sha256", $pw1);
        } else {
            return true;
        }
    } else {
        return false;
    }
}

if (isset($_POST['create_cms_admin'])) {
    // formerly 'submit_credentials' or 'send_credentials'
    $msg= '';
    global $db; global $route; global $emailHeaders;
    $failed = false;
    if (isset($_POST['username']) && $_POST['username']) {
        $name = stripHTML($_POST['username']);
    } else {
        $name = 'Admin';
    }
    if (!($_POST['email'] && $_POST['password'] && $_POST['password2'])) {
        $msg .= 'Please fill out the following fields.';
    } else {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $msg .= 'Please enter a valid email address.<br/>';
            $failed=true;
        }
        if (strcasecmp($_POST['password'], $email) === 0) {
            $msg .= "Your password can't be the same as your email.<br/>";
            $failed=true;
        }
        $password = pwCmp($_POST['password'],$_POST['password2']);
        if ($password === false) {
            $msg .= 'Your password and password confirmation do not match.<br/>';
            $failed=true;
        }
    }
    if ($failed) {
        return;
    } elseif ($password && $email) {
        $conn = new SQLite3($db);
        $dateQry = "UPDATE `Accounts` SET 
                        `Username` = :u_name, 
                        `Temp_Email` = :email, 
                        `Activation_Timestamp` = :exp, 
                        `Activation_Key` = :key,
                        `Password` = :pw,
                        `Account_Created` = :time
                    WHERE `ID` = 1;";
        $stmt = $conn->prepare($dateQry);
        $time = time();
        $exp = addToUnixTime($time,3,"hours");
        $key = bin2hex(random_bytes(22));
        $keyHash = hash("sha256", $key);
        $stmt->bindValue(':u_name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':exp', $exp, SQLITE3_INTEGER);
        $stmt->bindValue(':key', $keyHash, SQLITE3_TEXT);
        $stmt->bindValue(':pw', $password, SQLITE3_TEXT);
        $stmt->bindValue(':time', $time, SQLITE3_INTEGER);
        if ($stmt->execute()) {
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
            $confirmEmail = mail($email, 'Validate your credentials', $body, $emailHeaders);
             if ($confirmEmail) {
                $msg .= "<p>Thank you! A confirmation email has been sent to ".$email.". If it hasn't shown up after a few minutes, 
                check your spam or junk folder.<br/>
                <strong>Your activation key expires in 3 hours (".timestampToDate($exp).").</strong> If you
                do not activate your account before that time, you will have to input your credentials again.</p>";
                $msg=fdbkMsg(1,$msg,null,true);
                $_POST = array();
             } else {
                $msg .= "<p class='red'>The email to validate your account failed to send. Please try again.</p>";
             }
             error_log('Activation link failsafe: '.$activateUrl);
             // // FOR TESTING 
             // global $testing;
                // if ($testing) {
                    // echo $body;
                // }
        } else {
            $msg = fdbkMsg(0,'Credential submission failed. Please try again.');
        }
        $stmt->close();
    }
}

function getCredentials($key) {
    global $db;
    $keyHash = hash("sha256", $key);
    $conn = new SQLite3($db);
    $qry = "SELECT `ID`, `Email`, `Temp_Email`, `Activation_Timestamp`, `Activation_Key`, `Permissions` 
    FROM `Accounts` WHERE `Activation_Key`=? LIMIT 1;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1, $keyHash, SQLITE3_TEXT);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if ($row['Email'] && !$row['Temp_Email']) {
            $row['Email_Valid'] = true;
        } else {
            $row['Email_Valid'] = false;
        }
        if (!$row['Email_Valid']) {
            if ($row['Activation_Timestamp']<time()) {
                $row['Activation_Expired'] = true;
            } else {
                $row['Activation_Expired'] = false;
            }
        } else {
            $row['Activation_Expired'] = null;
        }
        $stmt->close();
        return $row;
    }
    $stmt->close();
    return false;
}

function anointAccount($key=null) {
    global $db; global $set;
    $password=null;
    if (isset($_SESSION)) {
        logout();
    }
    if (is_null($key)) {
        $key = htmlspecialchars($_POST['key'] ?? ($_GET['key'] ?? null));
    }
    $creds = getCredentials($key);
    if (cleanInt($creds['ID'])===1) {
        $addedAccount=false;
    } else {
        $addedAccount=true;
    }
    $keyHash = hash("sha256", $key);
    $username = stripHTML($_POST['username']);
    if ($addedAccount) {
        $pw1 = ($_POST['password'] ?? null);
        $pw2 = ($_POST['password2'] ?? null);
        $password = pwCmp($pw1,$pw2);
    } 
    $time = time();
    $conn = new SQLite3($db);
    $updQry = "UPDATE `Accounts` SET 
    `Username`=:usern, ".($addedAccount ? "`Password`=:pw, " : "")."`Email`=`Temp_Email`, `Activation_Timestamp`=NULL, `Temp_Email`=NULL, `Account_Created`=:time 
    WHERE `Activation_Key`=:akey;";
    $updStmt = $conn->prepare($updQry);
    $updStmt->bindValue(':usern',$username,SQLITE3_TEXT);
    $updStmt->bindValue(':akey',$keyHash,SQLITE3_TEXT);
    $updStmt->bindValue(':time',$time,SQLITE3_INTEGER);
    if ($addedAccount && $password) {
        $updStmt->bindValue(':pw',$password,SQLITE3_TEXT);
    } 
    $result = $updStmt->execute();
    // if install.php still exists and is writable...
    if($result && (file_exists('install.php') && is_writable('install.php'))){
        // ...delete the install.php file
        if (!unlink('install.php')) {
            error_log("The 'install.php' file failed to delete.");
        }
    }
    $updStmt->close();
    return $result;
}

function validateEmail($key=null) {
    global $db; $success=false;
    $emailValid = null; $newEmail = null;
    $expired = true; $hashEquals = false;
    $creds=array();
    if (is_null($key)) {
        $key = htmlspecialchars($_POST['key'] ?? ($_GET['key'] ?? null));
    } else {
        $key = htmlspecialchars($key);
    }
    if ($key) {
        $creds = getCredentials($key);
        if ($creds) {
            $creds['Validated']=false;
            if (!$creds['Activation_Expired']) {
                if (!$creds['Temp_Email']) {
                    $msg = fdbkMsg(0,"Could not find your new email. Try changing it again in your settings.");
                    return $creds;
                } elseif (!$creds['Email_Valid'] && $_SESSION) {
                    $conn = new SQLite3($db);
                    $sessID = session_id();
                    $updQry = "UPDATE `Accounts` SET `Email` = ?, `Temp_Email`=NULL, `Activation_Key`=NULL WHERE `Curr_Sess_ID` = ?;";
                    $updStmt = $conn->prepare($updQry);
                    $updStmt->bindValue(1,$creds['Temp_Email'], SQLITE3_TEXT);
                    $updStmt->bindValue(2,$sessID, SQLITE3_TEXT);
                    if ($updStmt->execute()) {
                        $success=true;
                    }
                    $updStmt->close();
                    $creds['Validated']=$success;
                    $msg="Your email looks good!";
                    return $creds;
                } else {
                    $msg="Your email looks good!";
                    $creds['Validated']=true;
                    return $creds;
                }
            } else {
                $creds['Validated']=true;
                return $creds;
            }
        } else {
            $msg = fdbkMsg(0, 'Credentials do no match or were set incorrectly.');
            
            $creds['Validated']=false;
            return $creds;
        }
    } else {
        $msg = fdbkMsg(0,'Something is wrong with your activation link. Check your invitation email and try again.');
        $creds['Validated']=false;
        return $creds;
    }
}

if (isset($_POST['verify_account'])) {
    $key = htmlspecialchars($_POST['key']);
    if (isset($_SESSION)) {
        logout();
    }
    if (anointAccount($key)) {
        $msg="Account verified! Feel free to login!";
    } else {
        $msg="Account failed to verify. Please try again later.";
    }
}

function setNewPassword($key,$pw1,$pw2) {
    global $db; $valid=false;
    $unixTS = 0;
    $msg = '';
    $keyHash = hash("sha256",htmlspecialchars($key));
    $conn = new SQLite3($db);
    $getQry = "SELECT `Activation_Timestamp` FROM `Accounts` WHERE `Activation_Key` = :actkey LIMIT 1;";
    $getStmt = $conn->prepare($getQry);
    $getStmt->bindValue(':actkey', $key, SQLITE3_INTEGER);
    $result = $getStmt->execute();
    if (!$result) {
        $msg= "Your activation key is incorrect. Check your activation link and try again.";
        return array('Result'=>false, 'Msg'=>$msg);
    } else {
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $unixTS = $row['Activation_Timestamp'];
        }
    }
    $result->finalize();
    $getStmt->close();
    if ($unixTS>time()) {
        $msg= "The activation key for this password reset has expired. Please try again.";
        return array('Result'=>false, 'Msg'=>$msg);
    }
    if ($pw1 && $pw2) {
        // compare and hash
        $password = pwCmp($pw1,$pw2);
        if ($password === false) {
            $msg .= 'Your password and password confirmation do not match.';
            return array('Result'=>false, 'Msg'=>$msg);
        }
    } else {
        $msg .= 'Please fill out both the password and confirmation password fields.';
        return array('Result'=>false, 'Msg'=>$msg);
    }
    if ($password) {
        $updtQry = "UPDATE `Accounts` SET `Password`=:pw, `Activation_Key`=NULL WHERE `Activation_Key` = :actkey;";
        $stmt = $conn->prepare($updtQry);
        $stmt->bindValue(':pw',$password, SQLITE3_TEXT);
        $stmt->bindValue(':actkey',$key, SQLITE3_TEXT);
        if (!$stmt->execute()) {
            $msg = fdbkMsg(0,"A database error occurred. Try again.");
            return array('Result'=>false, 'Msg'=>$msg);
        } else {
            return array('Result'=>true, 'Msg'=>$msg);
        }
    }
}

if (isset($_POST['send_password_reset'])) {
    $msg= '';
    global $db; global $route; global $testing;
    $failed = false; $emailValid = false;
    $conn = new SQLite3($db);
    $email = filter_var($_POST['email'],FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $msg.="Invalid email.";
        return;
    }
    $getQry = "SELECT `Email`, `Temp_Email` FROM `Accounts` WHERE `Email`=:email LIMIT 1;";
    $stmt = $conn->prepare($getQry);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $email = $row['Email'];
            if ($email && !$row['Temp_Email']) {
                $emailValid = true;
            }
    } 
    if ($emailValid) {
        $updtQry = "UPDATE `Accounts` SET 
                    `Activation_Timestamp` = :time, 
                    `Activation_Key` = :key
                    WHERE `Email` = :email;";
        $stmt = $conn->prepare($updtQry);
        $time = time(); 
        $time = addToUnixTime($time,24,"hours");
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
            $body .= '<a href="'.$activateUrl.'">'.$activateUrl.'</a><br/><br/>
            This activation key expires on '.timestampToDate($time);
            $confirmEmail = mail($email, 'CeeMyStuff Password Reset', $body, $emailHeaders);
                if ($confirmEmail) {
                    $msg .= "<p>An email with the link to reset your password has been sent. If it hasn't shown up after a few minutes, 
                    check your spam folder.<br/>
                    You have 24 hours before your activation key for the new password expires (".timestampToDate($time).").</p> ".($testing ? $email.PHP_EOL.$emailHeaders.PHP_EOL.$body : 'null test');
                    $msg=fdbkMsg(1,$msg,null,true);
                    return;
                }
                echoIfTesting($email.PHP_EOL.$emailHeaders.PHP_EOL.$body);
                echo 'TESTING';
        } else {
            $msg = fdbkMsg(0,'Credential submission failed. Please try again.');
        }
    } else {
        $msg = 'The email for this account was never confirmed. Please <a href="'.$route.'">reconfirm your email at the login page</a>.';
        $msg = fdbkMsg(0,$msg);
    }
}

if (isset($_POST['reset_password'])) {
    $newPass = setNewPassword($_POST['key'], $_POST['password'], $_POST['password2']);
    if ($newPass['Result']) {
        $msg = fdbkMsg(1,'Password has been reset! <a href="'.$set['dir'].'/admin">Login here.</a>');
    } else {
        $msg = fdbkMsg(0,$newPass['Msg']);
    }
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
    global $db; $loginSuccess = $clearTempEmail = $locked = false;
    $creds = null;
    $conn = new SQLite3($db);
    $msg = '';
    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $email = stripHTML($_POST['email']);
    }
    $password = hash('sha256', $_POST['password']);
    $qry= "SELECT `ID`, `Username`, `Email`, `Password`, `Curr_Sess_ID`, `Login_Attempts`, `Locked_Until`, 
    `Permissions`, `Temp_Email`, `Activation_Timestamp`
    FROM `Accounts` WHERE `Email` = ? LIMIT 1;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1,$email,SQLITE3_TEXT);
    $result = $stmt->execute();
    if ($result) {
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $creds = $row;
        }
    }
    $result->finalize();
    $stmt->close();
    if (!is_null($creds)) {
        if ($creds['Email'] && $creds['Permissions']===0) {
            $msg='This account has been deactivated. If you want access to the admin panel, please contact an active admin to reinstate your account.';
            return;
        }
        if ($creds['Temp_Email'] && $creds['Activation_Timestamp']<time()) {
            $clearTempEmail = true;
        }
        $loginAttempts = intval($creds['Login_Attempts'] ?? 0);
        if (time()>$creds['Locked_Until'] && 
        ($email === $creds['Email'] && hash_equals($creds['Password'], $password))) {
            if (session_id()) {
                session_commit();
            }
            session_start();
            $sessID = session_create_id();
            $sessKey = hash("SHA256", $_SERVER['HTTP_USER_AGENT'].$sessID);
            session_commit();
            if ($creds['Curr_Sess_ID']) {
                session_id($creds['Curr_Sess_ID']);
                session_start();
                session_destroy();
                session_commit();
            }
            session_id($sessID);
            session_start();
            $_SESSION['ID'] = $sessID;
            $_SESSION['Key'] = $sessKey;
            $_SESSION['UserID'] = $creds['ID'];
            $_SESSION['Username'] = $creds['Username'];
            $_SESSION['Permissions'] = $creds['Permissions'];
            session_commit();
            
            //record new session 
            $updtQry= "UPDATE `Accounts` SET 
                        `Curr_Sess_ID` = :sessid, 
                        `Curr_Sess_Key` = :sesskey,
                        `Login_Attempts` = 0,
                        `Locked_Until` = 0,
                        `Last_Login` = :time".($clearTempEmail ? ", `Temp_Email`=NULL " : null)."
                    WHERE `Email` = :email;";
            $stmt = $conn->prepare($updtQry);
            $time = time();
            $stmt->bindValue(':sessid', $sessID, SQLITE3_TEXT);
            $stmt->bindValue(':sesskey', $sessKey, SQLITE3_TEXT);
            $stmt->bindValue(':time', $time, SQLITE3_INTEGER);
            $stmt->bindValue(':email', $email, SQLITE3_TEXT);
            if ($stmt->execute()) {
                $msg .= 'Logged in!';
                $loginSuccess=true;
                return true;
            } else {
                $msg .= 'Something went wrong when creating your session. Try again.';
                return false;
            }
        } else {
            $msg .= 'Your email or password did not match.';
            $loginAttempts = ($loginAttempts+1);
            $updtQry= "UPDATE `Accounts` SET `Login_Attempts` = :la";
            if ($loginAttempts >= 4) {
                $locked=true;
                $lockedUntil = (time()+(60*8)); //lock for 8 minutes
                $updtQry .= ", `Locked_Until` = :lu";
                $msg .= '<br/>Too many failed login attempts. Try again in a few minutes.';
            }
            if ($clearTempEmail) {
                $updtQry .= ", `Temp_Email`=NULL ";
                $clearTempEmail = false;
            }
            $updtQry .= " WHERE `Email` = :email;";
            $stmt = $conn->prepare($updtQry);
            $stmt->bindValue(':la', $loginAttempts, SQLITE3_INTEGER);
            if ($locked) {
                $stmt->bindValue(':lu', $lockedUntil, SQLITE3_INTEGER);
            }
            $stmt->bindValue(':email', $email, SQLITE3_TEXT);
            $stmt->execute();
            
            $msg .= '<br/><a href="'.$set['dir'].'/admin/account-settings.php?task=pw-reset">Click here if you forgot your password.</a>';
            return false;
        }
    } else {
        $msg = fdbkMsg(0,"Your email or password did not match.");
    }
}

function logout() {
    global $db; global $sessID;
    ob_start();
    if(session_id() <= ''){
        session_start();
    }
    $conn = new SQLite3($db);
    $qry= "UPDATE `Accounts` SET `Curr_Sess_ID`=null, `Curr_Sess_Key`=null WHERE `Curr_Sess_ID`=?;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1,$sessID,SQLITE3_TEXT);
    $stmt->execute();
    $_SESSION = array();
    session_unset();
    session_destroy();
    session_write_close();
    //setcookie(session_name(),'',0,'/');
}

function getPage($page, $key="id", $tag=null){
    global $db;
    global $admin_panel;
    global $loggedIn;
    global $set;
    $iWhere = ($admin_panel===false ? ' WHERE `Hidden`=0 ' : '');
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
            m.`Img_Path` AS Menu_Link_Img, m.`Hidden` AS Menu_Hidden, 
            COUNT(i.`ID`) AS Total_Items
    FROM `Pages` AS p
    LEFT JOIN `Sections` AS s ON p.ID=s.Page_ID
    LEFT JOIN `Automenu` AS m ON p.ID=m.Ref_ID
    LEFT JOIN  (SELECT `ID`, `Sect_ID` FROM `Items`".$iWhere.") AS i ON s.`ID`=i.`Sect_ID`
    WHERE ".$where.($admin_panel===false ? " AND p.`Hidden`=0" : '')." COLLATE NOCASE LIMIT 1;";
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
    global $baseURL; global $testing; global $kickOut;
    if (($_SERVER['REQUEST_URI'] != $baseURL.'/admin/') && 
    ($testing && isset($kickOut) && $kickOut != false)) {
        header('Location: '.$baseURL.'/admin/');
        // if header fails, do it with Javascript instead:
            echo '<script>window.location.replace("'.$baseURL.'/admin/")</script>';
        exit();
    }
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
        $sectPageID = cleanInt($sectPageID);
    }
    $conn = new SQLite3($db);
    $pageList = array();
    // TODO: Fix this, 'Can_Add_Sect' is inconsistent
    $qry = 'SELECT p.`ID`, p.`Name`, p.`Link`, p.`Multi_Sect`, s.`Sect_Num`, p.`Hidden`,
    (CASE
    WHEN (p.`Multi_Sect`=0 AND s.`Sect_Num` >= 1)
    THEN 0
    ELSE 1
    END) AS Can_Add_Sect
    FROM `Pages` AS p
    LEFT JOIN (
    SELECT COUNT(`ID`) AS Sect_Num, `Page_ID` FROM `Sections`
    GROUP BY `Page_ID`
    ) AS s ON s.`Page_ID` = p.`ID`
    WHERE p.`ID`>0';
    if (!$admin_panel || !$loggedIn) {
        $qry .= ' AND p.`Hidden`=0';
    }
    $qry .= ' ORDER BY p.`Hidden`, p.`ID`;';
    $result = $conn->prepare($qry)->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $pageList[]=$row;
    } 
    return $pageList;
}

function getSectList($pageID=false, $excludeRefSects=false, $incThumbInfo=false) {
    global $db;
    global $admin_panel;
    global $loggedIn;
    $conn = new SQLite3($db);
    $sectList = array();
    $qry = 'SELECT s.`ID`, s.`Page_ID`, s.`Name`, s.`Is_Reference`,
    r.`Ref_Sect_IDs`,
    p.`Name` AS Page_Name, p.`Link`, s.`Hidden`';
    if ($incThumbInfo) {
        $qry .=", s.`Thumb_Size`, s.`Thumb_Size_Axis`";
    }
    $qry .=' FROM `Sections` AS s 
    LEFT JOIN `Reference_Sections` AS r ON s.`ID`=r.`Sect_ID`
    LEFT JOIN `Pages` AS p ON p.`ID`=s.`Page_ID` 
    WHERE s.ID>0 ';
    if ($pageID) {
        $pageID = cleanInt($pageID);
        $qry .= ' AND s.`Page_ID`='.$pageID;
    }
    if ($excludeRefSects) {
        $qry .= ' AND s.`Is_Reference`=0';
    }
    if (!($admin_panel && $loggedIn)) {
        $qry .= ' AND s.`Hidden`=0';
    }
    $qry .= ' ORDER BY s.`Hidden`, s.`Page_ID`, s.`Name`;';
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

function getPageSects($id, $idType='page', $onlyIDs=false) {
    global $db;
    global $admin_panel;
    global $loggedIn;
    global $set;
    $sectList = array();
    $id = cleanInt($id);
    $conn = new SQLite3($db);
    if ($onlyIDs) {
        $qry = 'SELECT s.`ID`';
    } else {
        $qry = 'SELECT s.*, COUNT(i.`Sect_ID`) AS Total_Items,
        r.`Ref_Sect_IDs`, r.`Item_Limit`, 
        r.`Date_Cutoff_On`, r.`Date_Cutoff`, r.`Date_Cutoff_Dir`,
        r.`Tag_Filter_On`, r.`Tag_Filter_List`, r.`Tag_Filter_Mode`';
    }
        $qry .= ' FROM `Sections` AS s
        LEFT JOIN `Reference_Sections` AS r ON s.`ID`=r.`Sect_ID`
        LEFT JOIN (SELECT `Sect_ID` FROM `Items`';    
        if (!($admin_panel && $loggedIn)) {
            $qry .= ' WHERE `Hidden`=0';
        }
        $qry .=') AS i ON s.`ID`=i.`Sect_ID` ';
        if ($idType==='section') {
            $qry .= 'WHERE s.`ID` = :id';
        } else {
            $qry .= 'WHERE s.`Page_ID` = :id';
        }
        if (!($admin_panel && $loggedIn)) {
            $qry .= ' AND s.`Hidden`=0';
        }
        $qry .=' GROUP BY s.`ID`
        ORDER BY s.`Page_Index_Order`';
        if ($idType==='section') {$qry .=' LIMIT 1';}
        $qry .=';';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if ($onlyIDs) {
            $sectList[] = $row['ID'];
        } else {
            $row['Text'] = strDecode($row['Text']);
            $row['Item_Click_Area'] = explode(',',$row['Item_Click_Area']);
            $sectList[]= $row;
        }
    } 
    $stmt->close();
    return $sectList;
}

function getSectInfo($id) {
    global $db;
    $id = cleanInt($id);
    $conn = new SQLite3($db);
    $qry = 'SELECT s.*, 
    r.`Ref_Sect_IDs`, r.`Item_Limit`, r.`Date_Cutoff_On`, r.`Date_Cutoff`, r.`Date_Cutoff_Dir`,
        r.`Tag_Filter_On`, r.`Tag_Filter_List`, r.`Tag_Filter_Mode`,
    p.`ID` AS Page_ID, p.`Name` AS Page_Name, p.`Link` AS Page_Link, p.`Hidden` as Page_Hidden
    FROM `Sections` AS s
    LEFT JOIN `Reference_Sections` AS r ON s.`ID`=r.`Sect_ID`
    LEFT JOIN `Pages` AS p ON s.`Page_ID`=p.`ID`
    WHERE s.`ID` = :id LIMIT 1;';
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
        $ids[$i] = cleanInt($ids[$i]);
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
    if ($orderDir==1 || $orderDir==='DESC') {
        $orderDir = ' DESC';
    } else {
        $orderDir = ' ASC';
    }
    switch ($orderBy) {
        case 'Location':
            $order = 'p.`Name`'.$orderDir.', s.`Name`'.$orderDir;
            break;
        case 'Custom':
            $order = $table.'`Sect_Index_Order`'.$orderDir;
            break;
        case 'Random':
            $order = 'RANDOM()';
            break;
        case 'ID':
        case 'Title':
            $order = $table.'`'.$orderBy.'`'.$orderDir;
            break;
        case 'Date':
        default:
            $order = $table.'`Publish_Timestamp`'.$orderDir;
            break;
    }
    $qryStr = " ORDER BY ".$order;
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

function getItems($sect=null, $pageNum=1, $pgAfter=null, $tag=null, $orderBy=null, $orderDir=null) {
    // NOTE: '$sect' may be an ID number, list of ID numbers, or it may be an array with Section info
    global $db;global $set;global $admin_panel;global $loggedIn;
    $tagFilter=$tagIndex=$allItems=$multiSect=false;
    $isRef=0; //unless proven otherwise
     if (is_null($sect)) { 
        $sectID = null;
        $allItems=true;
        $orderBy = ($orderBy ? $orderBy : 'Title');
        $orderDir = (!is_null($orderDir) ? $orderDir : 0);
    } elseif (!is_array($sect)) {
        $sectID = (int)$sect;
        $orderBy = ($orderBy ? $orderBy : 'Date');
        $orderDir = (!is_null($orderDir) ? $orderDir : 1);
    } elseif (isset($sect['Name'])) {
        $sectID = $sect['ID'];
        $isRef=($sect['Is_Reference'] ?? 0);
        $orderBy = ($orderBy ? $orderBy : $sect['Order_By']);
        $orderDir = (!is_null($orderDir) ? $orderDir : $sect['Order_Dir']);
    } else {
        $multiSect=true;
        if (!is_array($sect)) {
            $sects=explode(',', $sect);
        } else {
            $sects=$sect;
        }
    }
    if ($tag && is_string($tag)) {
        $tagIndex=true;
        $sect['Tag_Filter_List'] = array(stripHTML($tag));
        $sect['Tag_Filter_On']=1;
        $sect['Tag_Filter_Mode']=2;
    }
    $conn = new SQLite3($db);
    $items=$ids=array();
    if ($isRef || $multiSect) {
        if ($multiSect && isset($sects)) {
            $ids = $sects;
        }
        if ($isRef) {
            if ($sect['Ref_Sect_IDs'] && !is_array($sect['Ref_Sect_IDs']) && is_string($sect['Ref_Sect_IDs'])) {
                array_push($ids, ...explode(',',$sect['Ref_Sect_IDs']));
            } elseif (is_array($sect['Ref_Sect_IDs'])) {
                array_push($ids, ...$sect['Ref_Sect_IDs']);
            }
        }
        if (is_array($ids) && !empty($ids)) {
            $idsLen = count($ids);
        } else {
            $idsLen = 0;
        }
        for ($i=0;$i<$idsLen;$i++) {
            $ids[$i] = cleanInt($ids[$i]);
        }
    } else {
        $ids = array(cleanInt($sectID));
        $idsLen = 1;
    }
    $qryOrderBy = itemQryOrder($orderBy,$orderDir,'i');
    if ($pgAfter && !$admin_panel) {
        if (isset($sect['Item_Limit']) && $sect['Item_Limit']<$pgAfter) {
            $lim = $sect['Item_Limit'];
        } else {
            $lim=$pgAfter;
        }
        $lim = cleanInt($lim);
        $strt = paginateStrt($pageNum, $lim);
        $qryLimit = ' LIMIT '.$strt.', '.$lim;
    } elseif ($sect['Item_Limit'] ?? null) {
        $sect['Item_Limit'] = cleanInt($sect['Item_Limit']);
        $qryLimit=" LIMIT ".$sect['Item_Limit'];
    } else {
        $qryLimit="";
    }
    $qry = "SELECT i.*,";
    if ($admin_panel && $loggedIn) {
        $qry .= " CASE
        WHEN `Publish_Timestamp`>=strftime('%s','now')
        THEN 1
        ELSE 0
        END AS Queued,";
    }
    $qry .= " s.`Page_ID`, ";
    if ($allItems || $orderBy='Location') {
        $qry .= " s.`Name` AS Section_Name, p.`Name` AS Page_Name, ";
    }
    $qry .= " p.`Link` AS Page_Link FROM `Items` AS i 
    LEFT JOIN `Sections` AS s ON i.`Sect_ID` = s.`ID`
    LEFT JOIN `Pages` AS p ON s.`Page_ID`=p.`ID`";
    if (!$allItems || $isRef || $multiSect || $tagIndex) {
        $qry .= " WHERE ";
    }
    if (!$allItems || $multiSect) {
        if ($idsLen) {
            $qry .= "(";
            for ($i=0;$i<$idsLen;$i++) {
                $qry .= " Sect_ID = ? ";
                if (($i+1)<$idsLen) {
                    $qry .= "OR";
                }
            }
            $qry .= " ) ";
        } else {
            $qry .= " 1 ";
        }
    }
    if ($isRef && $sect['Date_Cutoff_On'] && $sect['Date_Cutoff']) {
        if (!$allItems) {$qry .= " AND";}
        $qry .= " Publish_Timestamp";
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
            $qryAdd = " UPPER(i.`Tags`) LIKE ? ESCAPE '\' ";
        } else {
            // for 'Excludes items with tags'
            $qryAdd = " UPPER(i.`Tags`) NOT LIKE ? ESCAPE '\' ";
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
        if (!isset($sect['Date_Cutoff_On']) || !$sect['Date_Cutoff_On'] || $sect['Date_Cutoff_Dir']==1 || $date>date('Y-m-d\TH:i')) {
            $qry .= " AND `Publish_Timestamp`<=strftime('%s','now') ";
        }
    }
    $qry .= $qryOrderBy.$qryLimit.";";
    $stmt = $conn->prepare($qry);
    if (!$allItems || $multiSect) {
        $index=1;
        foreach ($ids AS $id) {
            $stmt->bindValue($index, $id, SQLITE3_INTEGER);
            $index++;
        }
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
        $items[] = $row;
    } 
    return $items;
}

function getItemIDs($sectID=null, $orderBy='Date', $orderDir=false) {
    global $db;global $loggedIn;global $admin_panel;
    $itemIDs = array();
    $sectID = cleanInt($sectID);
    $conn = new SQLite3($db);
    $qryOrderBy = itemQryOrder($orderBy,$orderDir);
    $qry = "SELECT `ID`, `Title`";
    if ($admin_panel) {
        $qry .= ", CASE
        WHEN `Publish_Timestamp`>=strftime('%s','now')
        THEN 1
        ELSE 0
        END AS Queued ";
    }
    $qry .= " FROM `Items`";
    if (!is_null($sectID)) {$qry .= " WHERE `Sect_ID` = :sectid ";}
    if (!$loggedIn || !$admin_panel) {
        if (is_null($sectID)) {$qry .= " WHERE";}
        else {$qry .= "AND";}
        $qry .= " `Hidden`=0 AND `Publish_Timestamp`<=strftime('%s','now')";
    }
    $qry .= $qryOrderBy.';';
    $stmt = $conn->prepare($qry);
    if (!is_null($sectID)) {$stmt->bindValue(':sectid', $sectID, SQLITE3_INTEGER);}
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
    $id = cleanInt($id);
    $conn = new SQLite3($db);
    $qry = 'SELECT i.*, 
        s.`Name` AS Sect_Name, s.`Hidden` AS Sect_Hidden, 
        p.`ID` AS `Page_ID`, p.`Name` AS Page_Name, p.`Link` AS Page_Link, p.`Hidden` AS Page_Hidden
    FROM `Items` AS i 
    LEFT JOIN `Sections` AS s ON i.`Sect_ID`=s.`ID`
    LEFT JOIN `Pages` AS p ON s.`Page_ID`=p.`ID`
    WHERE i.`ID` = :itemid';
    if (!$admin_panel || !$loggedIn || (isset($view) && $view)) {
        $qry .= ' AND i.`Hidden`=0';
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
        ///
        $row['Tags'] = prepTags($row['Tags'],false,true);
        return $row;
    } 
}

function getSelectedItems($ids=array(), $pageNum=1,$pgAfter=null,$orderBy=null,$orderDir=null) {
    if (empty($ids)) {
        return false;
    }
    global $db;
    $items = array();
    $conn = new SQLite3($db);
    $qryOrderBy = itemQryOrder($orderBy,$orderDir);
    $qry = "SELECT `ID`,`Title`,
    `Img_Path`, `Img_Thumb_Path`, `Text`, `File_Path`, `Embed_HTML`, `Tags`, `Hidden`,
    CASE
        WHEN `Publish_Timestamp`>=strftime('%s','now')
        THEN 1
        ELSE 0
        END AS Queued
    FROM `Items` WHERE ";
    $first=true;
    foreach ($ids AS $id) {
        if (!$first) {
            $qry .= " OR ";
        }
        $qry .= "`ID`=".$id;
        $first=false;
    }
    $qry .= $qryOrderBy.";";
    $stmt = $conn->prepare($qry);
    $result = $stmt->execute();
    if (!$result) {
        $msg = fdbkMsg(0);
        return false;
    } else {
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $row['Tags'] = prepTags($row['Tags'],false,true);
            $items[]= $row;
        }
    } 
    $stmt->close();
    return $items;
}

function getSocials($smID=null) {
    global $db;
    global $admin_panel; global $loggedIn;
    if (!is_null($smID) && is_numeric($smID)) {
        $smID=cleanInt($smID);
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
    $rssItems = getItems($settings);

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
    switch ($setShowTitle) {
        case 2:
            // do nothing
            break;
        case 1:
            $title = '<h3 class="item-title">'.$title.'</h3>';
            break;
        case 0:
        default:
            $title = '';
        break;
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
        case 1: // truncate at character number
            // since we can't predict whether or not the truncation is going to stop right smack in middle of an HTML tag, we leave 'truncateTxt' on its default setting to obliterate any HTML.
            $text='<p>'.truncateTxt($text, $truncAt).'</p>';
            break;
        case 3: // custom truncation
            if ($text) {
                if (str_contains($text, '<!--truncate-->')) {
                    // Allow HTML tags if the truncation is custom
                    $text=truncateTxt($text, "Custom", null, false);
                }
            }
            // if there's no indicator for where to put the custom truncation, show the full text
            // ...which is to say, don't break here
        case 2: // show full text
            // do nothing
         break;
        default:
            $text = '';
            break;
    }
    if ($text) {
        $text = '<div class="item-text">'.closeHTML($text).'</div>';
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

function showFile($filePath, $sectPres, $itemPres, $linkTxt=false) {
    global $set; global $route; global $root;
    if ($filePath) {
        if (!$linkTxt) {
            $linkTxt = 'Click here';
        }
        if ($itemPres===null) {
            $itemPres=$sectPres;
        }
        $localFile = $set['dir'].$filePath;
        $globalFile = $route.$filePath;
        switch ($itemPres) {
            case 2:
                $file = '<a class="item-file link" href="'.$localFile.'">'.$linkTxt.'</a>';
            break;
            case 3:
                $file = '<a class="item-file dnld" href="'.$localFile.'" download>'.$linkTxt.'</a>';
            break;
            case 4:
                $mime = mime_content_type($root.$filePath);
                $file = '<audio class="item-file aud-player" controls>
                            <source src="'.$globalFile.'" type="'.$mime.'">
                            Your browser does not support this audio player. 
                            <a href="'.$localFile.'" target="_blank">Click here to open the file instead.</a>
                        </audio>';
            break;
            case 5:
                $mime = mime_content_type($root.$filePath);
                $file = '<video class="item-file vid-player" controls>
                            <source src="'.$globalFile.'" type="'.$mime.'">
                            Your browser does not support this video player. 
                            <a href="'.$localFile.'" target="_blank">Click here to open the file instead.</a>
                        </video>';
            break;
            case 0:
                $file = '';
                break;
            case 1:
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
function serializeItemTags($tags, $pageLink, $tagDisplay=3, $spacer=null, $spacerOnEnds=0) {
    global $set;
    // $tagDisplay Index:
    // 0 - none
    // 1 - text
    // 2 - html list
    // 3 - links
    $path = $_SERVER['REQUEST_URI'];
    if (!$tagDisplay) {
        return '';
    } elseif ($tagDisplay===3) {
        $tagLinks=true;
    } else {
        $tagLinks=false;
    }
    if ($tags) {
        if (!is_array($tags)) {
            $tags = prepTags($tags, false);
        }
        $tagsLen = count($tags);
    } else {
        $tags=array();
    }
    $aEnd = ($tagLinks ? '</a>' : '');
    $tagOutput = '';
    if ($tagDisplay>1) {$tagOutput = '<ul class="item-tag-list">';}
    if ($spacerOnEnds===1 || $spacerOnEnds>2) {
        $tagOutput .= $spacer;
    }
    $i = 0;
    foreach ($tags AS $tag) {
        if ($tagDisplay>1) {$tagOutput.= "<li class='item-tag'>";}
        if ($tagLinks) {
            $tagOutput.= '<a href="'.$set['dir'].'/'.urlencode($pageLink).'?tag='.urlencode($tag).'">';
        }
        $tagOutput.= $tag.$aEnd;
        if ($tagDisplay>1) {$tagOutput.= "</li>";}
        $i++;
        if ($i<$tagsLen && $spacer) {
            $tagOutput.= $spacer;
        }
    } unset($tag);
    if ($spacerOnEnds>1) {
        $tagOutput .= $spacer;
    }
    if ($tagDisplay>1) {$tagOutput = '</ul>';}
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
        $onClickAction = $sect['On_Click_Action'];
        $image_src=$thumbnail_src=$filepath_src=$srcImgFull=$srcImgThumb=$srcFilePath = '';
        if ($item['Img_Path']) {$image_src = $srcImgFull = $set['dir'].$item['Img_Path'];}
        if ($item['Img_Thumb_Path']) {$thumbnail_src = $srcImgThumb = $set['dir'].$item['Img_Thumb_Path'];}
        if ($item['File_Path']) {$filepath_src = $srcFilePath = $set['dir'].$item['File_Path'];}
        $text_full = $textFull = $item['Text'];
        $alt = addslashes($item['Img_Alt_Text']);
        $title_text = $item['Title'];
        $title = showTitle($sect['Show_Item_Titles'], $item['Title']);
        if ($title) {
            $title = addItemLinks($sect['Item_Click_Area'], $id, $title, 'Title', $onClickAction, $sect['ID']);
        }
        $text = showText($sect['Show_Item_Text'], $textFull, ($sect['Truncate_Text_At'] ?? 140));
        //$isTruncated = strlen($textFull)>strlen($text);
        if ($text) {
            $text = addItemLinks($sect['Item_Click_Area'], $id, $text, 'Text', $onClickAction, $sect['ID']);
        }
        $image = showImage($sect['Show_Item_Images'], $item['Img_Path'], $item['Img_Thumb_Path'], $alt, $item['Title']);
        if ($image) {
            $image = addItemLinks($sect['Item_Click_Area'], $id, $image, 'Image', $onClickAction, $sect['ID']);
            $image_full = $imageFull = '<img src="'.$srcImgFull.'" alt="'.($item['Img_Alt_Text'] ? $item['Img_Alt_Text'] : $item['Title']).'">';
        }
        $file = showFile($item['File_Path'], $sect['Show_Item_Files'], $item['Show_File'], ($sect['Default_File_Link_Text'] ?? null));
        if ($file) {
            $file = addItemLinks($sect['Item_Click_Area'], $id, $file, 'File', $onClickAction, $sect['ID']);
        }
        $embed = $item['Embed_HTML'];
        if ($embed) {
            $embed = closeHTML(strDecode($embed));
        }
        $tags = serializeItemTags($item['Tags'],$item['Page_Link'],$sect['Show_Item_Tags'],$sect['Tag_List_Spacer'], $sect['Tag_Spacer_On_Ends']);
        $date = showDate($sect['Show_Item_Dates'],$item['Date']);
        $class = className($item['Title']);
        $viewLink = '';
        if ($onClickAction && (in_array('Link',$sect['Item_Click_Area']) || $sect['Item_Click_Area']=='Link')) {
            $viewLink = addItemLinks($sect['Item_Click_Area'], $id, $sect['Default_Item_Link_Text'], 'Link', $onClickAction, $sect['ID']);
        }

        if ($item['Format'] <= '' && isset($sect['Default_Item_Format'])) {
            $item['Format'] = $sect['Default_Item_Format'];
        }

        $formatFile = $root.$item['Format'];
        $dataAttr = ($onClickAction==2 ? ' data-lightbox="section-'.$sect['ID'].'" ' : ' ' );
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
            $itemContent .= $viewLink;
        }
        $itemContent = addItemLinks($sect['Item_Click_Area'], $id, $itemContent, 'All', $onClickAction, $sect['ID']);
        $itemContent = $itemElem.$itemContent.'</div>';
        if ($onClickAction==2) { //lightbox
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

function mkLightbox($content, $class){
    $lightbox= '<script type="text/javascript">
    var lbArrs = [];
    </script>'.$content.'
    <script src="components/_js/lightbox.js"></script>

<div id="lightbox" class="'.$class.'">
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
    return $lightbox;
}

function printPageSects($sectList=false, $pageNum=1, $pgAfter=null, $paginator='', $tag=null, $itemPrvw=false, $pageInfo=null) {
    global $set; global $root; global $themePath;
    if (!$sectList) {
        return '<!-- There are no sections associated with this page. -->';
    } elseif (!is_array($sectList)) {
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
         $text = (string)$sect['Text'];
         if (trim($text)==='' || $text===null) {
            $text='';
         }
        $class = className($sect['Name']);
        $itemList = getItems($sect,$pageNum,$pgAfter,($tag ?? false));
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
        $lbClass='';
        if ($sect['Format'] && file_exists($formatFile)) {
            ob_start();
            include($formatFile);
            $format = ob_get_clean();
            if ($hasLightbox===true) {
                $lbClass='lb-format-'.className(basename($sect['Lightbox_Format'],'.php'));
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
        $content = mkLightbox($content, $lbClass);
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
        p.`Name` AS Page_Name, p.`Link`, p.`Hidden` AS `Page_Hidden`
        FROM `Automenu` AS m
            LEFT JOIN `Pages` AS p ON m.`Type_Code`=1 AND p.`ID`=m.`Ref_ID`";
    if (!$admin_panel || !$loggedIn) {
        $qry .= " WHERE m.`Hidden`=0";
    }
    $qry .= " ORDER BY m.`Index_Order`;";
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
    $id=cleanInt($id);
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
    $menuLinks=array();
    if (!$subIndex) {
        $menuContent='<nav id="site-menu"> <ul class="automenu">';
    } else {
        $isIndex = true;
        $subIndex= urldecode($subIndex);
        $menuContent = '<ul class="automenu-submenu-index">';
    }
    $qry = "SELECT m.*, p.`Name` AS Page_Name, p.`Link` FROM `Automenu` AS m
            LEFT JOIN `Pages` AS p ON m.`Type_Code`=1 AND p.`ID`=m.`Ref_ID` 
            WHERE m.`Hidden`=0
            ORDER BY m.`Index_Order`;";
    $result = $conn->prepare($qry)->execute();
    $iMainLink=0;
    $inSubmenu=$leaveOpen= false;
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $menuLinks[]=$row;
    }
    foreach ($menuLinks AS $link) {
        // define type
        $link['Link_Type'] = addMenuLinkType($link['Type_Code'], $link['Ref_ID'], $link['Ext_Url']);

        //If the link in a submenu, open the submenu ul element...
        if ($link['Submenu']>=1 && !$inSubmenu) {
            $inSubmenu = true;
            if (!$indexOpen) {
                $menuContent .= '<ul class="automenu-submenu">';
                $leaveOpen=true;
            }
        } 
        //If we are no longer in a submenu, close the submenu list
        if ($link['Submenu']<=0 && $inSubmenu) {
            $inSubmenu = false;
            $menuContent .= '</a></li></ul>';
            if ($isIndex) {
                $indexOpen = false;
            }
        }
        $menuContent .= '</a>';
        // If this is not the first Main entry in the automenu, close the previous entry.
        if ($iMainLink>0) {
            if (!$leaveOpen) {
                $menuContent .= '</li>';
            }
            $leaveOpen=false;
        }
        // If this a link to a CeeMyStuff page, use the Page name as the link
        if ($link['Type_Code']==1 && !is_null($link['Ref_ID'])) {
            $linkText = $link['Page_Name'];
        } else {
            // Alternatively, set its designated link text there
            $linkText = $link['Link_Text'];
        }
        // If this is a CeeMyStuff menu index page, but the link text in a header
        if ($isIndex && strcasecmp($linkText,$subIndex)===0) {
            $indexOpen=true;
            $menuContent .= '<h3 class="submenu-index-header">'.$linkText.'</h3>';
        }

        // Determine the build of the anchor (a/link) element
        $target='';
        switch ($link['Type_Code']) {
            case 1: // Internal Page
                $href= $set['dir'].'/'.$link['Link'];
            break;
            // case 2: // Section Index
                //break;
            case 8 : // External Link
                $href= $link['Ext_Url'];
                $target=' target="_blank"';
            break;
            // case 9: // Heading
                //break;
            default :
                $href= $set['dir']."/menu-submenu-index/".urlencode(strtolower($liInner));
            break;
        }
        
        // If menu is set to display as images and an image exists, use that to fill the automenu li element
        if ($set['menu_format'] === 'Images' && $link['Img_Path']>'' && file_exists($root.$link['Img_Path'])) {
            $liInner = '<img src="'.$set['dir'].$link['Img_Path'].'" alt="'.$link['Page_Name'].'" title="'.$link['Page_Name'].'">';
        } else {
            $liInner = $linkText;
        }

        $classes= className($link['Link_Type']).' '.className($linkText);
        $linkContent = '<li class="automenu-item '.$classes.'">
        <a class="automenu-link '.$classes.'"'.$target.' href="'.$href.'">
                    '.$liInner;
        if (!$isIndex || ($inSubmenu && $indexOpen)) {
            $iMainLink++;
            $menuContent .= $linkContent;
        }
    }
    $menuContent .= '</a></li></ul>';
    if (!$isIndex) {
        $menuContent .= '</nav>';
    }
    return $menuContent;
}

function serializeSocials() {
    global $set; global $root;
    $socials=getSocials();
    $output = '<article class="cms-socials">
    <ul class="cms-socials-list">';
    foreach ($socials AS $social) {
        $id = ($social['ID'] ? $social['ID'] : 'rss');
        if ($set['sm_format']==='Icons' && $social['Icon']>'') {
            if (strtolower(fileExt($social['Icon']))==='svg') {
                $linkText = file_get_contents($root.$social['Icon']);
            } else {
                $linkText = '<img class="sm-icon" src="'.$set['dir'].$social['Icon'].'" alt="'.$social['Link_Name'].'">';
            }
        } else {
            $linkText = htmlspecialchars_decode($social['Link_Text']);
        }
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
            $content['Show_File'] = $cPost['n_show_file'];
            $content['Default_File_Link_Text'] = ($cPost['file_link_text'] ?? null);
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

function printPage($page=1,$pKey=1,$pageType=null) {
    global $db; global $set;
    global $root; global $route; global $themePath;
    $menuIndex = $singleItem = $singleSect = $isItemPrvw = $previewArea = $tagIndex = false;
    $conn = New SQLite3($db);
    switch ($pageType) {
        case 'item' :
            $singleItem = true;
            $pKey=cleanInt($pKey);
        break;
        case 'section' :
            $singleSect = true;
            $pKey=cleanInt($pKey);
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
            $feedback = ($page['Msg'] ?? null);
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
    $paginator = '';
    if ($singleItem === true) {
        // vars for single item pages
        $item = getItem($pKey);
        $sectInfo = getSectInfo($item['Sect_ID']);
        $name = $item['Title']; 
        $pageName = ($item['Page_Hidden']<1 ? $item['Page_Name'] : false); 
        $meta_text = truncateTxt($item['Text']);
        $page['Format'] = $sectInfo['View_Item_Format'];
        $pageURL = ($item['Page_Hidden']<1 ? $set['dir'].'/'.$item['Page_Link'] : false);
        $item['Page_Link'] = ($item['Page_Hidden']<1 ? $item['Page_Link'] : false);
        $pageLink = ($item['Page_Hidden']<1 ? '<a class="back-to-page" href="'.$pageURL.'">Back to '.$item['Page_Name'].'</a>' : false);
        if ($sectInfo['Paginate_Items']) {
            $itemList = getItemIDs($item['Sect_ID'], $sectInfo['Order_By'], $sectInfo['Order_Dir']);
            $paginator = printItemPaginator($itemList, $pKey, $item['Page_Link'], $pageName);
        }
    } elseif ($singleItem !== true && $menuIndex) {
        // vars for menu indexes
        $meta_text = truncateTxt($page['Meta_Text']);
        $name = ($page['Name'] ?? null);
    } else {
        // vars for pages
        if (!$singleSect) {
            $sectList = ($page['ID'] != "New" ? getPageSects($page['ID']) : null);
        } else {
            $sectList = getPageSects($pKey,'section');
            $sect=$sectList[0];
            $page['Format'] = $sect['Index_Format'];
        }
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
        if (!$singleSect) {
            $name = $page['Name'];
            $meta_text = truncateTxt($page['Meta_Text']);
            $section_content = printPageSects($sectList,$pageNum,$pgAfter,$paginator,($tagIndex ? $tag : null),($isItemPrvw ? $prvwContent : false),($isItemPrvw ? $page : null));
        } else {
            $name = $sect['Name'];
            $meta_text = truncateTxt($sect['Text']);
        }
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
        <title>'.(isset($meta_title) ? $meta_title : $set['site_name'].':').' '.$name.'</title>
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
        $file = showFile($item['File_Path'], 1, $item['Show_File'], ($sectInfo['Default_File_Link_Text'] ?? null));
        $date = '<div class="item-view item-date">'.$item['Date'].'</div>';
        $embed = strDecode($item['Embed_HTML']);
        $tags = serializeItemTags($item['Tags'],$item['Page_Link'],$sectInfo['Show_Item_Tags'],$sectInfo['Tag_List_Spacer'], $sectInfo['Tag_Spacer_On_Ends']);
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
            if ($image) {$content .= $image; }
            if ($embed) {$content .= $embed;}
            if ($text) {$content .= $text;}
            if ($file) {$content .= $file;}
            $content .= '</div></main>';
        }
        ob_start();
        include($footer);
        $content .= ob_get_clean();
        
    } 
    /////// TODO: finish section indexes /////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////
    elseif ($singleSect===true) {
        $id=cleanInt($sect['ID']);
        $class = className($sect['Name']);
        $title = '<h2 class="sect-title">'.$sect['Name'].'</h2>';
        $text = (string)$sect['Text'];
        if ($sect['Header_Img_Path']>''){
            $image = '<img src="'.$set['dir'].$sect['Header_Img_Path'].'" alt="'.$sect['Name'].' Header">';
            $srcImgFull = $set['dir'].$sect['Header_Img_Path'];
        } else {$srcImgFull=$image=null;}
        $itemList = getItems($sect,$pageNum,$pgAfter,($tag ?? false));
        $items_content = '<!-- No items found. -->';
        $hasLightbox = false;
        if ($itemList) {
            $items_content = printPageItems($itemList, $sect);
        }
        $hasLbClose = "false";
        $hasLbArrows = "false";
        $dataAttr='';
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
        ///////
        ob_start();
        include($header);
        $content .= ob_get_clean();
        // NOTE: $page['Format'] has been rewritten to the section index format.
        if ($page['Format'] && file_exists($formatFile)) {
            ob_start();
            include($formatFile);
            $content .= ob_get_clean();
        } 
        ///////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////
        else {
            $content .= '<main id="section_index_'.$sect['ID'].'" class="section-index '.$class.'">
            <div id="section_'.$id.'" class="section '.$class.'">';
            if ($title) {
                $content .= '<h3 class="index-section-title">'.$title.'</h3>';
            }
            $content .= '<!-- No valid section index format assigned. -->';
            if ($image) {$content .= $image; }
            if ($text) {$content .= $text;}
            if ($itemList) {$content .= '<div class="sect-items">'.$items_content.'</div>';}
            $content .= '</div></main>';
            if ($hasLightbox===true) {
                $lbClass=('lb-format-'.$class ?? null);
                $content = mkLightbox($content, $lbClass);
            }
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
        // regular pages
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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// v ADMIN FUNCTIONS v //////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if (isset($loggedIn) && $loggedIn===true) {


function fetchSettings($selectKeys=false,$selectVals=null) {
    global $db;
    $conn = new SQLite3($db);
    $settings = array();
    $vals='';
    if ($selectVals) {
        if (!is_array($selectVals)) {
            $selectVals=explode(',',$selectVals);
        }
        $first=true;
        foreach($selectVals AS $val) {
            $val = trim($val);
            $add = '`'.$val.'`';
            if (!$first) {
                $add = ', '.$add;
            }
            $vals .= $add;
            $first = false;
        } unset($val);
    } else {
        $vals='*';
    }
    $qry = 'SELECT '.$vals.' FROM `Settings` WHERE';
    if ($selectKeys) {
        if (!is_array($selectKeys)) {
            $selectKeys=explode(',',$selectKeys);
        }
        $selectsLen = count($selectKeys);
        $first = true;
        foreach ($selectKeys AS &$i) {
            $i = trim($i);
            $add = ' `Key` = ? ';
            if (!$first) {
                $add = ' OR '.$add;
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
    if ($selectKeys) {
        $index = 1;
        foreach ($selectKeys AS &$i) {
            $stmt->bindValue($index, $i, SQLITE3_TEXT);
            $index++;
        } unset($i);
    }
    $result = $stmt->execute();
    if ($result) {
        if (!$selectKeys) {
            $info = array();
            $display = array();
            $advanced = array();
        }
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($row['Type']==='checklist') {
                $row['Value']=explode(',',$row['Value']);
            }
            if (!$selectKeys) {
                if ($row['Options']) {
                    $row['Options'] = explode(',', $row['Options']);
                    for ($i=0;$i<count($row['Options']);$i++) {
                        $row['Options'][$i] = trim($row['Options'][$i]);
                    }unset($i);
                }
                if ($row['Heading']) {
                    $settings[$row['Heading']][] = $row;
                }
            } else if ($selectsLen===1) {
                return $row;
            } else {
                $settings[] = $row;
            }
        }
        return $settings;
    } else {
        $msg = fdbkMsg(0,"An error occurred while trying to fetch your settings. Please try again.");
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
    $conn = new SQLite3($db);
    $msg= "";
    $error = false;
    //$_POST = array_map('cleanServerPost', $_POST);
    $cPost=cleanServerPost($_POST);
    $setCmp = serializeSettings(false);
    foreach ($setCmp AS $key=>&$val) {
        if ($val===true) {$val="checked";};
    } unset($val);
    if (!empty($_FILES)) {
        require_once 'handleFiles.inc.php';
    }
        $dir = '/assets/uploads/settings/';
        // header
        if (isset($_FILES['header_img']) && $_FILES['header_img']['name']>'') {
            $cPost['header_img'] = createFilePath(
                                            $dir, 
                                            $_FILES['header_img'],
                                            $validImgTypes,
                                            formatDimensLims());
        } elseif (isset($cPost['n_rmv_header_img']) && intval($cPost['n_rmv_header_img'])>0) {
            $cPost['header_img']='';
            unset($cPost['n_rmv_header_img']);
        } else {
            unset($cPost['header_img'],$setCmp['header_img'],$cPost['n_rmv_header_img']);
        }
        // mobile icon
        if (isset($_FILES['mobile_icon_img']) && $_FILES['mobile_icon_img']['name']>'') {
            $upload = createFilePath(
                                    $dir, 
                                    $_FILES['mobile_icon_img'],
                                    $validImgTypes,
                                    formatDimensLims(180, 180, false, false));
            if ($upload['result']) {
                $cPost['mobile_icon_img']=$upload['result'];
            } else {
                $msg.=fdbkMsg(0,'There was an error uploading your mobile icon: '.$upload['msg'],'');
            } unset($upload);
        } else if (intval(($cPost['n_rmv_mobile_icon_img'] ?? null))>0) {
            $cPost['mobile_icon_img']='';
            unset($cPost['n_rmv_mobile_icon_img']);
        } else {
            unset($cPost['mobile_icon_img'],$setCmp['mobile_icon_img'],$cPost['n_rmv_mobile_icon_img']);
        }
        // favicon
        if (isset($_FILES['favicon_img']) && $_FILES['favicon_img']['name']>'') {
            $upload = createFilePath (
                                        $dir, 
                                        $_FILES['favicon_img'],
                                        'gif',
                                        formatDimensLims(16, 16, false, false),
                                        null,
                                        'favicon_img');
            if ($upload['result']) {
                $cPost['favicon_img']=$upload['result'];
            } else {
                $msg.=fdbkMsg(0,'There was an error uploading your favicon: '.$upload['msg'],'');
            } unset($upload);
        } else if (intval($cPost['n_rmv_favicon_img'] ?? null)>0) {
            $cPost['favicon_img']='';
            unset($cPost['n_rmv_favicon_img']);
        } else {
            unset($cPost['favicon_img'],$setCmp['favicon_img'],$cPost['n_rmv_favicon_img']);
        }
    $postLen = count($cPost); $cmpCount = count($setCmp);
    $setLen = ($postLen>$cmpCount ? $postLen : $cmpCount);
    $changes = array();
    unset($cPost['save_settings']);
    foreach ($cPost AS $key=>$val) {
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
                            $msg .= fdbkMsg(0,"Subdirectory folder is invalid. Check to see if it is correct and try again.",'');
                            $error = true;
                        }
                    }
                break;
                case ('c_year'):
                    if ($val!='' && ($val<1000 || $val>date("Y") || !is_numeric($val))) {
                        $val='';
                        $msg .= fdbkMsg(0,"Invalid inital copyright year entry.",'');
                    }
                break;
                case ('thumb_quality'):
                    if (is_numeric($val)) {
                        $val=cleanInt($val);
                        if ($val>100) {
                            $val=100;
                        } elseif ($val<0) {
                            $val=0;
                        }
                    } else {$val=75;}
                    break;
                case ('preview_item_content'):
                    if (!$val) {
                        $val='Icons';
                    } else {
                        $list='';
                        $start=true;
                        foreach ($val AS $prev) {
                            if ($prev>'') {
                                if (!$start) {
                                    $list.=',';
                                }
                                $list.=$prev;
                                $start=false;
                            }
                        }
                        $val=$list;
                    }
                    break;
                case ('max_img_dimns'):
                case ('max_upld_storage'):
                    if ($val>50000) {
                        $val = 50000;
                        $prop = ($key=="max_upld_storage" ? 'Max Upload Storage (in megabytes)' : 'Max Image Dimensions (in pixels)');
                        $msg .= fdbkMsg(0,"...If you're going to set your level for '".$prop."' that high, you may as well just turn the setting off. 
                        It was lowered to '50,000'.",'');
                    }
                break;
                default:
                    if (substr($key,-4)!=='_img' && strlen($val)>85) {
                        $val = substr($val, 0, 85);
                        $msg .= fdbkMsg(0,"One of your entries was too long. It was shortened to 85 characters.",'');
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
        $msg = fdbkMsg(1,"Changes were saved at ".date('d F, Y h:i:s').'.').$msg;
    } else {
        $msg = fdbkMsg(0,"There was a problem saving your changes. Check your inputs and try again.").$msg;
    }
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
    $dateQry = "UPDATE `Accounts` SET 
                            `Password` = :pw
                        WHERE `Password` = :oldpw AND `Curr_Sess_ID` = :sessid;";
    $stmt = $conn->prepare($dateQry);
    $stmt->bindValue(':pw', $password, SQLITE3_TEXT);
    $stmt->bindValue(':oldpw', $oldPW, SQLITE3_TEXT);
    $stmt->bindValue(':sessid', $sessID, SQLITE3_TEXT);
    if (!$stmt->execute()) {
        $msg = fdbkMsg(0,"A database error occurred. Try again.");
        return ;
    } else if ($conn->changes()<1) {
        $msg = fdbkMsg(0,"Your current password is incorrect.");
        return ;
    }
    $msg = fdbkMsg(1, "Your password has been changed!");
}

if (isset($_POST['change_email'])) {
    global $db; global $emailHeaders;
    $failed = false;
    $email = filter_var($_POST['new_email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $msg = fdbkMsg(0,"The email input is invalid. Check to make sure that it's written correctly, and try again.");
        return;
    }
    $conn = new SQLite3($db);
    $time = time();
    $time = addToUnixTime($time,3,"hours");
    $key = bin2hex(random_bytes(22));
    $keyHash = hash("sha256", $key);
    $sessID = session_id();
    $qry = "UPDATE `Accounts` SET 
                    `Temp_Email` = :email,
                    `Activation_Timestamp` = :time, 
                    `Activation_Key` = :key
                WHERE `Curr_Sess_ID` = :sessid;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
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
            if (mail($email, 'Validate your new email address', $body, $emailHeaders)) {
                $msg = "<p>A confirmation email has been sent to your new email address! If it hasn't shown up within a few minutes, 
                check your spam or junk folder.<br/>
                <strong>You have 3 hours before the activation key for your email address change expires (".timestampToDate($time).")</strong>,
                after which you will have to resend the request.</p>";
                $msg = fdbkMsg(1,$msg, "Good to go!",true);
            } else {
                $msg = fdbkMsg(0,"The email to validate your new email address failed to send. Please try again.");
            }
            error_log('Email activation link failsafe: '.$activateUrl);
            // // FOR TESTING 
            // global $testing;
            // if ($testing) {
                // echo $body;
            // }
            
    } else {
        $msg = fdbkMsg(0,'Something went wrong. Please try again.');
        
        return;
    }
}

if (isset($_POST['change_username'])) {
    $msg= '';
    global $db; global $sessID;
    if ((string)$_POST['username']) {
        $username = stripHTML($_POST['username']);
    } else {
        $msg= "Please enter a valid username.";
        return false;
    }
    if ((string)$user['Name'] ?? null) {
        $oldname = $user['Name'];
    } else {
        $msg= "Invalid session. Please login again and retry.";
        return false;
    }
    $conn = new SQLite3($db);
    $qry = "UPDATE `Accounts` SET 
                            `Username` = :newname
                        WHERE `Username` = :oldname AND `Curr_Sess_ID` = :sessid;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':newname', $username, SQLITE3_TEXT);
    $stmt->bindValue(':oldname', $oldname, SQLITE3_TEXT);
    $stmt->bindValue(':sessid', $sessID, SQLITE3_TEXT);
    if (!$stmt->execute()) {
        if (failedConstrait($conn)) {
            $msg = "There is already an account with this username. Please choose something else.";
        } else {
            $msg = "A database error occurred. Try again.";
        }
        $msg = fdbkMsg(0,$msg);
    } else if ($conn->changes()<1) {
        $msg = fdbkMsg(2,"Nothing changed! Make sure that your new username is different than your current one.", "Huh?");
    } else {
        $msg = fdbkMsg(1,"Your username has been changed!");
    }
    $stmt->close();
}

if (isset($_POST['change_user_icon'])) {
    // IN PROGRESS
    global $validImgTypes;
    $msg= '';
    global $db; $rmvIcon=false; $iconPath=null;
    $iconUpld = ($_FILES['icon_upload']['name'] ?? null);
    if ($iconUpld) {
        require_once 'handleFiles.inc.php';
        $dir = '/assets/uploads/user-icons/';
        $iconPath = createFilePath(
                                $dir,
                                $_FILES['icon_upload'],
                                $validImgTypes,
                                formatDimensLims(120, 120, false, false));
    } else if (isset($_POST['icon_stored']) && !$_POST['icon_stored']) {
        $rmvIcon=true;
    } else {
        $msg="Please upload a valid image.";
        return;
    }
    if ($iconPath || $rmvIcon) {
        $userID = cleanInt($_SESSION['UserID']);
        $conn = new SQLite3($db);
        $qry = "UPDATE `Accounts` SET `Icon_Path` = :icon WHERE `ID` = :id;";
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(':icon', $iconPath, SQLITE3_TEXT);
        $stmt->bindValue(':id', $userID, SQLITE3_INTEGER);
        if ($stmt->execute()) {
            $msg="Icon changes saved!";
            if ($iconPath) {
                mkThumb($dir, $iconUpld, $iconPath, 50, null, "50px");
            }
        } else {
            $msg=fdbkMsg(0,"Icon changes failed to save. Please try again.");
        }
    } else {
        $msg=fdbkMsg(0,"Image failed to upload. Please try again.");
        return;
    }
}

function createPage($cPost) {
    global $db;
    global $set;
    $conn = new SQLite3($db);
    if (!isset($_SESSION['Msg'])) {
        $_SESSION['Msg'] = '';
    }
    $qry = 'INSERT INTO `Pages` (`Name`,`Link`,`Meta_Text`,`Header_Img_Path`,`Show_Title`,`Show_Header_Img`,`Multi_Sect`,`Paginate`,`Paginate_After`,`Format`,`Hidden`) 
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
        $_SESSION['Msg'] .= '<span class="red">Page creation failed.';
        if (failedConstrait($conn)) {
            $_SESSION['Msg'] .= ' All Page names must be unique.';
        }
        $_SESSION['Msg'] .= '</span>';
        return false;
    } else {
        $pageID = $conn->lastInsertRowID();
        $sectQry = 'INSERT INTO `Sections` (`Name`,`Page_ID`,`Page_Index_Order`,`Show_Title`) 
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
            
            $menuQry = 'INSERT INTO Automenu (`Type_Code`, `Index_Order`, `Ref_ID`, `Hidden`'.($cPost['menu_img_path'] ? ', `Img_Path`' : null).') 
            VALUES (1,?,?,?'.($cPost['menu_img_path'] ? ', ?' : null).');';
            $menuStmt = $conn->prepare($menuQry);
            $menuStmt->bindValue(1,$pageID, SQLITE3_INTEGER);
            $menuStmt->bindValue(2,$pageID, SQLITE3_INTEGER);
            $menuStmt->bindValue(3,1, SQLITE3_INTEGER);
            if ($cPost['menu_img_path']) {
                $menuStmt->bindValue(4,$cPost['menu_img_path'], SQLITE3_TEXT);
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
    if (!isset($_SESSION['Msg'])) {
        $_SESSION['Msg'] = '';
    }
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
        $_SESSION['Msg'] .= '<span class="red">Page edit failed.';
        if (failedConstrait($conn)) {
            $_SESSION['Msg'] .= ' All Page names must be unique.';
        }
        $_SESSION['Msg'] .= '</span>';
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
                $sectID = cleanInt($sectArr[0]['n_sect_id']);
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
    if (!isset($_SESSION['Msg'])) {
        $_SESSION['Msg'] = '';
    }
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
    $stmt->bindValue(':viformat',($cPost['view_item_format'] ?? NULL), SQLITE3_TEXT);
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
        unset($_SESSION['Msg']);
        return $sectID;
    } else {
        $_SESSION['Msg'] .= 'Section creation failed.';
        if (failedConstrait($conn)) {
            $_SESSION['Msg'] .= ' All Sections on a single page must have a unique name.';
        }
        return false;
    }
}

function editSection($cPost) {
    global $db;
    if (!isset($_SESSION['Msg'])) {
        $_SESSION['Msg'] = '';
    }
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
        unset($_SESSION['Msg']);
        if ($cPost['n_sect_id']>0) {
            return $cPost['n_sect_id'];
        } else {
            // if the edit was to section defaults
            return true;
        }
    } else {
        $_SESSION['Msg'] .= 'Section edit failed.';
        if (failedConstrait($conn)) {
            $_SESSION['Msg'] .= ' All Sections on a single page must have a unique name.';
        }
        return false;
    }
}

function createItem($cPost) {
    global $db;
    $conn = new SQLite3($db);
    $qry = "INSERT INTO `Items` (`Posted_By_ID`,`Sect_ID`,`Title`,`Img_Path`,`Img_Alt_Text`,`File_Path`,`Show_File`,`File_Link_Text`,
    `Embed_HTML`,`Img_Thumb_Path`,`Text`,`Publish_Timestamp`,`Tags`,`Hidden`,`Format`)
    VALUES (:userid,:sectid,:title,:img,:alttext,:file,:showfile,:linktext,:embed,:imgthumb,:text,:ts,:tags,:hide,:format);";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':userid',($cPost['n_user_id'] ?? $_SESSION['UserID']), SQLITE3_INTEGER);
    $stmt->bindValue(':sectid',($cPost['n_sect_id'] ?? 0), SQLITE3_INTEGER);
    $stmt->bindValue(':title',($cPost['title'] ?? 'Untitled'), SQLITE3_TEXT);
    $stmt->bindValue(':img',($cPost['img_path'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':alttext',($cPost['img_alt_text'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':file',($cPost['file_path'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':showfile',($cPost['n_show_file'] ?? null), SQLITE3_INTEGER);
    $stmt->bindValue(':linktext',($cPost['file_link_text'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':embed',($cPost['b_embed'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':imgthumb',($cPost['thumb_path'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':text',($cPost['m_text'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':ts',($cPost['publish_datetime'] ?? time()), SQLITE3_INTEGER);
    $stmt->bindValue(':hide',($cPost['n_hidden'] ?? 0), SQLITE3_INTEGER);
    $stmt->bindValue(':tags',($cPost['tags'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':format',($cPost['format'] ?? null), SQLITE3_TEXT);
    return $stmt->execute();
}

function editItem($cPost) {
    global $db;
    $conn = new SQLite3($db);
    $qry = "UPDATE Items 
    SET `Posted_By_ID`=:userid,`Sect_ID`=:sectid,`Title`=:title,`Publish_Timestamp`=:ts,`Text`=:text,`Img_Path`=:img, `Img_Alt_Text`=:alttext,
    `File_Path`=:file, `Show_File`=:showfile, `File_Link_Text`=:linktext, `Embed_HTML`=:embed,`Img_Thumb_Path`=:imgthumb,`Format`=:format,
    `Tags`=:tags,`Hidden`=:hide
    WHERE ID=:id;";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':userid',($cPost['n_user_id'] ?? $_SESSION['UserID']), SQLITE3_INTEGER);
    $stmt->bindValue(':sectid',($cPost['n_sect_id'] ?? 0), SQLITE3_INTEGER);
    $stmt->bindValue(':title',($cPost['title'] ?? 'Untitled'), SQLITE3_TEXT);
    $stmt->bindValue(':ts',($cPost['publish_datetime'] ?? time()), SQLITE3_INTEGER);
    $stmt->bindValue(':text',($cPost['m_text'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':img',($cPost['img_path'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':alttext',($cPost['img_alt_text'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':file',($cPost['file_path'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':showfile',($cPost['n_show_file'] ?? null), SQLITE3_INTEGER);
    $stmt->bindValue(':linktext',($cPost['file_link_text'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':embed',($cPost['b_embed'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':imgthumb',($cPost['thumb_path'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':hide',($cPost['n_hidden'] ?? 0), SQLITE3_INTEGER);
    $stmt->bindValue(':format',($cPost['format'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':tags',($cPost['tags'] ?? null), SQLITE3_TEXT);
    $stmt->bindValue(':id',$cPost['n_item_id'], SQLITE3_INTEGER);
    $exec = $stmt->execute();
    return $exec;
}

// page create/edit...
if (isset($_POST['create_page']) || isset($_POST['edit_page'])) {
    global $validImgTypes;
    if (isset($_POST['create_page'])) {
        $newPage = true;
    } else {
        $newPage = false;
    }
    global $set;
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
    if (!$newPage && !$cPost['n_page_id']) {
        $cPost['name']= 'Page Setting Defaults';
    } else {
        $storedPageName = ($cPost['name_stored'] ?? '');
    }
    if ($newPage || $cPost['n_page_id']>1) {
        $cPost['link'] = strtolower(preg_replace("/[^A-Za-z0-9-]/",'',str_replace(' ','-',$cPost['name'])));
        if (strcasecmp($cPost['name'], $storedPageName)!=0) {
            $pageLinks = getPageLinks();
            if (in_array($cPost['link'],$pageLinks)) {$cPost['link'] .= '_';}
        }
    } else {
        $cPost['link']='';
    }
    if ($imgUpld || $menuImgUpld) {
        require_once 'handleFiles.inc.php';
        if ($imgUpld) {
            $time=time();
            $imgName = cleanUpldName($imgUpld).'-'.$time;
            $dir = '/assets/uploads/page-headers/';
            $newImg = createFilePath(
                                  $dir, 
                                  $_FILES['header_img_upload'],
                                  $validImgTypes,
                                  formatDimensLims(),
                                  $imgName);
            $imgPath = $newImg['result'];
        }
        if ($menuImgUpld || $rmvMenuImg) {
            if ($menuImgUpld) {
                $dir = '/assets/uploads/menu/';
                $menuImgUpld = cleanFileName($menuImgUpld);
                $menuImgPath = createFilePath(
                                          $dir,
                                          $_FILES['menu_img_upload'],
                                          $validImgTypes,
                                          formatDimensLims());
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
        $msg = fdbkMsg(0,"Changes to this page failed to save. Please try again.");
        
    } else {
        $msg =fdbkMsg(1,"Changes to '".$cPost['name']."' were saved at ".date('d F, Y h:i:s').'.');
    }
}


if (isset($_POST['delete_page'])) {
    global $db;
    $conn = new SQLite3($db);
    $pageID = cleanInt($_POST['n_page_id']);
    if ($pageID<2) {
        $msg=fdbkMsg(0,"You cannot delete essential pages, such as home or the error page.","That's not gonna work.");
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
            $msg=fdbkMsg(1,"Page deleted.");
            $menuQry = 'DELETE FROM `Automenu` WHERE `Type_Code`=1 AND `Ref_ID`=?;';
            $menuStmt = $conn->prepare($menuQry);
            $menuStmt->bindValue(1,$pageID, SQLITE3_INTEGER);
            $menuStmt->execute();
        } else {
            $msg=fdbkMsg(0,"Something prevented this page from deleting. Please try again.");
        }
    } else {
        $msg=fdbkMsg(0,"Page failed to delete. Please try again.");
    }
}

function bulkMkThumbs($sectID,$thumbSize=125,$thumbAxis=0) {
    require_once 'handleFiles.inc.php';
    global $db;
    $sectID = cleanInt($sectID);
    $newThumbs = array("succeeded"=>array(),"failed"=>array());
    $conn = new SQLite3($db);
    $itemsQry = "SELECT `ID`,`Title`,`Img_Path` FROM `Items` WHERE `Sect_ID`=? AND `Img_Path`>'';";
    $itemsStmt = $conn->prepare($itemsQry);
    $itemsStmt->bindValue(1,$sectID, SQLITE3_INTEGER);
    $result = $itemsStmt->execute();
    if ($result) {
        $dir = '/assets/uploads/items/';
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $thumbGen = mkThumb($dir, str_replace($dir,'',$row['Img_Path']), $row['Img_Path'], $thumbSize, $thumbAxis);
            if ($thumbGen['result']) {
                $thumbQry = "UPDATE `Items` SET `Img_Thumb_Path`=:thumb WHERE `ID`=:id;";
                $thumbStmt = $conn->prepare($thumbQry);
                $thumbStmt->bindValue(':thumb',$thumbGen['result'], SQLITE3_TEXT);
                $thumbStmt->bindValue(':id',$row['ID'], SQLITE3_INTEGER);
                if ($thumbStmt->execute()) {
                    $newThumbs['succeeded'][] = $row['Title'];
                } else {
                    $newThumbs['failed'][] = $row['Title'].": ".$thumbGen['msg']."";
                }
            } else {
                $newThumbs['failed'][] = $row['Title'].": Thumbnail creation failed";
            }
        }
        return $newThumbs;
    } else {
        return false;
    }
}

// create/edit section
if (isset($_POST['create_section']) || isset($_POST['edit_section'])) {
    global $set;global $validImgTypes;
    $bulkThumbs=$bulkThumbMsg=null;
    $msg='';
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
            $cPost['n_item_limit'] = cleanInt($cPost['n_item_limit']);
        }
    }
    // in case I decide whether Section Text gets cleaned up as code or markup text should be toggable...
    $cPost['text'] = closeHTML($cPost['b_text'] ?? ($cPost['m_text'] ?? null));
    //
    $storedImg = ($cPost['header_img_stored'] ?? false);
    if ($imgUpld) {
        $time=time();
        $imgName = cleanUpldName($imgUpld).'-'.$time;
        $dir = '/assets/uploads/sect-headers/';
        require_once 'handleFiles.inc.php';
        $upload = createFilePath(
                              $dir,
                              $_FILES['header_img_upload'],
                              $validImgTypes,
                              formatDimensLims(),
                              $storedImg,
                              $imgName);
        if ($upload['result']) {
            $imgPath=$upload['result'];
        } else {
            $msg.=fdbkMsg(0,$upload['msg']);
        }
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
    $cPost['n_paginate_items'] = ($cPost['n_paginate_items'] ?? 0);
    if ($newSection) {
        $exec = createSection($cPost);
    } else {
        $exec = editSection($cPost);
        if (($cPost['n_regen_all_thumbs'] ?? null)===1) {
            $bulkThumbs = bulkMkThumbs($cPost['n_sect_id'], $cPost['n_thumb_size'], $cPost['n_thumb_axis']);
            if ($bulkThumbs===false) {
                $bulkThumbMsg.="<br/><br/>New thumbnail generation failed.";
            } else {
                if (is_array($bulkThumbs['succeeded']) && !empty($bulkThumbs['succeeded']) && empty($bulkThumbs['failure'])) {
                    $bulkThumbMsg.="<br/><br/>New thumbnail generation successful for all items.";
                } else if (!empty($bulkThumbs['succeeded']) && !empty($bulkThumbs['failure'])) {
                    if (is_array($bulkThumbs['succeeded']) && !empty($bulkThumbs['succeeded'])) {
                        $bulkThumbMsg.="<br/><br/>New thumbnails generated successfully for: ".implode(', ',$bulkThumbs['succeeded']);
                    } 
                    if (is_array($bulkThumbs['failure']) && !empty($bulkThumbs['failure'])) {
                        $bulkThumbMsg.="<br/><br/>New thumbnails failed to generate for: ".implode(', ',$bulkThumbs['failure']);
                    }
                } else {
                    $bulkThumbMsg.="<br/><br/>Thumbnails failed to generate for any items.";
                }              
            }
        }
    }
    if (!$exec) {
        if ($_SESSION['Msg']) {
            $msgTxt = $_SESSION['Msg'];
            unset($_SESSION['Msg']);
        } else {
            $msgTxt ="Changes to this section failed to save. Please try again.";
        }
        $msg.=fdbkMsg(0,$msgTxt.$bulkThumbMsg);
    } else {
        $msg.=fdbkMsg(1,"Section settings were saved at ".date('d F, Y h:i:s').'.'.$bulkThumbMsg);
    }
}

if (isset($_POST['delete_section'])) {
    global $db;
    $conn = new SQLite3($db);
    $sectID = cleanInt($_POST['n_sect_id']);
    if ($sectID===0) {
        $msg=fdbkMsg(0,"You cannot delete the default section.", "That's a No-Go.");
        return;
    }
    $moveItemsQry = 'UPDATE `Items` SET `Sect_ID`=0 WHERE `Sect_ID`=?;';
    $itemStmt = $conn->prepare($moveItemsQry);
    $itemStmt->bindValue(1,$sectID, SQLITE3_INTEGER);
    if ($itemStmt->execute()) {
        $qry = 'DELETE FROM Sections WHERE ID=?;';
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(1,$sectID, SQLITE3_INTEGER);
        if ($stmt->execute()) {
            $msg=fdbkMsg(1,"Section deleted.");
        } else {
            $msg=fdbkMsg(0,"Section failed to delete due to a database error. Please try again.");
        }
    } else {
        $msg=fdbkMsg(0,"Section failed to delete. Please try again.");
    }
}

function cleanItemVals($post, $files=null){
    global $set;global $validImgTypes;
    $error = false; $msg='';
    $post['n_user_id'] = $_SESSION['UserID'];
    // maintence, not counting id ('n_item_id')
    $itemKeys = array('title','n_sect_id','publish_datetime','format','tags',
    'm_text','img_alt_text','sect_create_thumbnail','n_sect_thumb_size',
    'n_sect_thumb_size_axis','item_create_thumbnail','n_item_thumb_size',
    'n_item_thumb_size_axis','n_show_file','file_stored','b_embed','n_hidden');
    $postKeys = array_keys($post);
    $keyDiff = array_diff($itemKeys,$postKeys);
    foreach ($keyDiff AS $key) {
        if (!in_array($key, $postKeys)) {
            $post[$key] = null; 
        }
    } unset($key);
    $cPost = cleanServerPost($post, true);
    // sort out what's going on with files...
    $newImgUpld=false;
    $imgUpld = ($files['img_upload']['name'] ?? false);
    $thumbUpld = ($files['thumb_upload']['name'] ?? false);
    $fileUpld = ($files['file_upload']['name'] ?? false);
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
        require_once 'handleFiles.inc.php';
        $time=time();
    }
    $dir = '/assets/uploads/items/';
    // image upload
    $imgStored = ($cPost['img_stored'] ?? false);
    if ($imgUpld) {
        $imgName = cleanUpldName($imgUpld).'-'.$time;
        $newImg = createFilePath(
                               $dir,
                               $files['img_upload'],
                               $validImgTypes,
                               formatDimensLims(),
                               $imgStored,
                               $imgName);
        $imgPath = $newImg['result'];
        if (!$imgPath) {
            $error=true;
            $msg .= $newImg['msg'];
        } else {
            $newImgUpld=true;
        }
    } else {
        if (isset($_POST['edit_item']) && $imgStored) {
            $imgName = cleanUpldName($imgStored, $dir);
            $imgPath = $imgStored;
        } else {
            $imgPath = null;
        }
    }
    // image thumbnail
    $thumbStored = ($cPost['thumb_stored'] ?? false);
    if (($newImgUpld && $createThumb) || ($imgPath && $createThumb)) {
        // custom thumbs
        if ($thumbUpld) {
            $newThumb = createFilePath(
                                        $dir,
                                        $files['thumb_upload'],
                                        $validImgTypes,
                                        formatDimensLims(),
                                        $thumbStored,
                                        $imgName.'_thumb');
            $imgThumbPath = $newThumb['result'];
            if (!$imgThumbPath) {
                $error = true;
                $msg .= $newThumb['msg'];
            }
        } else {
            // auto-thumbs
            if ($imgUpld && ($newImg['result'] ?? null)) {
                $oriImg = insertFilenameTag($imgUpld,$time,'-');
            } else {
                $oriImg = $imgStored;
            }
            $genThumb=mkThumb($dir, $oriImg, $imgPath, $cPost['n_thumb_size'], $cPost['n_thumb_size_axis']);
            if ($genThumb['result']) {
                $imgThumbPath = $genThumb['result'];
            } else {
                $imgThumbPath=null;
                $error=true;
                $msg.=$genThumb['msg'];
            }
        }
    } else if (($cPost['n_rmv_thumb_img'] ?? 0)>0) {
        $imgThumbPath = '';
    } else {
        $imgThumbPath = ($cPost['stored_thumb_img'] ?? false);
    }
    // file upload
    $fileStored = ($cPost['file_stored'] ?? false);
    if ($fileUpld) {
        $newFile = createFilePath(
                               $dir, 
                               $files['file_upload'],
                               null, // any filetype
                               null, // no dimension params
                               $fileStored);
        $filePath = $newFile['result'];
        if (!$filePath) {
            $error=true;
            $msg .= $newFile['msg'];
        }
    } else {
        if ($fileStored) {
            $filePath = $fileStored;
        } else {
            $filePath = null;
        }
    }
    if (!isset($cPost['n_show_file'])) {
        $cPost['n_show_file'] = 2;
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
    return array("cleaned"=>$cPost, "error"=>$error, "msg"=>$msg);
}

// item create/item
if (isset($_POST['create_item']) || isset($_POST['edit_item'])) {
    if (isset($_POST['create_item'])) {
        $newItem=true;
    } else {
        $newItem=false;
    }
    $p = cleanItemVals($_POST, $_FILES);
    if ($p['error']) {
        $msg=fdbkMsg(0,$p['msg']);
        return;
    } else {
        $cPost = $p['cleaned'];
    }
    if ($newItem) {
        $exec = createItem($cPost);
    } else {
        $exec = editItem($cPost);
    }
    if ($exec) {
        $msg=fdbkMsg(1,"Changes were saved at ".date('d F, Y h:i:s').'.');
    } else {
        $msg=fdbkMsg(0,"Changes failed to save. Please try again.");
    }
}

if (isset($_POST['delete_item'])) {
    global $db;
    $conn = new SQLite3($db);
    $itemID = cleanInt($_POST['n_item_id']);
    $qry = 'DELETE FROM `Items` WHERE `ID`=?;';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1,$itemID, SQLITE3_INTEGER);
    if ($stmt->execute()) {
        $msg=fdbkMsg(1,"Item deleted.");
    } else {
        $msg=fdbkMsg(0,"Item failed to delete. Please try again.");
    }
}


if (isset($_POST['save_item_order'])) {
    global $db;
    $conn = new SQLite3($db);
    usort($_POST["item"], function ($a, $b) {
        return $a['n_index_order'] <=> $b['n_index_order'];
    });
    $indexOrder=0;
    $error = false;
    $qry = "UPDATE `Items` SET `Sect_Index_Order`=:num WHERE `ID`=:itemid;";
    foreach ($_POST["item"] AS &$item) {
        if ($error) {
            $msg= fdbkMsg(0,"There was an error saving your item order changes. Please try again.");
            return;
        }
        $cPost = cleanServerPost($item);
        $indexOrder++;
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(':num',$indexOrder, SQLITE3_INTEGER);
        $stmt->bindValue(':itemid',$cPost['n_item_id'], SQLITE3_INTEGER);
        if (!$stmt->execute()) {
            $error = true;
        }
    }
    if (!$error) {
        $msg=fdbkMsg(1,"Changes were saved at ".date('d F, Y h:i:s').".");
    } else {
        $msg.=fdbkMsg(0,"There was an error saving your item order changes. Please try again.");
    }
}

function bulkCreateItems($fileArr, $sectID, $importDir, $targetDir){
    global $validImgTypes;
    $succeeded = array();
    $failed = array();
    foreach($fileArr AS $itemFile) {
        $filePath = $importDir.'/'.$itemFile;
        //$file = file($filePath);
        $fileInfo = formatFileInfo($itemFile,$importDir);
        $post = array(
            'n_sect_id'=>cleanInt($sectID),
            'title'=>cleanUpldName($fileInfo['name'])
        );
        $files = array('img_upload'=>array(),'file_upload'=>array());
        // wipe $cPost
        $cPost = array();
        switch ($fileInfo['type']) {
            case "text":
                global $set;
                $post['m_text'] = file_get_contents($filePath);
                createFilePath(
                                $targetDir,
                                $fileInfo);
                break;
            case "image":
                if (in_array($fileInfo['ext'], $validImgTypes)) {
                    //$files['img_upload'] = $file;
                    $files['img_upload']['name'] = $itemFile;
                    $files['img_upload']['tmp_name'] = $filePath;
                    break;
                }
            default:
                //$files['file_upload'] = $file;
                $files['file_upload']['name'] = $itemFile;
                $files['file_upload']['tmp_name'] = $filePath;
            break;
        }
        
        $p = cleanItemVals($post, $files);
        if ($p['error']) {
            $msg=fdbkMsg(0,$p['msg']);
            $failed[] = $itemFile;
        } else {
            $cPost = $p['cleaned'];

            $exec = createItem($cPost);
            if (!$exec) {
                if (isset($_SESSION['Msg']) && $_SESSION['Msg']>'') {
                    $itemFile .= '; '.$_SESSION['Msg'];
                }
                $failed[] = $itemFile;
            } else {
                $succeeded[] = $itemFile;
            }
        }
    }
    $results = array("Succeeded" => $succeeded, "Failed" => $failed);
    return $results;
}

if (isset($_POST['bulk_create_thumbs'])) {
    $cPost = cleanServerPost($_POST);
    $newThumbs = bulkMkThumbs($cPost['n_sect_id'],$cPost['n_thumb_size'],$cPost['n_thumb_axis']);
    $msgHeader = null;
    if (!empty($newThumbs['succeeded'])) {
        $fdbkStatus=1;
        $msg = '<br/><strong>Successfully generated thumbnails for:</strong><br/> '.implode(', ', $newThumbs['succeeded']).'.';
        if (!empty($newThumbs['failed'])) {
            $fdbkStatus=2;
            $msgHeader = 'Mixed Results';
            $msg .= '<br/><br/>
            <strong>Thumbnail generation failed for:</strong><br/> '.implode(', ', $newThumbs['failed']).'.';
        }
    } else {
        $fdbkStatus=0;
        $msg = "Thumbnail generation failed.";
    }
    $msg = fdbkMsg($fdbkStatus,$msg,$msgHeader);
}

function bulkEditItems($itemIDsArr, $task='move', $input=null) {
    global $db;
    $conn = new SQLite3($db);
    if (!is_array($itemIDsArr) || empty($itemIDsArr)) {
        return false;
    }
    $idCount = count($itemIDsArr);

    // until proven otherwise...
    $bindIDs=1;
    $needsInput=false;
    $sqliteType='int';

    switch ($task) {
        case 'add-tags':
            $qry = "UPDATE `Items` 
                    SET `Tags`=(CASE WHEN `Tags`>'' THEN (`Tags`||?) ELSE (','||?) END) 
                    WHERE ";
            $bindIDs=3;
            $sqliteType='text';
            $input=stripHTML($input);
            $needsInput=true;
            break;
        case 'clear-tags': 
            $qry = "UPDATE `Items` SET `Tags`=null WHERE ";
            break;
        case 'toggle-hide': 
            $qry = "UPDATE `Items` 
                    SET `Hidden`=(CASE WHEN `Hidden`<1 THEN 1 ELSE 0 END) 
                    WHERE ";
            break;
        case 'delete': 
            $qry = "DELETE FROM `Items` WHERE ";
            break;
        case 'move':
        default:
            $qry = "UPDATE `Items` SET `Sect_ID`=? WHERE ";
            $input=cleanInt($input);
            $bindIDs=2;
            $needsInput=true;
            break;
    }
    if ($needsInput && (is_null($input) || $input===false)) {
        return false;
    }
    $first=true;
    for ($i=0;$idCount>$i;$i++) {
        if (!$first) {
            $qry .= ' OR ';
        }
        $qry .= '`ID`=?';
        $first=false;
    } unset($i);
    $qry .= ';';
    $stmt = $conn->prepare($qry);
    if ($needsInput) {
        for ($i=1;$i<$bindIDs;$i++) {
            if ($sqliteType==='text') {
                $stmt->bindValue($i, $input, SQLITE3_TEXT);
            } else {
                $stmt->bindValue($i, $input, SQLITE3_INTEGER);
            }
        } unset($i);
    }
    foreach ($itemIDsArr AS $id) {
        $stmt->bindValue($bindIDs,$id, SQLITE3_INTEGER);
        $bindIDs++;
    }
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return false;
    }
}

function bulkMoveItems($itemIDsArr, $newSectID) {
    global $db;
    $conn = new SQLite3($db);
    $qry = "UPDATE `Items` SET `Sect_ID`=? WHERE ";
    $idCount = count($itemIDsArr);
    $first=true;
    for ($i=0;$idCount>$i;$i++) {
        if (!$first) {
            $qry .= ' OR ';
        }
        $qry .= '`ID`=?';
        $first=false;
    } unset($i);
    $qry .= ';';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1,$newSectID, SQLITE3_INTEGER);
    $i=2;
    foreach ($itemIDsArr AS $id) {
        $stmt->bindValue($i,$id, SQLITE3_INTEGER);
        $i++;
    }
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function bulkAddItemTags($itemIDsArr, $newTagStr) {
    $newTags = prepTags($newTagStr).',';
    global $db;
    $conn = new SQLite3($db);
    $qry = "UPDATE `Items` 
            SET `Tags`=(CASE WHEN `Tags`>'' THEN (`Tags`||?) ELSE (','||?) END) 
            WHERE ";
    $idCount = count($itemIDsArr);
    $first=true;
    for ($i=0;$idCount>$i;$i++) {
        if (!$first) {
            $qry .= ' OR ';
        }
        $qry .= '`ID`=?';
        $first=false;
    } unset($i);
    $qry .= ';';
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1,$newTags, SQLITE3_TEXT);
    $stmt->bindValue(2,$newTags, SQLITE3_TEXT);
    $i=3;
    foreach ($itemIDsArr AS $id) {
        $stmt->bindValue($i,$id, SQLITE3_INTEGER);
        $i++;
    }
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}


if (isset($_POST['item_bulk_select'])) {
    $selected=array();
    $items = cleanServerPost($_POST['item']);
    foreach ($items AS $item) {
        if (is_numeric($item['n_selected']) && $item['n_selected']>0) {
            $selected[]=$item['n_selected'];
        }
    }
    $_SESSION['items_selected']=$selected;
}

if (isset($_POST['bulk_items_move'])) {
    if (!validID($_POST['n_sect_id'])) {
        $msg=fdbkMsg(0,'Invalid Section selection.');
        return;
    }
    $sectID = cleanInt($_POST['n_sect_id']);
    if (!isset($_POST['item']) || !is_array($_POST['item']) || empty($_POST['item'])) {
        $msg=fdbkMsg(0,'No selected Items with which to make changes.');
        return;
    }
    $items = cleanServerPost($_POST['item']);
    $selected=array();
    foreach ($items AS $item) {
        if (is_numeric($item['n_item_id']) && $item['n_item_id']>0) {
            $selected[]=$item['n_item_id'];
        }
    }
    if (bulkEditItems($selected, 'move', $sectID)){
        $msg=fdbkMsg(1);
    } else {
        $msg=fdbkMsg(0);
    }
}

if (isset($_POST['bulk_items_add_tags'])) {
    if (!$_POST['new_tags']) {
        $msg=fdbkMsg(2,"No new tags to add.");
        return;
    }
    $newTags=stripHTML($_POST['new_tags']);
    $items = cleanServerPost($_POST['item']);
    if (!is_array($items) || empty($items)) {
        $msg=fdbkMsg(0,'No selected Items with which to make changes.');
        return;
    }
    $selected=array();
    foreach ($items AS $item) {
        if (is_numeric($item['n_item_id']) && $item['n_item_id']>0) {
            $selected[]=$item['n_item_id'];
        }
    }
    if (bulkEditItems($selected, 'add-tags', $newTags)){
        $msg=fdbkMsg(1, "Tags added!");
    } else {
        $msg=fdbkMsg(0);
    }
}

if (isset($_POST['bulk_items_clear_tags'])) {
    $items = cleanServerPost($_POST['item']);
    if (!is_array($items) || empty($items)) {
        $msg=fdbkMsg(0,'No selected Items with which to make changes.');
        return;
    }
    $selected=array();
    foreach ($items AS $item) {
        if (is_numeric($item['n_item_id']) && $item['n_item_id']>0) {
            $selected[]=$item['n_item_id'];
        }
    }
    if (bulkEditItems($selected, 'clear-tags')){
        $msg=fdbkMsg(1);
    } else {
        $msg=fdbkMsg(0);
    }
}

if (isset($_POST['bulk_items_toggle_hide'])) {
    $items = cleanServerPost($_POST['item']);
    if (!is_array($items) || empty($items)) {
        $msg=fdbkMsg(0,'No selected Items with which to make changes.');
        return;
    }
    $selected=array();
    foreach ($items AS $item) {
        if (is_numeric($item['n_item_id']) && $item['n_item_id']>0) {
            $selected[]=$item['n_item_id'];
        }
    }
    if (bulkEditItems($selected, 'toggle-hide')){
        $msg=fdbkMsg(1, "Item 'Hidden' state has been toggled.");
    } else {
        $msg=fdbkMsg(0);
    }
}

if (isset($_POST['bulk_items_delete'])) {
    $items = cleanServerPost($_POST['item']);
    if (!is_array($items) || empty($items)) {
        $msg=fdbkMsg(0,'No selected Items with which to make changes.');
        return;
    }
    $selected=array();
    foreach ($items AS $item) {
        if (is_numeric($item['n_item_id']) && $item['n_item_id']>0) {
            $selected[]=$item['n_item_id'];
        }
    }
    if (bulkEditItems($selected, 'delete')){
        $msg=fdbkMsg(1,"Items deleted.", "Bye-bye!");
    } else {
        $msg=fdbkMsg(0);
    }
}

function getImportDirs($folder="import") {
    global $root;
    $imports = scandir($root.'/'.$folder);
    $return = array_slice($imports, 2);
    return $return;
}

if (isset($_POST['create_multi_items'])) {
    global $root;
    require_once 'handleFiles.php';
    $cPost = cleanServerPost($_POST);
    $importDir = $root.'/import/'.$cPost['folder'];
    $targetDir = $root.'/assets/uploads/items/section-'.$cPost['n_sect_id'].'/';
    $fileArr = array_slice(scandir($importDir),2);
    $results = bulkCreateItems($fileArr, $cPost['n_sect_id'], $importDir, $targetDir);
}

if (isset($_POST['save_menu'])) {
    global $db;
    $conn = new SQLite3($db);
    usort($_POST["option"], function ($a, $b) {
        return $a['n_index'] <=> $b['n_index'];
    });
    $msg='';
    $indexOrder=0;
    $error = false;
    $qry = "UPDATE `Automenu` 
        SET  `Index_Order`=:inorder, `Submenu`=:insub, `Hidden`=:hidden
            WHERE `ID`=:id";
    foreach ($_POST["option"] AS &$opt) {
        if ($error) {
            $msg= fdbkMsg(0,"There was an error saving your menu changes. Please try again.");
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
        $msg = fdbkMsg(1,$msg);
    } else {
        $msg.="There was an error saving your menu changes. Please try again.";
        $msg = fdbkMsg(0,$msg);
    }
}

if (isset($_POST['menu_add_item'])) {
    global $db; global $set; global $validImgTypes;
    $conn = new SQLite3($db);
    $error = false;
    require_once 'handleFiles.inc.php';
    $dir = '/assets/uploads/menu/';
    if (isset($_FILES['link_img']) && $_FILES['link_img']['name']>'') {
        $newImg = createFilePath(
                              $dir, 
                              $_FILES['link_img'],
                              $validImgTypes,
                              formatDimensLims());
        $imgPath = $newImg['result'];
        if (is_null($imgPath)) {
            $msg = fdbkMsg(0,$newImg['msg']);
            return;
        }
    } else {
        $imgPath = null;
    }
    $qry = 'INSERT INTO `Automenu` 
        (`Type_Code`, `Link_Text`, `Ext_Url`'.($imgPath ? ', `Img_Path`' : null).') 
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
        $msg=fdbkMsg(1,"Changes were saved at ".date('d F, Y h:i:s').'.');
    } else {
        $msg=fdbkMsg(0,"There was an error saving your menu changes. Please try again.");
    }
}

if (isset($_POST['menu_edit_item'])) {
    global $db; global $set; global $validImgTypes;
    $msg='';
    $conn = new SQLite3($db);
    $cPost = cleanServerPost($_POST);
        if ($cPost['n_type_code']==8 && (isset($cPost['ext_url']) && !$cPost['ext_url'])) {
            $cPost['ext_url']='/';
        } else {
            $cPost['ext_url']=null;
        }
    $dir = '/assets/uploads/menu/';
    if (isset($_FILES['link_img']) && $_FILES['link_img']['name']>'') {
        require_once 'handleFiles.inc.php';
        $upload = createFilePath(
                              $dir, 
                              $_FILES['link_img'],
                              $validImgTypes,
                              formatDimensLims());
        $imgPath=$upload['result'];
        if (!$imgPath) {
            $msg.=fbbkMsg(0,$upload['msg']);
        }
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
        $msg=fdbkMsg(0,"There was an error saving your menu changes. Please try again.");
    } else {
        $msg=fdbkMsg(1,"Changes were saved at ".date('d F, Y h:i:s').'.');
    }
}

if (isset($_POST['delete_menu_item'])) {
    global $db; global $set;
    $cPost = cleanServerPost($_POST);
    $conn = new SQLite3($db);
    $id = cleanInt($_POST['n_menu_id']);
    $msg="";
    if ($_SESSION['MenuItemType'] < 8) {
        $msg .= "<div class='red'>You can only delete custom links and headings via the automenu.</div>";
        return false;
    } else {
        unset($_SESSION['MenuItemType']);
    }
    $qry = "DELETE FROM Automenu WHERE ID=? AND (Type_Code>3 AND Ref_ID IS NULL);";
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(1,$id, SQLITE3_INTEGER);
    if (!$stmt->execute() || $conn->changes()<1) {
        $msg.=" There was an error deleting this menu item. Please try again.";
        $msgType=0;
    } else {
        $msg.="Menu item deleted at ".date('d F, Y h:i:s').'.';
        $msgType=1;
    }
    $msg=fdbkMsg($msgType,$msg);
}

if (isset($_POST['add_new_social']) || isset($_POST['edit_social'])) {
    global $db; global $set; global $validImgTypes;
    $new=false;
    $msg='';
    $action='edit';
    if (isset($_POST['add_new_social'])) {
        $new=true;
        $action='add';
    }
    $cPost = cleanServerPost($_POST);
    $cPost['icon_path']=null;
    $iconUpld = ($_FILES['icon_img_upload']['name'] ?? false);
    if ($iconUpld) {
        require_once 'handleFiles.inc.php';
        $dir = '/assets/uploads/socials/';
        $upload = createFilePath(
                                         $dir, 
                                         $_FILES['icon_img_upload'],
                                         $validImgTypes,
                                         formatDimensLims());
        if ($upload['result']) {
            $cPost['icon_path']=$upload['result'];
        } else {
            $msg.=fdbkMsg(0,$upload['msg']);
        }
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
        $msg.=fdbkMsg(0,"There was an error ".$action."ing this social media link. Please try again.");
    } else {
        $msg.=fdbkMsg(1, "Social media link ".$action."ed at ".date('d F, Y h:i:s').'.');
    }
}

if (isset($_POST['delete_social'])) {
    global $db;
    $cPost = cleanServerPost($_POST);
    if ((int)$cPost['n_link_id']===0) {
        $msg=fdbkMsg(0,'You cannot delete the site RSS link. Try hiding it, or disabling your RSS Feed.', "Nope.");
        return;
    }
    $qry = "DELETE FROM `Social_Media` WHERE `ID`=:id;";
    $conn = new SQLite3($db);
    $stmt = $conn->prepare($qry);
    $stmt->bindValue(':id',$cPost['n_link_id'],SQLITE3_INTEGER);
    if (!$stmt->execute() || $conn->changes()<1) {
        $msg=fdbkMsg(0,"There was an error deleting this social media link. Please try again.");
    } else {
        $msg=fdbkMsg(1,"Social media link deleted at ".date('d F, Y h:i:s').'.');
    }
}

if (isset($_POST['save_socials_list'])) {
    global $db;
    $conn = new SQLite3($db);
    usort($_POST["option"], function ($a, $b) {
        return $a['n_index'] <=> $b['n_index'];
    });
    $indexOrder=0;
    $error = false;
    $qry = "UPDATE `Social_Media` 
        SET  `Index_Order`=:inorder, `Hidden`=:hidden
            WHERE ID=:id";
    foreach ($_POST["option"] AS &$opt) {
        if ($error) {
            $msg=fdbkMsg(0,"There was an error saving the changes to your social media links. Please try again.");
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
        $msg=fdbkMsg(1,"Changes were saved at ".date('d F, Y h:i:s').'.');
    } else {
        $msg=fdbkMsg(0,"There was an error saving your menu changes. Please try again.");
    }
}

if (isset($_POST['edit_rss'])) {
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
            $cPost['n_item_limit'] = cleanInt($cPost['n_item_limit']);
        }
    }
    if (updateRefSects($cPost)) {
        $msg = fdbkMsg(1,'RSS Feed settings saved successfully at '.date('d F, Y h:i:s').'.');
    } else {
        $msg = fdbkMsg(0,'There was an error saving the changes to your RSS Feed settings. Please try again later.');
    }
}

if (isset($_POST['admin_media_upload'])) {
    $paths = array();
    $names = array();
    $error = false;
    $msg='';
    // suggested by https://www.tutorialspoint.com/how-to-upload-multiple-files-and-store-them-in-a-folder-with-php
    if (!empty($_FILES['upload'])) {
        $filesLen = count($_FILES['upload']['name']);
        $files = separateUploads($_FILES['upload'], $filesLen);
    } else {
        $msg = fdbkMsg(0,"No valid files were found to upload.");
        return false;
    }
    $successLen = 0;
    if ($filesLen) {
        require_once('handleFiles.inc.php');
        $dir = '/assets/uploads/media/';
        for ($i=0;$i<$filesLen;$i++) {
            if ($files[$i]['tmp_name']) {
                $upload = createFilePath(
                                       $dir, 
                                       $files[$i]);
                if ($upload['result']) {
                    $successLen++;
                    $names[] = cleanFileName($files[$i]['name']);
                    $paths[] = $upload['result'];
                } else {
                    $error=true;
                    $msg .= fdbkMsg(0,$upload['msg']);
                }
            }
        }
        if ($successLen) {
            if ($msg) {$msg.="<br/><br/>";}
            $msg .= "The follow files were uploaded successfully: <ol class='text-left'>";
            for ($s=0;$s<$successLen;$s++) {
                $msg .= '<li>
                <a href="'.$set['dir'].'/admin/media-manager.php?file='.$names[$s].'" target="_blank">'.$names[$s].'</a>
                <a class="button small" data-copy="'.$baseURL.$paths[$s].'" onclick="copyToClipboard(this)"><i class="fi fi-rs-duplicate"></i> Absolute Path</a>
                <a class="button small" data-copy="'.$set['dir'].$paths[$s].'" onclick="copyToClipboard(this)"><i class="fi fi-rs-duplicate"></i> Relative Path</a>
                </li>';
            }
            $msg .="</ol>";
            if ($error) {
                $msgType=2;
                $header="Parital Success";
            } else {
                $msgType=1;
                $header=null;
            }
            $msg.=fdbkMsg($msgType,$msg,$header,true);
        }
        return true;
    } else {
        $msg.=fdbkMsg(0,"No valid files were found to upload.");
    }
    return false;
}
if (isset($_POST['admin_delete_file'])) {
    $name=cleanFileName($_POST['name']);
    $dir = $root.'/assets/uploads/media/';
    if (unlink($dir.$name)) {
        $msg = fdbkMsg(1,"The file '".$name."' has been deleted from the server.", "Deleted.");
    } else {
        $msg = fdbkMsg(0,"The file '".$name."' failed to delete from the server.");
    }
}

function getAdminAccounts($id=null) {
    global $db; global $set; $all=false;
    $accouts = array();
    $conn = new SQLite3($db);
    if (isMasterAccount() && !$id) {
        $all=true;
    } elseif (!$id) {
        $id=$_SESSION['UserID'];
    }
    $id = cleanInt($id);
    $qry = "SELECT 
    `ID`,`Username`,`Email`,`Temp_Email`,`Permissions`,`Icon_Path`,
    `Activation_Timestamp`,`Activation_Key`,`Locked_Until`,
    `Curr_Sess_ID`,`Last_Login`,`Account_Created`
    FROM `Accounts`";
    if ($all) {
        $qry .= " ORDER BY `Permissions` DESC, `ID` ASC";
    } else {
        $qry .= " WHERE `ID`=? LIMIT 1";
    }
    $qry .= ';';
    $stmt = $conn->prepare($qry);
    if (!$all) {
        $stmt->bindValue(1,$id,SQLITE3_INTEGER);
    }
    $result = $stmt->execute();
    if ($result) {
        $dateFormat = "d M Y <\b\\r> H:i T";
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $row['Username'] = ($row['Username']>'' ? $row['Username'] : "User ID#".$row['ID']);
            if ($row['Account_Created']) {
                $row['Account_Created_Date'] =  timestampToDate($row['Account_Created'],$dateFormat);
            } else {
                $row['Account_Created_Date'] = $row['Account_Created'] = "Unknown";
            }
            if ($row['Last_Login']) {
                $row['Last_Login_Date'] = timestampToDate($row['Last_Login'],$dateFormat);
            } else {
                $row['Last_Login_Date'] = $row['Last_Login_Date'] = "Unknown";
            }
            if ($row['Email'] && !$row['Temp_Email']) {
                $row['Email_Valid'] = true;
            } else {
                $row['Email'] = $row['Temp_Email'];
                $row['Email_Valid'] = false;
            }
            if (!$row['Email_Valid']) {
                if ($row['Activation_Timestamp']<time()) {
                    $row['Activation_Expired'] = true;
                } else {
                    $row['Activation_Expired'] = false;
                }
            } else {
                $row['Activation_Expired'] = null;
            }
            switch ($row['Permissions']) {
                case $set['full_permissions'] :
                    $row['Title'] = 'Master Account';
                    break;
                case 0 :
                    $row['Title'] = 'Deactivated';
                    break;
                case $set['standard_permissions'] :
                default :
                    $row['Title'] = 'Standard';
                    break;
            }

            if ($all) {
                $accounts[] = $row;
            } else {
                $stmt->close();
                return $row;
            }
        }
        $stmt->close();
        return $accounts;
    } else {
        $stmt->close();
        $msg = fdbkMsg(0,'Could not find any accounts. Please try again.');
        return false;
    }
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// v ACCOUNT EDITING PERMISSIONS v //////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (($_SESSION['Permissions'] ?? 0)>=$set['full_permissions']) {

    if (isset($_POST['send_account_invite']) || isset($_POST['resend_account_invite'])) {
        $msg = ''; $email=false; $inviteeID = $resend = false;
        if (isset($_POST['resend_account_invite'])) {
            $resend = true;
            $inviteeID = cleanInt($_POST['n_user_id']);
        }
        if (filter_var($_POST['account_email'], FILTER_VALIDATE_EMAIL)) {
            $email = strtolower(stripHTML($_POST['account_email']));
        } 
        if (!$email) {
            if ($resend) {
                $msg .= 'Something is wrong with the email listed for this account invite. Delete this unverified account and try again.';
            } else {
                $msg .= 'Please enter a valid email address.';
            }
            return false;
        }
        $conn = new SQLite3($db);
        $selQry = "SELECT `ID`, `Email`, `Temp_Email` FROM `Accounts` WHERE `Email`=:email OR `Temp_Email`=:email";
        if ($resend) {
            $selQry .= " OR `ID`=:id";
        }
        $selQry .= ";";
        $selStmt = $conn->prepare($selQry);
        $selStmt->bindValue(':email', $email, SQLITE3_TEXT);
        if ($resend) {
            $selStmt->bindValue(':id', $inviteeID, SQLITE3_INTEGER);
        }
        $selResult = $selStmt->execute();
        $match=null;
        if ($selResult) {
            while ($row = $selResult->fetchArray(SQLITE3_ASSOC)) {
                if ($row) {
                    if ($row['ID']==$inviteeID && $row['Email']>'') {
                        $msg = fdbkMsg(0,"This account has already been activated.","Hold on there.");
                        return;
                    }
                    if ($row['Email']) {
                        $match = $row['Email'];
                    } elseif ($row['Temp_Email'] && $row['ID']!=$inviteeID) {
                        $match = $row['Temp_Email'];
                    }
                }
            }
        }
        if ($match) {
            $msg = fdbkMsg(0,"There is already an account using this email address. Please choose something else.","Pardon?");
            return;
        }
        $selResult->finalize(); $selStmt->close();
        $time = time();
        $expiration = addToUnixTime($time,2,"days");
        $key = bin2hex(random_bytes(22));
        $keyHash = hash("sha256", $key);
        if (!$resend) {
            $insertQry = "INSERT INTO `Accounts` (`Temp_Email`, `Activation_Timestamp`, `Activation_Key`)
            VALUES (:tempmail,:exp,:key);";
        } else {
            $insertQry = "UPDATE `Accounts` SET `Activation_Timestamp`=:exp, `Activation_Key`=:key
                    WHERE `Temp_Email`=:tempmail;";
        }
        $stmt = $conn->prepare($insertQry);
        $stmt->bindValue(':tempmail', $email, SQLITE3_TEXT);
        $stmt->bindValue(':exp', $expiration, SQLITE3_INTEGER);
        $stmt->bindValue(':key', $keyHash, SQLITE3_TEXT);
        if (!$stmt->execute()) {
            if (failedConstrait($conn)) {
                $msg = $msg = fdbkMsg(0,"There is already an account using this email address. Please choose something else.","Pardon?");
            } else {
                $msg = fdbkMsg(0,"A database error occurred. Try again.");
            }
        } else {
            $body = "You're invited to the ".($set['site_name'] ? $set['site_name'] : $baseURL)." team!<br/><br/>"; 
            $body .= "To activate your account, click the following link:<br/>";
            $activateUrl = html_entity_decode($route."/index.php?key=$key");
            $body .=  '<a href="'.$activateUrl.'">'.$activateUrl.'</a>';
            $body .=  '<br/>This activation key expires at '.timestampToDate($time).". 
            If it has already expired, please contact the site's admin to send you a new invite.";
                if (mail($email, "Create your account on ".$baseURL."!", $body, $emailHeaders)) {
                $msg .= "<p>Thank you! A confirmation email has been sent to <strong><em>".$email."</em></strong>. Remember to tell them to
                check their spam or junk folder if it doesn't appear after a few minutes.</p>
                <p><strong>They have 48 hours before their account activation key expires (".timestampToDate($time).").</strong> 
                If they do not activate their account in that timespan, you will need to send another invitation.</p>";
                $msg=fdbkMsg(1,$msg,null,true);
                $_POST = array();
                } else {
                $msg .= "<p>The email to validate the account failed to send. Please try again.</p>";
                $msg=fdbkMsg(0,$msg);
                }
                error_log('Activation link failsafe for '.$email.': '.$activateUrl);
        }
        $_POST=array();
        $stmt->close();
    }

    if (isset($_POST['admin_change_password'])) {
        global $db;
        $id = cleanInt($_POST['n_user_id']);
        if ($_POST['password'] && $_POST['password2']) {
            $password = pwCmp($_POST['password'],$_POST['password2']);
            if ($password === false) {
                $msg .= 'Password and password confirmation do not match.<br/>';
                return ;
            }
        } else {
            $msg = fdbkMsg(0,'Please fill out all of the relevant fields.',"Not so fast!");
            return ;
        }
        $conn = new SQLite3($db);
        $qry = "UPDATE `Accounts` SET `Password` = :pw WHERE `ID` = :id;";
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(':pw', $password, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        if (!$stmt->execute()) {
            $msg = fdbkMsg(0,"A database error occurred. Try again.");
        } else {
            $msg = fdbkMsg(1,ucfirst($_POST['account_username'])."'s password has been changed!");
        }
        $stmt->close();
    }

    if (isset($_POST['admin_change_email'])) {
        global $db; global $emailHeaders;
        $id = cleanInt($_POST['n_user_id']);
        $email = filter_var($_POST['new_email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $msg = fdbkMsg(0,"The email input is invalid. Check to make sure that it's written correctly, and try again.","Something's wrong.");
            return;
        }
        $conn = new SQLite3($db);
        $time = time();
        $time = addToUnixTime($time,2,"days");
        $key = bin2hex(random_bytes(22));
        $keyHash = hash("sha256", $key);
        $qry = "UPDATE `Accounts` SET 
                        `Temp_Email` = :email,
                        `Activation_Timestamp` = :time, 
                        `Activation_Key` = :key
                    WHERE `ID` = :id;";
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(':time', $time, SQLITE3_INTEGER);
        $stmt->bindValue(':key', $keyHash, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_TEXT);
        if ($stmt->execute()) {
            $body = "To activate your new email address, click the following link:<br/>";
            $activateUrl = html_entity_decode($route."/index.php?key=$key");
            $body .=  '<a href="'.$activateUrl.'">'.$activateUrl.'</a>
            <br/><br/>
            This activation key expires in 48 hours ('.timestampToDate($time).").</p> 
            If it has already expired, please contact the site's admin to send you another activation email.";
                if (mail($email, 'Validate your new email address', $body, $emailHeaders)) {
                    $msg = "A confirmation email has been sent to your new email address! If it hasn't shown up within a few minutes, 
                    check your spam or junk folder.
                    This activation key expires in 48 hours (".timestampToDate($time).").";
                    $msg=fdbkMsg(1,$msg,"Hurray!",true);
                } else {
                    $msg = fdbkMsg(0,"The email to validate your new email address failed to send. Please try again.");
                }
                error_log($_POST['account_username']."'s email activation link failsafe: ".$activateUrl);
                // // FOR TESTING 
                // global $testing;
                // if ($testing) {
                    // echo $body;
                // }
        } else {
            $msg = fdbkMsg(0,'Something went wrong. Please try again.');
        }
    }

    if (isset($_POST['admin_change_permissions'])) {
        $permissions=cleanInt($_POST['n_permissions']);
        if (!$permissions) {
            $permissions=0;
        }
        $userID = cleanInt($_POST['n_user_id']);
        if ($userID==$_SESSION['UserID']) {
            $msg=fdbkMsg(0,'You cannot change the permissions for your own account. Ask another active Master Account to do it for you.',"Sorry, Charlie.");
            return;
        }
        $sessID = session_id();
        $conn = new Sqlite3($db);
        $qry = "UPDATE `Accounts` SET `Permissions`=? WHERE `ID`=?;";
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(1,$permissions,SQLITE3_INTEGER);
        $stmt->bindValue(2,$userID,SQLITE3_INTEGER);
        //$stmt->bindValue(3,$sessID,SQLITE3_TEXT);
        $result = $stmt->execute();
        if ($result && $conn->changes()>0) {
            if ($permissions>=$set['full_permissions']) {
                $msg = ucfirst($_POST['account_username'])." now has Master Account permissions!";
            } elseif (!$permissions) {
                $msg = ucfirst($_POST['account_username'])." account has been deactivated.";
            } else {
                $msg = ucfirst($_POST['account_username'])." account has been giving standard permissions.";
            }
            $msg=fdbkMsg(1,$msg);
        } else {
            if (!$result) {
                $msg .= 'A database error occurred.
                ';
            }
            $msg .= ucfirst($_POST['account_username'])."'s account permissions changes failed to save.";
            $msg=fdbkMsg(0,$msg);
        }
        $result->finalize();
        $stmt->close();
    }

    if (isset($_POST['admin_delete_account'])) {
        $userID = cleanInt($_POST['n_user_id']);
        if ($userID==$_SESSION['UserID']) {
            $msg=fdbkMsg(0,'You cannot delete your own account. Ask another active admin to delete it for you.',"No Can-Do...");
            return;
        }
        $conn = new Sqlite3($db);
        $qry = "DELETE FROM `Accounts` WHERE `ID`=:id;";
        $stmt = $conn->prepare($qry);
        $stmt->bindValue(':id',$userID,SQLITE3_INTEGER);
        if ($stmt->execute()) {
                $msg = fdbkMsg(1,ucfirst($_POST['account_username'])."'s account has been deleted.","Bye-bye!");
        } else {
            $msg = fdbkMsg(0,ucfirst($_POST['account_username'])."'s account failed to delete. Try again.");
        }
    }

} //end "if ($_SESSION['Permissions']>=$set['full_permissions'])"
} //end "if ($loggedIn)"