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
        <label for="show_images">Show Item Images:</label>
        <select id="show_images" name="n_show_images">
            <option value="0">No</option>
            <option value="1" selected>Show Thumbnails</option>
            <option value="2">Show Full-Sized Images</option>
        </select>
    </li>

    <li>
        <label for="show_titles">Show Item Titles:</label>
        <select id="show_titles" name="n_show_titles">
            <option value="0">No</option>
            <option value="2" selected>Yes</option>
        </select>
    </li>

    <li>
        <label for="show_captions">Show Item Captions:</label>
        <select id="show_captions" name="n_show_captions">
            <option value="0">No</option>
            <option value="1" selected>Show Truncated Captions</option>
            <option value="2">Show Full Captions</option>
        </select>
    </li>

    <li>
        <label for="create-thumbs">Auto-Create Thumbnails for this Category:</label>
        <input type="hidden" name="n_create_thumbs" value="0">
        <input type="checkbox" id="create-thumbs" name="n_create_thumbs" class="show-sib-chktoggle" value="1" <?php echo ($set['auto_thumbs'] ? "checked=checked" : null );?>>
        <div class="show-on-chk">
            <ul class="form-list">
                <li>
                    <label for="thumb_size">Choose default thumbnail size:</label>
                    <input type="number" id="thumb_size" name="n_thumb_size" value="<?php echo ($set['thumb_size'] ? $set['thumb_size'] : 125 );?>">
                </li>
                <li>
                    <label for="thumb_axis">Axis of thumbnail size:</label>
                    <select id="thumb_axis" name="n_thumb_axis">
                        <option value="0" <?echo ($set["thumb_size_axis"] == 'width' ? 'selected' : null);?>>Width</option>
                        <option value="1" <?echo ($set["thumb_size_axis"] == 'height' ? 'selected' : null);?>>Height</option>
                    </select>
                </li>
            </ul>
        </div>
    </li>

    <li>
            <label for="hidden"> Hide this category:</label>
            <input type="hidden" id="hidden" name="n_hidden" value="0">
            <input type="checkbox" id="hidden" name="n_hidden" value="1">
    </li>
    </ul>
    <input type="hidden" id="format_id" name="n_format_id" value='0'>

  <button name="create_category">Submit</button>
</form>