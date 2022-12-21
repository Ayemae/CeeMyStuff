<h1>Create New <?show($type)?> Item</h1>

<form method="post" enctype="multipart/form-data">
<input type="hidden" name="n_cat_id" value="<?show($catID)?>">
<input type="hidden" name="type" value="<?show($type)?>">
<ul class="form-list">
    <li>
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" max-length="255" required>
    </li>

    <?php if ($type==="Image") :?>
    <li>
        <label for="img_upload">Image:</label>
        <input type="file" id="img_upload" name="img_upload">
    </li>

    <?php elseif ($type==="Embed") :?>
    <li>
        <label for="embed">Embed HTML/Script:</label><br/>
        <textarea id="embed" name="b_embed"></textarea>
    </li>
    <?php endif;?>

    <li>
        <label for="text">Text:</label><br/>
        <textarea id="text" name="b_text"></textarea>
    </li>


    <li>
        <label >Publish Date:</label>
                <input type="checkbox" class="chktoggle" id="pub-date-now" checked>
                <label for="pub-date-now"> Now</label>
            <div class="chktoggle-hide">
                <label for="publish-date">Choose a date and time:</label>
                <input type="datetime-local" id="publish-datetime" name="publish_datetime">
            </div>
</li>

    <li>
    <?php if ($type === 'Image' && $catInfo['Auto_Thumbs']) :?>
        <i>A thumbnail image with a <?echo(!$catInfo['Thumb_Size_Axis'] ? 'width' : 'height');?> of <?show($catInfo['Thumb_Size'])?>px will be created for this item.<br/>
        <a href="<?show($route)?>/categories.php?task=edit&catid=<?show($catInfo['ID'])?>">Click here to change this setting.</a></i>
        <input type="hidden" name="create_thumbnail" value="checked">
        <input type="hidden" name="n_thumb_size" value="<?show($catInfo['Thumb_Size'])?>">
        <input type="hidden" name="n_thumb_size_axis" value="<?show($catInfo['Thumb_Size_Axis'])?>">
    <?php else : ?>
        <label for="thumb_upload">Upload Thumbnail Image:</label>
        <input type="file" id="thumb_upload" name="thumb_upload">
        <?php if ($type === 'Image') :?>
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
        <?php endif;?>
    <?php endif; ?>
    </li>

    <?php if ($formatList) :?>
    <li>
        <label for="format">Display Format:</label>
        <select name="format" id="format">
            <?php foreach ($formatList AS $format) :?>
                <option value="<?show($format['Path'])?>" <?=($catInfo['Default_Item_Format']===$format['Path'] ? 'selected' : null)?>>
                <?show($format['Name'])?>
            </option>
            <?php endforeach;?>
        </select>
    </li>
    <?php endif;?>
    
    <li>
        <label for="hidden">Hide this item:</label>
        <input type="hidden" name="n_hidden" value="0">
        <input type="checkbox" id="hidden" name="n_hidden" value="1">
    </li>

  <button name="create_item">Submit</button>
</form>