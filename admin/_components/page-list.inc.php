<div class="space-btwn">
    <h1><i class="fi fi-rs-file"></i> Page List</h1>
    <a class="button" href="?task=create"><i class="fi fi-rs-plus"></i> New Page</a>
</div>

<?php if ($pgList) : ?>
    <ul class="page-list">
    <?php foreach ($pgList AS $page) : 
        $sectList = getSectList($page['ID']); ?>
        <li class="page-box">
            <input type="checkbox" id="page_<?show($page['ID']);?>" class="chktoggle invis">
            <label class="page-label" for="page_<?show($page['ID']);?>">
            <?if ($page['ID'] == 1) :?> 
                <i class="fi fi-rs-home" title="Home Page"></i>
            <?else : ?>
                <i class="fi fi-rs-file"></i>
            <? endif;?>
            <?=(isset($page['Hidden']) && $page['Hidden'] ? '<i class="fi fi-rs-eye-crossed"></i>&nbsp;' : null)?>
                &nbsp;<b><?show($page['Name']);?></b>
            </label>
            <div class="btns-box chktoggle-show">
                <div class="btns-box">
                    <div class="page-options">
                        <a class="opt settings" href="?task=edit&id=<?show($page['ID'])?>"><i class="fi fi-rs-settings"></i> Edit Page Settings</a>
                        <a class="opt settings" href="<?=$set['dir'].'/'.$page['Link']?>" target="_blank"><i class="fi fi-rs-eye"></i> View on Site</a>
                    </div>
                    <?php if ($sectList) : ?>
                        <ul class="section-list">
                            <?=($page['Multi_Sect'] ? '<li><b>Multi-section enabled:</b></li>' : null)?>
                        <?php foreach ($sectList AS $sect) : ?>
                            <li class="sect-label">
                                <label class="block width-all" for="sect_<?show($sect['ID']);?>">
                                <? if (($sect['Is_Reference'] ?? null)<1) :?>
                                    <i class="fi fi-rs-list"></i> 
                                <?else :?>
                                    <i class="fi fi-rs-rectangle-list"></i> 
                                <? endif;?>
                                <?show($sect['Name']);?></label>
                                <input type="checkbox" id="sect_<?show($sect['ID']);?>" class="chktoggle invis">
                                <div class="btns-box <?=($page['Multi_Sect'] ? 'chktoggle-show' : null)?>">
                                    <hr>
                                    <div class="sect-options">
                                    <?if (($sect['Is_Reference'] ?? null)>0) :?> 
                                        <b>Reference Section</b> 
                                        <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                                        <article class="help-text">
                                            This is a Reference Section. A Reference Section is a section that inherits items from other sections. It cannot have its own items.
                                        </article>
                                        </i>
                                    <? else: ?>
                                        <a class="opt new-item" href="<?show($route)?>/items.php?task=create&sectid=<?show($sect['ID']);?>"><i class="fi fi-rs-plus"></i> New Item</a>
                                        <a class="opt" href="<?show($route)?>/sections.php?task=view&sectid=<?show($sect['ID']);?>"><i class="fi fi-rs-eye"></i> View Items</a>
                                    <? endif;?>
                                    <a class="opt" href="<?show($route)?>/sections.php?task=edit&sectid=<?show($sect['ID'])?>"><i class="fi fi-rs-settings-sliders"></i> Edit Settings</a>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach;?>
                        </ul>
                        <?php if ($page['Multi_Sect']) :?>
                            <a class="opt" href="<?show($route)?>/sections.php?task=create&pageid=<?show($page['ID']);?>"><i class="fi fi-rs-plus"></i> Add New Section</a>
                        <?php endif;?>
                    <?php else : ?>
                        <p><i>There are no sections associated with this page, and you'll need one to start adding content! 
                            <a href="<?show($route)?>/sections.php?task=create&pageid=<?show($page['ID']);?>">Click here to create a section!</a></i></p>

                    <?php endif;?>
                </div>
            </div>
        </li>
    <?php endforeach;?>
    </ul>
<?php endif;?>