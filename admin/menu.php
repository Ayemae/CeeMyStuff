<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Menu Settings';
include '_components/admin-header.inc.php';
//$menu = fetchMenu();
?>

<main>

<h1>Site Menu Settings</h1>

<form method="post">
    <ul class="form-list">
        <?php foreach ($menu AS $option) :?>
        <li class="flex">
            <input type="hidden" name="id[<?show($option['ID'])?>]" value="<?show($option['ID'])?>">
            <input type="hidden" name="page_id[<?show($option['ID'])?>]" value="<?show($option['Page_ID'])?>">
            <input type="hidden" name="index[<?show($option['ID'])?>]" value="<?show($option['Index_Order'])?>">
            <div>
                <?show($option['Page_ID'] ? $option['Page_Name'] : $option['Link_Name'])?>
                <?show($option['Img_Path'] ? '<img src="'.$option['Img_Path'].'" alt="">' : null)?>
            </div>
            <div>
                <input type="hidden" id="dropdown" name="n_dropdown[<?show($option['ID'])?>]" value="0">
                <input type="checkbox" id="dropdown" name="n_dropdown[<?show($option['ID'])?>]" value="1" <?(!isset($option['In_Dropdown']) ? null : show((!$option['In_Dropdown'] ? null : 'checked')))?>>
            </div>
            <div>
                <input type="hidden" id="hidden" name="n_hidden[<?show($option['ID'])?>]" value="0">
                <input type="checkbox" id="hidden" name="n_hidden[<?show($option['ID'])?>]" value="1" <?(!isset($option['Hidden']) ? null : show((!$option['Hidden'] ? null : 'checked')))?>>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
<button name="save_menu">Save</button>
</form>

</main>

<?php
include '../components/footer.php';