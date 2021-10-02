<a href="?task=list"><i class="fi fi-rs-angle-double-small-left"></i> back to Category List</a>

<h1>Edit Category Settings</h1>

<form method="post" enctype="multipart/form-data">

<input type="hidden" name="n_cat_id" value="<?show($cat['ID'])?>">

<ul class="form-list">
    <li>
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" max-length="255" value="<?show($cat['Name'])?>">
        <br/>
        <label for="n_show_title">Show category name on the website:</label>
        <input type="hidden" name="n_show_title" value="0">
        <input type="checkbox" name="n_show_title" id="n_show_title" value="1" <?=(isset($_POST['n_show_title']) && $_POST['n_show_title']<1 ? null : 'checked')?>>
    </li>

    <li>
    <label for="header_img_upload">Header Image:</label>
        <input type="file" id="header_img_upload" name="header_img_upload">
        <input type="hidden" name="header_img_stored" value="<?show($cat['Img_Path'])?>">
        <br/>
        <label for="n_show_title">Show header image on the website:</label>
        <input type="hidden" name="n_show_header_img" value="0">
        <input type="checkbox" name="n_show_header_img" id="n_show_header_img" value="1" <?=(isset($_POST['n_show_header_img']) && $_POST['n_show_header_img']<1 ? null : 'checked')?>>
    </li>

    <li>
        <label for="b_text">Text:</label><br/>
        <textarea name="b_text"><?show($cat['Text'])?></textarea>
    </li>
</ul>

    <h2>Category Page Display Settings</h2>
    <ul class="form-list">

    <li>
        <label for="show_images">Show Item Images:</label>
        <select id="show_images" name="n_show_images">
            <option value="0" <?show((!$cat['Show_Images'] ? 'selected' : null ))?>>No</option>
            <option value="1" <?show(($cat['Show_Images'] ==1 ? 'selected' : null ))?>>Show Thumbnails</option>
            <option value="2" <?show(($cat['Show_Images'] ==2 ? 'selected' : null ))?>>Show Full-Sized Images</option>
        </select>
    </li>

    <li>
        <label for="show_titles">Show Item Titles:</label>
        <select id="show_titles" name="n_show_titles">
            <option value="0" <?show((!$cat['Show_Titles'] ? 'selected' : null ))?>>No</option>
            <option value="2"  <?show(($cat['Show_Titles'] ? 'selected' : null ))?>>Yes</option>
        </select>
    </li>

    <li>
        <label for="show_text">Show Item Text:</label>
        <select id="show_text" name="n_show_text">
            <option value="0" <?show((!$cat['Show_text'] ? 'selected' : null ))?>>No</option>
            <option value="1" <?show(($cat['Show_text']==1 ? 'selected' : null ))?>>Show Truncated Text</option>
            <option value="2" <?show(($cat['Show_text']==2 ? 'selected' : null ))?>>Show Full Text</option>
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

    <li>
        <label for="create-thumbs">Auto-Create Thumbnails for this Category:</label>
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

    <li>
            <label for="hidden"> Hide this category:</label>
            <input type="hidden" id="hidden" name="n_hidden" value="0">
            <input type="checkbox" id="hidden" name="n_hidden" value="1" <?show(($cat['Hidden'] ? 'checked' : null ))?>>
    </li>
    </ul>
    <input type="hidden" id="format" name="format" value=''>

  <button name="edit_category">Submit</button>
</form>