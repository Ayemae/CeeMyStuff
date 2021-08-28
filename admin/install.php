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
    Field TEXT UNIQUE,
    Key TEXT UNIQUE,
    Value TEXT,
    Type TEXT,
    Description TEXT,
    Options TEXT,
    Index_Order INTEGER
)');

$conn->exec('INSERT INTO Settings (ID, Index_Order, Field, Key, Value, Type, Description, Options)
    VALUES 
    (1, 3, "Sub-directory", "dir", "", "text", "If your CeeMyStuff site is in a subdirectory, write it in here.", null),
    (2, 1, "Site Name", "site_name", "My Portfolio", "text", null, null),
    (3, 2, "Owner Name", "owner_name", "My Name", "text", "Your name, or the name of the group this site belongs to.", null),
    (4, 4, "Initial Copyright Year", "c_year", "", "number", null, null),
    (5, 5, "Header Image", "header_img", null, "file", "If you want to use a header image, upload it here.",null),
    (6, 6, "Timezone", "timezone", "America/New_York", "timezone", null,null),
    (7, 7, "Social Media Button Format", "sm_format", "Icons", "select", "How your social media buttons will display.", "Icons, Text"),
    (8, 8, "Collapse Menu on Mobile","mobile_collapse_menu","checked","checkbox", null, null),
    (9, 9, "Automate Thumbnails by Default","auto_thumbs","checked","checkbox", "If you prefer that categories default to automatically making thumbnails.", null),
    (10, 10, "Thumbnail Size (in pixels)","thumb_size","125","number", null, null),
    (11, 11, "Thumbnail Size Axis","thumb_size_axis","width", "select", "Is your thumbnail size restriction for its width, or for its height?", "Width, Height"),
    (12, 12, "Enable Max Image Dimensions","has_max_img_dimns","checked", "checkbox", "Enable a maximum height/width on the images you can upload.", null),
    (13, 13, "Max Image Dimensions (in pixels)", "max_img_dimns", "2400","number", null, null),
    (14, 14, "Enable Max Image Storage Size","has_max_img_storage","checked", "checkbox", "Enable a maximum on how much storage a single image upload can take up.", null),
    (15, 15, "Max Image Storage Size (in Kilobytes)","max_img_storage","1500", "number", "For reference, roughly 1000 kilobytes are in a megabyte, and rougly 1000000 are in a gigabyte.", null)
    ;');

$conn->exec('CREATE TABLE IF NOT EXISTS Categories (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Index_Order INTEGER,
    Name TEXT UNIQUE NOT NULL,
    Blurb TEXT,
    Header_Img_Path TEXT,
    Show_Images INTEGER NOT NULL DEFAULT 1,
    Show_Titles INTEGER NOT NULL DEFAULT 1,
    Show_Captions INTEGER NOT NULL DEFAULT 1,
    Auto_Thumbs INTEGER NOT NULL DEFAULT 1,
    Thumb_Size INTEGER NOT NULL DEFAULT 125,
    Thumb_Size_Axis INTEGER NOT NULL DEFAULT 0,
    Order_By TEXT NOT NULL DEFAULT "date",
    Hidden INTEGER NOT NULL DEFAULT 0,
    Format_ID INTEGER
)');

$conn->exec('INSERT INTO Categories (ID, Index_Order, Name, Blurb, Hidden)
    VALUES 
    (0, 0, "None", "Items that are not sorted into any category.", 1);');

$conn->exec('CREATE TABLE IF NOT EXISTS Items (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Cat_ID INTEGER,
    Title TEXT NOT NULL,
    Img_Path TEXT,
    Img_Thumb_Path TEXT,
    Caption TEXT,
    Publish_Timestamp INTEGER,
    Index_Order INTEGER,
    Cat_Index_Order INTEGER,
    Format_ID INTEGER,
    Hidden INTEGER
)');


$conn->exec('CREATE TABLE IF NOT EXISTS Accounts (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Name TEXT,
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
    Name,
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
    "My Name",
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


$conn->exec('CREATE TABLE IF NOT EXISTS Pages (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Page_Name TEXT,
    Link_Text TEXT,
    Page_Header TEXT
)');

$conn->exec('CREATE TABLE IF NOT EXISTS Content (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Header TEXT,
    Content TEXT,
    Format_ID TEXT
)');

$conn->exec('CREATE TABLE IF NOT EXISTS Page_x_Content (
    Is_Cat INTEGER,
    Content_ID INTEGER,
    Page_ID INTEGER,
    Hidden INTEGER
)');

$conn->exec('CREATE TABLE IF NOT EXISTS Menu (
    Index_Order INTEGER,
    Page_ID INTEGER,
    Outgoing_Link TEXT,
    In_Drop INTEGER,
    Image TEXT,
    Hidden INTEGER
)');

$conn->exec('CREATE TABLE IF NOT EXISTS Social_Media (
    Platform TEXT,
    Icon TEXT,
    URL TEXT,
    Hidden INTEGER
)');

$conn->exec('CREATE TABLE IF NOT EXISTS Social_Media_Defaults (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Platform TEXT,
    Icon TEXT,
    URL TEXT
)');

$conn->exec('INSERT INTO Social_Media_Defaults (ID, Platform, Icon, URL)
    VALUES 
        (1, "Instagram","/assets/icons/instagram.svg","https://www.instagram.com/YOUR_HANDLE/"),
        (2, "Facebook","/assets/icons/facebook.svg","https://www.facebook.com/YOUR_HANDLE/"),
        (3, "LinkedIn","/assets/icons/linkedin.svg","https://www.linkedin.com/in/YOUR_HANDLE/"),
        (4, "Patreon","/assets/icons/patreon.svg","https://www.patreon.com/YOUR_HANDLE"),
        (5, "Tumblr","/assets/icons/tumblr.svg","https://YOUR_HANDLE.tumblr.com/"),
        (6, "Twitch","/assets/icons/twitch.svg","https://www.twitch.tv/YOUR_HANDLE"),
        (7, "Twitter","/assets/icons/twitter.svg","https://twitter.com/YOUR_HANDLE"),
        (8, "YouTube","/assets/icons/youtube.svg","https://www.youtube.com/user/YOUR_HANDLE");');

$conn->exec('CREATE TABLE IF NOT EXISTS Blog_Posts (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Title TEXT,
    Content TEXT,
    Timestamp INTEGER,
    Public INTEGER
)');


$conn->exec('CREATE TABLE IF NOT EXISTS Tags (
    Parent_Type TEXT,
    Parent_ID INTEGER,
    Name TEXT
)');



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
include '../components/header.php';
?>


<main>

<?php if (empty($_GET)) :?>
<h2>Welcome to CeeMyStuff!</h2>
<p>Let's add your user credentials.</p>

<form method="post" action="?submitted=1">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" max-length="255"/>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" max-length="255"/>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" max-length="255"/>

    <label for="password2">Confirm Password:</label>
    <input type="password" id="password2" name="password2" max-length="255"/>

    <button name="submit_credentials">Submit</button>
</form>
<?php endif;?>
</main>

<?php
include '../components/footer.php';