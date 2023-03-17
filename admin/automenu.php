<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Menu Settings';
include '_components/admin-header.inc.php';
$task = false;
$type = false;
$edit = false;
if ($_GET['task'] ?? null) {
    $task=$_GET['task'];
 }
?>

<main>

<? if ($task==('add' || 'edit')) :
    if ($task=='edit' && (isset($_GET['id']) && is_numeric($_GET['id']))) {
        $edit=true;
        $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
        $mItem = getMenuItem($id);
        $type = $mItem['Type_Code'];
    } else {
        if ($_GET['type'] ?? null) {
            switch ($_GET['type']) {
                case ('link') :
                    $type = 2;
                break;
                default :
                    $type = 3;
                break;
            }
         }
    }
    include '_components/automenu-add-edit.inc.php';
else : 
    $menu = getMenu(); ?>

<h1>Site Auto-Menu Settings</h1>

<noscript>Enabling Javascript is recommended.</noscript>

<div class="js-check">
    <i class="fi fi-rs-caret-right"></i> Add to Menu: 
        <select id="select-section" name="select-section" onchange="return (this.value !='' ? window.location.replace(this.value) : null)">
            <option value="" selected>Select option below</option>
            <option value="<?=$set['dir']?>/admin/automenu.php?task=add&type=link">Custom/External Link</option>
            <option value="<?=$set['dir']?>/admin/automenu.php?task=add&type=heading">Heading/Index</option>
            <option value="<?=$set['dir']?>/admin/pages.php?task=create">New Page</option>
        </select>
</div>
<div class="js-check else"> 
    <a class="button" href="<?=$set['dir']?>/admin/automenu.php?task=add&type=link">Add External/Custom Link</a>
    <a class="button" href="<?=$set['dir']?>/admin/automenu.php?task=add&type=heading">Add Heading/Index</a>
    <a class="button" href="<?=$set['dir']?>/admin/pages.php?task=create">Add New Page</a>
</div>

<form id="menu-settings-form" method="post">
    <div class="menu-settings-table">
        <ul class="menu-settings-table-head">
            <li>Order</li>
            <li>Name</li>
            <li>Image
                <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                <article class="help-text">
                    Optional; for if the site's settings are set to display the site menu as images.
                </article></i>
            </li>
            <li>Type</li>
            <li>Submenu 
                <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                <article class="help-text">
                    Enable if you want the menu item to appear in a dropdown or index. 
                    All checked items will be listed under the last previous unchecked item.
                    <em>The first item in the menu cannot be within a submenu.</em>
                </article></i>
            </li>
            <li>Hide
                <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                    <article class="help-text">
                        Hidden menu items will not display on the live site. 
                        Additionally, hidden Pages will not display a link on the automenu.
                    </article>
                </i>
            </li>
            <li>
                <!-- Edit -->
            </li>
        </ul>
    <ul class="menu-settings-item-list">
        <?php $i=0;
        foreach ($menu AS $option) :?>
        <li class="menu-settings-item<?=(($option['In_Dropdown'] ?? null) ? ' in-drop' : null )?>">
            <input type="hidden" name="option[<?show($option['ID'])?>][n_menu_id]" value="<?show($option['ID'])?>">
            <? if ($option['Type_Code']==1) :?>
                <input type="hidden" name="option[<?show($option['ID'])?>][n_page_id]" value="<?show($option['Page_ID'])?>">
            <? endif; ?>
            <div class="mensets-order">
                <!--<i class="fi fi-rs-expand-arrows"></i>-->
                <input type="number" class="menu-item-order" name="option[<?show($option['ID'])?>][n_index]" value="<?show($option['Index_Order'])?>">
            </div>
            <div class="mensets-name">
                <?if ($option['Type_Code']==1) :?>
                        <a href="<?=$set['dir']?>/admin/pages.php?task=edit&id=<?=$option['Page_ID']?>"><?=$option['Page_Name']?></a>
                <? else : ?>
                        <?=$option['Link_Text']?>
                <? endif;?>
                <input type="hidden" name="option[<?show($option['ID'])?>][name]" value="<?show($option['Type_Code']==1 ? $option['Page_Name'] : $option['Link_Text'])?>">
                <input type="hidden" name="option[<?show($option['ID'])?>][url]" value="<?show($option['Ext_Url'])?>">
            </div>
            <div class="mensets-image">
                <?show($option['Img_Path'] ? '<img src="'.$set['dir'].$option['Img_Path'].'" alt="">' : "n/a")?>
            </div>
            <div class="mensets-type">
                <?show($option['Link_Type'])?>
            </div>
            <div  class="mensets-dropdown">
                <?php if ($i>0) :?>
                    <input type="checkbox" id="dropdown" name="option[<?show($option['ID'])?>][n_dropdown]" value="1" <?(!isset($option['In_Dropdown']) ? null : show((!$option['In_Dropdown'] ? null : 'checked')))?>>
                <?php else:?>
                    <input type="checkbox" title="The first link in the menu cannot be in a dropdown." name="option[<?show($option['ID'])?>][n_dropdown]" value="1" disabled>
                <?php endif;?>
            </div>
            <div class="mensets-hidden">
                <input type="hidden" name="option[<?show($option['ID'])?>][n_page_hidden]" value="<?=$option['Page_Hidden']?>">
                <input type="hidden" name="option[<?show($option['ID'])?>][n_hidden]" value="0">
                <input type="checkbox" id="hidden" name="option[<?show($option['ID'])?>][n_hidden]" value="1" <?=(($option['Hidden'] ?? null) ? ($option['Hidden']==1 ? 'checked' : null) : (($option['Page_Hidden'] ?? null) !==1 ? null : 'checked'))?>>
            </div>
            <div class="mensets-edit">
            <? switch ($option['Type_Code']) {
                case 1 :
                    echo '<a href="'.$set['dir'].'/admin/pages.php?task=edit&id='.$option['Page_ID'].'">Edit</a>';
                break;
                case 2 :
                    echo '<a href="'.$set['dir'].'/admin/automenu.php?task=edit&id='.$option['ID'].'">Edit</a>';
                break;
                default: 
                    echo '<a href="'.$set['dir'].'/admin/automenu.php?task=edit&id='.$option['ID'].'">Edit</a>';
                break;
            }?>
                
            </div>
        </li>
        <?php $i++; endforeach; ?>
    </ul>
    </div>
<button name="save_menu">Save</button>
</form>
<? endif;?>

</main>

<?php
include '_components/admin-footer.php';