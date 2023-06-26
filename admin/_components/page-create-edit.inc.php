<? if ($edit && ($page['Header_Img_Path'] ?? null)>'') {
    $imgExists = true;
} else {
    $imgExists =false;
}?>

<form id="page-form" method="post" enctype="multipart/form-data">
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
        <input type="text" name="name" id="name" max-length="255" value="<?show($edit ? $page['Name'] : null)?>"  autocomplete="off" required>
            <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                <article class="help-text">
                    Your Page's link will be derived from its name. <em>Every Page must have a unique name.</em>
                </article>
            </i>
        <? if ($edit) :?>
            <input type="hidden" name="name_stored" value="<?show($page['Name'])?>">
        <? endif;?>
        <br/>
        <label for="n_show_title">Show page name on the website:</label>
        <input type="hidden" name="n_show_title" value="0">
        <input type="checkbox" name="n_show_title" id="n_show_title" value="1" <?=(($edit && $page['Show_Title']) || $create ? 'checked' : null)?>>
    </li>

    <li>
    <label for="header_img_upload">Header Image (Optional):</label>
        <input type="file" id="header-img-upload" name="header_img_upload" onchange="previewImg('header-img-upload', 'header-img')" value="<?(!isset($_POST['header_img_upload']) ? null : show($_POST['header_img_upload']))?>">
        <input type="hidden" id="stored-header-img" name="stored_header_img" value="<?show($edit ? $page['Header_Img_Path'] : null);?>">
            <div id="header-img-current" class="page-current-image-wrapper">
                <label>Current:</label> 
                    <img id="header-img-visual" class="visual<?=($imgExists ? ' block' : ' invis')?>" src="<?=$set['dir'].$page['Header_Img_Path']?>">
                    <input type="hidden" id="header-img-preview" name="header_img_preview" value="">
                    <div id="header-img-rmv-info" class="rvm-file-path-info invis">&#10060; File Removed</div>
                    <button id="header-img-rmv-btn" type="button" class="small red <?=($imgExists ? null : 'invis')?>" onclick="rmvFilePath(this, 'stored_header_img', 'header-img-current')">Remove Current Image</button>
                    <em id="header-img-none" class="<?=(!$imgExists ? null : 'invis')?>">none</em>
            </div>
        <label for="n_show_title">Show header image on the website:</label>
        <input type="hidden" name="n_show_header_img" value="0">
        <input type="checkbox" name="n_show_header_img" id="n_show_header_img" value="1" <?=(($edit && $page['Show_Header_Img']) || $create ? 'checked' : null)?>>
    </li>

    <li>
        <div>
            <label for="meta">Meta Description:</label>
            <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                <article class="help-text">
                    A description of your page that will show up in search engines or external thumbnails. 
                    Normally, <em>this will not be visible on the site itself.</em>
                </article>
            </i>
        </div>
        <input type="text" id="meta" name="meta_text" max-length="255" value="<?show($edit ? $page['Meta_Text'] : null)?>" style="width: 80%"  autocomplete="off">
    </li>

    <li>
        <label for="n_multi_sect">Enable Multiple Content Sections:</label>
        <input type="hidden" name="n_multi_sect" value="0">
        <input type="checkbox" name="n_multi_sect" id="n_multi_sect" class="chktoggle" value="1" <?=($edit && $page['Multi_Sect'] ? 'checked' : null)?>>
            <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                <article class="help-text">
                    This will allow you to add more than one content Section to this page. 
                    Keep in mind that <em>pagination is only available on pages with a single content section.</em>
                </article>
            </i>
        <input type="hidden" name="n_paginate" value="0">
        <input type="hidden" name="n_paginate_after" value="20">
        <ul class="chktoggle-hide form-list">
            <li>
                <label for="n_paginate">Allow Pagination (single-section pages only):</label>
                <input type="checkbox" name="n_paginate" id="n_paginate" class="chktoggle" value="1" <?=($edit && $page['Paginate'] ? 'checked' : null)?>>
                <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                    <article class="help-text">
                        'Pagination' will allow this Page to contain an array of pages that you can navigate between,
                        which can be useful if your Page/content Section has a lot of Items or material that may involve a lot of scrolling.
                    </article>
                </i>
                <div class="chktoggle-show">
                    <label for="n_paginate_after">Items Per Page:</label>
                    <input type="number" name="n_paginate_after" id="n_paginate_after" value="<?show($edit && $page['Paginate_After'] ? $page['Paginate_After'] : 15);?>" style="width:50px">
                </div>
            </li>
        </ul>
    </li>

    <?php if ($formatList) :?>
    <li>
        <label for="format">Display Format:</label>
        <select name="format" id="format">
            <?php foreach ($formatList AS $format) :?>
            <option value="<?show($format['Path'])?>" <?=($edit && $page['Format']===$format['Path'] ? 'selected' : null)?>>
                <?show($format['From'])?> > <?show($format['Name'])?>
            </option>
            <?php endforeach;?>
        </select>
    </li>
    <?php endif;?>

    <li>
        <div>
            <label for="menu_img_upload">Menu Link Image (Optional):</label>
            <i class="help icon"><i class="fi fi-rs-interrogation"></i>
            <article class="help-text">
                If the settings are set to display the Automenu links as images, you can upload your page's link image here.
            </article>
        </i>
        </div>
        <input type="file" id="menu_img_upload" name="menu_img_upload" value="<?(!isset($_POST['menu_img_upload']) ? null : show($_POST['menu_img_upload']))?>">
        <?if ($edit && isset($page['Menu_Link_Img']) && $page['Menu_Link_Img']>''):?>
            <div id="menu-img-current">
                Current: <img class="visual" src="<?=$set['dir'].$page['Menu_Link_Img']?>">
                <div class="rvm-file-path-info invis">&#10060; File Removed</div>
            </div>
            <input type="hidden" id="rmv_menu_img" name="n_rmv_menu_img" value="0">
            <button type="button" class="small red" onclick="rmvFilePath(this, 'rmv_menu_img', 'menu-img-current', 1)">Remove Current Image</button>
        <?endif;?>
    </li>

    <li>
            <label for="hidden">Hide this page:</label>
            <? if ($edit) : ?>
                <input type="hidden" id="hidden" name="n_menu_hidden" value="<?show($page['Menu_Hidden'])?>">
            <? endif?>
            <input type="hidden" id="hidden" name="n_hidden" value="0">
            <input type="checkbox" id="hidden" name="n_hidden" value="1" <?=($edit && $page['Hidden'] ? 'checked' : null)?>>
            <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                <article class="help-text">
                    Hidden Pages will not display on the live site. 
                    Additionally, hidden Pages will not display a link on the Automenu.
                </article>
            </i>
    </li>
</ul>

<? if ($edit && $page['Multi_Sect']) :?>
    <h2>Sections</h2>
    <p>Section order editing is available on multi-section pages only.</p>
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
        <a class="button small" href="<?=$set['dir']?>/admin/sections.php?task=create&pageid=<?=$page['ID']?>"><i class="fi fi-rs-plus"></i> Add New Section</a>
    </ul>
<? endif;?>

<div class="space-btwn">
    <button id="page-form-submit" name="<?=($create ? "create_page" : "edit_page")?>" formaction="?task=list" onclick="addTarget('_self')">
        <i class="fi fi-rs-check"></i> Submit
    </button>
    <button id="page-preview" class="js-check" name="page_preview" formaction="<?=$baseURL?>/preview/page" onclick="addTarget('_blank')">
        Preview
    </button>
</div>
  <div id="modal-home"></div>
</form>

<script src="_js/preview-img.js"></script>
<script>
    const form = document.getElementById('page-form');
    function addTarget(target) {
        form.target= target;
    }
</script>
<? if ($edit) :?>
<script src="_js/enumerate.js"></script>
<script src="_js/modal.js"></script>
<script src="_js/rmv-file-paths.js"></script>
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