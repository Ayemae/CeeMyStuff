<form method="post" enctype="multipart/form-data" action="<?=$set['dir']?>/admin/sections.php?task=<?=($edit ? 'view&sectid='.$item['Sect_ID'] : 'list')?>">
    <div class="space-btwn">
        <h1><?=($create ? "Create New Item" : "Edit Item : ".$item['Title'])?></h1>
        <? if ($edit) :?>
        <input type="hidden" name="n_item_id" value="<?show($item['ID'])?>">
        <button name="delete_item" id="delete-item" class="small red"><i class="fi fi-rs-trash"></i> Delete Item</button>
        <? endif;?>
    </div>

<noscript>Enable Javascript for more dynamic features.</noscript>

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
        <i class="help icon"><i class="fi fi-rs-interrogation"></i>
            <article class="help-text">
                Keep in mind that different Sections may have different settings. Review your Item submission again if you change it.
            </article></i>
    </li>


    <li>
        <? if ($create) : ?>
        <label for="pub-date-now">Publish Now:
        </label>
                <input type="checkbox" class="chktoggle" id="pub-date-now" checked>
            <div class="chktoggle-hide">
        <?endif;?>
                <label for="publish-date">Publish date/time:</label>
                <input type="datetime-local" id="publish-datetime" name="publish_datetime" value="<?show($edit ? $item['Publish_Timestamp'] : null )?>">
        <? if ($create) : ?>
            </div>
        <?endif;?>
            <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                <article class="help-text">
                    You can back-date your items, or, if you want to schedule your items to show up later, 
                    you can set the publish date/time to sometime the future.
                </article></i>
    </li>

    <?php if ($formatList) :?>
    <li>
        <label for="format">Display Format:</label>
        <select name="format" id="format">
            <option value="">Inherit from Section Default</option>
            <?php $selectedFormat = '';
            foreach ($formatList AS $format) :
                $fIsSelected = false;
                if (!$item['Format'] && $format['Path']===$sectInfo['Default_Item_Format']) {
                    $selectedFormat = $format['From'].' > '.$format['Name'];
                } else if ($item['Format']>'' && $item['Format']===$format['Path']) {
                    $fIsSelected = true;
                    $selectedFormat = $format['From'].' > '.$format['Name'];
                }?>
            <option value="<?show($format['Path'])?>" <?=($fIsSelected ? 'selected' : null)?>>
                <?show($format['From'])?> > <?show($format['Name'])?>
            </option>
            <?php endforeach;?>
        </select>
        <div id="sect-default-format-info">
            <?php if ($sectInfo['ID']>0 && $sectInfo['Default_Item_Format']>'') :?>
                <p><i>The default item format for this section is set to 
                <strong>'<?show($selectedFormat)?>'</strong>.
                </i></p>
            <?php else :?>
                <p><i class="red">This item is not assigned to a section, or its assigned section has an invalid default item format.</i></p>
            <?php endif;?>
        </div>
    </li>
    <?php endif;?>

    <li>
        <? if ($create || $item['Text']<='') :?>
            <input type="checkbox" class="chktoggle invis" id="add-text">
            <label for="add-text" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add Text</label>
            <div class="chktoggle-show" style="flex-direction:column">
        <? endif;?>
            <label for="text-editor" style="display:block">Text:</label>
            <div class="text-panel">
                <?include('_components/text-edit-panel.inc.php')?>
                <textarea id="text-editor" name="m_text"><?show($edit ? $item['Text'] : null)?></textarea>
            </div>
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
                <img class="visual" src="<?show($set['dir'].$item['Img_Path'])?>" alt="<?show($item['Title'])?> Image">
                <div class="rvm-file-path-info invis">&#10060; Image Removed</div>
            </div>
            <input type="hidden" id="img_stored" name="img_stored" value="<?show($item['Img_Path'])?>">
            <p>Image Path: <?show($item['Img_Path'] ? $set['dir'].$item['Img_Path'] : '<i>None</i>')?></p>
            <button type="button" class="small red" onclick="rmvFilePath(this, 'img_stored', 'img_current')">Remove Item Image</button>
        <? endif;?>
        <div>
            <label for="img-alt-text">Image Description (Alt Text):</label>
            <input type="text" name="img_alt_text" value="<?show($edit ? $item['Img_Alt_Text'] : null)?>">
        </div>
                

                <ul id="add-thumbnail">
                <?php if ($edit && $item['Img_Thumb_Path']) :?>
                <li>
                    <label class="">Thumbnail:<br/></label>
                    <div id="thumb_current" class="item-current-thumbnail-wrapper">
                        <img class="visual" src="<?show($set['dir'])?><?show($item['Img_Thumb_Path'])?>" alt="<?show($item['Title'])?> Thumbnail">
                        <div class="rvm-file-path-info invis">&#10060; Image Removed</div>
                    </div>
                    <input type="hidden" id="stored-thumb-img" name="stored_thumb_img" value="<?show($item['Img_Thumb_Path'])?>">
                    <input type="hidden" id="rmv_thumb_img" name="n_rmv_thumb_img" value="0">
                    <button type="button" class="small red" onclick="rmvFilePath(this, 'rmv_thumb_img', 'thumb_current', 1)">Remove Thumbnail Image</button>
                </li>
            <?php endif;?>


            <li id="thumbnail-area" class="select-cond-container">
                <input type="hidden" id="auto-thumbs" class="select-cond-master" value="<?=$sectInfo['Auto_Thumbs']?>">
                <div class="autothumb-info select-cond" data-sc-conditions="1">
                    <i>
                        If a new image is added, a thumbnail image with a 
                        <span id="sect-thumb-size">
                            <strong><?=(!$sectInfo['Thumb_Size_Axis'] ? 'width' : 'height');?></strong> of <strong><?=($sectInfo['Thumb_Size'])?>px</strong>
                        </span>
                        will be created for this item.<br/>
                        <a class="sect-link" href="<?show($route)?>/sections.php?task=edit&sectid=<?show($sectInfo['ID'])?>">
                            Click here to change this setting.
                        </a>
                    </i>
                    <input type="hidden" name="create_thumbnail" value="checked">
                    <input type="hidden" name="n_thumb_size" value="<?show($sectInfo['Thumb_Size'])?>">
                    <input type="hidden" name="n_thumb_size_axis" value="<?show($sectInfo['Thumb_Size_Axis'])?>">
                </div>
                <div class="thumb-upload select-cond" data-sc-conditions="0">
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
                </div>
            </li>
            </ul><!-- end 'add-thumbnail' -->
        </div>
    </li>


    <li>
    <? if ($create || $item['File_Path']<='') :?>
            <input type="checkbox" class="chktoggle invis" id="add-file">
            <label for="add-file" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add File</label>
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
                <div id="file-current" class="item-file-wrapper">
                    Current Uploaded File: <a class="visual" href="<?show($set['dir'].$item['File_Path'])?>" target="_blank">[File link]</a>
                    <div class="rvm-file-path-info invis">&#10060; File Removed</div>
                </div>
                <p>File Path: <?show($item['File_Path'] ? $set['dir'].$item['File_Path'] : '<i>None</i>')?></p>
                <button type="button" class="small red" onclick="rmvFilePath(this, 'file-stored', 'file-current')">Remove File</button>
            <?endif;?>
        <? if ($create || $item['File_Path']>'') :?>
            </div>
        <? endif;?>
    </li>


    <li>
        <? if ($create || $item['Embed_HTML']<='') :?>
            <input type="checkbox" class="chktoggle invis" id="add-embed">
            <label for="add-embed" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add Embed</label>
            <div class="chktoggle-show flex-col">
        <? endif;?>
            <label for="embed" style="display:block">Embed HTML/Script:</label>
            <p>For embed scripts such as iframes, from sites such as YouTube or Spotify.</p>
            <textarea id="embed" name="b_embed"><?show(isset($item) && $item['Embed_HTML'] ? $item['Embed_HTML'] : null)?></textarea>
        <? if ($create || $item['Embed_HTML']>'') :?>
            </div>
        <? endif;?>
    </li>
    
    <li>
        <label for="hidden">Hide this item:</hidden>
        <input type="hidden" name="n_hidden" value="0">
        <input type="checkbox" id="hidden" name="n_hidden" value="1" <?show($edit && $item['Hidden'] ? 'checked' : null)?>>
        <i class="help icon"><i class="fi fi-rs-interrogation"></i>
            <article class="help-text">
                Hidden Items will not display on the live site.
            </article>
        </i>
    </li>

  <button name="<?=($create ? "create_item" : "edit_item")?>">Submit</button>
  <div id="modal-home"></div>
</form>

<script src="_js/text-editor.js"></script>
<script src="_js/toggle-on-cond.js"></script>
<script src="_js/fetch-sect-info.js"></script>
<script>
    const sectDfltFrmt = document.getElementById("sect-default-format-info");
    const sectThmbSize = document.getElementById("sect-thumb-size");
    const sectLinks = document.querySelectorAll("a.sect-link");
    const autoThumbs = document.getElementById("auto-thumbs");

    document.getElementById("sect-id").addEventListener("change", function(e) {
        let val = e.target.value;
        postData("_js/fetch-sect-info.php", { sect_id : val }).then((sectInfo) => {
            // handle section links
            for (let i=0;i<sectLinks.length;i++) {
                sectLinks[i].href= '<?show($route)?>/sections.php?task=edit&sectid='+sectInfo.ID;
            }
            // handle default item format
            if (!sectInfo.Default_Item_Format) {
                sectDfltFrmt.innerHTML= `<p><i class="red">
                    This item is not assigned to a section, or its assigned section has an invalid default item format.
                    </i></p>`;
            } else {
                const dfltFrmtPath = sectInfo.Default_Item_Format.split('/');
                let dfltFormat = '';
                switch (dfltFrmtPath[1]) {
                    case ('assets') :
                    dfltFormat += 'Universal > ';
                    break;
                    case ('themes') :
                    dfltFormat += 'Theme: '+dfltFrmtPath[2]+' > ';
                    break;
                    default :
                    dfltFormat += dfltFrmtPath[1]+'/'+dfltFrmtPath[2]+' > ';
                    break;
                }
                dfltFormat += dfltFrmtPath[(dfltFrmtPath.length-1)].replace('.php','');
                sectDfltFrmt.innerHTML= `<p><i>
                    The default item format for this section is set to <strong>'${dfltFormat}'</strong>.
                    </i></p>`;
            }
            // handle autothumbs
            autoThumbs.value = sectInfo.Auto_Thumbs;
            let scChildren = document.getElementById('thumbnail-area').getElementsByClassName("select-cond");
            handleSCChildren(scChildren, sectInfo.Auto_Thumbs);
            sectThmbSize.innerHTML= `<strong>${(!sectInfo.Thumb_Size_Axis ? `width` : `height`)}</strong> of <strong>${sectInfo.Thumb_Size}px</strong>`;
        });
    });

</script>
<? if ($edit) :?>
<script src="_js/modal.js"></script>
<script src="_js/rmv-file-paths.js"></script>
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