<div class="space-btwn">
    <h1><i class="fi fi-rs-list"></i> Section List</h1>
    <a class="button" href="?task=create"><i class="fi fi-rs-plus"></i> New Section</a>
</div>

<?php if ($sectList) : ?>
    <ul class="section-list">
    <?php foreach ($sectList AS $sect) : ?>
        <li class="sect-box">
            <input type="checkbox" id="sect_<?show($sect['ID']);?>" class="chktoggle invis">
            <label class="sect-label<?=($sect['Hidden'] ? ' greyed-out' : '')?>" for="sect_<?show($sect['ID']);?>">
                <?=(($sect['Hidden'] ?? null) ? '<i class="fi fi-rs-crossed-eye"></i> ' : null)?>
                <?if (!$sect['Is_Reference']) :?> 
                    <i class="fi fi-rs-list"></i>
                <?else : ?>
                    <i title="Reference Section" class="fi fi-rs-rectangle-list"></i>
                <? endif;?>
                <strong>
                    &nbsp;<?show($sect['Name']);?>
                </strong> | <i class="fi fi-rs-file"></i> 
                <?php if ($sect['Page_ID']) : ?>
                    In '<a href="<?=$set['dir'].'/'.$sect['Link']?>" target="_blank" title="View on Site"><?show($sect['Page_Name']);?></a>'
                <?php else: ?>
                    <i>Orphaned Section</i>
                <?php endif;?>
            </label>
            <div class="btns-box chktoggle-show">
                <div class="sect-options">
                    <?if (isset($sect['Is_Reference']) && $sect['Is_Reference']>0) :?> 
                        <ul>
                        <b>Referencing Items From:</b> 
                        <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                        <article class="help-text">
                            This is a Reference Section. A Reference Section is a section that inherits items from other sections. It cannot have its own items.
                        </article>
                        </i>
                            <? if ($sect['Ref_Count']>0) :
                            foreach($sect['Ref_Sects'] AS $refSect) : ?>
                                <li><?=$refSect['Name']?> [<a href="<?show($route)?>/items.php?task=list&sectid=<?=$refSect['ID']?>">view items</a>]</li>
                            <?endforeach; else :?>
                                <li><i>None</i></li>
                            <? endif;?>
                        </ul>
                    <? else: ?>
                        <a class="opt new-item" href="<?show($route)?>/items.php?task=create&sectid=<?show($sect['ID']);?>"><i class="fi fi-rs-plus"></i> New Item</a>
                        <a class="opt" href="<?show($route)?>/items.php?task=list&sectid=<?show($sect['ID']);?>"><i class="fi fi-rs-eye"></i> View Items</a>
                        <a class="opt" href="?task=create&refsect=<?show($sect['ID'])?>"><i class="fi fi-rs-plus"></i> Create Reference Section</a>
                    <? endif;?>
                    <a class="opt" href="?task=edit&sectid=<?show($sect['ID'])?>"><i class="fi fi-rs-settings-sliders"></i> Edit Settings</a>
                </div>
            </div>
        </li>
    <?php endforeach;?>
    <li class="sect-box orphaned-items">
            <label class="no-sect-label" for="sect_0"><i class="fi fi-rs-link-slash-alt"></i> Orphaned Items</label>
            <input type="checkbox" id="sect_0" class="chktoggle invis">
            <div class="btns-box chktoggle-show">
                <hr>
                <div class="sect-options">
                    <a class="opt new-item" href="<?show($route)?>/items.php?task=create&sectid=0"><i class="fi fi-rs-plus"></i> New Item</a>
                    <a class="opt" href="<?show($route)?>/items.php?task=list&sectid=0"><i class="fi fi-rs-eye"></i> View Items</a>
                </div>
            </div>
        </li>
        <li>
            <a class="button" href="?task=edit&sectid=0"><i class="fi fi-rs-edit"></i> Edit Section Defaults</a>
        </li>
    </ul>
<?php else : ?>
    <p><i>There are no sections yet, and you'll need one to start adding items! <a href="?task=create">Click here to create one!</a></i></p>

<?php endif;?>