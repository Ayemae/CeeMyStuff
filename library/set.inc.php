<?php
function showImage($setShowImg, $img=false, $thumb=false, $title=false) {
    global $set;
    if (is_array($setShowImg)) {
        $setShowImg = $setShowImg['Show_Images'];
    }
    switch ($setShowImg) {
        case 0:
            $img='';
         break;
        case 1:
            if ($thumb) {
                $img = $thumb;
            }
    }
    $img = '<img class="piece-image" src="'.$img.'" alt="'.$title.' Image">';
    echo $img;
}

function showTitle($setShowTitle, $title) {
    global $set;
    if (is_array($setShowTitle)) {
        $setShowTitle = $setShowTitle['Show_Titles'];
    }
    if ($setShowTitle) {
        $title = '<div class="piece-title">'.$title.'</div>';
    } else {
        $title = '';
    }
    echo $title;
}

function showCaption($setShowCap, $caption) {
    global $set;
    if (is_array($setShowCap)) {
        $setShowCap = $setShowCap['Show_Captions'];
    }
    switch ($setShowCap) {
        case 1:
            if (strlen($caption)>140) {
                $caption = substr($caption,0,137).'...';
            }
        case 2:
            $caption = '<div class="piece-caption">'.$caption.'</div>';
         break;
        default:
            $caption = '';
    }
    echo $caption;
}