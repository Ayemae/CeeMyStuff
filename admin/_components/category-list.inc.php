<div class="space-btwn">
    <h1><i class="fi fi-rs-list"></i> Category List</h1>
    <a class="button" href="?task=create"><i class="fi fi-rs-plus"></i> New Category</a>
</div>

<?php if ($catList) : ?>
    <ul class="category-list">
    <?php foreach ($catList AS $cat) : ?>
        <li class="cat-box">
            <label class="cat-label" for="cat_<?show($cat['ID']);?>">
                <?=(isset($cat['Hidden']) && $cat['Hidden'] ? '<i class="fi fi-rs-eye-crossed"></i>&nbsp;' : null)?>
                <?show($cat['Name']);?> | <i class="fi fi-rs-file"></i> 
                <?php if ($cat['Page_Name']>'') : ?>
                    In '<?show($cat['Page_Name']);?>'
                <?php else: ?>
                    <i>Orphaned Category</i>
                <?php endif;?>
            </label>
            <input type="checkbox" id="cat_<?show($cat['ID']);?>" class="chktoggle invis">
            <div class="btns-box chktoggle-show">
                <div class="cat-options">
                    <a class="opt" href="?task=edit&catid=<?show($cat['ID'])?>"><i class="fi fi-rs-settings-sliders"></i> Edit Settings</a>
                    <a class="opt" href="?task=view&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-eye"></i> View Items</a>
                </div>
                <div class="cat-options">
                    <label><i class="fi fi-rs-plus"></i> New Item:</label>
                    <a class="opt new-item" href="<?show($route)?>/items.php?task=create&type=text&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-text"></i>&nbsp; Text</a>
                    <a class="opt new-item" href="<?show($route)?>/items.php?task=create&type=image&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-picture"></i>&nbsp; Image</a>
                    <a class="opt new-item" href="<?show($route)?>/items.php?task=create&type=embed&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-cursor-text-alt"></i>&nbsp; Embed</a>
                </div>
            </div>
        </li>
    <?php endforeach;?>
    <li class="cat-box">
            <label class="no-cat-label" for="cat_0"><i class="fi fi-rs-interrogation"></i> No Category / Orphaned Items</label>
            <input type="checkbox" id="cat_0" class="chktoggle invis">
            <div class="btns-box chktoggle-show">
                <hr>
                <div class="cat-options">
                    <a class="opt" href="?task=view&catid=0"><i class="fi fi-rs-eye"></i> View Items</a>
                    <a class="opt" href="<?show($route)?>/items.php?task=create&catid=0"><i class="fi fi-rs-plus"></i> New Item</a>
                </div>
            </div>
        </li>
    </ul>
<?php else : ?>
    <p><i>There are no categories yet, and you'll need one to start adding items! <a href="?task=create">Click here to create one!</a></i></p>

<?php endif;?>