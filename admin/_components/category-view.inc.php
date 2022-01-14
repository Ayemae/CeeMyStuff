<a href="?task=list"><i class="fi fi-rs-angle-double-small-left"></i> back to Category List</a>

<div class="space-btwn">
    <h1>View Category Items</h1>
    <a class="button" href="<?show($route)?>/items.php?task=create&catid=<?show($cat['ID']);?>"><i class="fi fi-rs-plus"></i> New Item</a>
</div>

<?php if ($items) : ?>
    <ul class="index-list">
    <?php foreach ($items AS $item) : ?>
        <a href="<?show($route)?>/items.php?task=edit&id=<?show($item['ID']);?>">
        <li id="item_<?show($item['ID']);?>">
            <?php if ($item['Type']==='Image' && $item['Img_Path']) : ?>
                <img src="<?show($set['dir'].($item['Img_Thumb_Path'] ? $item['Img_Thumb_Path'] : $item['Img_Path'] ));?>" alt="<?show($item['Title']);?> Image"/> 
            <?php endif;?>
                <?php if ($item['Hidden']) :?>
                    <i class="fi fi-rs-eye-crossed"></i>&nbsp;
                <?php elseif ($item['Queued']) :?>
                    <i class="fi fi-rs-clock"></i>&nbsp;
                <?php endif;?>
                <?show($item['Title']);?>
        </li>
        </a>
    <?php endforeach;?>
    </ul>
<?php endif;?>