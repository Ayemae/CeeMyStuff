<form id="menu-add-edit" method="post" enctype="multipart/form-data" action="<?=$set['dir']?>/admin/automenu.php">
    <div class="space-btwn">
        <h2>Auto-Menu: <?=(!$edit ? 'Add' : 'Edit')?> <?=($type==2 ? 'Custom/External Link' : 'Heading')?></h2>
        <button name="delete_menu_item" id="delete-menu_item" class="small red">
            <i class="fi fi-rs-trash"></i> Delete <?=($type==2 ? 'Link' : 'Heading')?>
        </button>
    </div>

    <ul class="form-list">
        <? if ($edit) :?>
            <input type="hidden" name="menu_id" value="<?=$mItem['ID']?>">
            <input type="hidden" name="type_code" value="<?=$mItem['Type_Code']?>">
        <? endif;?>
        <li>
            <label for="link-text"><?=($type==2 ? 'Link' : 'Heading')?> Text/Name:</label>
            <input type="text" id="link-text" maxlength="100" name="link_text" autocomplete="off" value=<?=($edit ? $mItem['Link_Text'] : '')?>>
        </li>

        <? if ($type==2) :?>
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
                <button type="button" class="small red" onclick="rmvFilePath('link_img_stored', 'link_img_current')">Remove Current Image</button>
            <? endif;?>
        </li>
    </ul>
    <button <?=($edit ? 'name="menu_edit_item"' : 'name="menu_add_item"')?>>Submit</button>
</form>
    
<? if ($edit && $mItem['Img_Path']>'') :?>
<script src="_js/rmvFilePaths.js"></script>
<? endif;?>