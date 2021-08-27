<h1>Category List</h1>

<a href="?task=create">Add New Category</a>

<?php if ($catList) : ?>
    <ul class="index-list">
    <?php foreach ($catList AS $cat) : 
        if ($cat['ID']===0) {$cat['ID'] = "0";} ?>
        <li>
            <label for="cat_<?show($cat['ID']);?>"><?show($cat['Name']);?></label>
            <input type="checkbox" id="cat_<?show($cat['ID']);?>" class="invis-chktoggle">
            <div class="show-when-sib-chkd">
                <a href="?task=view&catid=<?show($cat['ID']);?>">View Items</a>
                <a href="<?show($route)?>/items.php?task=create&catid=<?show($cat['ID']);?>">Add New Item</a>
                <a href="?task=edit">Edit Category Settings</a>
            </div>
        </li>
    <?php endforeach;?>
    </ul>
<?php endif;?>