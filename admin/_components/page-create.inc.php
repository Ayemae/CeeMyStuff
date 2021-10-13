<form method="post" enctype="multipart/form-data" action="?task=list">
<h1>Create New Page</h1>

<ul class="form-list">
    <li>
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" max-length="255" value="<?(!isset($_POST['name']) ? null : show($_POST['name']))?>">
        <br/>
        <label for="n_show_title">Show page name on the website:</label>
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
        <label for="meta">Description:</label><br/>
        <textarea id="meta" name="meta_text" max-length="255"><?(!isset($_POST['meta_text']) ? null : show($_POST['meta_text']))?></textarea>
    </li>

    <li>
        <label for="n_multi_cat">Allow Sub-Categories:</label>
        <input type="hidden" name="n_multi_cat" value="0">
        <input type="checkbox" name="n_multi_cat" id="n_multi_cat" class="chktoggle" value="1" <?=(!isset($_POST['n_multi_cat']) && $_POST['n_multi_cat']>0 ? 'checked' : null)?>>
        <input type="hidden" name="n_paginate" value="0">
        <input type="hidden" name="n_paginate_after" value="20">
        <ul class="chktoggle-hide form-list">
            <li>
                <label for="n_paginate">Allow Pagination:</label>
                <input type="checkbox" name="n_paginate" id="n_paginate" class="chktoggle" value="1" <?=(!isset($_POST['n_paginate']) && $_POST['n_paginate']>0 ? 'checked' : null)?>>
                <div class="chktoggle-show">
                    <label for="n_paginate_after">Items Per Page:</label>
                    <input type="number" name="n_paginate_after" id="n_paginate_after" value="<?=(isset($_POST['n_paginate_after']) ? $_POST['n_paginate_after'] : 20)?>" style="width:50px">
                </div>
            </li>
        </ul>
    </li>

    <?php if ($formatList) :?>
    <li>
        <label for="format">Display Format:</label>
        <select name="format" id="format">
            <?php foreach ($formatList AS $format) :?>
            <option value="<?show($format)?>">
                <?show($format)?>
            </option>
            <?php endforeach;?>
        </select>
    </li>
    <?php endif;?>

    <li>
        <label for="header_img_upload">Menu Link Image (Optional):</label>
        <input type="file" id="menu_img_upload" name="menu_img_upload" value="<?(!isset($_POST['menu_img_upload']) ? null : show($_POST['menu_img_upload']))?>">
    </li>

    <li>
            <label for="hidden"> Hide this page:</label>
            <input type="hidden" id="hidden" name="n_hidden" value="0">
            <input type="checkbox" id="hidden" name="n_hidden" value="1" <?(!isset($_POST['n_hidden']) ? null : show((!$_POST['n_hidden'] ? null : 'checked')))?>>
    </li>
</ul>

  <button name="create_page"><i class="fi fi-rs-check"></i> Submit</button>
</form>