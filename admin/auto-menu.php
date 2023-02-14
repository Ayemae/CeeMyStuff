<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Menu Settings';
include '_components/admin-header.inc.php';
$menu = getMenu();
?>

<main>

<h1>Site Auto-Menu Settings</h1>

<noscript>Enabling Javascript is recommended for best performance.</noscript>

<form id="menu-settings-form" method="post">
    <div class="menu-settings-table">
        <ul class="menu-settings-table-head">
            <li>Order</li>
            <li>Name</li>
            <li>Image</li>
            <li>In Dropdown</li>
            <li>Hidden</li>
        </ul>
    <ul class="menu-settings-item-list">
        <?php $i=0;
        foreach ($menu AS $option) :?>
        <li class="menu-settings-item">
            <input type="hidden" name="option[<?show($option['Page_ID'])?>][n_page_id]" value="<?show($option['Page_ID'])?>">
            <div class="mensets-order">
                <!--<i class="fi fi-rs-expand-arrows"></i>-->
                <input type="number" class="menu-item-order" name="option[<?show($option['Page_ID'])?>][n_index]" value="<?show($option['Index_Order'])?>">
            </div>
            <div class="mensets-name">
                <?show($option['External_Link'] < 1 ? $option['Page_Name'] : $option['Ext_Link_Name'])?>
                <input type="hidden" name="option[<?show($option['Page_ID'])?>][link]" value="<?show($option['Ext_Url'])?>">
            </div>
            <div class="mensets-image">
                <?show($option['Img_Path'] ? '<img src="'.$set['dir'].$option['Img_Path'].'" alt="">' : "n/a")?>
                <!-- link to edit? -->
            </div>
            <div  class="mensets-dropdown">
                <?php if ($i>0) :?>
                    <input type="checkbox" id="dropdown" name="option[<?show($option['Page_ID'])?>][n_dropdown]" value="1" <?(!isset($option['In_Dropdown']) ? null : show((!$option['In_Dropdown'] ? null : 'checked')))?>>
                <?php else:?>
                    <input type="checkbox" title="The first link in the menu cannot be in a dropdown." disabled>
                <?php endif;?>
            </div>
            <div class="mensets-hidden">
                <input type="hidden" name="option[<?show($option['Page_ID'])?>][n_hidden]" value="0">
                <input type="checkbox" id="hidden" name="option[<?show($option['Page_ID'])?>][n_hidden]" value="1" <? echo (isset($option['Hidden'])===true ? ($option['Hidden']==1 ? 'checked' : null) : null)?>>
            </div>
        </li>
        <?php $i++; endforeach; ?>
    </ul>
    </div>
<button name="save_menu">Save</button>
</form>

</main>

<?php
include '_components/admin-footer.php';