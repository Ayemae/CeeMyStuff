<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Test';
include '_components/admin-header.inc.php';
$imports = getImportDirs();
$sectList = getSectList(false, true);
?>

<main>
    <? foreach ($imports AS $folder) :?>
        <form id="import-<?=$folder?>" method="post">
        <input type="hidden" name="folder" value="<?=$folder?>">
        <select id="sect-id" name="n_sect_id">
            <option value="0">None (create orphaned items)</option>
                <? if (!empty($sectList)) :
                foreach ($sectList AS $sect) : ?>
                <option value="<?=$sect['ID']?>"><?=$sect['Name']?></option>
                <? endforeach; endif; ?>
            </select>
            <button type="submit" name="create_multi_items">Create Items from '<?=$folder?>'</button>
        </form>
    <? endforeach;?>

</main>

<?php
include '_components/admin-footer.php';