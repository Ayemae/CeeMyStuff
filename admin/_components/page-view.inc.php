<h1>View Category Items</h1>

<a href="?task=list">Back to Category List</a>

<?php if ($items) : ?>
    <ul class="index-list">
    <?php foreach ($items AS $item) : ?>
        <li id="item_<?show($item['ID']);?>">
            <img src="<?show(($item['Img_Thumb_Path'] ? $item['Img_Thumb_Path'] : $item['Img_Path'] ));?>" alt="<?show($item['Title']);?> Image"/> 
            <a href="<?show($route)?>/items.php?task=edit&id=<?show($item['ID']);?>"><?show($item['Title']);?></a>
        </li>
    <?php endforeach;?>
    </ul>
<?php endif;?>