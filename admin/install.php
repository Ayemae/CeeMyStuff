<?php 

$showErrors = 1;

if ($showErrors) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(-1);
}

// $root = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'], 2);

if (empty($_GET)) {
mkdir('../data', 755);
$conn = new SQLite3('../data/database.db');


$conn->exec('CREATE TABLE IF NOT EXISTS Settings (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Index_Order INTEGER,
    Field TEXT UNIQUE,
    Key TEXT UNIQUE,
    Value TEXT,
    Type TEXT,
    Description TEXT,
    Options TEXT
)');

$conn->exec('INSERT INTO Settings (ID, Index_Order, Field, Key, Value, Type, Description, Options)
    VALUES 
    (1, 3, "Sub-directory", "dir", "", "text", "If this portfolio site is in a subdirectory, write which subdirectory in here.", null),
    (2, 1, "Site Name", "site_name", "My Portfolio", "text", null, null),
    (3, 2, "Owner Name", "owner_name", "My Name", "text", "Your name, or the name of the group this site belongs to.", null),
    (4, 4, "Initial Copyright Year", "c_year", "", "number", null, null),
    (5, 5, "Timezone", "timezone", "America/New_York", "timezone", null,null),
    (6, 6, "Date Format", "date_format", "j F, Y g:i A", "text", "What format you\'d like the date to be in. <a href=\'https://www.php.net/manual/en/datetime.format.php\'>Check here for details</a>.",null),
    (7, 7, "Header Image", "header_img", null, "file", "If you want to use a header image, upload it here.",null),
    (8, 8, "Favicon", "favicon", null, "file", "Upload your favicon, or browser icon, here. Must be 16x16 pixels.",null),
    (9, 9, "Mobile Browser Icon", "mobile_icon", null, "file", "Upload your mobile browser icon here. Must be 180x180 pixels.",null),
    (10, 10, "Site Menu Button Format", "menu_format", "Text", "select", "How your site menu buttons will display.", "Images, Text"),
    (11, 11, "Social Media Button Format", "sm_format", "Icons", "select", "How your social media buttons will display.", "Icons, Text")
    (12, 12, "Enable Max Image Dimensions", "has_max_img_dimns","checked", "checkbox", "Enable a maximum height/width on the images you can upload.", null),
    (13, 13, "Max Image Dimensions (in pixels)", "max_img_dimns", "2400","number", "Must be enabled to take effect.", null),
    (14, 14, "Enable Max Upload Storage Size","has_max_upld_storage","checked", "checkbox", "Enable a maximum on how much storage a single image upload can take up.", null),
    (15, 15, "Max Upload Storage Size (in Megabytes)","max_upld_storage","25", "number", "For reference, rougly 1000 megabytes are in a gigabyte. Must be enabled to take effect.", null)
    ;');

$conn->exec('CREATE TABLE IF NOT EXISTS Pages (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Name TEXT COLLATE NOCASE UNIQUE NOT NULL,
    Meta_Text TEXT DEFAULT null,
    Header_Img_Path TEXT DEFAULT null,
    Show_Title INTEGER NOT NULL DEFAULT 1,
    Show_Header_Img INTEGER NOT NULL DEFAULT 1,
    Multi_Cat INTEGER NOT NULL DEFAULT 0,
    Paginate INTEGER NOT NULL DEFAULT 0,
    Paginate_After INTEGER NOT NULL DEFAULT 20,
    Default_Auto_Thumbs INTEGER NOT NULL DEFAULT 1,
    Default_Thumb_Size INTEGER NOT NULL DEFAULT 125,
    Default_Thumb_Size_Axis INTEGER NOT NULL DEFAULT 0,
    Format TEXT,
    Hidden INTEGER NOT NULL DEFAULT 0
)');

$conn->exec('INSERT INTO Pages (ID, Name, Meta_Text)
    VALUES 
    (0, "Home", "Portfolio homepage.");');

$conn->exec('CREATE TABLE IF NOT EXISTS Categories (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Name TEXT UNIQUE NOT NULL,
    Page_ID INTEGER DEFAULT 0,
    Page_Index_Order INTEGER,
    Text TEXT,
    Item_Type TEXT NOT NULL DEFAULT "Code",
    Header_Img_Path TEXT,
    Show_Title INTEGER NOT NULL DEFAULT 1,
    Show_Header_Img INTEGER NOT NULL DEFAULT 1,
    Show_Text INTEGER NOT NULL DEFAULT 1,
    Show_Item_Images INTEGER NOT NULL DEFAULT 1,
    Show_Item_Titles INTEGER NOT NULL DEFAULT 1,
    Show_Item_Text INTEGER NOT NULL DEFAULT 1,
    Order_By TEXT NOT NULL DEFAULT "Date",
    Order_Dir INTEGER NOT NULL DEFAULT 0,
    Auto_Thumbs INTEGER NOT NULL DEFAULT 1,
    Thumb_Size INTEGER NOT NULL DEFAULT 125,
    Thumb_Size_Axis INTEGER NOT NULL DEFAULT 0,
    Format TEXT,
    Hidden INTEGER NOT NULL DEFAULT 0
)');

$conn->exec('INSERT INTO Categories (ID, Page_ID, Page_Index_Order, Name, Text, Hidden)
    VALUES 
    (0, null, 0, "Orphaned Items", "Items that are not sorted into any category.", 1),
    (1, 0, 1, "Home Content", "See my stuff!", 0);');

$conn->exec('CREATE TABLE IF NOT EXISTS Items (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Cat_ID INTEGER,
    Type TEXT DEFAULT "Code",
    Title TEXT NOT NULL,
    Text TEXT,
    Img_Path TEXT,
    Img_Thumb_Path TEXT,
    File_Path TEXT,
    Embed_HTML TEXT,
    Publish_Timestamp INTEGER,
    Cat_Index_Order INTEGER,
    Format TEXT,
    Hidden INTEGER NOT NULL DEFAULT 0
)');


$conn->exec('CREATE TABLE IF NOT EXISTS Accounts (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Username TEXT,
    Email TEXT,
    Email_Valid INTEGER,
    Password TEXT,
    Is_Admin INTEGER,
    Activation_Timestamp INTEGER,
    Activation_Key TEXT,
    Login_Attempts INTEGER,
    Locked_Until INTEGER,
    Curr_Sess_ID TEXT,
    Curr_Sess_Key TEXT
)');

$conn->exec('INSERT INTO Accounts (
    ID,
    Username,
    Email,
    Password,
    Is_Admin,
    Email_Valid,
    Activation_Timestamp,
    Activation_Key,
    Login_Attempts,
    Locked_Until,
    Curr_Sess_ID,
    Curr_Sess_Key
)
    VALUES 
    (1,
    "Admin",
    null,
    null,
    1,
    0,
    0,
    null,
    0,
    0,
    null,
    null);'
    );


$conn->exec('CREATE TABLE IF NOT EXISTS Menu_Options (
    Page_ID INTEGER,
    Index_Order INTEGER,
    Outgoing_Link TEXT,
    In_Dropdown INTEGER,
    Img_Path TEXT,
    Hidden INTEGER
)');

$conn->exec('CREATE TABLE IF NOT EXISTS Social_Media (
    Platform TEXT,
    Icon TEXT,
    URL TEXT,
    Hidden INTEGER
)');

$conn->exec('CREATE TABLE IF NOT EXISTS Uploads (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    File_Path TEXT,
    File_Type TEXT DEFAUlT "Image",
    Timestamp INTEGER
)');

$conn->exec('CREATE TABLE IF NOT EXISTS Tags (
    Item_ID INTEGER,
    Name TEXT
)');

// $conn->exec('CREATE TABLE IF NOT EXISTS Social_Media_Defaults (
//     ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
//     Platform TEXT,
//     Icon TEXT,
//     URL TEXT
// )');

// $conn->exec('INSERT INTO Social_Media_Defaults (ID, Platform, Icon, URL)
//     VALUES 
//         (1, "Instagram","/assets/icons/instagram.svg","https://www.instagram.com/YOUR_HANDLE/"),
//         (2, "Facebook","/assets/icons/facebook.svg","https://www.facebook.com/YOUR_HANDLE/"),
//         (3, "LinkedIn","/assets/icons/linkedin.svg","https://www.linkedin.com/in/YOUR_HANDLE/"),
//         (4, "Patreon","/assets/icons/patreon.svg","https://www.patreon.com/YOUR_HANDLE"),
//         (5, "Tumblr","/assets/icons/tumblr.svg","https://YOUR_HANDLE.tumblr.com/"),
//         (6, "Twitch","/assets/icons/twitch.svg","https://www.twitch.tv/YOUR_HANDLE"),
//         (7, "Twitter","/assets/icons/twitter.svg","https://twitter.com/YOUR_HANDLE"),
//         (8, "YouTube","/assets/icons/youtube.svg","https://www.youtube.com/user/YOUR_HANDLE");');



// $conn->exec('CREATE TABLE IF NOT EXISTS Styles (
//     Page_Col_1 TEXT,
//     Page_Col_2 TEXT,
//     Font_Family_1 TEXT,
//     Font_Family_2 TEXT,
//     Font_Col_1 TEXT,
//     Font_Col_2 TEXT,
//     Text_Align TEXT,
//     Link_Col TEXT,
//     Link_Col_Hover TEXT,
//     Link_Col_Visited TEXT,
//     Background_Img TEXT,
//     Background_Repeat TEXT,
//     Background_Attach TEXT,
//     Background_Size TEXT
// )');
}

include_once '../library/functions.php';
$page_title = 'Install CeeMyStuff';
include '_components/admin-header.inc.php';
?>


<main>

<?php if (empty($_GET)) :?>
<h2>Welcome to CeeMyStuff!</h2>
<p>Let's add your user credentials.</p>

<form method="post" action="?submitted=1">
    <ul>
        <li>
            <label for="name">Choose a Username:</label>
            <input type="text" id="name" name="name" max-length="255"/>
        </li>
        <li>
            <label for="email">Your Email (a confirmation email will be sent):</label>
            <input type="email" id="email" name="email" max-length="255"/>
        </li>
        <li>
            <label for="password">Your Password:</label>
            <input type="password" id="password" name="password" max-length="255"/>
        </li>
        <li>
            <label for="password2">Confirm Your Password:</label>
            <input type="password" id="password2" name="password2" max-length="255"/>
        </li>
    </ul>

    <button name="submit_credentials">Submit</button>
</form>
<?php endif;?>
</main>

<?php
include '../components/footer.php';