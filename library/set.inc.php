<?php

function showTitle($setShowTitle, $title) {
    global $set;
    if (is_array($setShowTitle)) {
        $setShowTitle = $setShowTitle['Show_Titles'];
    }
    if ($setShowTitle) {
        $title = '<div class="item-title">'.$title.'</div>';
    } else {
        $title = '';
    }
    echo $title;
}

function showText($setShowText, $text) {
    global $set;
    if (is_array($setShowText)) {
        $setShowText = $setShowText['Show_Text'];
    }
    switch ($setShowText) {
        case 1:
            $text=truncateTxt($text);
        case 2:
            $text = '<div class="item-text">'.$text.'</div>';
         break;
        default:
            $text = '';
    }
    echo $text;
}

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
    $img = '<img class="item-image" src="'.$img.'" alt="'.$title.' Image">';
    echo $img;
}
