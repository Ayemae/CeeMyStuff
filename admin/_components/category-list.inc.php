<div class="space-btwn">
    <h1>Category List</h1>
    <a class="button" href="?task=create"><i class="fi fi-rs-plus"></i> New Category</a>
</div>

<?php if ($catList) : ?>
    <ul class="category-list">
    <?php foreach ($catList AS $cat) : 
        if ($cat['ID']===0) {$cat['ID'] = "0";} ?>
        <li>
            <label class="cat-box" for="cat_<?show($cat['ID']);?>"><?show($cat['Name']);?></label>
            <input type="checkbox" id="cat_<?show($cat['ID']);?>" class="chktoggle invis">
            <div class="chktoggle-show cat-options">
                <a href="?task=view&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-eye"></i> View Items</a>
                <a href="<?show($route)?>/items.php?task=create&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-plus"></i> New Item</a>
                <a href="?task=edit&catid=<?show($cat['ID'])?>"><i class="fi fi-rs-settings-sliders"></i> Edit Settings</a>
            </div>
        </li>
    <?php endforeach;?>
    </ul>
<?php else : ?>
    <p><i>There are no categories yet, and you'll need one to start adding items! <a href="?task=create">Click here to create one!</a></i></p>

<?php endif;?>