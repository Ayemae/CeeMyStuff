<?php 
$root = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'], 2);
include_once $root.'/components/info-head.php';
$admin_panel = true;
$page_title = 'Admin Panel';
include $root.'/components/header.php';
if (isset($_GET['catid'])) {
    $catID = filter_var($_GET['catid'], FILTER_SANITIZE_NUMBER_INT);
    $catInfo = getCatInfo($catID);
}
if (isset($_GET['task'])) {
    $task = htmlspecialchars($_GET['task']);
} else {
    $task = false;
}
if (isset($_GET['itemid'])) {
    $itemID = filter_var($_GET['itemid'], FILTER_SANITIZE_NUMBER_INT);
} else {
    $itemID = false;
}
// if (!$loggedIn && $admin_panel) {
//     kickOut();
//     exit();
// }
?>

<main>
    <?php if (isset($catID) && $task === 'create') :
    $catList = getCatList(); ?>
<h1>Create New Item</h1>

<form method="post" enctype="multipart/form-data">
<input type="hidden" name="n_cat_id" value="<?show($catID)?>">
<ul class="form-list">
    <li>
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" max-length="255" required>
    </li>

    <li>
        <label for="img_upload">Image:</label>
        <input type="file" id="img_upload" name="img_upload">
        <input type="hidden" id="img_stored" name="img_stored">
    </li>

    <li>
        <label for="caption">Caption:</label>
        <textarea id="caption" name="b_caption"></textarea>
    </li>


    <li>
        <label >Publish Date:</label>
                <input type="checkbox" class="hide-sib-chktoggle" id="pub-date-now" checked>
                <label for="pub-date-now"> Now</label>
            <div class="hide-on-chk">
                <label for="publish-date">Choose a date and time:</label>
                <input type="datetime-local" id="publish-datetime" name="publish_datetime">
            </div>
</li>

    <li>
    <?php if (!$catInfo['Automate_Thumbs']) :?>
        <label for="create-thumbnail"> Create Thumbnail:</label>
        <input type="hidden" name="create_thumbnail" value="">
        <input type="checkbox" id="create-thumbnail" class="show-sib-chktoggle" name="create_thumbnail" value="checked">
        <div class="show-on-chk">
            <label for="thumb_size">Thumbnail Size:</label>
            <input type="number" id="thumb_size" name="n_thumb_size" value="<?show($catInfo['Thumb_Size'])?>">
            <label for="thumb_size_axis">Axis of Thumbnail Size:</label>
            <select id="thumb_size_axis" name="n_thumb_size_axis">
                <option value="0" <?($catInfo['Thumb_Size_Axis'] == 0 ? 'selected' : null)?>>Width</option>
                <option value="1" <?($catInfo['Thumb_Size_Axis'] == 1 ? 'selected' : null)?>>Height</option>
            </select>
        </div>
    <?php else : ?>
        <i>A thumbnail image with a <?echo(!$catInfo['Thumb_Size_Axis'] ? 'width' : 'height');?> of <?show($catInfo['Thumb_Size'])?>px will be created for this item.<br/>
        <a href="">Click here to change this setting.</a></i>
        <input type="hidden" name="create_thumbnail" value="checked">
        <input type="hidden" name="n_thumb_size" value="<?show($catInfo['Thumb_Size'])?>">
        <input type="hidden" name="n_thumb_size_axis" value="<?show($catInfo['Thumb_Size_Axis'])?>">
    <?php endif; ?>
    </li>
    <input type="hidden" name="n_format_id" value="0">
  <button name="create_item">Submit</button>
</form>

<?php endif; ?>
</main>

<?php
include $root.'/components/footer.php';