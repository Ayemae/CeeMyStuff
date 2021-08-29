<a href="?task=list"><i class="fi fi-rs-angle-double-small-left"></i> back to Category List</a>

<h1>Create New Category</h1>

<form method="post" enctype="multipart/form-data">
<ul class="form-list">
    <li>
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" max-length="255" value="<?(!isset($_POST['name']) ? null : show($_POST['name']))?>">
    </li>

    <li>
    <label for="header_img_upload">Header Image:</label>
        <input type="file" id="header_img_upload" name="header_img_upload" value="<?(!isset($_POST['header_img_upload']) ? null : show($_POST['header_img_upload']))?>">
    </li>

    <li>
        <label for="blurb">Blurb:</label><br/>
        <textarea id="blurb" name="b_blurb"><?(!isset($_POST['b_blurb']) ? null : show($_POST['b_blurb']))?></textarea>
    </li>
</ul>

    <h2>Category Page Display Settings</h2>
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
        <label for="show_captions">Show Item Captions:</label>
        <select id="show_captions" name="n_show_captions">
            <option value="0" <?show((isset($_POST['n_show_captions']) && !$_POST['n_show_captions'] ? 'selected' : null ))?>>No</option>
            <option value="1" <?show((!isset($_POST['n_show_captions']) ? 'selected' : ($_POST['n_show_captions']==1 ? 'selected' : null )))?>>Show Truncated Captions</option>
            <option value="2" <?show((isset($_POST['n_show_captions']) && $_POST['n_show_captions']==2 ? 'selected' : null ))?>>Show Full Captions</option>
        </select>
    </li>

    <li>
        <label for="order_by">Order Items By:</label>
        <select id="order_by" name="order_by">
            <option value="date" <?(!isset($_POST['order_by']) ? null : show((!$_POST['order_by'] == 'date' ? 'selected' : null )))?>>Date</option>
            <option value="title" <?(!isset($_POST['order_by']) ? null : show(($_POST['order_by']== 'title' ? 'selected' : null )))?>>Title</option>
            <option value="random" <?(!isset($_POST['order_by']) ? null : show(($_POST['order_by']== 'random' ? 'selected' : null )))?>>Random</option>
            <!-- <option value="custom" <?(!isset($_POST['order_by']) ? null : show(($_POST['order_by']== 'custom' ? 'selected' : null )))?>>Custom</option> -->
        </select>
    </li>

    <li>
        <label for="create-thumbs">Auto-Create Thumbnails for this Category:</label>
        <input type="hidden" name="n_create_thumbs" value="0">
        <input type="checkbox" id="create-thumbs" name="n_create_thumbs" value="1" 
        <?show((!isset($_POST['n_create_thumbs']) ? ($set['auto_thumbs'] ? 'checked' : null) : ($_POST['n_create_thumbs']==1 ? 'checked' : null )))?>>
            <ul class="form-list">
                <li>
                    <label for="thumb_size">Choose default thumbnail size:</label>
                    <input type="number" id="thumb_size" name="n_thumb_size" 
                    value="<?show((!isset($_POST['n_thumb_size']) ? ($set['thumb_size'] ? $set['thumb_size'] : null) : $_POST['n_thumb_size']))?>">
                </li>
                <li>
                    <label for="thumb_axis">Axis of thumbnail size:</label>
                    <select id="thumb_axis" name="n_thumb_axis">
                        <option value="0" <?show((!isset($_POST['n_thumb_size']) ? ($set["thumb_size_axis"] == 'width' ? 'selected' : null) : null));?>>Width</option>
                        <option value="1" <?show((!isset($_POST['n_thumb_size']) ? ($set["thumb_size_axis"] == 'height' ? 'selected' : null) : null));?>>Height</option>
                    </select>
                </li>
            </ul>
    </li>

    <li>
            <label for="hidden"> Hide this category:</label>
            <input type="hidden" id="hidden" name="n_hidden" value="0">
            <input type="checkbox" id="hidden" name="n_hidden" value="1" <?(!isset($_POST['n_hidden']) ? null : show((!$_POST['n_hidden'] ? null : 'checked')))?>>
    </li>
    </ul>
    <input type="hidden" id="format_id" name="n_format_id" value='0'>

  <button name="create_category">Submit</button>
</form>