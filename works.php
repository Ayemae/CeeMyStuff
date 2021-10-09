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
    <section class="content-category">
    <?php foreach ($items AS $item) : ?>
        <div id="item_<?show($item['ID']);?>" class="item">
            <div class="item-image-wrapper">
                <?showImage($cat['Show_Images'], $item['Img_Path'], $item['Img_Thumb_Path'], $item['Title'])?>
            </div>
            <?showTitle($cat['Show_Titles'], $item['Title'])?>
            <?showText($cat['Show_Text'], $item['Text'])?>
        </div>
    <?php endforeach;?>
    </section>
<?php endif;?>

</main>

<?php
include 'components/footer.php';