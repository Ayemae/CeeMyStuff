<form method="post" enctype="multipart/form-data" action="?task=list">
    <div class="space-btwn">
        <h1><?=($create ? "Create Section" : "Edit Section Settings : ".$sect['Name'])?></h1>
        <? if ($edit) :?>
        <input type="hidden" name="n_sect_id" value="<?show($sect['ID'])?>">
        <button name="delete_section" id="delete-section" class="small red"><i class="fi fi-rs-trash"></i> Delete Section</button>
        <? endif;?>
    </div>

<?php if ($create || $sect['ID']>0) :?>
<ul class="form-list">
    <li>
        <label for="name">Title:</label>
        <input type="text" name="name" id="name" max-length="255" value="<?show($edit ? $sect['Name'] : null)?>">
        <br/>
        <label for="n-show-title">Show section title on the website:</label>
        <input type="hidden" name="n_show_title" value="0">
        <input type="checkbox" name="n_show_title" id="n-show-title" value="1" <?=($edit && isset($sect['Show_Title']) && $sect['Show_Title']<1 ? null : 'checked')?>>
    </li>

    <li>
    <label for="header_img_upload">Header Image:</label>
        <input type="file" id="header_img_upload" name="header_img_upload">
        <input type="hidden" id="header_img_stored" name="header_img_stored" value="<?=($edit && isset($sect['Img_Path']) ? show($sect['Img_Path']) : null )?>">
        <?if ($edit && isset($sect['Header_Img_Path']) && $sect['Header_Img_Path']>''):?>
            <div id="header_img_current" class="sect-current-image-wrapper">
                Current:<br/> <img src="<?=$sect['Header_Img_Path']?>">
            </div>
            <button type="button" class="small red" onclick="rmvFilePath('header_img_stored', 'header_img_current')">Remove Current Image</button>
        <?endif;?>
        <br/>
        <label for="n_show_title">Show header image on the website:</label>
        <input type="hidden" name="n_show_header_img" value="0">
        <input type="checkbox" name="n_show_header_img" id="n_show_header_img" value="1" <?=($edit && isset($_POST['n_show_header_img']) && $_POST['n_show_header_img']<1 ? null : 'checked')?>>
    </li>

    <li>
        <label for="text-editor">Section Text:</label><br/>
        <?include('_components/text-edit-panel.inc.php')?>
        <textarea id="text-editor" name="b_text"><?show($edit ? $sect['Text'] : null)?></textarea>
    </li>
    <li>
        <label for="n_page_id">In Page:</label>
        <p>If you don't see the page you want, make sure that page has 'Multiple Content Sections' enabled in its settings.</p>
        <select id="n_page_id" name="n_page_id">
            <option value="0">None</option>
            <?php foreach($pgList AS $page) : 
                if ($sect['Page_ID']==$page['ID'] || $page['Can_Add_Sect']) : ?>
                <option value="<?show($page['ID']);?>" <?=formCmp($sect['Page_ID'],$page['ID'],'s')?>>
                    <?show($page['Name']);?>
                </option>
            <?php endif;
        endforeach; unset($page);?>
        </select>
    </li>
</ul>
<?php endif;?>





    <label for="section-display-sets">
        <h2><i class="fi fi-rs-plus"></i> Section Display Settings</h2>
    </label>
    <input type="checkbox" class="chktoggle invis" id="section-display-sets">
    <ul class="form-list chktoggle-show">

    <li>
        <div>
            <label for="order_by">Order Items By:</label>
            <select id="order_by" name="order_by">
                <option value="Date" <?formCmp($sect['Order_By'],'Date','s')?>>Date</option>
                <option value="Title" <?formCmp($sect['Order_By'],'Title','s')?>>Title</option>
                <option value="Custom" <?formCmp($sect['Order_By'],'Custom','s')?>>Custom</option>
                <option value="ID" <?formCmp($sect['Order_By'],'ID','s')?>>When Added</option>
                <option value="Random" <?formCmp($sect['Order_By'],'Random','s')?>>Random</option>
            </select>
        </div>
        <div>
            <label for="order_dir">Order Direction:</label>
            <select id="order_dir" name="n_order_dir">
                <option value="0" <?=($sect['Order_Dir'] != 1 ? 'selected' : null )?>>Ascending</option>
                <option value="1" <?=($sect['Order_Dir'] == 1 ? 'selected' : null )?>>Descending</option>
            </select>
        </div>
    </li>

    <?php if ($create || $sect['ID']>0) :?>

        <li>
            <label for="format">Display Format:</label>
            <p>Select a format for how you would like this Section to display on the website.</p>
            <?php if ($sectFormats) :?>
                <select name="format" id="format">
                    <?php foreach ($sectFormats AS $sFormat) :?>
                    <option value="<?show($sFormat['Path'])?>" <?=($sect['Format']===$sFormat['Path'] ? 'selected' : null)?>>
                        <?show($sFormat['From'])?> > <?show($sFormat['Name'])?>
                    </option>
                    <?php endforeach;?>
                </select>
            <?php else:?>
                <i class="red">No valid Section formats were found.</i>
            <?php endif;?>
        </li>

        <li>
            <label for="item-format">Default Item Display Format:</label>
            <p>Select a format for how you would like this Section's individual items to display on the website.</p>
            <?php if ($itemFormats) :?>
                <select name="item_format" id="item-format">
                    <?php foreach ($itemFormats AS $iFormat) :?>
                    <option value="<?show($iFormat['Path'])?>" <?formCmp($sect['Default_Item_Format'],$iFormat['Path'],'s')?>>
                        <?show($iFormat['From'])?> > <?show($iFormat['Name'])?>
                    </option>
                    <?php endforeach;?>
                </select>
            <?php else :?>
                <i class="red">No valid Item formats were found.</i>
            <?php endif;?>
        </li>

    <li>
        <label for="show_titles">Show Item Titles:</label>
        <select id="show_titles" name="n_show_titles">
            <option value="0" <?show(!$sect['Show_Item_Titles'] ? 'selected' : null )?>>No</option>
            <option value="1"  <?show($sect['Show_Item_Titles'] ? 'selected' : null )?>>Yes</option>
        </select>
    </li>

    <li>
        <label for="show_text">Show Item Text:</label>
        <select id="show_text" name="n_show_text">
            <option value="0" <?formCmp($sect['Show_Item_Text'],0,'s')?>>No</option>
            <option value="1" <?formCmp($sect['Show_Item_Text'],1,'s')?>>Show Truncated Text</option>
            <option value="2" <?formCmp($sect['Show_Item_Text'],2,'s')?>>Show Full Text</option>
        </select>
    </li>

    <li>
        <label for="show_images">Show Item Images:</label>
        <select id="show_images" name="n_show_images">
            <option value="0" <?formCmp($sect['Show_Item_Images'],0,'s')?>>No</option>
            <option value="1" <?formCmp($sect['Show_Item_Images'],1,'s')?>>Show Thumbnails</option>
            <option value="2" <?formCmp($sect['Show_Item_Images'],2,'s')?>>Show Full-Sized Images</option>
        </select>
    </li>
    <?php endif;?>

    <li>
        <label for="create-thumbs">Auto-Create Thumbnails for Image Items:</label>
        <input type="hidden" name="n_create_thumbs" value="0">
        <input type="checkbox" id="create-thumbs" name="n_create_thumbs" class="chktoggle" value="1" <?=($sect['Auto_Thumbs'] ? "checked=checked" : null );?>>
        <div class="chktoggle-show">
            <ul class="sub-form-list">
                <li>
                    <label for="thumb_size">Choose default thumbnail size:</label>
                    <input type="number" id="thumb_size" name="n_thumb_size" value="<?=($sect['Thumb_Size'] ? $sect['Thumb_Size'] : null );?>">
                </li>
                <li>
                    <label for="thumb_axis">Axis of thumbnail size:</label>
                    <select id="thumb_axis" name="n_thumb_axis">
                        <option value="0" <?formCmp($sect['Thumb_Size_Axis'],'width','s')?>>Width</option>
                        <option value="1" <?formCmp($sect['Thumb_Size_Axis'],'height','s')?>>Height</option>
                    </select>
                </li>
            </ul>
        </div>
    </li>

    <?php if ($create || $sect['ID']>0) :?>

    <li class="select-cond-container">
        <div>
            <label for="show_files">Show Item Files:</label>
            <select id="show_files" name="n_show_files" class="select-cond-master">
                <option value="0" <?formCmp($sect['Show_Item_Files'],0,'s')?>>No</option>
                <option value="1" <?formCmp($sect['Show_Item_Files'],1,'s')?>>Show Link to File</option>
                <option value="2" <?formCmp($sect['Show_Item_Files'],2,'s')?>>Show File Download</option>
            </select>
        </div>
        <ul class="sub-form-list select-cond" data-sc-conditions="1,2">
            <li>
                <label for="name">Default File Link Text:</label>
                <input type="text" name="file_link_text" id="link-text" max-length="255" value="<?show($edit ? $sect['Default_File_Link_Text'] : "Click here")?>">
            <l/i>
        </ul>
    </li>

    <li class="select-cond-container">
            <label for="onclick-action">Item On-Click Actions:</label>
            <p>What should happen when a viewer clicks on an item?</p>
            <select id="onclick-action" class="select-cond-master" name="n_onclick_action">
                <option value="1" <?formCmp($sect['On_Click_Action'],1,'s')?>>Load a single-item viewing page</option>
                <option value="2" <?formCmp($sect['On_Click_Action'],2,'s')?>>Open a lightbox to view item</option>
                <option value="3" <?formCmp($sect['On_Click_Action'],3,'s')?>>Open new window with single-item viewing page</option>
                <option value="0" <?formCmp($sect['On_Click_Action'],0,'s')?>>Nothing; items should not be clickable</option>
            </select>

        <ul class="sub-form-list">
            <li id="item-click-area-sc" class="select-cond" data-sc-conditions="0" data-sc-exclude="true" data-sc-reverse="true">
                <div><label for="click-area">Item Click Area:</label></div>
                <input type="checkbox" class="chktoggle fl-chkbox" id="clk-anywhere" name="item_click_area[1]" value="All" <?=(in_array("All",$sect['Item_Click_Area']) ? "checked" : null )?>>
                <label class="fl-chkbox" for="clk-anywhere">Anywhere</label>
                <div class="chktoggle-hide">
                    <fieldset>
                        <label for="clk-title">
                            <input type="checkbox" id="clk-title" name="item_click_area[2]" value="Title" <?=(in_array("Title",$sect['Item_Click_Area']) ? "checked" : null )?>> Title
                        </label>
                        <label for="clk-image"><input type="checkbox" id="clk-image" name="item_click_area[3]" value="Image" <?=(in_array("Image",$sect['Item_Click_Area']) ? "checked" : null )?>> Image
                        </label>
                        <label for="clk-text">
                            <input type="checkbox" id="clk-text" name="item_click_area[4]" value="Text" <?=(in_array("Text",$sect['Item_Click_Area']) ? "checked" : null )?>> Text
                        </label>
                        <label for="clk-link">
                            <input type="checkbox" id="clk-link" name="item_click_area[5]" value="Link" <?=(in_array("Link",$sect['Item_Click_Area']) ? "checked" : null )?>> Added 'View' Link
                        </label>
                    </fieldset>
                </div>
            </li>
        

            <li id="lightbox-format-sc" class="select-cond" data-sc-conditions="2" data-sc-exclude="" data-sc-reverse="">
                <label for="lightbox-format">Lightbox Format:</label>
                <p>Select a format for how you would like Section's items to display within lightbox.</p>
                <?php if ($lightboxFormats) :?>
                    <select name="lightbox_format" id="lightbox-format">
                        <?php foreach ($lightboxFormats AS $lFormat) :?>
                        <option value="<?show($lFormat['Path'])?>" <?formCmp($sect['Lightbox_Format'],$lFormat['Path'],'s')?>>
                            <?show($lFormat['From'])?> > <?show($lFormat['Name'])?>
                        </option>
                        <?php endforeach;?>
                    </select>
                <?php else :?>
                    <i class="red">No valid Lightbox formats found.</i>
                <?php endif;?>
            </li>


            <li id="viewitem-format-sc" class="select-cond" data-sc-conditions="1,3" data-sc-exclude="" data-sc-reverse="">
                <label for="view-item-format">Default View-Item Page Display Format:</label>
                <p>Select a format for how you would like Section's items to display on their individual 'view' pages on the website.</p>
                <?php if ($viewItemFormats) :?>
                    <select name="view_item_format" id="view-item-format">
                        <?php foreach ($viewItemFormats AS $vFormat) :?>
                        <option value="<?show($vFormat['Path'])?>" <?formCmp($sect['View_Item_Format'],$vFormat['Path'],'s')?>>
                            <?show($vFormat['From'])?> > <?show($vFormat['Name'])?>
                        </option>
                        <?php endforeach;?>
                    </select>
                <?php else :?>
                    <i class="red">No valid View-Item Page formats found.</i>
                <?php endif;?>
            </li>

            <li class="select-cond" data-sc-conditions="0" data-sc-exclude="true" data-sc-reverse="true">
                <label for="paginate-items">
                    Enable pagination between items:
                </label>
                <input type="hidden" name="n_paginate_items" value="0">
                <input type="checkbox" id="paginate-items" name="n_paginate_items" value="1" <?($edit ? formCmp($sect['Paginate_Items'],1) : "checked")?>>
            </li>

        </ul>
    </li>


        

    <li>
            <label for="hidden"> Hide this section:</label>
            <input type="hidden" id="hidden" name="n_hidden" value="0">
            <input type="checkbox" id="hidden" name="n_hidden" value="1" <?=formCmp($sect['Hidden'],1)?>>
    </li>
    <?php endif;?>
    </ul>

  <button name="<?=($create ? "create_section" : "edit_section")?>"><i class="fi fi-rs-check"></i> Submit</button>
  <div id="modal-home"></div>
</form>

<? if ($edit) :?>
<script type="text/javascript">
</script>
<script src="_js/modal.js"></script>
<script src="_js/rmvFilePaths.js"></script>
<script src="_js/toggle-on-cond.js"></script>
<script src="_js/text-editor.js"></script>
<script>
let modalHTML = `<h2>Are you sure you want to delete the '<?=$sect['Name']?>' Section?</h2>
                <p>This cannot be undone.</p>
                <div class="flex">
                <button type="submit" class="button red" name="delete_section"/>Yes, delete this section</button>
                <button class="button modal-close" onclick="event.preventDefault()"/>Never mind</button>
                </div>`;
const modalSectDelete = new Modal('modal-sect-delete', modalHTML, false, false);
modalSectDelete.appendToForm('modal-home');

document.getElementById('delete-section').addEventListener('click', function(e) {
    e.preventDefault();
    modalSectDelete.trigger();
}, false);
</script>
<?endif;?>