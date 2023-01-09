<a href="?task=list"><i class="fi fi-rs-angle-double-small-left"></i> back to Section List</a>

<div class="space-btwn">
    <h1>View Section Items : <?show($sect['Name']);?></h1>
    <a class="button" href="<?show($route)?>/items.php?task=create&sectid=<?show($sect['ID']);?>"><i class="fi fi-rs-plus"></i> New Item</a>
</div>

<?php if ($items) : 
    if ($sect['Order_By']==='Custom') {
        $customOrder=true;
    }?>

<div class="order-header">
    <div class="space-btwn">
        <div>
            <label for="item-order"><b>Order Items By:</b></label>
            <select id="order" name="order" onChange="setOrderParams()">
                <option value="Date" <?=($orderBy==='Date' ? 'selected' : null )?>>Publish Date</option>
                <option value="Title" <?=($orderBy==='Title' ? 'selected' : null )?>>Title</option>
                <option value="Custom"<?=($orderBy==='Custom' ? 'selected' : null )?>>Custom</option>
                <option value="ID" <?=($orderBy==='ID' ? 'selected' : null )?>>When Added</option>
                <option value="Random" <?=($orderBy==='Random' ? 'selected' : null )?>>Random</option>
            </select>
        </div>
        <div>
            <label for="item-order-dir"><b>Order Direction:</b></label>
            <select id="order-dir" name="orderdir" onChange="setOrderParams()">
                <option value="0" <?=($orderDir != 1 ? 'selected' : null )?>>Ascending</option>
                <option value="1" <?=($orderDir == 1 ? 'selected' : null )?>>Descending</option>
            </select>
        </div>

        <a class="button" id="change-view" href="?task=view&sectid=<?=$sect['ID']?>&order=<?=$sect['Order_By']?>&orderdir=<?=$sect['Order_Dir']?>">Change Viewing Order</a> 
    </div>
        
    <a href="<?=$set['dir']?>/admin/sections.php?task=edit&sectid=<?=$sect['ID']?>">
        <i>To change the item order on the site itself, edit this section's settings here.</i>
    </a>
</div>

<hr>
    
    <ul class="index-table-head">
            <li>
            <? if($customOrder):?>
                Order /
            <? endif;?>
            Name</li>
            <li>Publish Datetime</li>
            <li>Contents</li>
            <li>Hidden</li>
        </ul>

    <form id="item-index-order" method="post">
        <input type="hidden" name="n_sect_id" value="<?=$sect['ID']?>">
    <ul class="index-list index-item-list">
    <?php foreach ($items AS $item) : ?>
        <li id="item_<?show($item['ID']);?>">
        <div>
        <? if($customOrder):?>
            <input type="hidden" name="item[<?show($item['ID']);?>][n_item_id]" value="<?show($item['ID']);?>">
            <input type="number" class="enumerate" name="item[<?show($item['ID']);?>][n_index_order]" 
                value="<?show($item['Sect_Index_Order'] ? $item['Sect_Index_Order'] : null )?>" style="width:3em"> &nbsp;
        <? endif;?>
            <a href="<?show($route)?>/items.php?task=edit&id=<?show($item['ID']);?>">
                <?show($item['Title']);?>
            </a>
        </div>
            <div class="item-datetime">
                <? if ($item['Queued']) :?><i class="fi fi-rs-clock"></i><? endif;?>
                    <?show($item['Date'])?>
            </div>
            <div class="item-contents">
                <? if ($item['Text']>''):?><i class="fi fi-rs-text" title="Text"></i><?endif;?>
                <? if ($item['Img_Path']>''):?><i class="fi fi-rs-picture" title="Image"></i><?endif;?>
                <? if ($item['Embed_HTML']>''):?><i class="fi fi-rs-rectangle-code" title="Embed"></i><?endif;?>
                <? if ($item['File_Path']>''):?><i class="fi fi-rs-add-document" title="File"></i><?endif;?>
            </div>
            <div class="item-hidden"><?php show($item['Hidden']>0 ? 'Yes' : 'No' )?></div>
        </li>
    <?php endforeach;?>
    </ul>
    <?if ($customOrder) :?>
        <button name="save_item_order">Save Custom Item Order</button>
    <?endif;?>
    </form>
<?php else :?>
<p><i>This section has no items yet! <a href="<?show($route)?>/items.php?task=create&sectid=<?show($sect['ID']);?>">Click here to add an item.</a></i></p>
<?php endif;?>

<script src="_js/enumerate.js"></script>
<script>
    
// May not need this after all.
//enumerate('enumerate');

function setOrderParams() {
    var order = document.getElementById('order').value;
    var orderDir = document.getElementById('order-dir').value;
    const sectID = <?=$sect['ID']?>;
    document.getElementById('change-view').setAttribute("href", 
        "<?=$set['dir']?>/admin/sections.php?task=view&sectid=<?=$sect['ID']?>&order="+order+"&orderdir="+orderDir);
}
setOrderParams();

</script>