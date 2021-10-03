<div class="space-btwn">
    <h1>Category List</h1>
    <a class="button" href="?task=create"><i class="fi fi-rs-plus"></i> New Category</a>
</div>

<?php if ($catList) : ?>
    <ul class="category-list">
    <?php foreach ($catList AS $cat) : ?>
        <li class="cat-box">
            <label for="cat_<?show($cat['ID']);?>"><?show($cat['Name']);?></label>
            <input type="checkbox" id="cat_<?show($cat['ID']);?>" class="chktoggle invis">
            <div class="chktoggle-show flex-col">
                <hr>
                <div class="cat-options">
                    <a class="opt" href="?task=edit&catid=<?show($cat['ID'])?>"><i class="fi fi-rs-settings-sliders"></i> Edit Settings</a>
                    <a class="opt" href="?task=view&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-eye"></i> View Items</a>
                    <a class="opt" href="<?show($route)?>/items.php?task=create&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-plus"></i> New Item</a>
                </div>
            </div>
        </li>
    <?php endforeach;?>
    </ul>
<?php else : ?>
    <p><i>There are no categories yet, and you'll need one to start adding items! <a href="?task=create">Click here to create one!</a></i></p>

<?php endif;?>