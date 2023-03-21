<?$_SESSION['MenuItemType']=$type;?>
<form id="menu-add-edit" method="post" enctype="multipart/form-data" action="<?=$set['dir']?>/admin/automenu.php">
    <div class="space-btwn">
        <h2>Auto-Menu: <?=(!$edit ? 'Add' : 'Edit')?> <?=$mItem['Link_Type']?></h2>
        <button id="delete-menu-item" name="delete_menu_item" class="small red" on-click="return false;">
            <i class="fi fi-rs-trash"></i> Delete <?=$mItem['Link_Type']?>
        </button>
    </div>

    <ul class="form-list">
        <? if ($edit) :?>
            <input type="hidden" name="n_menu_id" value="<?=$mItem['ID']?>">
            <input type="hidden" name="n_type_code" value="<?=$mItem['Type_Code']?>">
        <? endif;?>
        <li>
            <label for="link-text"><?=$mItem['Link_Type']?> Text/Name:</label>
            <input type="text" id="link-text" maxlength="100" name="link_text" autocomplete="off" value="<?=($edit ? $mItem['Link_Text'] : '')?>">
        </li>

        <? if ($type==8) :?>
        <li>
            <label for="ext-url">Link URL:</label>
            <p>If this is an external URL (as in, not from this site) make sure that you 
                include the 'http://' or 'https://' in front!</p>
            <input type="text" id="ext-url" maxlength="100" name="ext_url" autocomplete="off" value=<?=($edit ? $mItem['Ext_Url'] : '')?>>
        </li>
        <? endif;?>

        <li>
            <label for="link-img">Image (optional):</label>
            <p>Must be a .png, .jpg, .gif, or .webp file.</p>
            <input type="file" id="link-img" name="link_img">
            <? if ($edit && $mItem['Img_Path']>'') :?>
                <input type="hidden" id="link_img_stored" name="link_img_stored" value="<?=$mItem['Img_Path']?>">
                <label class="block">Current:</label>
                <div class="link_img_current">
                    <img src="<?=$set['dir'].$mItem['Img_Path']?>" alt="Preview of the current image for this link"/>
                </div>
                <button type="button" class="small red" onclick="rmvFilePath(this, 'link_img_stored', 'link_img_current')">Remove Current Image</button>
            <? endif;?>
        </li>
    </ul>
    <button <?=($edit ? 'name="menu_edit_item"' : 'name="menu_add_item"')?>>Submit</button>
    <div id="modal-home"></div>
</form>
    
<? if ($edit) :?>
<script src="_js/modal.js"></script>
<script>
let modalHTML = `<h2>Are you sure you want to delete this <?=($type==8 ? 'link' : 'heading')?>?</h2>
                <p>This cannot be undone.</p>
                <div class="flex">
                <button type="submit" class="button red" name="delete_menu_item">Yes, delete this <?=($type==8 ? 'link' : 'heading')?></button>
                <button type="button" class="button modal-close">Never mind</button>
                </div>`;
const modalMItemDelete = new Modal('modal-menu-item-delete', modalHTML, false, false);
modalMItemDelete.appendToForm('modal-home');

document.getElementById('delete-menu-item').addEventListener('click', function(e) {
    e.preventDefault();
    modalMItemDelete.trigger();
}, false);
</script>
    <? if ($mItem['Img_Path']>'') : ?>
        <script src="_js/rmv-file-paths.js"></script>
    <?endif;?>
<? endif;?>