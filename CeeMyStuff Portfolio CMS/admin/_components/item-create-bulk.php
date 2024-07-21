<a href="<?show($route)?>/items.php?task=list&sectid=<?=($item['Sect_ID'] ?? $sectID)?>"><i class="fi fi-rs-angle-double-small-left"></i> back to this Section's Item List</a>

<form id="item-form" method="post" enctype="multipart/form-data" action="<?=$currentURL?>?task=bulk&">
    <div class="space-btwn">
        <h1><?=($create ? "Create New Item" : "Edit Item : ".$item['Title'])?></h1>
        <? if ($edit) :?>
        <input type="hidden" name="n_item_id" value="<?show($item['ID'])?>">
        <button for="item-delete" name="delete_item" id="delete-item" class="small red"><i class="fi fi-rs-trash"></i> Delete Item</button>
        <? endif;?>
    </div>

<noscript>Enable Javascript for more dynamic features.</noscript>

<ul class="form-list">
    <li>
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" max-length="140" value="<?show($edit && $item['Title']>'' ? $item['Title'] : null )?>"  autocomplete="off" required>
    </li>

    <li>
        <label for="sect-id">In Section:</label>
        <i class="help icon"><i class="fi fi-rs-interrogation"></i>
            <article class="help-text">
                Keep in mind that different Sections may have different settings. Review your Item submission again if you change it.
            </article></i>
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
                <label for="publish-date">Publish date/time:
                    <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                        <article class="help-text">
                            You can back-date your items, or, if you want to schedule your items to show up later, 
                            you can set the publish date/time to sometime the future.
                        </article>
                    </i>
                </label>
                <input type="datetime-local" id="publish-datetime" name="publish_datetime" value="<?show($edit ? $item['Publish_Timestamp'] : null )?>">
        <? if ($create) : ?>
            </div>
        <?endif;?>
    </li>

    <?php if ($formatList) :?>
    <li>
        <label for="format">Display Format:</label>
        <select name="format" id="format">
            <option value="">Inherit from Section Default</option>
            <?php $selectedFormat = '';
            foreach ($formatList AS $format) :
                $fIsSelected = false;
                if (!is_null($item['Format'])) {
                    if (!$item['Format'] && $format['Path']===$sectInfo['Default_Item_Format']) {
                        $selectedFormat = $format['From'].' > '.$format['Name'];
                    } else if ($item['Format']>'' && $item['Format']===$format['Path']) {
                        $fIsSelected = true;
                        $selectedFormat = $format['From'].' > '.$format['Name'];
                    }
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
        <label for="tags">Tags:</label>
        <p>Separate tags with a comma.</p>
        <input type="text" name="tags" class="width-all" id="tags" value="<?show($edit && $item['Tags']>'' ? $item['Tags'] : null )?>"  autocomplete="off">
    </li>

    <li>
        <? if ($create || $item['Text']<='') :?>
            <input type="checkbox" class="chktoggle invis" id="add-text">
            <label for="add-text" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add Text
            <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                    <article class="help-text">
                        For text and very simple HTML. Paragraph formatting and breaks ('&lt;p&gt;'/'&lt;br&gt;') will be done automatically.
                    </article>
                </i>
            </label>
            <div class="chktoggle-show" style="flex-direction:column">
        <? endif;?>
            <label for="text-editor">Text:</label>
            <div class="text-panel">
                <?include('_components/text-edit-panel.inc.php')?>
                <textarea id="text-editor" name="m_text"><?show($edit ? $item['Text'] : null)?></textarea>
            </div>
        <? if ($edit ? $item['Text']<='' : null) :?>
            </div>
        <? endif;?>
    </li>


    <li id="image-fields">
        <? if ($create || $item['Img_Path']<='') :?>
            <input type="checkbox" class="chktoggle invis" id="add-image">
            <label for="add-image" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add Image
            <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                    <article class="help-text">
                        Allows jpg, png, gif, or webp image filetypes.
                    </article>
                </i>
            </label>
            <div class="chktoggle-show" style="flex-direction:column">
        <? endif;?>
        <label for="img_upload"><?=($create || $item['Img_Path']<='' ? 'U' : 'Reu')?>pload  Image File:</label>
                <input type="file" id="img-upload" name="img_upload" onchange="previewImg('img-upload', 'img')">
            <label>Current:</label>
            <div id="img_current" class="item-current-image-wrapper">
                <img id="img-visual" class="visual<?=($item['Img_Path'] ? ' block' : ' invis')?>" src="<?=($item['Img_Path'] ? $set['dir'].$item['Img_Path'] : null)?>" alt="<?show($item['Title'])?> Image">
                <input type="hidden" id="img-preview" name="img_preview" value="">
                <div id="img-rmv-info" class="rvm-file-path-info invis">&#10060; Image Removed</div>
                <em id="img-none" class="<?=(!$item['Img_Path'] ? null : 'invis')?>">none</em>
            </div>
        <? if ($edit && $item['Img_Path']>'') :?>
            <input type="hidden" id="img-stored" name="img_stored" value="<?show($item['Img_Path'])?>">
            <p><label>Image Path:</label> <?show($item['Img_Path'] ? $set['dir'].$item['Img_Path'] : '<i>None</i>')?></p>
        <? endif;?>
            <button id="img-rmv-btn" type="button" class="small red<?=(($create || !$item['Img_Path']) ? ' invis' : null)?>" onclick="rmvFilePath(this, 'img_stored', 'img_current')">Remove Item Image</button>
        <div>
            <label for="img-alt-text">Image Description (Alt Text):</label><i class="help icon"><i class="fi fi-rs-interrogation"></i>
                    <article class="help-text">
                        A description of the image for the visually impaired, or if the image is invalid.
                    </article>
                </i>
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
                    <? if ($create) :?>
                        <input type="hidden" name="sect_create_thumbnail" value="<?=$sectInfo['Auto_Thumbs']?>">
                    <? else : ?>
                        <input type="hidden" name="sect_create_thumbnail" value="0">
                        <label for="sect-create-thumbnail">Recreate Thumbnail Image:</label>
                        <input type="checkbox" id="sect-create-thumbnail" name="sect_create_thumbnail" value="1">
                    <? endif;?>
                        <p>If a new image is uploaded, a new thumbnail will be created automatically.</p>
                        <p>A thumbnail image with a 
                        <span id="sect-thumb-size">
                            <strong><?=(!$sectInfo['Thumb_Size_Axis'] ? 'width' : 'height');?></strong> of <strong><?=($sectInfo['Thumb_Size'])?>px</strong>
                        </span>
                        will be created for this item.<br/>
                        <a class="sect-link" href="<?show($route)?>/sections.php?task=edit&sectid=<?show($sectInfo['ID'])?>">
                            Click here to change this setting.
                        </a></p>
                    <input type="hidden" id="sect-thumb-size" name="n_sect_thumb_size" value="<?show($sectInfo['Thumb_Size'])?>">
                    <input type="hidden" id="sect-thumb-axis" name="n_sect_thumb_size_axis" value="<?show($sectInfo['Thumb_Size_Axis'])?>">
                </div>
                <div class="thumb-upload select-cond" data-sc-conditions="0">
                    <label for="thumb_upload"><?=(isset($item) && $item['Img_Thumb_Path'] ? 'Re-upload' : 'Upload')?> Thumbnail:</label>
                    <input type="file" id="thumb_upload" name="thumb_upload">
                    <p> - OR - </p>
                    <label for="create-thumbnail"> Auto-Create Thumbnail From Item's Image:</label>
                    <input type="hidden" name="item_create_thumbnail" value="0">
                    <input type="checkbox" id="create-thumbnail" name="item_create_thumbnail" value="1">
                    <ul class="form-list">
                        <li>
                            <label for="item-thumb-size">Thumbnail Size:</label>
                            <input type="number" id="item-thumb-size" name="n_item_thumb_size" value="<?show($sectInfo['Thumb_Size'])?>">
                        </li>
                        <li>
                            <label for="item-thumb-axis">Axis of Thumbnail Size:</label>
                            <select id="item-thumb-axis" name="n_item_thumb_size_axis">
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
            <label for="add-file" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add File
                <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                    <article class="help-text">
                        For any file that you do not want to display as an image. This can be any from a pdf to sound files to videos.
                        <?if ($set['has_max_upld_storage']):?>
                            <br>Keep in mind that your max upload size is <?=$set['max_upld_storage']?> MB.
                        <? endif;?>
                    </article>
                </i>
            </label>
            <div class="chktoggle-show" style="flex-direction:column">
        <? endif;?>
             <label for="file_upload"><?=($create || $item['File_Path']<='' ? 'U' : 'Reu')?>pload File:</label>
                <input type="file" id="file_upload" name="file_upload">
                <?if ($set['has_max_upld_storage']):?>
                    <p>Your max file upload size is <?=$set['max_upld_storage']?> MB.</p>
                <? endif;?>
            <div>
                <label for="file-pres">File Presentation:</label>
                <select id="file-pres" name="file_pres">
                    <option value="lnk" <?($edit ? formCmp($item['File_Pres'],'lnk','s') : null)?>>Link</option>
                    <option value="txt" <?($edit ? formCmp($item['File_Pres'],'txt','s') : null)?>>Text</option>
                    <option value="dld" <?($edit ? formCmp($item['File_Pres'],'dld','s') : null)?>>Download</option>
                    <option value="aud" <?($edit ? formCmp($item['File_Pres'],'aud','s') : null)?>>Audio Player</option>
                    <option value="vid" <?($edit ? formCmp($item['File_Pres'],'vid','s') : null)?>>Video Player</option>
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
            <label for="add-embed" class="chktoggle-label"><i class="fi fi-rs-plus"></i> Add Embed
                <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                    <article class="help-text">
                        For HTML, or embed scripts such as iframes, such as from sites like YouTube or Spotify.
                        Some simple Javascript may be entered as well, depending on your website host's security settings.
                    </article>
                </i>
            </label>
            <div class="chktoggle-show flex-col">
        <? endif;?>
            <label for="embed">Embed HTML/Script:</label>
            <textarea id="embed" class="block" name="b_embed"><?show(isset($item) && $item['Embed_HTML'] ? $item['Embed_HTML'] : null)?></textarea>
        <? if ($create || $item['Embed_HTML']>'') :?>
            </div>
        <? endif;?>
    </li>
    
    <li>
        <label for="hidden">Hide this item:
            <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                <article class="help-text">
                    Hidden Items will not display on the live site.
                </article>
            </i>
        </label>
        <input type="hidden" name="n_hidden" value="0">
        <input type="checkbox" id="hidden" name="n_hidden" value="1" <?show($edit && $item['Hidden'] ? 'checked' : null)?>>
    </li>

    <div class="space-btwn">
        <button for="item-form" type="submit" id="item-submit" name="<?=($create ? "create_item" : "edit_item")?>" formaction="<?=$baseURL?>/admin/sections.php?task=view&sectid=<?=$sectCmp?>" onclick="addTarget('_self')">
            <i class="fi fi-rs-check"></i> Submit
        </button>
        <button type="submit" id="item-preview" class="js-check" name="item_preview" formaction="<?=$baseURL?>/preview/item"  onclick="addTarget('_blank')">
            Preview
        </button>
    </div>
  <div id="modal-home"></div>
</form>

<script src="_js/text-editor.js"></script>
<script src="_js/toggle-on-cond.js"></script>
<script src="_js/fetch-sect-info.js"></script>
<script src="_js/preview-img.js"></script>
<script>
    const form = document.getElementById('item-form');
    function addTarget(target) {
        form.target= target;
    }

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
            if (sectInfo.Auto_Thumbs==1) {
                document.getElementById("sect-thumb-size").value = sectInfo.Thumb_Size;
                document.getElementById("sect-thumb-axis").value = sectInfo.Thumb_Size_Axis;
            } else {
                document.getElementById("item-thumb-size").value = sectInfo.Thumb_Size;
                document.getElementById("item-thumb-axis").value = sectInfo.Thumb_Size_Axis;
            }
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