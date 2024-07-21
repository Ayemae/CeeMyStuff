<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Test';
include '_components/admin-header.inc.php';
$sectList = getSectList(false, true, true);
?>

<main>
    <? foreach ($sectList AS $sect) :?>
        <form id="bulk-<?=$sect['ID']?>" method="post">
        <input type="hidden" name="n_sect_id" value="<?=$sect['ID']?>">
        <input type="hidden" name="n_thumb_size" value="<?=$sect['Thumb_Size']?>">
        <input type="hidden" name="n_thumb_axis" value="<?=$sect['Thumb_Size_Axis']?>">
        <button type="submit" name="bulk_create_thumbs">Create Thumbs for '<?=$sect['Name']?>'</button>
        </form>
    <? endforeach;?>

</main>

<?php
include '_components/admin-footer.php';