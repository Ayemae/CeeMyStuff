<? include_once($header); ?>

<main id="view_item_<?show($id);?>" class="view-item-page <?show($class);?>">
    <section class="item">
        <?show($title)?>
        <figure class="item-image-wrapper">
            <?show($image)?>
        </figure>
        <div class="item-embed-wrapper">
            <?show($embed)?>
        </div>
        <?show($date)?>
        <?show($text)?>
        <?show($paginator)?>
        <div class="back-link">
            <?show($pageLink)?>
        </div>
    </section>
</main>

<? include_once($footer); ?>