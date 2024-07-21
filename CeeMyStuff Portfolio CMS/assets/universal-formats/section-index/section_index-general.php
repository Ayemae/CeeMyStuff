<? include_once($header); ?>

<main id="section_index_<?show($id);?>" class="section-index-page <?show($class);?>">
    <section class="section">
        <div class="sect-header-wrapper"><?show($image)?></div>
        <?show($title)?>
        <div class="sect-text"><?show($text)?></div>
        <div class="sect-items"><?show($items_content)?></div>
    </section>
</main>

<? include_once($footer); ?>