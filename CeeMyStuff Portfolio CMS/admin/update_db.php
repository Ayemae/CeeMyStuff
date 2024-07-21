<?php 
$admin_panel = true;
$loginArea = true;
?>

<main>

<?php 
$conn = new SQLite3('../data/database.db');

$conn->exec('ALTER TABLE `Settings` 
ADD COLUMN Global INTEGER NOT NULL DEFAULT 1
;');

$conn->exec('ALTER TABLE `Sections` 
ADD COLUMN Index_Format TEXT
;');

$conn->exec('ALTER TABLE `Items` 
ADD COLUMN Show_File INTEGER
;');


$conn->exec('UPDATE `Sections` 
SET `Show_Item_Tags`= (CASE WHEN `Show_Item_Tags`>0 THEN (`Show_Item_Tags`+1) ELSE 0 END)
;');

$conn->exec('UPDATE `Settings` 
SET `Heading`="Optimization" 
WHERE `Heading`="Advanced"
;');

$conn->exec('UPDATE `Settings` 
SET `Heading`="Website Display" 
WHERE `Heading`="Display"
;');

$conn->exec('UPDATE `Settings` 
SET `Options`="5, 50000" 
WHERE `Key`="max_img_dimns" OR `Key`="max_upld_storage"
;');

$conn->exec('INSERT INTO `Pages` 
(`ID`,`Name`,`Link`)
VALUES (0, "Page Setting Defaults", "home");');

$conn->exec('INSERT INTO `Pages` 
(`Index_Order`,`Field`,`Key`,`Value`,`Type`,`Options`,`Description`,`Heading`,`Global`)
VALUES (19,"Auto-Thumbnail Quality (% out of 100)","thumb_quality",75,"number","1, 100","How high you want the image quality of generated thumbnails.<br/>Keep in mind that the higher the quality, the more taxing it will be on the browser to load it &mdash; even if the image is tiny.","Optimization",1),
(13,"Item Content Previews","preview_item_content","Icons","checklist","Icons, Image Thumbnail, Text Excerpt, File Path, Embed Script, Tags","What kind of previews of item contents that you would like to be visible in the Admin Item Indexes. (If an image thumbnail does not exist, a resized version of the full-sized image will be used instead.)","Admin Panel Options",0)
;');

$conn->exec('INSERT INTO `Settings` 
(`ID`,`Index_Order`, `Field`, `Key`, `Value`, `Type`, `Description`, 
`Options`, `Heading`, `Global`) 
VALUES (19, 19, "Auto-Thumbnail Quality (% out of 100)", 
"thumb_quality", 
"75", 
"number",
"How high you want the image quality of generated thumbnails. Keep in mind that the higher the quality, the more taxing it will be on the browser to load it &mdash; even if the image is tiny.",
"1,100",
"Optimization", 1)
;');

$conn->exec('INSERT INTO `Settings` 
(`ID`,`Index_Order`, `Field`, `Key`, `Value`, `Type`, `Description`, 
`Options`, `Heading`, `Global`) 
VALUES (20, 13, "Item Content Previews", 
"preview_item_content", 
"Icons", 
"checklist",
"What kind of previews of item contents that you would like to be visible in the Admin Item Indexes. (If an image thumbnail does not exist, a resized version of the full-sized image will be used instead.)",
"Icons, Image Thumbnail, Text Excerpt, File Path, Embed Script, Tags",
"Admin Panel Options", 0)
;');
?>

</main>

<?php
include '_components/admin-footer.php';