<? if ($task==='list' || !$task) :?>
    <div class="item-index-header">
        <div class="header-left item-index-titles">
            <h1><i class="fi fi-rs-grip-dots-vertical"></i> Item Index</h1>
            <h3>
            <? if (isset($pageID) && validID($pageID)) :?>
                <i class="fi fi-rs-duplicate"></i> From Page :
                <? if (!$pageID) :?>
                    <i>None</i>
                <? else :?>
                    <a class="page-name" href="<?=$set['dir']?>/admin/pages.php?task=edit&id=<?=$pageID?>" title="Go to this page's settings">
                        <?=show($items[0]['Page_Name'])?>
                    </a>
                <? endif?>
            <? elseif (isset($sect['ID']) && validID($sect['ID'])) :?>
                <i class="fi fi-rs-list"></i> From Section : 
                <a class="section-name" href="<?=$set['dir']?>/admin/sections.php?task=edit&sectid=<?=$sect['ID']?>" title="Go to this section's settings">
                    <?=($sect['ID']>0 ? $sect['Name'] : '<i>Orphaned Items</i>')?>
            </a>
            <? else :?>
                All
            <?endif;?>
            </h3>
        </div>
        <div class="header-right">
            <a class="button" href="?task=create&sectid=<?show($sect['ID']);?>"><i class="fi fi-rs-plus"></i> New Item</a>
        </div>
    </div>
<? elseif ($task='view-selection' && isset($bulkTask)) :?>
    <div>
        <h1><i class="fi fi-rs-grip-dots-vertical"> </i> Multiple Item Edit: <?=ucfirst(strtolower($bulkTask))?> Items</h1>
    </div>
<?endif;?>

<?php if ($items) : 
    if ($sect['Order_By']==='Custom') {
        $customOrder=true;
    }?>

<form id="item-index-order" method="post">
<div class="item-index-tools">
    <div class="select-tools">
        <div class="flex-col">
            <label for="bulk_item_tools">Multi-Item Edit Tools:</label>
            <select name="bulk_item_tools" 
                onclick="goLocation(this, 'bulk-edit-go', '<?=$location?>?task=view-selection&bulk-edit=')" 
                onchange="goLocation(this, 'bulk-edit-go', '<?=$location?>?task=view-selection&bulk-edit=')">
                <option value="move">Move to Section</option>
                <option value="add-tags">Add Tags</option>
                <option value="clear-tags">Clear Tags</option>
                <option value="toggle-hide">Toggle Hidden State</option>
                <option value="delete">Delete Items</option>
            </select>
            <button type="submit" id="bulk-edit-go" class="item-bulk-edit small" name="item_bulk_select" formaction="<?=$location?>?task=view-selection&bulk-edit=move">Edit Selected Items</button>
        </div>
        <button type="button" class="item-select-all" name="select_all" onclick="selectAll()">Select All</button>
    </div>

    <div class="order-tools">
        <div>
            <label for="item-order"><b>Order Items By:</b></label>
            <select id="order" name="order" onChange="setOrderParams()">
                <option value="Date" <?=($orderBy==='Date' ? 'selected' : null )?>>Publish Date</option>
                <option value="Title" <?=($orderBy==='Title' ? 'selected' : null )?>>Title</option>
                <option value="ID" <?=($orderBy==='ID' ? 'selected' : null )?>>When Added</option>
                <option value="Random" <?=($orderBy==='Random' ? 'selected' : null )?>>Random</option>
                <? if ($fullIndex) :?>
                    <option value="Location" <?=($orderBy==='Location' ? 'selected' : null )?>>Location</option>
                <? endif?>
                <option value="Custom"<?=($orderBy==='Custom' ? 'selected' : null )?> 
                    <?=($fullIndex ? 'title="Must be a Section index to use Custom order" disabled' : '' )?>>Custom</option>
            </select>
        </div>
        <div>
            <label for="item-order-dir"><b>Order Direction:</b></label>
            <select id="order-dir" name="orderdir" onChange="setOrderParams()">
                <option value="0" <?=($orderDir != 1 ? 'selected' : null )?>>Ascending</option>
                <option value="1" <?=($orderDir == 1 ? 'selected' : null )?>>Descending</option>
            </select>
        </div>
        <div>
            <a class="button small" id="change-view" href="?task=view&sectid=<?=$sect['ID']?>&order=<?=$sect['Order_By']?>&orderdir=<?=$sect['Order_Dir']?>">Change Viewing Order</a>
        </div>

    <? if (!is_null($sect['ID']) || !$fullIndex) : ?>
        <i>* To change the item order on the site itself, 
            <a href="<?=$set['dir']?>/admin/sections.php?task=edit&sectid=<?=$sect['ID']?>">    
                edit this section's settings here.
            </a>
        </i>
    <? endif; ?>
    </div>
</div>

<hr>
    
<div class="admin-table item-index">
    <ul class="table-head">
            <li>Select</li>
            <li>
            <? if($customOrder):?>
                Order /
            <?endif;?>
            Title</li>
            <li>Location</li>
            <li>Publish Datetime</li>
            <li class="item-index-contents">Contents</li>
            <li class="item-hidden">Hidden</li>
        </ul>

        <input type="hidden" name="n_sect_id" value="<?=$sect['ID']?>">
    <ul class="table-index">
    <?php foreach ($items AS $item) : ?>
        <li id="item_<?show($item['ID']);?>" class="<?=($item['Hidden'] ? 'greyed-out' : null)?>">
        <div>
            <input type="hidden" name="item[<?show($item['ID']);?>][n_selected]" value="">
            <input class="item-chkbox" id="item-chkbox-<?show($item['ID']);?>" type="checkbox" name="item[<?show($item['ID']);?>][n_selected]" value="<?show($item['ID']);?>">
        </div>
        <div class="item-title custom-order">
        <input type="hidden" name="item[<?show($item['ID']);?>][n_item_id]" value="<?show($item['ID']);?>">
        <? if($customOrder):?>
            <input type="number" class="enumerate" name="item[<?show($item['ID']);?>][n_index_order]" 
                value="<?show($item['Sect_Index_Order'] ? $item['Sect_Index_Order'] : null )?>" style="width:5em"> &nbsp;
        <? endif;?>
            <a href="<?show($route)?>/items.php?task=edit&id=<?show($item['ID']);?>">
                <span class="item-title"><?show($item['Title'])?></span>
            </a>
        </div>
        <div class="location">
            <? if (!isset($pageID) || !validID($pageID)) :?>
                <div class="page-name">
                    <a href="<?=$set['dir']?>/admin/items.php?pageid=<?show($item['Page_ID'],0)?>">
                        <i class="fi fi-rs-duplicate"></i> <?show($item['Page_ID']>0 ? $item['Page_Name'] : '<i>No page</i>')?>
                    </a>
                </div>     
            <? endif;?>
            <? if (!isset($sect['ID']) || !validID($sect['ID'])) :?>
                <div class="section-name">
                    <a href="<?=$set['dir']?>/admin/items.php?sectid=<?=$item['Sect_ID']?>">
                    <i class="fi fi-rs-list"></i> <?show($item['Section_Name']>'' ? $item['Section_Name'] : '<i>No section</i>')?>
                    </a>
                </div>
            <?endif;?>
        </div>
            <div class="item-datetime">
                <? if ($item['Queued']) :?><i class="fi fi-rs-clock"></i><? endif;?>
                    <?show($item['Date'])?>
            </div>
            <div class="item-index-contents">
                <? if (in_array('Icons',$itemPrevs) || empty($itemPrevs)) :?>
                    <div class="icons">
                        <? if ($item['Text']>''):?><i class="fi fi-rs-text" title="Contains Text"></i><?endif;?>
                        <? if ($item['Img_Path']>''):?><i class="fi fi-rs-picture" title="Contains an Image"></i><?endif;?>
                        <? if ($item['Embed_HTML']>''):?><i class="fi fi-rs-rectangle-code" title="Contains an Embed"></i><?endif;?>
                        <? if ($item['File_Path']>''):?><i class="fi fi-rs-add-document" title="Conatins a File"></i><?endif;?>
                        <? if ($item['Tags']>''):?><i class="fi fi-rs-tags" title="Tags"></i><?endif;?>
                    </div>
                <? endif;?>
                <? if (in_array('Image Thumbnail',$itemPrevs) && $item['Img_Path']>'') :?>
                    <figure class="item-index-img-preview">
                        <img src="<?=$set['dir'].($item['Img_Thumb_Path'] ? $item['Img_Thumb_Path'] : $item['Img_Path'])?>" alt="<?show($item['Title'])?>">
                    </figure>
                <? endif;?>
                <? if (in_array('Text Excerpt',$itemPrevs) && $item['Text']>'') :?>
                    <div class="text-preview" title="Text preview">
                        <?show(truncateTxt($item['Text']))?>
                    </div>
                <? endif;?>
                <? if (in_array('File Path',$itemPrevs) && $item['File_Path']>'') :?>
                    <div class="file-preview">
                        <?show(truncateTxt($item['File_Path']))?>
                    </div>
                <? endif;?>
                <? if (in_array('Embed Script',$itemPrevs) && $item['Embed_HTML']>'') :?>
                    <div class="embed-preview">
                        <?show(truncateTxt($item['Embed_HTML']))?>
                    </div>
                <? endif;?>
                <? if (in_array('Tags',$itemPrevs) && $item['Tags']>'') :?>
                    <div class="tags">
                        <?show(truncateTxt(prepTags($item['Tags'])))?>
                    </div>
                <? endif;?>
            </div>
            <div class="item-hidden"><?php show($item['Hidden']>0 ? 'Yes' : 'No' )?></div>
        </li>
    <?php endforeach;?>
    </ul>
    </div>
    <?if ($customOrder) :?>
        <button name="save_item_order">Save Custom Item Order</button>
    <?endif;?>
    </form>
<?php else :?>
<p><i>This section has no items yet! <a href="<?show($route)?>/items.php?task=create&sectid=<?show($sect['ID']);?>">Click here to add an item.</a></i></p>
<?php endif;?>

<script src="_js/go-location.js"></script>
<script src="_js/enumerate.js"></script>
<script type="text/javascript">

const chkboxes= document.querySelectorAll('.item-chkbox');
const orderBy='<?=$orderBy?>';

if (orderBy == 'Custom') {
    enumerate('enumerate', 'class');
}

function setOrderParams() {
    var order = document.getElementById('order').value;
    var orderDir = document.getElementById('order-dir').value;
    const sectID = <?=($sect['ID']>'' ? $sect['ID'] : '""')?>;
    document.getElementById('change-view').setAttribute("href", 
        "<?=$set['dir']?>/admin/items.php?task=list&sectid=<?=$sect['ID']?>&order="+order+"&orderdir="+orderDir);
}
setOrderParams();

function selectAll(){
    for(let i=0;i<chkboxes.length;i++) {
        chkboxes[i].checked=true;
        chkboxes[i].closest("li").classList.add('chk-selected');
    }
}

chkboxes.forEach((chkbox) => {
chkbox.addEventListener("change", function(){
        if(chkbox.checked==true) {
            chkbox.closest("li").classList.add('chk-selected');
        }else if (chkbox.checked==false) {
            chkbox.closest("li").classList.remove('chk-selected');
        }
    })
})

</script>