<h1>Edit Page Settings</h1>

<form method="post" enctype="multipart/form-data">
<input type="hidden" name="n_page_id" value="<?show($pageID);?>">
<ul class="form-list">
    <li>
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" max-length="255" value="<?show($page['Name'])?>">
        <br/>
        <label for="n_show_title">Show page name on the website:</label>
        <input type="hidden" name="n_show_title" value="0">
        <input type="checkbox" name="n_show_title" id="n_show_title" value="1" <?=($page['Show_Title'] ? 'checked' : null)?>>
    </li>

    <li>
    <label for="header_img_upload">Header Image (Optional):</label>
        <input type="file" id="header_img_upload" name="header_img_upload" value="<?(!isset($_POST['header_img_upload']) ? null : show($_POST['header_img_upload']))?>">
        <input type="hidden" name="stored_header_img" value="<?show($page['Header_Img_Path']);?>">
        <br/>
        <label for="n_show_title">Show header image on the website:</label>
        <input type="hidden" name="n_show_header_img" value="0">
        <input type="checkbox" name="n_show_header_img" id="n_show_header_img" value="1" <?=($page['Show_Header_Img'] ? 'checked' : null)?>>
    </li>

    <li>
        <label for="meta">Description:</label><br/>
        <textarea id="meta" name="meta_text" max-length="255"><?show($page['Meta_Text'])?></textarea>
    </li>

    <li>
        <label for="n_multi_cat">Enable Multiple Content Categories:</label>
        <input type="hidden" name="n_multi_cat" value="0">
        <input type="checkbox" name="n_multi_cat" id="n_multi_cat" class="chktoggle" value="1" <?=($page['Multi_Cat'] ? 'checked' : null)?>>
        <input type="hidden" name="n_paginate" value="0">
        <input type="hidden" name="n_paginate_after" value="20">
        <ul class="chktoggle-hide form-list">
            <li>
                <label for="n_paginate">Allow Pagination:</label>
                <input type="checkbox" name="n_paginate" id="n_paginate" class="chktoggle" value="1" <?=($page['Paginate'] ? 'checked' : null)?>>
                <div class="chktoggle-show">
                    <label for="n_paginate_after">Items Per Page:</label>
                    <input type="number" name="n_paginate_after" id="n_paginate_after" value="<?show($page['Paginate_After']);?>" style="width:50px">
                </div>
            </li>
        </ul>
    </li>

    <li>
        <label for="header_img_upload">Menu Link Image (Optional):</label>
        <input type="file" id="menu_img_upload" name="menu_img_upload" value="<?(!isset($_POST['menu_img_upload']) ? null : show($_POST['menu_img_upload']))?>">
        <input type="hidden" name="stored_menu_img" value="<?show($page['Menu_Link_Img']);?>">
    </li>

    <li>
            <label for="hidden">Hide this page:</label>
            <input type="hidden" id="hidden" name="n_hidden" value="0">
            <input type="checkbox" id="hidden" name="n_hidden" value="1" <?=($page['Hidden'] ? 'checked' : null)?>>
    </li>
</ul>

    <input type="hidden" id="format" name="format" value=''>

  <button name="edit_page">Submit</button>
</form>