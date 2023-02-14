<form method="post" enctype="multipart/form-data" action="?task=list">
<div class="space-btwn">
    <h1><?=($create ? "Create New Page" : "Edit Page Settings : ".$page['Name'])?></h1>
    <? if ($edit) : ?>
    <input type="hidden" name="n_page_id" value="<?show($pageID);?>">
    <button name="delete_page" id="delete-page" class="small red"><i class="fi fi-rs-trash"></i> Delete Page</button>
    <? endif;?>
</div>

<ul class="form-list">
    <li>
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" max-length="255" value="<?show($edit ? $page['Name'] : null)?>">
        <br/>
        <label for="n_show_title">Show page name on the website:</label>
        <input type="hidden" name="n_show_title" value="0">
        <input type="checkbox" name="n_show_title" id="n_show_title" value="1" <?=(($edit && $page['Show_Title']) || $create ? 'checked' : null)?>>
    </li>

    <li>
    <label for="header_img_upload">Header Image (Optional):</label>
        <input type="file" id="header_img_upload" name="header_img_upload" value="<?(!isset($_POST['header_img_upload']) ? null : show($_POST['header_img_upload']))?>">
        <input type="hidden" id="stored_header_img" name="stored_header_img" value="<?show($edit ? $page['Header_Img_Path'] : null);?>">
        <?if ($edit && isset($page['Header_Img_Path']) && $page['Header_Img_Path']>''):?>
            <div id="header_img_current" class="page-current-image-wrapper">
                Current:<br/> <img src="<?=$page['Header_Img_Path']?>">
            </div>
            <button type="button" class="small red" onclick="rmvFilePath('stored_header_img', 'header_img_current')">Remove Current Image</button>
        <?endif;?>
        <br/>
        <label for="n_show_title">Show header image on the website:</label>
        <input type="hidden" name="n_show_header_img" value="0">
        <input type="checkbox" name="n_show_header_img" id="n_show_header_img" value="1" <?=(($edit && $page['Show_Header_Img']) || $create ? 'checked' : null)?>>
    </li>

    <li>
        <label for="meta">Meta Description:</label><br/>
        <p>A description of your page that will show up in search engines or external thumbnails (this will not be visible on the site itself).</p>
        <input type="text" id="meta" name="meta_text" max-length="255" value="<?show($edit ? $page['Meta_Text'] : null)?>" style="width: 80%">
    </li>

    <li>
        <label for="n_multi_sect">Enable Multiple Content Sections:</label>
        <input type="hidden" name="n_multi_sect" value="0">
        <input type="checkbox" name="n_multi_sect" id="n_multi_sect" class="chktoggle" value="1" <?=($edit && $page['Multi_Sect'] ? 'checked' : null)?>>
        <input type="hidden" name="n_paginate" value="0">
        <input type="hidden" name="n_paginate_after" value="20">
        <ul class="chktoggle-hide form-list">
            <li>
                <label for="n_paginate">Allow Pagination (single-section pages only):</label>
                <input type="checkbox" name="n_paginate" id="n_paginate" class="chktoggle" value="1" <?=($edit && $page['Paginate'] ? 'checked' : null)?>>
                <div class="chktoggle-show">
                    <label for="n_paginate_after">Items Per Page:</label>
                    <input type="number" name="n_paginate_after" id="n_paginate_after" value="<?show($edit && $page['Paginate_After']);?>" style="width:50px">
                </div>
            </li>
        </ul>
    </li>

    <?php if ($formatList) :?>
    <li>
        <label for="format">Display Format:</label>
        <select name="format" id="format">
            <?php foreach ($formatList AS $format) :?>
            <option value="<?show($format['Path'])?>" <?($edit ? formCmp($page['Format'],$format['Path'],'s') : null)?>>
                <?show($format['Name'])?>
            </option>
            <?php endforeach;?>
        </select>
    </li>
    <?php endif;?>

    <li>
        <label for="menu_img_upload">Menu Link Image (Optional):</label>
        <input type="file" id="menu_img_upload" name="menu_img_upload" value="<?(!isset($_POST['menu_img_upload']) ? null : show($_POST['menu_img_upload']))?>">
        <?if ($edit && isset($page['Menu_Link_Img']) && $page['Menu_Link_Img']>''):?>
            <div>Current: <img id="menu_img_current" src="<?=$set['dir'].$page['Menu_Link_Img']?>"></div>
            <input type="hidden" id="rmv_menu_img" name="n_rmv_menu_img" value="0">
            <button type="button" class="small red" onclick="rmvFilePath('rmv_menu_img', 'menu_img_current', 1)">Remove Current Image</button>
        <?endif;?>
    </li>

    <li>
            <label for="hidden">Hide this page:</label>
            <input type="hidden" id="hidden" name="n_hidden" value="0">
            <input type="checkbox" id="hidden" name="n_hidden" value="1" <?=($edit && $page['Hidden'] ? 'checked' : null)?>>
    </li>
</ul>

<? if ($edit && $page['Multi_Sect']) :?>
    <h2>Section Order</h2>
    <p>For multi-section pages only.</p>
    <ul class="form-list">
        <? foreach ($sectList AS $sect) :?>
    <li class="menu-settings-item">
            <input type="hidden" name="sect[<?show($sect['ID'])?>][n_sect_id]" value="<?show($sect['ID'])?>">
            <div>
                <!--<i class="fi fi-rs-expand-arrows"></i>-->
                <input type="number" class="menu-item-order" name="sect[<?show($sect['ID'])?>][n_index]" value="<?show($sect['Page_Index_Order'])?>" style="width:3em;">
                <?=$sect['Name']?>
            </div>
        </li>
        <? endforeach;?>
        <a class="button small" href="<?=$set['dir']?>/admin//sections.php?task=create&pageid=<?=$page['ID']?>"><i class="fi fi-rs-plus"></i> Add New Section</a>
    </ul>
<? endif;?>

  <button name="<?=($create ? "create_page" : "edit_page")?>"><i class="fi fi-rs-check"></i> Submit</button>
  <div id="modal-home"></div>
</form>

<? if ($edit) :?>
<script src="_js/enumerate.js"></script>
<script src="_js/modal.js"></script>
<script src="_js/rmvFilePaths.js"></script>
<script>
enumerate('menu-item-order', 'class');
let modalHTML = `<h2>Are you sure you want to delete the '<?=$page['Name']?>' page?</h2>
                <p>This cannot be undone.</p>
                <div class="flex">
                <button type="submit" class="button red" name="delete_page"/>Yes, delete this page</button>
                <button class="button modal-close" onclick="event.preventDefault()"/>Never mind</button>
                </div>`;
const modalPageDelete = new Modal('modal-page-delete', modalHTML, false, false);
modalPageDelete.appendToForm('modal-home');

document.getElementById('delete-page').addEventListener('click', function(e) {
    e.preventDefault();
    modalPageDelete.trigger();
}, false);

</script>
<? endif;?>