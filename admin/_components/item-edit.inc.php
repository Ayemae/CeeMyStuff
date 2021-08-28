
<h1>Edit Item</h1>

<form method="post" enctype="multipart/form-data">
<input type="hidden" name="n_item_id" value="<?show($item['ID'])?>">
<input type="hidden" name="n_cat_id" value="<?show($item['Cat_ID'])?>">
<ul class="form-list">
    <li>
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" max-length="255" value="<?show($item['Title'])?>" required>
    </li>

    <li>
        <label for="img_upload">Image:</label>
        <input type="file" id="img_upload" name="img_upload">
        <input type="hidden" id="img_stored" name="img_stored" value="<?show($item['Img_Path'])?>">
    </li>

    <li>
        <label for="caption">Caption:</label>
        <textarea id="caption" name="b_caption"><?show($item['Caption'])?></textarea>
    </li>


    <li>
        <label for="publish-datetime">Publish Date:</label>
            <input type="datetime-local" id="publish-datetime" name="publish_datetime" value="<?show($item['Publish_Timestamp'])?>">
</li>

    <li>
    <input type="hidden" id="img_stored" name="thumb_stored" value="<?show($item['Img_Thumb_Path'])?>">
    <?php if (!$catInfo['Auto_Thumbs']) :?>
        <label for="create-thumbnail"> Create Thumbnail:</label>
        <input type="hidden" name="create_thumbnail" value="">
        <input type="checkbox" id="create-thumbnail" name="create_thumbnail" value="checked">
        <ul class="form-list">
            <li>
                <label for="thumb_size">Thumbnail Size:</label>
                <input type="number" id="thumb_size" name="n_thumb_size" value="<?show($catInfo['Thumb_Size'])?>">
            </li>
            <li>
                <label for="thumb_size_axis">Axis of Thumbnail Size:</label>
                <select id="thumb_size_axis" name="n_thumb_size_axis">
                    <option value="0" <?($catInfo['Thumb_Size_Axis'] == 0 ? 'selected' : null)?>>Width</option>
                    <option value="1" <?($catInfo['Thumb_Size_Axis'] == 1 ? 'selected' : null)?>>Height</option>
                </select>
            </li>
        </ul>
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
        <input type="checkbox" id="hidden" name="n_hidden" value="1" <?show((!$item['Hidden'] ? null : 'checked'))?>>
    </li>

    <input type="hidden" name="n_format_id" value="0">
  <button name="edit_item">Submit</button>
</form>
