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
<h1>Create New Item</h1>

<form method="post" enctype="multipart/form-data">
<ul class="form-list">
    <li>
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" max-length="255">
    </li>

    <li>
        <input type="file" id="img_upload" name="img_upload">
    </li>

    <li>
        <label for="caption">Caption:</label>
        <textarea name="caption"></textarea>
    </li>

    <li>
        <label for="category">Category:</label>
        <select id="category" name="category">
            <option>None</option>
        </select>
    </li>

    <li>
        <label >Publish Date:</label>
                <input type="checkbox" class="show-sib-chktoggle" id="pub-date-now" checked>
                <label for="pub-date-now"> Now</label>
            <div class="show-on-chk">
                <label for="publish-date">Choose a date/time:</label>
                <input type="datetime-local" id="publish-date" name="publish_date">
            </div>
        <select id="category" name="category">
            <option value="0">None</option>
        </select>
    </li>

    <?php if (!$set['auto_thumbs']) :?>
    <li>
        <input type="checkbox" id="create-thumbnail" name="create_thumbnail" value="checked">
        <label for="create-thumbnail"> Create Thumbnail</label>
    </li>
    <?php endif; ?>

  <button name="create_item">Submit</button>
</form>

</main>

<?php
include $root.'/components/footer.php';