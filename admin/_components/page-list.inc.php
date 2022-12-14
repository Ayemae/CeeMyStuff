<div class="space-btwn">
    <h1>Page List</h1>
    <a class="button" href="?task=create"><i class="fi fi-rs-plus"></i> New Page</a>
</div>

<?php if ($pgList) : ?>
    <ul class="page-list">
    <?php foreach ($pgList AS $page) : 
        if ($page['ID']===0) {$page['ID'] = "0";} 
        $catList = getCatList($page['ID']); ?>
        <li class="page-box">
            <label class="page-label" for="page_<?show($page['ID']);?>">
            <?=($page['ID'] === "0" ? '<i class="fi fi-rs-home"></i>&nbsp;' : null)?>
            <?=(isset($page['Hidden']) && $page['Hidden'] ? '<i class="fi fi-rs-eye-crossed"></i>&nbsp;' : null)?>
                <?show($page['Name']);?>
            </label>
            <input type="checkbox" id="page_<?show($page['ID']);?>" class="chktoggle invis">
            <div class="btns-box chktoggle-show">
                <hr>
                <div class="btns-box page-options">
                    <a class="opt settings" href="?task=edit&id=<?show($page['ID'])?>"><i class="fi fi-rs-settings"></i> Edit Page Settings</a>
                    <?php if ($page['Multi_Cat']) :?>
                        <a class="opt" href="<?show($route)?>/categories.php?task=create&pageid=<?show($page['ID']);?>"><i class="fi fi-rs-plus"></i> Add New Category</a>
                    <?php endif;?>
                    <?php if ($catList) : ?>
                        <ul class="category-list">
                            <?=($page['Multi_Cat'] ? '<li>Content Categories:</li>' : null)?>
                        <?php foreach ($catList AS $cat) : ?>
                            <li class="cat-box" >
                                <label for="cat_<?show($cat['ID']);?>"><?show($cat['Name']);?></label>
                                <input type="checkbox" id="cat_<?show($cat['ID']);?>" class="chktoggle invis">
                                <div class="btns-box <?=($page['Multi_Cat'] ? 'chktoggle-show' : null)?>">
                                    <hr>
                                    <div class="cat-options">
                                        <a class="opt" href="<?show($route)?>/categories.php?task=edit&catid=<?show($cat['ID'])?>"><i class="fi fi-rs-settings-sliders"></i> Edit Content Settings</a>
                                        <a class="opt" href="<?show($route)?>/categories.php?task=view&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-eye"></i> View Items</a>
                                    </div>
                                    <div class="cat-options">
                                        <label><i class="fi fi-rs-plus"></i> New Item:</label>
                                        <a class="opt settings" href="<?show($route)?>/items.php?task=create&type=text&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-text"></i>&nbsp; Text</a>
                                        <a class="opt settings" href="<?show($route)?>/items.php?task=create&type=image&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-picture"></i>&nbsp; Image</a>
                                        <a class="opt settings" href="<?show($route)?>/items.php?task=create&type=embed&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-cursor-text-alt"></i>&nbsp; Embed</a>
                                </div>
                            </li>
                        <?php endforeach;?>
                        </ul>
                    <?php else : ?>
                        <p><i>There are no categories associated with this page, and you'll need one to start adding content! 
                            <a href="<?show($route)?>/categories.php?task=create&pageid=<?show($page['ID']);?>">Click here to create a category!</a></i></p>

                    <?php endif;?>
                </div>
            </div>
        </li>
    <?php endforeach;?>
    </ul>
<?php endif;?>