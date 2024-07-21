<div class="item-index-header">
        <div class="header-left item-index-titles">
            <h1><i class="fi fi-rs-grip-dots-vertical"></i> <?=$taskTitle?></h1>
        </div>
    </div>

<main>

<form id="item-index" method="post">
    <ul class="form-list">
        <li>
            <? switch ($bulkTask) :
                case 'add-tags':
                case 'add-tag': ?>
                    <label for="new_tags">Add these tags to all of the following selected items:</label>
                    <p>Separate individual tags with a comma.</p>
                    <input class="width-all" type="text" name="new_tags" autocomplete="off">
                    <button name="bulk_items_add_tags">Submit</button>
                    <? break;
                case 'clear-tags':?>
                    <label for="bulk_items_clear_tags">Are you sure you want to remove all tags from all of the following items?</label>
                    <p>This cannot be undone.</p>
                    <button name="bulk_items_clear_tags">Yes, Submit</button>
                    <? break;
                case 'toggle-hide':?>
                    <label for="bulk_items_toggle_hide">Toggle 'Hidden' state for all of the follow items?</label>
                    <p>Selected items that are currently hidden will be un-hidden, and selected items that are unhidden will be switched to the hidden state.</p>
                    <button name="bulk_items_toggle_hide">Yes, Submit</button>
                    <? break;
                case 'delete':?>
                    <label for="bulk_items_delete">Are you sure you want to delete all of the follow select items?</label>
                    <p>These items will be gone. You will not be able to get them back.</p>
                    <button name="bulk_items_delete">Yes, Delete All of the Selected Items</button>
                    <? break;
                case 'move':
                default: ?>
            <div>
                <label for="n_sect_id">Move all of the selected items into Section:</label>
                <select name="n_sect_id">
                    <? foreach ($sectList AS $section) :?>
                        <option value="<?show($section['ID'])?>">
                            <?show($section['Page_Name'], '<i>Orphaned Section</i>')?> > <?show($section['Name'])?>
                        </option>
                    <? endforeach; unset($section);?>
                </select>
            </div>
            <button name="bulk_items_move">Submit</button>
            <? break;
            endswitch;?>
        </li>
    </ul>

<div class="item-selection-area">
<h3>Selected Items:</h3>
    
<? if ($items) :?>
    <ul class="item-select-index">
    <?php foreach ($items AS $item) : ?>
        <li id="item_<?show($item['ID']);?>" class="item <?=($item['Hidden'] ? 'greyed-out' : null)?>">
            <input type="hidden" name="item[<?show($item['ID']);?>][n_item_id]" value="<?show($item['ID']);?>">
            <? if ($bulkTask==='add-tags' || $bulkTask==='add-tag') :?>
                <input type="hidden" name="item[<?show($item['ID']);?>][tags]" value="<?show(prepTags($item['Tags']));?>">
            <? endif;?>
            <div class="item-close-btn" tabindex="0">
                &#x274c;
            </div>
            <div class="item-title">
                <h6>Title:</h6>
                <a href="<?show($route)?>/items.php?task=edit&id=<?show($item['ID']);?>" target="_blank">
                    <span class="item-title"><?show($item['Title'])?></span>
                </a>
            </div>
            <div class="item-contents">
                <h6>Contents:</h6>
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
                        <?show($item['Tags'])?>
                    </div>
                <? endif;?>
            </div>
        </li>
    <?php endforeach;?>
    </ul>
    </div>
    <div id="modal-home"></div>
<?php else :?>
<p><i>No items selected.</a></i></p>
<?php endif;?>
</div>

</form>
</main>

<script src="_js/modal.js"></script>
<script type="text/javascript">

const rvmItems= document.querySelectorAll('.item-close-btn');

rvmItems.forEach((rmvItem) => {
rmvItem.addEventListener("click", function(){
        rmvItem.closest("li").remove();
    })
    rmvItem.addEventListener("keypress", function(){
        if (event.key === "Enter") {
            rmvItem.closest("li").remove();
        }
    })
})


let modalHTML = `<h2>Are you sure you want to delete the following items?</h2>
                <p class="red">This cannot be undone.</p>
                <div class="flex">
                <button type="submit" class="button red" name="delete_items_bulk"/>Yes, delete these items forever</button>
                <button class="button modal-close" onclick="event.preventDefault()"/>Never mind</button>
                </div>`;
const modalItemDelete = new Modal('modal-item-bulk-delete', modalHTML, false, false);
modalItemDelete.appendToForm('modal-home');

</script>