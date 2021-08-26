<?php 
$root = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'], 2);
include_once $root.'/components/info-head.php';
$admin_panel = true;
$page_title = 'Admin Panel';
include $root.'/components/header.php';
if (!$loggedIn && $admin_panel) {
    kickOut();
    exit();
}
?>

<main>
<h1>Create New Category</h1>

<form method="post" enctype="multipart/form-data">
<ul class="form-list">
    <li>
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" max-length="255">
    </li>

    <li>
    <label for="header_img_upload">Header Image:</label>
        <input type="file" id="header_img_upload" name="header_img_upload">
    </li>

    <li>
        <label for="b_blurb">Blurb:</label><br/>
        <textarea name="b_blurb"></textarea>
    </li>
</ul>

    <h2>Category Page Display Settings</h2>
    <ul class="form-list">

    <li>
        <label for="show_images">Show Images:</label>
        <select id="show_images" name="n_show_images">
            <option value="0">No</option>
            <option value="1" selected>Show Thumbnails</option>
            <option value="2">Show Full-Sized Images</option>
        </select>
    </li>

    <li>
        <label for="show_titles">Show Titles:</label>
        <select id="show_titles" name="n_show_titles">
            <option value="0">No</option>
            <option value="2" selected>Yes</option>
        </select>
    </li>

    <li>
        <label for="show_captions">Show Captions:</label>
        <select id="show_captions" name="n_show_captions">
            <option value="0">No</option>
            <option value="1" selected>Show Truncated Captions</option>
            <option value="2">Show Full Captions</option>
        </select>
    </li>

    <li>
        <label for="create-thumbnail">Auto-Create Thumbnails for this Category:</label>
        <input type="checkbox" id="create-thumbnail" name="create_thumbnail" class="show-sib-chktoggle" value="checked" <?php echo ($set['auto_thumbs'] ? "checked=checked" : null );?>>
        <div class="show-on-chk">
            <ul class="form-list">
                <li>
                    <label for="thumb_size">Choose default thumbnail size:</label>
                    <input type="number" id="thumb_size" name="thumb_size" value="<?php echo ($set['thumb_size'] ? $set['thumb_size'] : 125 );?>">
                </li>
                <li>
                    <label for="thumb_axis">Axis of thumbnail size:</label>
                    <select id="thumb_axis" name="thumb_axis">
                        <option value="0" <?echo ($set["thumb_size_axis"] == 'width' ? 'selected' : null);?>>Width</option>
                        <option value="1" <?echo ($set["thumb_size_axis"] == 'height' ? 'selected' : null);?>>Height</option>
                    </select>
                </li>
            </ul>
        </div>
    </li>

    <li>
            <label for="hidden"> Hide this category:</label>
            <input type="checkbox" id="hidden" name="hidden">
    </li>
    </ul>
    

  <button name="create_category">Submit</button>
</form>

</main>

<?php
include $root.'/components/footer.php';