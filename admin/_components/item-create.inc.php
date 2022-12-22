<h1>Create New Item</h1>

<form method="post" enctype="multipart/form-data">
<!-- <input type="hidden" name="type" value="<?show($type)?>"> -->
<ul class="form-list">
    <li>
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" max-length="255" required>
    </li>

    <li>
        <label for="sect-id">In Section:</label>
        <select name="n_sect_id" id="sect-id">
            <option value="">None</option>
            <? foreach ($sectList AS $section) :?>
                <option value="<?show($section['ID'])?>"><?show($section['Page_Name'])?> > <?show($section['Name'])?></option>
            <? endforeach; unset($section);?>
        </select>
    </li>

    <li>
        <input type="checkbox" class="chktoggle invis" id="add-text">
        <label for="add-text" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add Text</label>
        <div class="chktoggle-show" style="flex-direction:column">
            <label for="text">Text:</label>
            <textarea id="text" name="b_text"></textarea>
        </div>
    </li>

    <li>
        <input type="checkbox" class="chktoggle invis" id="add-image">
        <label for="add-image" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add Image</label>
        <div class="chktoggle-show">
            <label for="img_upload">Upload (jpg, png, gif, or webp):</label>
            <input type="file" id="img_upload" name="img_upload">
        </div>
    </li>

    <li>
        <input type="checkbox" class="chktoggle invis" id="add-embed">
        <label for="add-embed" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add Embed</label>
        <div class="chktoggle-show" style="flex-direction:column">
            <label for="embed">Embed HTML/Script:</label>
            <textarea id="embed" name="b_embed"></textarea>
        </div>
    </li>


    <li>
        <label for="pub-date-now">Publish Now:</label>
                <input type="checkbox" class="chktoggle" id="pub-date-now" checked>
            <div class="chktoggle-hide">
                <label for="publish-date">Publish date/time:</label>
                <input type="datetime-local" id="publish-datetime" name="publish_datetime">
            </div>
            <p>Note: If you want to schedule your items to show up later, you can set the publish date/time to the future.</p>
    </li>

    <li>
    <?php if (isset($sectInfo) && $sectInfo['Auto_Thumbs'] > 0) : ?>
        <i>If an image was added, a thumbnail image with a <?echo(!$sectInfo['Thumb_Size_Axis'] ? 'width' : 'height');?> of <?show($sectInfo['Thumb_Size'])?>px will be created for this item.<br/>
        <a href="<?show($route)?>/sections.php?task=edit&sectid=<?show($sectInfo['ID'])?>">Click here to change this setting.</a></i>
        <input type="hidden" name="create_thumbnail" value="checked">
        <input type="hidden" name="n_thumb_size" value="<?show($sectInfo['Thumb_Size'])?>">
        <input type="hidden" name="n_thumb_size_axis" value="<?show($sectInfo['Thumb_Size_Axis'])?>">
    <?php else : ?>
        <label for="thumb_upload">Upload Thumbnail Image (Optional):</label>
        <input type="file" id="thumb_upload" name="thumb_upload">
        <p> - OR - </p>
        <label for="create-thumbnail"> Auto-Create Thumbnail (Optional):</label>
        <input type="hidden" name="create_thumbnail" value="">
        <input type="checkbox" id="create-thumbnail" class="chktoggle" name="create_thumbnail" value="checked">
        <div class="chktoggle-show">
            <label for="thumb_size">Thumbnail Size:</label>
            <input type="number" id="thumb_size" name="n_thumb_size" value="<?show($sectInfo['Thumb_Size'])?>">
            <label for="thumb_size_axis">Axis of Thumbnail Size:</label>
            <select id="thumb_size_axis" name="n_thumb_size_axis">
                <option value="0" <?($sectInfo['Thumb_Size_Axis'] == 0 ? 'selected' : null)?>>Width</option>
                <option value="1" <?($sectInfo['Thumb_Size_Axis'] == 1 ? 'selected' : null)?>>Height</option>
            </select>
        </div>
    <?php endif; ?>
    </li>

    <?php if ($formatList) :?>
    <li>
        <label for="format">Display Format:</label>
        <select name="format" id="format">
                <option value="" selected>Inherit from Section Default</option>
            <?php foreach ($formatList AS $format) :?>
                <option value="<?show($format['Path'])?>" <?=(isset($sectInfo) && $sectInfo['Default_Item_Format']===$format['Path'] ? 'selected' : null)?>>
                <?show($format['Name'])?>
            </option>
            <?php endforeach;?>
        </select>
        <?php if (isset($sectInfo) && $sectInfo['ID']>0) :?>
            <p><i>Note: The section default item format is set to '<?show(basename($sectInfo['Default_Item_Format']), '.php')?>'.</i></p>
        <?php else :?>
            <p><i>Note: This item is not assigned to a section, or its assigned section has an invalid default item format.</i></p>
        <?php endif;?>
    </li>
    <?php endif;?>
    
    <li>
        <label for="hidden">Hide this item:</label>
        <input type="hidden" name="n_hidden" value="0">
        <input type="checkbox" id="hidden" name="n_hidden" value="1">
    </li>

  <button name="create_item">Submit</button>
</form>