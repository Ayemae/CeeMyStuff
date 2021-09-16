<a href="?task=list"><i class="fi fi-rs-angle-double-small-left"></i> back to Category List</a>
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
    <?php if (!$catInfo['Auto_Thumbs']) :?>
        <label for="thumb_upload">Upload Thumbnail:</label>
        <input type="file" id="thumb_upload" name="thumb_upload">
        <p> - OR - </p>
        <label for="create-thumbnail"> Auto-Create Thumbnail:</label>
        <input type="hidden" name="create_thumbnail" value="">
        <input type="checkbox" id="create-thumbnail" class="chktoggle" name="create_thumbnail" value="checked">
        <div class="chktoggle-show">
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
    
    <li>
        <label for="hidden">Hide this item:</hidden>
        <input type="hidden" name="n_hidden" value="0">
        <input type="checkbox" id="hidden" name="n_hidden" value="1">
    </li>

    <input type="hidden" name="n_format_id" value="0">
  <button name="create_item">Submit</button>
</form>