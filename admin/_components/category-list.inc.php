<div class="space-btwn">
    <h1>Category List</h1>
    <a class="button" href="?task=create"><i class="fi fi-rs-plus"></i> New Category</a>
</div>

<?php if ($catList) : ?>
    <ul class="category-list">
    <?php foreach ($catList AS $cat) : ?>
        <li class="cat-box">
            <label for="cat_<?show($cat['ID']);?>">
                <?=($cat['Hidden'] ? '<i class="fi fi-rs-eye-crossed"></i>&nbsp;' : null)?>
                <?show($cat['Name']);?> | In '<?show($cat['Page_Name']);?>'
            </label>
            <input type="checkbox" id="cat_<?show($cat['ID']);?>" class="chktoggle invis">
            <div class="btns-box chktoggle-show">
                <hr>
                <div class="cat-options">
                    <a class="opt" href="?task=edit&catid=<?show($cat['ID'])?>"><i class="fi fi-rs-settings-sliders"></i> Edit Settings</a>
                    <a class="opt" href="?task=view&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-eye"></i> View Items</a>
                </div>
                <div class="cat-options">
                    <label><i class="fi fi-rs-plus"></i> New Item:</label>
                    <a class="opt settings" href="<?show($route)?>/items.php?task=create&type=text&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-text"></i>&nbsp; Text</a>
                    <a class="opt settings" href="<?show($route)?>/items.php?task=create&type=image&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-picture"></i>&nbsp; Image</a>
                    <a class="opt settings" href="<?show($route)?>/items.php?task=create&type=embed&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-cursor-text-alt"></i>&nbsp; Embed</a>
                </div>
            </div>
        </li>
    <?php endforeach;?>
    <li class="cat-box">
            <label for="cat_0"><i class="fi fi-rs-interrogation"></i> No Category / Orphaned Items</label>
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