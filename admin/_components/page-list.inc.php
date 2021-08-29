<h1>Category List</h1>

<a href="?task=create">Add New Category</a>

<?php if ($catList) : ?>
    <ul class="category-list">
    <?php foreach ($catList AS $cat) : 
        if ($cat['ID']===0) {$cat['ID'] = "0";} ?>
        <li>
            <label class="cat-box" for="cat_<?show($cat['ID']);?>"><?show($cat['Name']);?></label>
            <input type="checkbox" id="cat_<?show($cat['ID']);?>" class="chktoggle invis">
            <div class="chktoggle-show cat-options">
                <a href="?task=view&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-eye"></i> View Items</a>
                <a href="<?show($route)?>/items.php?task=create&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-plus"></i> Add New Item</a>
                <a href="?task=edit&catid=<?show($cat['ID'])?>"><i class="fi fi-rs-settings"></i> Edit Category Settings</a>
            </div>
        </li>
    <?php endforeach;?>
    </ul>
<?php endif;?>