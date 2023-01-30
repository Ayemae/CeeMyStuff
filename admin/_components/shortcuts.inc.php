<section>
    <h2>Quick-Add Shortcuts</h2>

    <noscript>Enable Javascript to access quick-add shortcuts.</noscript>

    <div class="js-check">
        <div>
            <i class="fi fi-rs-caret-right"></i> Add item to section: 
                <select id="select-item-section" name="select_item_section" onchange="goLocation(this, 'go-section', '<?=$set['dir']?>/admin/items.php?task=create&sectid=')">
                    <option value="0">None (create orphaned item)</option>
                    <? foreach ($sectList AS $sect) :?>
                        <option value="<?=$sect['ID']?>"><?=$sect['Name']?></option>
                    <? endforeach;?>
                </select>
                <a class="button" id="go-section" href="<?=$set['dir']?>/admin/items.php?task=create">Go!</a>
        </div>
        <div>
            <i class="fi fi-rs-caret-right"></i> Add section to page: 
            <select id="select-item-section" name="select_item_section" onchange="goLocation(this, 'go-page', '<?=$set['dir']?>/admin/sections.php?task=create&pageid=')">
                <option value="">None (create orphaned section)</option>
                <? foreach ($pageList AS $page) :
                    if ($page['Can_Add_Sect']) :?>
                        <option value="<?=$page['ID']?>"><?=$page['Name']?></option>
                    <? endif; endforeach;?>
            </select>
                <a class="button" id="go-page" href="<?=$set['dir']?>/admin/sections.php?task=create">Go!</a>
        </div>
        <div>
            <i class="fi fi-rs-caret-right"></i> Add new page: 
                <a class="button" href="<?=$set['dir']?>/admin/pages.php?task=create">Go!</a>
        </div>
    </div>
    
</section>
<script src="_js/go-location.js"></script>