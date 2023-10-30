<section>
    <h2>Quick-Add Shortcuts</h2>

    <noscript>Enable Javascript to access quick-add shortcuts.</noscript>

    <form id="shortcuts" class="js-check">
        <div class="shortcut teal">
            <i class="fi fi-rs-caret-right"></i> Add item to section: <br class="mobile-only"/>
                <select id="select-section" name="select-section"
                    onclick="goLocation(this, 'select-section-go', '<?=$set['dir']?>/admin/items.php?task=create&sectid=')" 
                    onchange="goLocation(this, 'select-section-go', '<?=$set['dir']?>/admin/items.php?task=create&sectid=')" 
                        data-go-page="items" data-go-id="sectid">
                    <option value="0" selected>None (create orphaned item)</option>
                    <? foreach ($sectList AS $sect) :?>
                        <option value="<?=$sect['ID']?>"><?=$sect['Name']?></option>
                    <? endforeach;?>
                </select>
                <a class="small button" id="select-section-go" href="<?=$set['dir']?>/admin/items.php?task=create">Go!</a>
        </div>
        <div class="shortcut powblu">
            <i class="fi fi-rs-caret-right"></i> Add section to page: <br class="mobile-only"/>
            <select id="select-page" name="select-page" 
                onclick="goLocation(this, 'select-page-go', '<?=$set['dir']?>/admin/sections.php?task=create&pageid=')" 
                onchange="goLocation(this, 'select-page-go', '<?=$set['dir']?>/admin/sections.php?task=create&pageid=')" 
                    data-go-page="sections" data-go-id="pageid">
                <option value="" selected>None (create orphaned section)</option>
                <? foreach ($pageList AS $page) :
                    if ($page['Can_Add_Sect']) :?>
                        <option value="<?=$page['ID']?>"><?=$page['Name']?></option>
                    <? endif; endforeach;?>
            </select>
                <a class="small button" id="select-page-go" href="<?=$set['dir']?>/admin/sections.php?task=create">Go!</a>
        </div>
        <div class="shortcut rose">
            <i class="fi fi-rs-caret-right"></i> Add new page: <br class="mobile-only"/>
                <a class="small button" href="<?=$set['dir']?>/admin/pages.php?task=create">Go!</a>
        </div>
    </form>
    
</section>
<script src="_js/go-location.js"></script>