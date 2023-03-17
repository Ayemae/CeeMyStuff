<div class="space-btwn">
    <h1><i class="fi fi-rs-list"></i> Section List</h1>
    <a class="button" href="?task=create"><i class="fi fi-rs-plus"></i> New Section</a>
</div>

<?php if ($sectList) : ?>
    <ul class="section-list">
    <?php foreach ($sectList AS $sect) : ?>
        <li class="sect-box">
            <input type="checkbox" id="sect_<?show($sect['ID']);?>" class="chktoggle invis">
            <label class="sect-label" for="sect_<?show($sect['ID']);?>">
                <?=(isset($sect['Hidden']) && $sect['Hidden'] ? '<i class="fi fi-rs-eye-crossed"></i>&nbsp;' : null)?>
                <?show($sect['Name']);?> | <i class="fi fi-rs-file"></i> 
                <?php if ($sect['Page_Name']>'') : ?>
                    In '<?show($sect['Page_Name']);?>' <a href="<?=$set['dir'].'/'.$sect['Link']?>" target="_blank">[View]</a>
                <?php else: ?>
                    <i>Orphaned Section</i>
                <?php endif;?>
            </label>
            <div class="btns-box chktoggle-show">
                <div class="sect-options">
                    <a class="opt" href="?task=edit&sectid=<?show($sect['ID'])?>"><i class="fi fi-rs-settings-sliders"></i> Edit Settings</a>
                    <a class="opt" href="?task=view&sectid=<?show($sect['ID']);?>"><i class="fi fi-rs-eye"></i> View Items</a>
                    <a class="opt new-item" href="<?show($route)?>/items.php?task=create&sectid=<?show($sect['ID']);?>"><i class="fi fi-rs-plus"></i> New Item</a>
                </div>
            </div>
        </li>
    <?php endforeach;?>
    <li class="sect-box">
            <label class="no-sect-label" for="sect_0"><i class="fi fi-rs-interrogation"></i> No Page / Orphaned Sections</label>
            <input type="checkbox" id="sect_0" class="chktoggle invis">
            <div class="btns-box chktoggle-show">
                <hr>
                <div class="sect-options">
                    <a class="opt" href="?task=view&sectid=0"><i class="fi fi-rs-eye"></i> View Items</a>
                    <a class="opt" href="<?show($route)?>/items.php?task=create&sectid=0"><i class="fi fi-rs-plus"></i> New Item</a>
                </div>
            </div>
        </li>
    </ul>
<?php else : ?>
    <p><i>There are no sections yet, and you'll need one to start adding items! <a href="?task=create">Click here to create one!</a></i></p>

<?php endif;?>