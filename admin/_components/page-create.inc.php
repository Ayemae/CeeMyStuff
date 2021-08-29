<h1>Create New Page</h1>

<form method="post" enctype="multipart/form-data">
<ul class="form-list">
    <li>
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" max-length="255" value="<?(!isset($_POST['name']) ? null : show($_POST['name']))?>">
    </li>

    <li>
    <label for="header_img_upload">Header:</label>
        <input type="file" id="header_img_upload" name="header_img_upload" value="<?(!isset($_POST['header_img_upload']) ? null : show($_POST['header_img_upload']))?>">
    </li>

    <li>
        <label for="meta">Meta Description:</label><br/>
        <textarea id="meta" name="b_meta"><?(!isset($_POST['b_meta']) ? null : show($_POST['b_meta']))?></textarea>
    </li>
</ul>

    <input type="hidden" id="format_id" name="n_format_id" value='0'>

  <button name="create_category">Submit</button>
</form>