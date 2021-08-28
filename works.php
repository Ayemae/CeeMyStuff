<?php 
include_once 'components/info-head.php';
include 'components/header.php';
if (isset($_GET['catid'])) {
    $catID = $_GET['catid'];
} else {
    $catID = "0";
}
include_once 'library/set.inc.php';
$cat = getCatInfo($catID);
$items = getCatItems($catID);
?>

<main>

<?php if ($items) : ?>
    <ul class="works-list">
    <?php foreach ($items AS $item) : ?>
        <li id="item_<?show($item['ID']);?>" class="piece">
            <div class="piece-image-wrapper">
                <?showImage($cat['Show_Images'], $item['Img_Path'], $item['Img_Thumb_Path'], $item['Title'])?>
            </div>
            <?showTitle($cat['Show_Titles'], $item['Title'])?>
            <?showCaption($cat['Show_Captions'], $item['Caption'])?>
        </li>
    <?php endforeach;?>
    </ul>
<?php endif;?>

</main>

<?php
include 'components/footer.php';