<h1>Edit Item</h1>

<form method="post" enctype="multipart/form-data">
<input type="hidden" name="n_item_id" value="<?show($item['ID'])?>">
<input type="hidden" name="n_cat_id" value="<?show($item['Cat_ID'])?>">
<input type="hidden" name="type" value="<?show($item['Type'])?>">
<ul class="form-list">
    <li>
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" max-length="140" value="<?show($item['Title'])?>" required>
    </li>


    <?php if ($item['Type']==="Image") :
        if ($item['Img_Path']): ?>
    <li>
        <label>Image:</label><br/>
        <img src="<?show($set['dir'].$item['Img_Path'])?>" alt="<?show($item['Title'])?> Image">
        <input type="hidden" id="img_stored" name="img_stored" value="<?show($item['Img_Path'])?>">
    </li>
    <?php endif;?>
    <li>
        <label for="img_upload"><?=($item['Img_Path'] ? 'Re-upload' : 'Upload')?> Image:</label>
        <input type="file" id="img_upload" name="img_upload">
    </li>

    <?php elseif ($item['Type']==="Embed") :?>
    <li>
        <label for="embed">Embed HTML/Script:</label><br/>
        <textarea id="embed" name="b_embed"><?show($item['Embed_HTML'])?></textarea>
    </li>
    <?php endif;?>

    <li>
        <label for="text">Text:</label><br/>
        <textarea id="text" name="b_text"><?show($item['Text'])?></textarea>
    </li>


    <li>
        <label for="publish-datetime">Publish Date:</label>
            <input type="datetime-local" id="publish-datetime" name="publish_datetime" value="<?show($item['Publish_Timestamp'])?>">
</li>

    <?php if ($item['Img_Thumb_Path']) :?>
        <li>
            <label>Thumbnail:</label>
            <img src="<?show($set['dir'])?><?show($item['Img_Thumb_Path'])?>" alt="<?show($item['Title'])?> Thumbnail">
            <input type="hidden" id="thumb_stored" name="thumb_stored" value="<?show($item['Img_Thumb_Path'])?>">
        </li>
    <?php endif;?>

    <li>
    <?php if ($item['Type'] === 'Image' && $catInfo['Auto_Thumbs']) :?>
        <i>A thumbnail image with a <?echo(!$catInfo['Thumb_Size_Axis'] ? 'width' : 'height');?> of <?show($catInfo['Thumb_Size'])?>px will be created for this item.<br/>
        <a href="">Click here to change this setting.</a></i>
        <input type="hidden" name="create_thumbnail" value="checked">
        <input type="hidden" name="n_thumb_size" value="<?show($catInfo['Thumb_Size'])?>">
        <input type="hidden" name="n_thumb_size_axis" value="<?show($catInfo['Thumb_Size_Axis'])?>">
    <?php else : ?>
        <label for="thumb_upload"><?=($item['Img_Thumb_Path'] ? 'Re-upload' : 'Upload')?> Thumbnail:</label>
        <input type="file" id="thumb_upload" name="thumb_upload">
        <?php if ($item['Type'] === 'Image') :?>
        <p> - OR - </p>
        <label for="create-thumbnail"> Auto-Create Thumbnail:</label>
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
        <?php endif; ?>
    <?php endif; ?>
    </li>

    <?php if ($formatList) :?>
    <li>
        <label for="format">Display Format:</label>
        <select name="format" id="format">
            <?php foreach ($formatList AS $format) :?>
            <option value="<?show($format)?>" <?show($item['Format']===$format ? 'selected' : null)?>>
                <?show($format)?>
            </option>
            <?php endforeach;?>
        </select>
    </li>
    <?php endif;?>
    
    <li>
        <label for="hidden">Hide this item:</hidden>
        <input type="hidden" name="n_hidden" value="0">
        <input type="checkbox" id="hidden" name="n_hidden" value="1" <?show((!$item['Hidden'] ? null : 'checked'))?>>
    </li>

  <button name="edit_item">Submit</button>
</form>
