<form method="post" enctype="multipart/form-data" action="<?=$set['dir']?>/admin/sections.php?task=<?=($edit ? 'view&sectid='.$item['Sect_ID'] : 'list')?>">
    <div class="space-btwn">
        <h1><?=($create ? "Create New Item" : "Edit Item : ".$item['Title'])?></h1>
        <? if ($edit) :?>
        <input type="hidden" name="n_item_id" value="<?show($item['ID'])?>">
        <button name="delete_item" id="delete-item" class="small red"><i class="fi fi-rs-trash"></i> Delete Item</button>
        <? endif;?>
    </div>

<ul class="form-list">
    <li>
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" max-length="140" value="<?show($edit && $item['Title']>'' ? $item['Title'] : null )?>" required>
    </li>

    <li>
        <label for="sect-id">In Section:</label>
        <? if ($create) {$sectCmp = $sectID;} else {$sectCmp = $item['Sect_ID'];}?>
        <select name="n_sect_id" id="sect-id">
            <option value="">None (create orphaned item)</option>
            <? foreach ($sectList AS $section) :?>
                <option value="<?show($section['ID'])?>" <?=formCmp($sectCmp,$section['ID'],'s')?>>
                    <?show($section['Page_Name'])?> > <?show($section['Name'])?>
                </option>
            <? endforeach; unset($section);?>
        </select>
    </li>


    <li>
        <? if ($create) : ?>
        <label for="pub-date-now">Publish Now:</label>
                <input type="checkbox" class="chktoggle" id="pub-date-now" checked>
            <div class="chktoggle-hide">
        <?endif;?>
                <label for="publish-date">Publish date/time:</label>
                <input type="datetime-local" id="publish-datetime" name="publish_datetime" value="<?show($edit ? $item['Publish_Timestamp'] : null )?>">
        <? if ($create) : ?>
            </div>
        <?endif;?>
            <p>Note: If you want to schedule your items to show up later, you can set the publish date/time to the future.</p>
    </li>

    <?php if ($formatList) :?>
    <li>
        <label for="format">Display Format:</label>
        <select name="format" id="format">
            <option value="">Inherit from Section Default</option>
            <?php foreach ($formatList AS $format) :?>
            <option value="<?show($format['Path'])?>" <?($edit ? formCmp($item['Format'],$format['Path'],'s') : null)?>>
                <?show($format['Name'])?>
            </option>
            <?php endforeach;?>
        </select>
        <?php if ($sectInfo['ID']>0 && $sectInfo['Default_Item_Format']>'') :?>
            <p><i>Note: The section default item format is set to '<?show(basename($sectInfo['Default_Item_Format']), '.php')?>'.</i></p>
        <?php else :?>
            <p><i class="red">Note: This item is not assigned to a section, or its assigned section has an invalid default item format.</i></p>
        <?php endif;?>
    </li>
    <?php endif;?>

    <li>
        <? if ($create || $item['Text']<='') :?>
            <input type="checkbox" class="chktoggle invis" id="add-text">
            <label for="add-text" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add Text</label>
            <div class="chktoggle-show" style="flex-direction:column">
        <? endif;?>
            <label for="text" style="display:block">Text:</label>
            <textarea id="text" name="b_text"><?show($edit ? $item['Text'] : null)?></textarea>
        <? if ($edit ? $item['Text']<='' : null) :?>
            </div>
        <? endif;?>
    </li>


    <li>
        <? if ($create || $item['Img_Path']<='') :?>
            <input type="checkbox" class="chktoggle invis" id="add-image">
            <label for="add-image" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add Image</label>
            <div class="chktoggle-show" style="flex-direction:column">
        <? endif;?>
        <label for="img_upload"><?=($create || $item['Img_Path']<='' ? 'U' : 'Reu')?>pload  Image File (jpg, png, gif, or webp):</label>
                <input type="file" id="img_upload" name="img_upload">
        <? if ($edit && $item['Img_Path']>'') :?>
            <label><br/>Current:<br/></label>
            <div id="img_current" class="item-current-image-wrapper">
                <img src="<?show($set['dir'].$item['Img_Path'])?>" alt="<?show($item['Title'])?> Image">
            </div>
            <input type="hidden" id="img_stored" name="img_stored" value="<?show($item['Img_Path'])?>">
            <p>Image Path: <?show($item['Img_Path'] ? $set['dir'].$item['Img_Path'] : '<i>None</i>')?></p>
            <button type="button" class="small red" onclick="rmvFilePath('img_stored', 'img_current')">Remove Item Image</button>
        <? endif;?>
                

                <ul id="add-thumbnail">
                <?php if ($edit && $item['Img_Thumb_Path']) :?>
                <li>
                    <label class="">Thumbnail:<br/></label>
                    <div id="thumb_current" class="item-current-thumbnail-wrapper">
                        <img src="<?show($set['dir'])?><?show($item['Img_Thumb_Path'])?>" alt="<?show($item['Title'])?> Thumbnail">
                    </div>
                    <input type="hidden" id="rmv_thumb_img" name="n_rmv_thumb_img" value="0">
                    <button type="button" class="small red" onclick="rmvFilePath('rmv_thumb_img', 'thumb_current', 1)">Remove Thumbnail Image</button>
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
                <label for="thumb_upload"><?=(isset($item) && $item['Img_Thumb_Path'] ? 'Re-upload' : 'Upload')?> Thumbnail:</label>
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
                            <option value="0" <?=formCmp($sectInfo['Thumb_Size_Axis'],0,'s')?>>Width</option>
                            <option value="1" <?=formCmp($sectInfo['Thumb_Size_Axis'],1,'s')?>>Height</option>
                        </select>
                    </li>
                </ul>
            <?php endif; ?>
            </li>
            </ul><!-- end 'add-thumbnail' -->
        </div>
    </li>


    <li>
    <? if ($create || $item['File_Path']<='') :?>
            <input type="checkbox" class="chktoggle invis" id="add-file">
            <label for="add-file" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add File Download</label>
            <div class="chktoggle-show" style="flex-direction:column">
        <? endif;?>
             <label for="file_upload"><?=($create || $item['File_Path']<='' ? 'U' : 'Reu')?>pload File:</label>
                <input type="file" id="file_upload" name="file_upload">
            <div>
                <label for="file-pres">File Presentation:</label>
                <select id="file-pres" name="file_pres">
                    <option value="lnk" <?($edit ? formCmp($item['File_Pres'],'lnk','s') : null)?>>Link</option>
                    <option value="dld" <?($edit ? formCmp($item['File_Pres'],'dld','s') : null)?>>Download</option>
                    <option value="txt" <?($edit ? formCmp($item['File_Pres'],'txt','s') : null)?>>Text</option>
                </select>
            </div>
            
                <?if ($edit && $item['File_Path']>'') :?>
            <input type="hidden" id="file-stored" name="file_stored" value="<?show($item['File_Path'])?>">
                <div class="item-file-wrapper">
                    Current Uploaded File: <span id="file_current"><a href="<?show($set['dir'].$item['File_Path'])?>" target="_blank">[File link]</a></span>
                </div>
                <p>File Path: <?show($item['File_Path'] ? $set['dir'].$item['File_Path'] : '<i>None</i>')?></p>
                <button type="button" class="small red" onclick="rmvFile('file-stored', 'file_current')">Remove File</button>
            <?endif;?>
        <? if ($create || $item['File_Path']>'') :?>
            </div>
        <? endif;?>
    </li>


    <li>
        <? if ($create || $item['Embed_HTML']<='') :?>
            <input type="checkbox" class="chktoggle invis" id="add-embed">
            <label for="add-embed" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add Embed</label>
            <div class="chktoggle-show" style="flex-direction:column">
        <? endif;?>
            <label for="embed" style="display:block">Embed HTML/Script:</label>
            <textarea id="embed" name="b_embed"><?show(isset($item) && $item['Embed_HTML'] ? $item['Embed_HTML'] : null)?></textarea>
        <? if ($create || $item['Embed_HTML']>'') :?>
            </div>
        <? endif;?>
    </li>
    
    <li>
        <label for="hidden">Hide this item:</hidden>
        <input type="hidden" name="n_hidden" value="0">
        <input type="checkbox" id="hidden" name="n_hidden" value="1" <?show($edit && $item['Hidden'] ? 'checked' : null)?>>
    </li>

  <button name="<?=($create ? "create_item" : "edit_item")?>">Submit</button>
  <div id="modal-home"></div>
</form>

<? if ($edit) :?>
<script src="_js/modal.js"></script>
<script src="_js/rmvFilePaths.js"></script>
<script>
let modalHTML = `<h2>Are you sure you want to delete '<?=($item['Title'])?>'?</h2>
                <p>This cannot be undone.</p>
                <div class="flex">
                <button type="submit" class="button red" name="delete_item"/>Yes, delete this item</button>
                <button class="button modal-close" onclick="event.preventDefault()"/>Never mind</button>
                </div>`;
const modalItemDelete = new Modal('modal-item-delete', modalHTML, false, false);
modalItemDelete.appendToForm('modal-home');

document.getElementById('delete-item').addEventListener('click', function(e) {
    e.preventDefault();
    modalItemDelete.trigger();
}, false);
</script>
<?endif;?>