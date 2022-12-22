<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Social Media';
include '_components/admin-header.inc.php';
$socials = getSocials();

if (isset($_GET['task'])) {
    $task = $_GET['task'];
} else {
    $task = false;
}
if (isset($_GET['id'])) {
    $smID = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
} else {
    $smID = 0;
}
?>


<main>

<h1>Social Media Links</h1>

<noscript>Enabling Javascript is recommended for best performance.</noscript>

<form id="social-media-form" method="post">
    <div class="social-media-table">
        <ul class="social-media-table-head">
            <li>Order</li>
            <li>Platform</li>
            <li>URL</li>
            <li>Icon Image</li>
            <li>Display</li>
        </ul>
    <ul class="menu-settings-item-list">
        <?php foreach ($menu AS $option) :?>
        <li class="menu-settings-item">
            <input type="hidden" name="option[<?show($option['Page_ID'])?>][n_page_id]" value="<?show($option['Page_ID'])?>">
            <div>
                <!--<i class="fi fi-rs-expand-arrows"></i>-->
                <input type="number" class="menu-item-order" name="option[<?show($option['Page_ID'])?>][n_index]" value="<?show($option['Index_Order'])?>">
            </div>
            <div>
                <?show($option['External_Link'] < 1 ? $option['Page_Name'] : $option['Ext_Link_Name'])?>
                <?show($option['Img_Path'] ? '<img src="'.$option['Img_Path'].'" alt="">' : null)?>
                <input type="hidden" name="option[<?show($option['Page_ID'])?>][link]" value="<?show($option['Ext_Url'])?>">
                <!-- link to edit? -->
            </div>
            <div>
                <?php if ($option['Page_ID']>0) :?>
                    <input type="hidden" name="option[<?show($option['Page_ID'])?>][n_dropdown]" value="0">
                    <input type="checkbox" id="dropdown" name="option[<?show($option['Page_ID'])?>][n_dropdown]" value="1" <?(!isset($option['In_Dropdown']) ? null : show((!$option['In_Dropdown'] ? null : 'checked')))?>>
                <?php else:?>
                    <input type="checkbox" title="Home pages cannot be in a dropdown." disabled>
                <?php endif;?>
            </div>
            <div>
                <input type="hidden" name="option[<?show($option['Page_ID'])?>][n_hidden]" value="0">
                <input type="checkbox" id="hidden" name="option[<?show($option['Page_ID'])?>][n_hidden]" value="1" <? echo (isset($option['Hidden'])===true ? ($option['Hidden']==1 ? 'checked' : null) : null)?>>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
    </div>
<button name="save_menu">Save</button>
</form>

</main>

<?php
include '_components/admin-footer.php';