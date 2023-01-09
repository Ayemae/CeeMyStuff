<h1>Edit Item</h1>

<form method="post" enctype="multipart/form-data">
<input type="hidden" name="n_item_id" value="<?show($item['ID'])?>">
<ul class="form-list">
    <li>
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" max-length="140" value="<?show($item['Title'])?>" required>
    </li>

    <li>
        <label for="sect-id">In Section:</label>
        <select name="n_sect_id" id="sect-id">
            <option value="">None</option>
            <? foreach ($sectList AS $section) :?>
                <option value="<?show($section['ID'])?>" <?=($item['Sect_ID']===$section['ID'] ? 'selected' : null )?>>
                    <?show($section['Page_Name'])?> > <?show($section['Name'])?>
                </option>
            <? endforeach; unset($section);?>
        </select>
    </li>

    <li>
        <label for="publish-datetime">Publish Date:</label>
            <input type="datetime-local" id="publish-datetime" name="publish_datetime" value="<?show($item['Publish_Timestamp'])?>">
    </li>

    <?php if ($formatList) :?>
    <li>
        <label for="format">Display Format:</label>
        <select name="format" id="format">
            <option value="">Inherit from Section Default</option>
            <?php foreach ($formatList AS $format) :?>
            <option value="<?show($format['Path'])?>" <?show($item['Format']===$format['Path'] ? 'selected' : null)?>>
                <?show($format['Name'])?>
            </option>
            <?php endforeach;?>
        </select>
        <?php if ($sectInfo['ID']>0 && $sectInfo['Default_Item_Format']>'') :?>
            <p><i>Note: The section default item format is set to '<?show(basename($sectInfo['Default_Item_Format']), '.php')?>'.</i></p>
        <?php else :?>
            <p><i>Note: This item is not assigned to a section, or its assigned section has an invalid default item format.</i></p>
        <?php endif;?>
    </li>
    <?php endif;?>

    <li>
        <? if ($item['Text']<='') :?>
            <input type="checkbox" class="chktoggle invis" id="add-text">
            <label for="add-text" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add Text</label>
            <div class="chktoggle-show" style="flex-direction:column">
        <? endif;?>
            <label for="text" style="display:block">Text:</label>
            <textarea id="text" name="b_text"><?show($item['Text'])?></textarea>
        <? if ($item['Text']<='') :?>
            </div>
        <? endif;?>
    </li>


    <li>
        <? if ($item['Img_Path']<='') :?>
            <input type="checkbox" class="chktoggle invis" id="add-image">
            <label for="add-image" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add Image</label>
            <div class="chktoggle-show" style="flex-direction:column">
                <label for="img_upload">Upload (jpg, png, gif, or webp):</label>
                <input type="file" id="img_upload" name="img_upload">
            </div>
        <? else : ?>
            <label style="display:block">Image:</label>
            <img src="<?show($set['dir'].$item['Img_Path'])?>" alt="<?show($item['Title'])?> Image">
            <input type="hidden" id="img_stored" name="img_stored" value="<?show($item['Img_Path'])?>">
            <p>Image Path: <?show($item['Img_Path'] ? $set['dir'].$item['Img_Path'] : '<i>None</i>')?></p>
            <div>
                <label for="img_upload">Reupload (jpg, png, gif, or webp):</label>
                <input type="file" id="img_upload" name="img_upload">
            </div>
        <? endif;?>
    </li>

    <?php if ($item['Img_Thumb_Path']) :?>
        <li>
            <label>Thumbnail:</label>
            <img src="<?show($set['dir'])?><?show($item['Img_Thumb_Path'])?>" alt="<?show($item['Title'])?> Thumbnail">
            <input type="hidden" id="thumb_stored" name="thumb_stored" value="<?show($item['Img_Thumb_Path'])?>">
        </li>
    <?php endif;?>

    <li>
    <?php if ($sectInfo['Auto_Thumbs']) :?>
        <i>If an image was added, a thumbnail image with a <?echo(!$sectInfo['Thumb_Size_Axis'] ? 'width' : 'height');?> of <?show($sectInfo['Thumb_Size'])?>px will be created for this item.<br/>
        <a href="<?show($route)?>/sections.php?task=edit&sectid=<?show($sectInfo['ID'])?>">Click here to change this setting.</a></i>
        <input type="hidden" name="create_thumbnail" value="checked">
        <input type="hidden" name="n_thumb_size" value="<?show($sectInfo['Thumb_Size'])?>">
        <input type="hidden" name="n_thumb_size_axis" value="<?show($sectInfo['Thumb_Size_Axis'])?>">
    <?php else : ?>
        <label for="thumb_upload"><?=($item['Img_Thumb_Path'] ? 'Re-upload' : 'Upload')?> Thumbnail:</label>
        <input type="file" id="thumb_upload" name="thumb_upload">
        <p> - OR - </p>
        <label for="create-thumbnail"> Auto-Create Thumbnail From Item's Image:</label>
        <input type="hidden" name="create_thumbnail" value="">
        <input type="checkbox" id="create-thumbnail" name="create_thumbnail" value="checked">
        <ul class="form-list">
            <li>
                <label for="thumb_size">Thumbnail Size:</label>
                <input type="number" id="thumb_size" name="n_thumb_size" value="<?show($sectInfo['Thumb_Size'])?>">
            </li>
            <li>
                <label for="thumb_size_axis">Axis of Thumbnail Size:</label>
                <select id="thumb_size_axis" name="n_thumb_size_axis">
                    <option value="0" <?($sectInfo['Thumb_Size_Axis'] == 0 ? 'selected' : null)?>>Width</option>
                    <option value="1" <?($sectInfo['Thumb_Size_Axis'] == 1 ? 'selected' : null)?>>Height</option>
                </select>
            </li>
        </ul>
    <?php endif; ?>
    </li>

    <li>
        <? if ($item['Embed_HTML']<='') :?>
            <input type="checkbox" class="chktoggle invis" id="add-embed">
            <label for="add-embed" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add Embed</label>
            <div class="chktoggle-show" style="flex-direction:column">
        <? endif;?>
            <label for="embed" style="display:block">Embed HTML/Script:</label>
            <textarea id="embed" name="b_embed"><?show($item['Embed_HTML'])?></textarea>
        <? if ($item['Embed_HTML']>'') :?>
            </div>
        <? endif;?>
    </li>

    <li>
    <? if ($item['File_Path']<='') :?>
            <input type="checkbox" class="chktoggle invis" id="add-file">
            <label for="add-file" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add File Download</label>
            <div class="chktoggle-show" style="flex-direction:column">
        <? endif;?>
            <input type="hidden" id="file-stored" name="file_stored" value="<?show($item['File_Path'])?>">
            <div>Uploaded File: <a href="<?show($set['dir'].$item['File_Path'])?>" target="_blank">[File link]</a></div>
            <p>File Path: <?show($item['File_Path'] ? $set['dir'].$item['File_Path'] : '<i>None</i>')?></p>
            <div>
                <label for="file-pres">File Presentation:</label>
                <select id="file-pres" name="file_pres">
                    <option value="lnk" <?show($item['File_Pres'] === 'lnk' ? 'selected' : null)?>>Link</option>
                    <option value="dld" <?show($item['File_Pres'] === 'dld' ? 'selected' : null)?>>Download</option>
                    <option value="txt" <?show($item['File_Pres'] === 'txt' ? 'selected' : null)?>>Text</option>
                </select>
            </div>
            <label for="file_upload">Reupload File:</label>
            <input type="file" id="file_upload" name="file_upload">
        <? if ($item['File_Path']>'') :?>
            </div>
        <? endif;?>
    </li>
    
    <li>
        <label for="hidden">Hide this item:</hidden>
        <input type="hidden" name="n_hidden" value="0">
        <input type="checkbox" id="hidden" name="n_hidden" value="1" <?show((!$item['Hidden'] ? null : 'checked'))?>>
    </li>

  <button name="edit_item">Submit</button>
</form>
