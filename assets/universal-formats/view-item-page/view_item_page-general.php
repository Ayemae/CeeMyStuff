<? include_once($header); ?>

<main id="view_item_<?show($id);?>" class="view-item-page <?show($class);?>">
        <?show($title)?>
        <div class="item-image-wrapper">
            <?show($image)?>
        </div>
        <div class="item-embed-wrapper">
            <?show($embed)?>
        </div>
        <?show($date)?>
        <?show($text)?>
    <?show($paginator)?>
    <div class="back-link">
        <?show($pageLink)?>
    </div>
</main>

<? include_once($footer); ?>