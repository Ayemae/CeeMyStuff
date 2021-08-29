<h1>Edit Page</h1>

<form method="post" enctype="multipart/form-data">

<input type="hidden" name="n_page_id" value="<?show($page['ID'])?>">

<ul class="form-list">
    <li>
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" max-length="255" value="<?show($page['Name'])?>">
    </li>

    <li>
    <label for="header_img_upload">Header:</label>
    <input type="text" name="header" id="header" max-length="255" value="<?show($page['Page_Header'])?>">
    </li>

    <li>
        <label for="b_meta">Meta Description:</label><br/>
        <textarea name="b_meta"><?show($page['Meta'])?></textarea>
    </li>

    <li>
            <label for="hidden"> Hide this page:</label>
            <input type="hidden" id="hidden" name="n_hidden" value="0">
            <input type="checkbox" id="hidden" name="n_hidden" value="1" <?show(($cat['Hidden'] ? 'checked' : null ))?>>
    </li>
    </ul>
    <input type="hidden" id="format_id" name="n_format_id" value='0'>

  <button name="edit_category">Submit</button>
</form>