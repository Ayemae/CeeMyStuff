<form method="post" enctype="multipart/form-data" >
    <!-- action="?task=list" -->
    <div class="space-btwn">
        <h1>Edit Section Settings : <?show($sect['Name'])?></h1>
        <button name="delete_section" id="delete-section" class="small red"><i class="fi fi-rs-trash"></i> Delete Section</button>
    </div>

<input type="hidden" name="n_sect_id" value="<?show($sect['ID'])?>">

<?php if ($sect['ID']>0) :?>
<ul class="form-list">
    <li>
        <label for="name">Title:</label>
        <input type="text" name="name" id="name" max-length="255" value="<?show($sect['Name'])?>">
        <br/>
        <label for="n-show-title">Show section title on the website:</label>
        <input type="hidden" name="n_show_title" value="0">
        <input type="checkbox" name="n_show_title" id="n-show-title" value="1" <?=(isset($sect['Show_Title']) && $sect['Show_Title']<1 ? null : 'checked')?>>
    </li>

    <li>
    <label for="header_img_upload">Header Image:</label>
        <input type="file" id="header_img_upload" name="header_img_upload">
        <input type="hidden" name="header_img_stored" value="<? isset($sect['Img_Path']) ? show($sect['Img_Path']) : null ?>">
        <br/>
        <label for="n_show_title">Show header image on the website:</label>
        <input type="hidden" name="n_show_header_img" value="0">
        <input type="checkbox" name="n_show_header_img" id="n_show_header_img" value="1" <?=(isset($_POST['n_show_header_img']) && $_POST['n_show_header_img']<1 ? null : 'checked')?>>
    </li>

    <li>
        <label for="b_text">Text:</label><br/>
        <textarea name="b_text"><?show($sect['Text'])?></textarea>
    </li>
    <li>
        <label for="n_page_id">In Page:</label>
        <p>If you don't see the page you want, make sure that page has 'Multiple Content Sections' enabled in its settings.</p>
        <select id="n_page_id" name="n_page_id">
            <option value="0">None</option>
            <?php foreach($pgList AS $page) : 
                if ($sect['Page_ID']===$page['ID'] || $page['Can_Add_Sect']) : ?>
                <option value="<?show($page['ID']);?>" <?=($sect['Page_ID']===$page['ID'] ? 'selected' : null)?>>
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
                <option value="Date" <?show(($sect['Order_By'] == 'Date' ? 'selected' : null ))?>>Date</option>
                <option value="Title" <?show(($sect['Order_By']== 'Title' ? 'selected' : null ))?>>Title</option>
                <option value="Custom" <?show(($sect['Order_By']== 'Custom' ? 'selected' : null ))?>>Custom</option>
                <option value="ID" <?show(($sect['Order_By'] == 'ID' ? 'selected' : null ))?>>When Added</option>
                <option value="Random" <?show(($sect['Order_By']== 'Random' ? 'selected' : null ))?>>Random</option>
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

    <?php if ($sect['ID']>0) :?>

        <?php if ($sectFormats) :?>
        <li>
            <label for="format">Display Format:</label>
            <select name="format" id="format">
                <?php foreach ($sectFormats AS $sFormat) :?>
                <option value="<?show($sFormat['Path'])?>" <?=($sect['Format']===$sFormat['Path'] ? 'selected' : null)?>>
                    <?show($sFormat['Name'])?>
                </option>
                <?php endforeach;?>
            </select>
        </li>
        <?php endif;?>

        <?php if ($itemFormats) :?>
        <li>
            <label for="item-format">Default Item Display Format:</label>
            <select name="item_format" id="item-format">
                <?php foreach ($itemFormats AS $iFormat) :?>
                <option value="<?show($iFormat['Path'])?>" <?formCmp($sect['Default_Item_Format'],$iFormat['Path'],'s')?>>
                    <?show($iFormat['Name'])?>
                </option>
                <?php endforeach;?>
            </select>
        </li>
        <?php endif;?>

    <li>
        <label for="show_titles">Show Item Titles:</label>
        <select id="show_titles" name="n_show_titles">
            <option value="0" <?show((!$sect['Show_Item_Titles'] ? 'selected' : null ))?>>No</option>
            <option value="1"  <?show(($sect['Show_Item_Titles'] ? 'selected' : null ))?>>Yes</option>
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
        <input type="checkbox" id="create-thumbs" name="n_create_thumbs" class="chktoggle" value="1" <?php echo ($sect['Auto_Thumbs'] ? "checked=checked" : null );?>>
        <div class="chktoggle-show">
            <ul class="form-list">
                <li>
                    <label for="thumb_size">Choose default thumbnail size:</label>
                    <input type="number" id="thumb_size" name="n_thumb_size" value="<?php echo ($sect['Thumb_Size'] ? $sect['Thumb_Size'] : null );?>">
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

    <?php if ($sect['ID']>0) :?>

    <li>
        <div>
            <label for="show_files">Show Item Files:</label>
            <select id="show_files" name="n_show_files">
                <option value="0" <?formCmp($sect['Show_Item_Files'],0,'s')?>>No</option>
                <option value="1" <?formCmp($sect['Show_Item_Files'],1,'s')?>>Show Link to File</option>
                <option value="2" <?formCmp($sect['Show_Item_Files'],2,'s')?>>Show File Download</option>
            </select>
        </div>
        <div>
            <label for="name">File Link Text:</label>
            <input type="text" name="link_text" id="link-text" max-length="255" value="<?show($sect['Link_Text'])?>">
        </div>
    </li>

    <li>
        <div>
            <label for="onclick-action">Item On-Click Actions:</label>
            <p>What should happen when a viewer clicks on an item?</p>
            <select id="onclick-action" name="onclick_action">
                <option value="0">Load a single-item viewing page</option>
                <option value="1">Open a lightbox with the subject</option>
                <option value="1">Open the subject file in a new window</option>
                <option value="0">Nothing; items should not be clickable</option>
            </select>
        </div>

        <ul class="form-list">
            <div>
                <label for="click-area">Item Display Subject:</label>
                <p>What aspect of the item do you want the spotlight on?</p>
                <select id="onclick-action" name="n_show_titles">
                    <option value="1">Image</option>
                    <option value="0">Text</option>
                    <option value="1">File</option>
                    <option value="0">Embed</option>
                </select>
            </div>

            <div>
                <div><label for="click-area">Item Click Area:</label></div>
                <input type="checkbox" class="chktoggle" id="clk-anywhere" name="item_click_area[1]" value="Anywhere">
                <label for="anywhere">Anywhere</label>
                <div class="chktoggle-hide">
                    <label for="clk-title"><input type="checkbox" id="clk-title" name="item_click_area[2]" value="Title"> Title</label>
                    <label for="clk-image"><input type="checkbox" id="clk-image" name="item_click_area[3]" value="Image"> Image</label>
                    <label for="clk-text"><input type="checkbox" id="clk-text" name="item_click_area[4]" value="Text"> Text</label>
                    <label for="clk-link"><input type="checkbox" id="clk-link" name="item_click_area[5]" value="Link"> Added 'View' Link</label>
                </div>
            </div>

        </ul>
    </li>

    <li>
            <label for="hidden"> Hide this section:</label>
            <input type="hidden" id="hidden" name="n_hidden" value="0">
            <input type="checkbox" id="hidden" name="n_hidden" value="1" <?formCmp($sect['Hidden'])?>>
    </li>
    <?php endif;?>
    </ul>

  <button name="edit_section"><i class="fi fi-rs-check"></i> Submit</button>
  <div id="modal-home"></div>
</form>

<script src="_js/modal.js"></script>
<script>
let modalHTML = `<h2>Are you sure?</h2>
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