<? include_once($header); ?>
<?show($feedback)?>

<p>
    This is a test.
</p>

<main id="page_<?show($id);?>" class="page <?show($class);?>">
    <div class="page-header-wrapper">
        <?show($image)?>
    </div>
    <?show($title)?>
    <div class="page-sects">
        <?show($section_content)?>
    </div>
    <?show($paginator)?>
</main>

<? include_once($footer); ?>