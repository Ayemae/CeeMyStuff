<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Menu Settings';
include '_components/admin-header.inc.php';
$menu = getMenu();
?>

<main>

<h1>Site Menu Settings</h1>

<form method="post">
    <ul class="form-list">
        <?php foreach ($menu AS $option) :?>
        <li class="flex space-btwn">
            <input type="hidden" name="page_id[<?show($option['Page_ID'])?>]" value="<?show($option['Page_ID'])?>">
            <input type="hidden" name="index[<?show($option['Page_ID'])?>]" value="<?show($option['Index_Order'])?>">
            <div>
                <?show($option['Page_ID'] != NULL ? $option['Page_Name'] : $option['Link'])?>
                <?show($option['Img_Path'] ? '<img src="'.$option['Img_Path'].'" alt="">' : null)?>
                <? ?>
            </div>
            <div>
                <label>Show in Menu</label>
                <input type="hidden" id="hidden" name="n_hidden[<?show($option['Page_ID'])?>]" value="1">
                <input type="checkbox" id="hidden" name="n_hidden[<?show($option['Page_ID'])?>]" value="0" <? echo (isset($option['Hidden'])===false ? ($option['Hidden']==0 ? 'checked' : null) : null)?>>
            </div>
            <div>
                <?php if ($option['Page_ID']>0) :?>
                    <label>In Dropdown?</label>
                    <input type="hidden" id="dropdown" name="n_dropdown[<?show($option['Page_ID'])?>]" value="0">
                    <input type="checkbox" id="dropdown" name="n_dropdown[<?show($option['Page_ID'])?>]" value="1" <?(!isset($option['In_Dropdown']) ? null : show((!$option['In_Dropdown'] ? null : 'checked')))?>>
                <?php endif;?>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
<button name="save_menu">Save</button>
</form>

</main>

<?php
include '../components/footer.php';