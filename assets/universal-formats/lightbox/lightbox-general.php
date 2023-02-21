<div id="lb_item_<?show($id);?>" class="lb_item lb_<?show($class);?>">
    <?show($title)?>
    <div class="lb-image-wrapper">
        <img src="<?show($srcImgFull)?>" alt="<?show($alt)?>"/>
    </div>
    <div class="lb-file-wrapper">
        <?show($srcFilePath)?>
    </div>
    <div class="lb-embed-wrapper">
        <?show($embed)?>
    </div>
    <?show($date)?>
    <?show($textFull)?>
</div>