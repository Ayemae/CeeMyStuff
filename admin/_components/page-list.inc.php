<div class="space-btwn">
    <h1>Page List</h1>
    <a class="button" href="?task=create"><i class="fi fi-rs-plus"></i> New Page</a>
</div>

<?php if ($pgList) : ?>
    <ul class="category-list">
    <?php foreach ($pgList AS $page) : 
        if ($page['ID']===0) {$page['ID'] = "0";} 
        $catList = getCatList($page['ID']); ?>
        <li>
            <label class="cat-box" for="page_<?show($page['ID']);?>"><?show($page['Name']);?></label>
            <input type="checkbox" id="page_<?show($page['ID']);?>" class="chktoggle invis">
            <div class="chktoggle-show cat-options">
                <a href="?task=edit&id=<?show($page['ID'])?>"><i class="fi fi-rs-settings"></i> Edit Page Settings</a>
                <?php if ($page['Multi_Cat']) :?>
                    <a href="<?show($route)?>/categories?task=create&pageid=<?show($page['ID']);?>"><i class="fi fi-rs-plus"></i> Add New Category</a>
                <?php endif;?>
                <?php if ($catList) : ?>
                    <ul class="category-list">
                        <?=($page['Multi_Cat'] ? '<li>Categories:</li>' : null)?>
                    <?php foreach ($catList AS $cat) : ?>
                        <li>
                            <label class="cat-box" for="cat_<?show($cat['ID']);?>"><?show($cat['Name']);?></label>
                            <input type="checkbox" id="cat_<?show($cat['ID']);?>" class="chktoggle invis">
                            <div class="<?=($page['Multi_Cat'] ? 'chktoggle-show' : 'flex')?> cat-options">
                                <a href="<?show($route)?>/categories.php?task=edit&catid=<?show($cat['ID'])?>"><i class="fi fi-rs-settings-sliders"></i> Edit Settings</a>
                                <a href="<?show($route)?>/categories.php?task=view&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-eye"></i> View Items</a>
                                <a href="<?show($route)?>/items.php?task=create&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-plus"></i> New Item</a>
                            </div>
                        </li>
                    <?php endforeach;?>
                    </ul>
                <?php else : ?>
                    <p><i>There are no categories associated with this page, and you'll need one to start adding content! 
                        <a href="<?show($route)?>/categories.php?task=create&pageid=<?show($page['ID']);?>">Click here to create a category!</a></i></p>

                <?php endif;?>
            </div>
        </li>
    <?php endforeach;?>
    </ul>
<?php endif;?>