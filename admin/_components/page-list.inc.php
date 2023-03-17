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
            <?=($page['ID'] == 1 ? '<i class="fi fi-rs-home"></i>&nbsp;' : null)?>
            <?=(isset($page['Hidden']) && $page['Hidden'] ? '<i class="fi fi-rs-eye-crossed"></i>&nbsp;' : null)?>
                <?show($page['Name']);?> |&nbsp;<a href="<?=$set['dir'].'/'.$page['Link']?>" target="_blank">[View]</a>
            </label>
            <div class="btns-box chktoggle-show">
                <div class="btns-box page-options">
                    <a class="opt settings" href="?task=edit&id=<?show($page['ID'])?>"><i class="fi fi-rs-settings"></i> Edit Page Settings</a>
                    <?php if ($sectList) : ?>
                        <ul class="section-list">
                            <?=($page['Multi_Sect'] ? '<li>Multi-section enabled:</li>' : null)?>
                        <?php foreach ($sectList AS $sect) : ?>
                            <li class="sect-label" >
                                <label for="sect_<?show($sect['ID']);?>"><i class="fi fi-rs-list"></i> <?show($sect['Name']);?></label>
                                <input type="checkbox" id="sect_<?show($sect['ID']);?>" class="chktoggle invis">
                                <div class="btns-box <?=($page['Multi_Sect'] ? 'chktoggle-show' : null)?>">
                                    <hr>
                                    <div class="sect-options">
                                        <a class="opt" href="<?show($route)?>/sections.php?task=edit&sectid=<?show($sect['ID'])?>"><i class="fi fi-rs-settings-sliders"></i> Edit Section Settings</a>
                                        <a class="opt" href="<?show($route)?>/sections.php?task=view&sectid=<?show($sect['ID']);?>"><i class="fi fi-rs-eye"></i> View Items</a>
                                        <a class="opt new-item" href="<?show($route)?>/items.php?task=create&sectid=<?show($sect['ID']);?>"><i class="fi fi-rs-plus"></i> New Item</a>
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