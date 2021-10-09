<?php
if (isset($cat) && isset($item)) {
    $id = $item['ID'];
    $title = showTitle($cat['Show_Item_Titles'], $item['Title']);
    $text = showTitle($cat['Show_Item_Titles'], $item['Title']);
    $image = showImage($cat['Show_Item_Images'], $item['Img_Path'], $item['Img_Thumb_Path'], $item['Title']);
    $srcImgFull = $set['dir'].$item['Img_Path'];
    $srcImgThumb = $set['dir'].$item['Img_Thumb_Path'];
}