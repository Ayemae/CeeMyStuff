<?php 

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
    Options TEXT,
    Heading TEXT DEFAULT "Info",
    Hidden INTEGER NOT NULL DEFAULT 0
)');

$conn->exec('INSERT INTO Settings (Index_Order, Field, Key, Value, Type, Description, Options, Heading)
    VALUES 
    (3, "Subdirectory", "dir", "", "text", "If this portfolio site is in a subdirectory, write which subdirectory in here.", null, "Info"),
    (1, "Site Name", "site_name", "My Portfolio", "text", null, null, "Info"),
    (2, "Owner Name", "owner_name", "", "text", "Your name, or the name of the group this site belongs to.", null, "Info"),
    (4, "Theme", "theme", "White-Bread", "function", "What theme you want to use for the aesthetic look of your site.",null, "Display"),
    (5, "Initial Copyright Year", "c_year", "", "number", null, null, "Info"),
    (6, "Timezone", "timezone", "America/New_York", "function", null,null, "Info"),
    (7, "Date Format", "date_format", "j F, Y g:i A", "text", "What format you want the date to be in. Look up PHP date formats for details.",null,"Display"),
    (8, "Header Image", "header_img", null, "img-file", "If you want to use a header image, upload it here.",null,"Display"),
    (9, "Favicon", "favicon_img", null, "img-file", "Upload your favicon, or browser icon, here. Must be a \'.gif\' file, and 16x16 pixels.",null,"Display"),
    (10, "Mobile Browser Icon", "mobile_icon_img", null, "img-file", "Upload your mobile browser icon here. Must be 180x180 pixels.",null,"Display"),
    (11, "Site Menu Button Format", "menu_format", "Text", "select", "How your site menu buttons will display.", "Images, Text","Display"),
    (12, "Social Media Button Format", "sm_format", "Icons", "select", "How your social media buttons will display.", "Icons, Text","Display"),
    (13, "Enable Max Image Dimensions", "has_max_img_dimns","checked", "checkbox", "Enable a maximum height/width on the images you can upload. (Strongly recommended.)", null,"Advanced"),
    (14, "Max Image Dimensions (in pixels)", "max_img_dimns", "2400","number", "Must be enabled to take effect.", null,"Advanced"),
    (15, "Enable Max Upload Storage Size","has_max_upld_storage","checked", "checkbox", "Enable a maximum on how much storage a single uploaded file can take up. (Strongly recommended.)", null,"Advanced"),
    (16, "Max Upload Storage Size (in Megabytes)","max_upld_storage","25", "number", "For reference, rougly 1000 megabytes are in a gigabyte. Must be enabled to take effect.", null,"Advanced"),
    ;');
    $conn->exec('INSERT INTO `Settings` (`Index_Order`, `Field`, `Key`, `Value`, `Type`, `Description`, `Heading`, `Hidden`)
    VALUES 
    (17, "Enable RSS Feed", "has_rss", "", "checkbox", "Add an RSS feed to your site.", "", 1)
    ;');

$conn->exec('CREATE TABLE IF NOT EXISTS Pages (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Name TEXT UNIQUE COLLATE NOCASE NOT NULL,
    Link TEXT UNIQUE COLLATE NOCASE NOT NULL,
    Meta_Text TEXT DEFAULT null,
    Content TEXT DEFAULT null,
    Header_Img_Path TEXT DEFAULT null,
    Show_Title INTEGER NOT NULL DEFAULT 1,
    Show_Header_Img INTEGER NOT NULL DEFAULT 1,
    Multi_Sect INTEGER NOT NULL DEFAULT 0,
    Paginate INTEGER NOT NULL DEFAULT 0,
    Paginate_After INTEGER NOT NULL DEFAULT 15,
    Format TEXT,
    Hidden INTEGER NOT NULL DEFAULT 0
)');

$conn->exec('INSERT INTO Pages (ID, Name, Link, Meta_Text)
    VALUES 
    (1, "Home", "", "Portfolio homepage.");');

$conn->exec('CREATE TABLE IF NOT EXISTS Sections (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Name TEXT NOT NULL,
    Page_ID INTEGER DEFAULT NULL,
    Page_Index_Order INTEGER,
    Is_Reference INTEGER NOT NULL DEFAULT 0,
    Text TEXT,
    Header_Img_Path TEXT DEFAULT NULL,
    Show_Title INTEGER NOT NULL DEFAULT 0,
    Show_Header_Img INTEGER NOT NULL DEFAULT 1,
    Show_Text INTEGER NOT NULL DEFAULT 2,
    Show_Item_Images INTEGER NOT NULL DEFAULT 1,
    Show_Item_Titles INTEGER NOT NULL DEFAULT 1,
    Show_Item_Dates INTEGER NOT NULL DEFAULT 1,
    Show_Item_Text INTEGER NOT NULL DEFAULT 2,
    Show_Item_Files INTEGER NOT NULL DEFAULT 1,
    Show_Item_Tags INTEGER NOT NULL DEFAULT 2,
    Tag_List_Spacer TEXT DEFAULT ", ",
    Tag_Spacer_On_Ends INTEGER NOT NULL DEFAULT 0,
    Item_Click_Area TEXT DEFAULT "All",
    On_Click_Action INTEGER NOT NULL DEFAULT 1,
    Paginate_Items INTEGER NOT NULL DEFAULT 1,
    Default_File_Link_Text TEXT DEFAULT "Click here",
    Order_By TEXT NOT NULL DEFAULT "Date",
    Order_Dir INTEGER NOT NULL DEFAULT 0,
    Auto_Thumbs INTEGER NOT NULL DEFAULT 1,
    Thumb_Size INTEGER NOT NULL DEFAULT 125,
    Thumb_Size_Axis INTEGER NOT NULL DEFAULT 0,
    Format TEXT,
    Default_Item_Format TEXT,
    View_Item_Format TEXT,
    Lightbox_Format TEXT,
    Item_Display_Limit INTEGER DEFAULT NULL,
    Truncate_Text_At INTEGER NOT NULL DEFAULT 140,
    Default_Item_Link_Text TEXT NOT NULL DEFAULT "View",
    Hidden INTEGER NOT NULL DEFAULT 0,
    UNIQUE(Name,Page_ID)
)');

$conn->exec('CREATE TABLE IF NOT EXISTS Reference_Sections (
    Sect_ID INTEGER PRIMARY KEY UNIQUE NOT NULL,
    Ref_Sect_IDs TEXT DEFAULT NULL,
    Date_Cutoff_On INTEGER NOT NULL DEFAULT 0,
    Date_Cutoff TEXT DEFAULT NULL,
    Date_Cutoff_Dir INTEGER NOT NULL DEFAULT 1,
    Tag_Filter_On INTEGER NOT NULL DEFAULT 0,
    Tag_Filter_List TEXT DEFAULT NULL,
    Tag_Filter_Mode INTEGER NOT NULL DEFAULT 1,
    Item_Limit INTEGER DEFAULT NULL
)');


$conn->exec('INSERT INTO Sections (ID, Page_ID, Page_Index_Order, Name, Text, Format, Default_Item_Format, Auto_Thumbs)
    VALUES 
    (0, null, 0, "Orphaned Items", "Items that are not sorted into any section.", null, null, 0),
    (1, 0, 1, "Home Content", "See my stuff!", "/assets/universal-formats/section/section-general.php", "/assets/universal-formats/item/item-general.php", 1);');

$conn->exec('INSERT INTO Reference_Sections (Sect_ID, Ref_Sect_IDs, Date_Cutoff_On, Date_Cutoff, Date_Cutoff_Dir, Item_Limit)
    VALUES 
    (0, "1", 1, "[2]4 months", 1, 15);');


$conn->exec('CREATE TABLE IF NOT EXISTS Items (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Sect_ID INTEGER,
    Sect_Index_Order INTEGER,
    Title TEXT NOT NULL,
    Publish_Timestamp INTEGER,
    Format TEXT DEFAULT NULL,
    Text TEXT,
    Img_Path TEXT DEFAULT NULL,
    Img_Thumb_Path TEXT DEFAULT NULL,
    Img_Alt_Text TEXT DEFAULT NULL,
    File_Path TEXT DEFAULT NULL,
    File_Link_Text TEXT,
    Embed_HTML TEXT DEFAULT NULL,
    Tags TEXT DEFAULT NULL,
    Item_Link_Text TEXT DEFAULT NULL,
    Hidden INTEGER NOT NULL DEFAULT 0
)');


$conn->exec('CREATE TABLE IF NOT EXISTS Accounts (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Username TEXT COLLATE NOCASE,
    Email TEXT COLLATE NOCASE,
    Email_Valid INTEGER DEFAULT 0,
    Password TEXT,
    Is_Admin INTEGER,
    Icon_Path TEXT,
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


$conn->exec('CREATE TABLE IF NOT EXISTS Automenu (
    ID INTEGER PRIMARY KEY AUTOINCREMENT UNIQUE NOT NULL,
    Type_Code INTEGER DEFAULT NULL,
    Ref_ID INTEGER DEFAULT NULL,
    Index_Order INTEGER DEFAULT 999,
    Link_Text TEXT DEFAULT NULL,
    Ext_Url TEXT DEFAULT NULL,
    Submenu INTEGER DEFAULT 0,
    Img_Path TEXT DEFAULT NULL,
    Hidden INTEGER DEFAULT 0,
    UNIQUE(Type_Code,Ref_ID)
)');

$conn->exec('INSERT INTO Automenu (Type_Code, Ref_ID, Index_Order)
    VALUES (1,1,1);'
    );

$conn->exec('CREATE TABLE IF NOT EXISTS Social_Media (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    Index_Order INTEGER NOT NULL DEFAULT 99,
    Link_Name TEXT NOT NULL,
    Link_Text TEXT NOT NULL,
    Icon TEXT DEFAULT NULL,
    URL TEXT NOT NULL,
    Hidden INTEGER NOT NULL DEFAULT 0
)');

$conn->exec('INSERT INTO `Social_Media` (`ID`, `Index_Order`, `Link_Name`, `Link_Text`, `URL`, `Hidden`)
VALUES (0,1,"RSS","RSS","/rss/",1);'
);

$conn->exec('CREATE TABLE IF NOT EXISTS Uploads (
    ID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    File_Path TEXT,
    File_Type TEXT DEFAULT "Image",
    Timestamp INTEGER
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
include '_components/admin-header.inc.php';
?>


<main>

<?php if (empty($_GET)) :?>
<header>
    <h2>Welcome to CeeMyStuff!</h2>
</header>

<p>Let's add your user credentials.</p>

<form method="post" action="?submitted=1">
    <ul>
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
include '../components/admin-footer.php';