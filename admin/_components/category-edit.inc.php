<form method="post" enctype="multipart/form-data" action="?task=list">
    <div class="space-btwn">
        <h1>Edit Category Settings : <?show($cat['Name'])?></h1>
        <button name="delete_category" id="delete-category" class="small red"><i class="fi fi-rs-trash"></i> Delete Category</button>
    </div>

<input type="hidden" name="n_cat_id" value="<?show($cat['ID'])?>">

<?php if ($cat['ID']>0) :?>
<ul class="form-list">
    <li>
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" max-length="255" value="<?show($cat['Name'])?>">
        <br/>
        <label for="n_show_title">Show category name on the website:</label>
        <input type="hidden" name="n_show_title" value="0">
        <input type="checkbox" name="n_show_title" id="n_show_title" value="1" <?=(isset($cat['Show_Title']) && $cat['Show_Title']<1 ? null : 'checked')?>>
    </li>

    <li>
    <label for="header_img_upload">Header Image:</label>
        <input type="file" id="header_img_upload" name="header_img_upload">
        <input type="hidden" name="header_img_stored" value="<? isset($cat['Img_Path']) ? show($cat['Img_Path']) : null ?>">
        <br/>
        <label for="n_show_title">Show header image on the website:</label>
        <input type="hidden" name="n_show_header_img" value="0">
        <input type="checkbox" name="n_show_header_img" id="n_show_header_img" value="1" <?=(isset($_POST['n_show_header_img']) && $_POST['n_show_header_img']<1 ? null : 'checked')?>>
    </li>

    <li>
        <label for="b_text">Text:</label><br/>
        <textarea name="b_text"><?show($cat['Text'])?></textarea>
    </li>
    <li>
        <label for="n_page_id">In Page:</label>
        <p>If you don't see the page you want, make sure that page has 'Multiple Content Categories' enabled in its settings.</p>
        <select id="n_page_id" name="n_page_id">
            <option>None</option>
            <?php foreach($pgList AS $page) : 
                if ($cat['Page_ID']===$page['ID'] || $page['Can_Add_Cat']) : ?>
                <option value="<?show($page['ID']);?>" <?=($cat['Page_ID']===$page['ID'] ? 'selected' : null)?>>
                    <?show($page['Name']);?>
                </option>
            <?php endif;
        endforeach; ?>
        </select>
    </li>
</ul>
<?php endif;?>

    <h2>Category Page Display Settings</h2>
    <ul class="form-list">
    <?php if ($cat['ID']>0) :?>
    <li>
        <label for="show_images">Show Item Images:</label>
        <select id="show_images" name="n_show_images">
            <option value="0" <?show((!$cat['Show_Item_Images'] ? 'selected' : null ))?>>No</option>
            <option value="1" <?show(($cat['Show_Item_Images'] ==1 ? 'selected' : null ))?>>Show Thumbnails</option>
            <option value="2" <?show(($cat['Show_Item_Images'] ==2 ? 'selected' : null ))?>>Show Full-Sized Images</option>
        </select>
    </li>

    <li>
        <label for="show_titles">Show Item Titles:</label>
        <select id="show_titles" name="n_show_titles">
            <option value="0" <?show((!$cat['Show_Item_Titles'] ? 'selected' : null ))?>>No</option>
            <option value="2"  <?show(($cat['Show_Item_Titles'] ? 'selected' : null ))?>>Yes</option>
        </select>
    </li>

    <li>
        <label for="show_text">Show Item Text:</label>
        <select id="show_text" name="n_show_text">
            <option value="0" <?show((!$cat['Show_Item_Text'] ? 'selected' : null ))?>>No</option>
            <option value="1" <?show(($cat['Show_Item_Text']==1 ? 'selected' : null ))?>>Show Truncated Text</option>
            <option value="2" <?show(($cat['Show_Item_Text']==2 ? 'selected' : null ))?>>Show Full Text</option>
        </select>
    </li>

    <li>
        <label for="order_by">Order Items By:</label>
        <select id="order_by" name="order_by">
            <option value="Date" <?show((!$cat['Order_By'] == 'date' ? 'selected' : null ))?>>Date</option>
            <option value="Title" <?show(($cat['Order_By']== 'title' ? 'selected' : null ))?>>Title</option>
            <option value="Random" <?show(($cat['Order_By']== 'random' ? 'selected' : null ))?>>Random</option>
            <!-- <option value="custom" <?show(($cat['Order_By']== 'custom' ? 'selected' : null ))?>>Custom</option> -->
        </select>
    </li>
    <?php endif;?>

    <li>
        <label for="create-thumbs">Auto-Create Thumbnails for Image Items:</label>
        <input type="hidden" name="n_create_thumbs" value="0">
        <input type="checkbox" id="create-thumbs" name="n_create_thumbs" class="chktoggle" value="1" <?php echo ($cat['Auto_Thumbs'] ? "checked=checked" : null );?>>
        <div class="chktoggle-show">
            <ul class="form-list">
                <li>
                    <label for="thumb_size">Choose default thumbnail size:</label>
                    <input type="number" id="thumb_size" name="n_thumb_size" value="<?php echo ($cat['Thumb_Size'] ? $cat['Thumb_Size'] : null );?>">
                </li>
                <li>
                    <label for="thumb_axis">Axis of thumbnail size:</label>
                    <select id="thumb_axis" name="n_thumb_axis">
                        <option value="0" <?echo ($cat["Thumb_Size_Axis"] == 'width' ? 'selected' : null);?>>Width</option>
                        <option value="1" <?echo ($cat["Thumb_Size_Axis"] == 'height' ? 'selected' : null);?>>Height</option>
                    </select>
                </li>
            </ul>
        </div>
    </li>

    <?php if ($catFormats) :?>
    <li>
        <label for="format">Display Format:</label>
        <select name="format" id="format">
            <?php foreach ($catFormats AS $cFormat) :?>
            <option value="<?show($cFormat['Path'])?>" <?=($cat['Format']===$cFormat['Path'] ? 'selected' : null)?>>
                <?show($cFormat['Name'])?>
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
            <option value="<?show($iFormat['Path'])?>" <?=($cat['Default_Item_Format']===$iFormat['Path'] ? 'selected' : null)?>>
                <?show($iFormat['Name'])?>
            </option>
            <?php endforeach;?>
        </select>
    </li>
    <?php endif;?>

    <?php if ($cat['ID']>0) :?>
    <li>
            <label for="hidden"> Hide this category:</label>
            <input type="hidden" id="hidden" name="n_hidden" value="0">
            <input type="checkbox" id="hidden" name="n_hidden" value="1" <?show(($cat['Hidden'] ? 'checked' : null ))?>>
    </li>
    <?php endif;?>
    </ul>

  <button name="edit_category"><i class="fi fi-rs-check"></i> Submit</button>
  <div id="modal-home"></div>
</form>

<script src="_js/modal.js"></script>
<script>
let modalHTML = `<h2>Are you sure?</h2>
                <p>This cannot be undone.</p>
                <div class="flex">
                <button type="submit" class="button red" name="delete_category"/>Yes, delete this category</button>
                <button class="button modal-close" onclick="event.preventDefault()"/>Never mind</button>
                </div>`;
const modalCatDelete = new Modal('modal-cat-delete', modalHTML, false, false);
modalCatDelete.appendToForm('modal-home');

document.getElementById('delete-category').addEventListener('click', function(e) {
    e.preventDefault();
    modalCatDelete.trigger();
}, false);
</script>