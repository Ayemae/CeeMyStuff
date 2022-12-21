
<form method="post" enctype="multipart/form-data" action="?task=list">
<h1>Create New Category</h1>

<input type="hidden" name="n_page_id" value="<?show($pageID);?>">
<ul class="form-list">
    <li>
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" max-length="255" value="<?(!isset($_POST['name']) ? null : show($_POST['name']))?>">
        <br/>
        <label for="n_show_title">Show category name on the website:</label>
        <input type="hidden" name="n_show_title" value="0">
        <input type="checkbox" name="n_show_title" id="n_show_title" value="1" <?=(isset($_POST['n_show_title']) && $_POST['n_show_title']<1 ? null : 'checked')?>>
    </li>

    <li>
    <label for="header_img_upload">Header Image (Optional):</label>
        <input type="file" id="header_img_upload" name="header_img_upload" value="<?(!isset($_POST['header_img_upload']) ? null : show($_POST['header_img_upload']))?>">
        <br/>
        <label for="n_show_title">Show header image on the website:</label>
        <input type="hidden" name="n_show_header_img" value="0">
        <input type="checkbox" name="n_show_header_img" id="n_show_header_img" value="1" <?=(isset($_POST['n_show_header_img']) && $_POST['n_show_header_img']<1 ? null : 'checked')?>>
    </li>

    <li>
        <label for="text">Text:</label><br/>
        <textarea id="text" name="b_text"><?(!isset($_POST['b_text']) ? null : show($_POST['b_text']))?></textarea>
    </li>
    <?php if ($pageID != 0 && !$pageList) :
        $pgList = getPageList();?>
        <li>
            <label for="n_page_id">In Page:</label>
            <p>If you don't see the page you want, make sure that page has 'Multiple Content Categories' enabled in its settings.</p>
            <select id="n_page_id" name="n_page_id">
                <option selected>None</option>
                <?php foreach($pgList AS $page) : ?>
                    <option value="<?show($page['ID']);?>" <?=($pageID===$page['ID'] ? 'selected' : null)?>>
                        <?show($page['Name']);?>
                    </option>
                <?php endforeach; ?>
            </select>
    </li>
    <?php endif;?>
</ul>

    <h2>Category Display Settings</h2>
    <ul class="form-list">

    <li>
        <label for="show_images">Show Item Images:</label>
        <select id="show_images" name="n_show_images">
            <option value="0" <?show((isset($_POST['n_show_images']) && !$_POST['n_show_images'] ? 'selected' : null ))?>>No</option>
            <option value="1" <?show((!isset($_POST['n_show_images']) ? 'selected' : ($_POST['n_show_images']==1 ? 'selected' : null )))?>>Show Thumbnails</option>
            <option value="2" <?show((isset($_POST['n_show_images']) && $_POST['n_show_images']==2 ? 'selected' : null ))?>>Show Full-Sized Images</option>
        </select>
    </li>

    <li>
        <label for="show_titles">Show Item Titles:</label>
        <select id="show_titles" name="n_show_titles">
            <option value="0" <?show((isset($_POST['n_show_titles']) && !$_POST['n_show_titles'] ? 'selected' : null ))?>>No</option>
            <option value="1" <?show((!isset($_POST['n_show_titles']) ? 'selected' : ($_POST['n_show_titles']==1 ? 'selected' : null )))?>>Yes</option>
        </select>
    </li>

    <li>
        <label for="show_text">Show Item Text:</label>
        <select id="show_text" name="n_show_text">
            <option value="0" <?show((isset($_POST['n_show_text']) && !$_POST['n_show_text'] ? 'selected' : null ))?>>No</option>
            <option value="1" <?show((isset($_POST['n_show_text']) && $_POST['n_show_text']==1 ? 'selected' : null ))?>>Show Truncated Text</option>
            <option value="2" <?show((!isset($_POST['n_show_text']) ? 'selected' : ($_POST['n_show_text']==1 ? 'selected' : null )))?>>Show Full Text</option>
        </select>
    </li>

    <li>
        <label for="order_by">Order Items By:</label>
        <select id="order_by" name="order_by">
            <option value="Date" <?(!isset($_POST['order_by']) ? null : show((!$_POST['order_by'] == 'Date' ? 'selected' : null )))?>>Date</option>
            <option value="Title" <?(!isset($_POST['order_by']) ? null : show(($_POST['order_by']== 'Title' ? 'selected' : null )))?>>Title</option>
            <option value="Random" <?(!isset($_POST['order_by']) ? null : show(($_POST['order_by']== 'Random' ? 'selected' : null )))?>>Random</option>
            <!-- <option value="custom" <?(!isset($_POST['order_by']) ? null : show(($_POST['order_by']== 'custom' ? 'selected' : null )))?>>Custom</option> -->
        </select>
    </li>

    <li>
        <label for="create-thumbs">Auto-Create Thumbnails for Image Items:</label>
        <input type="hidden" name="n_create_thumbs" value="0">
        <input type="checkbox" id="create-thumbs" name="n_create_thumbs" value="1" 
        <?show((!isset($_POST['n_create_thumbs']) ? ($page['auto_thumbs'] ? 'checked' : null) : ($_POST['n_create_thumbs']==1 ? 'checked' : null )))?>>
            <ul class="form-list">
                <li>
                    <label for="thumb_size">Choose default thumbnail size:</label>
                    <input type="number" id="thumb_size" name="n_thumb_size" 
                    value="<?show((!isset($_POST['n_thumb_size']) ? ($page['thumb_size'] ? $page['thumb_size'] : 125) : $_POST['n_thumb_size']))?>">
                </li>
                <li>
                    <label for="thumb_axis">Axis of thumbnail size:</label>
                    <select id="thumb_axis" name="n_thumb_axis">
                        <option value="0" <?show((!isset($_POST['n_thumb_size']) ? ($page["thumb_size_axis"] == 'width' ? 'selected' : null) : null));?>>Width</option>
                        <option value="1" <?show((!isset($_POST['n_thumb_size']) ? ($page["thumb_size_axis"] == 'height' ? 'selected' : null) : null));?>>Height</option>
                    </select>
                </li>
            </ul>
    </li>

    <?php if ($catFormats) :?>
    <li>
        <label for="format">Display Format:</label>
        <select name="format" id="format">
            <?php foreach ($catFormats AS $cFormat) :?>
            <option value="<?show($cFormat['Path'])?>">
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
            <option value="<?show($iFormat['Path'])?>">
                <?show($iFormat['Name'])?>
            </option>
            <?php endforeach;?>
        </select>
    </li>
    <?php endif;?>

    <li>
            <label for="hidden"> Hide this category:</label>
            <input type="hidden" id="hidden" name="n_hidden" value="0">
            <input type="checkbox" id="hidden" name="n_hidden" value="1" <?(!isset($_POST['n_hidden']) ? null : show((!$_POST['n_hidden'] ? null : 'checked')))?>>
    </li>
    </ul>

  <button name="create_category"><i class="fi fi-rs-check"></i> Submit</button>
</form>