<form id="section-form" method="post" enctype="multipart/form-data">
    <div class="space-btwn">
        <h1><?=($create ? "Create".($isRef ? " Reference" : null)." Section" : "Edit".($isRef ? " Reference" : null)." Section Settings : ".($sect['ID']>0 ? $sect['Name'] : 'Section Defaults'))?></h1>
        <? if ($edit) :?>
        <input type="hidden" name="n_sect_id" value="<?show($sect['ID'])?>">
            <? if ($sect['ID']>0) :?>
                <button name="delete_section" id="delete-section" class="small red">
                    <i class="fi fi-rs-trash"></i> Delete Section
                </button>
            <? endif;?>
        <? endif;?>
    </div>
    <? if ($isRef) :?>
        <div>
            <p>Reference sections are used to reference items of one or more other sections. You cannot upload items to them on their own.</p>
        </div>
        <input type="hidden" name="is_reference" value="1">
    <? endif;?>

    <section>
<ul class="form-list">
    <li>
        <?php if ($create || $sect['ID']>0) :?>
            <label for="name">Title:</label>
            <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                <article class="help-text">
                    All Sections titles on a single page must be unique.
                </article>
            </i>
            <input type="text" name="name" id="name" max-length="255" value="<?show($edit ? $sect['Name'] : null)?>"  autocomplete="off" required>
        <? endif; //end if not section defaults ?>
        <div>
            <label for="n_show_title">Show section title on the website:</label>
            <input type="hidden" name="n_show_title" value="0">
            <input type="checkbox" name="n_show_title" id="n_show_title" value="1" <?=($edit && isset($sect['Show_Title']) && $sect['Show_Title']<1 ? null : 'checked')?>>
        </div>
    </li>

    <? if ($isRef) : ?>
        <li>
            <label for="name">Referencing Section(s):</label>
            <i class="help icon"><i class="fi fi-rs-interrogation"></i>
            <article class="help-text">
                Which section(s) is this section referencing?
            </article>
        </i>
        <noscript>
            <p class="red">Enable Javascript to add more than one section.</p>
        </noscript>
        <? if ($edit && isset($sect['Ref_Sect_IDs'])) :?>
            <input type="hidden" name="ref_sect_list" value="<?show($sect['Ref_Sect_IDs'])?>">
        <? endif;?>
        <div id="ref-select-area">
            <div class="ref-sect-list js-check">
                <ul id="ref-sect-list" class="button-labels ref-sects">
                    <? if (count($refSects)>0) :
                    foreach($refSects AS $ref) :?>
                        <li data-sectid="<?=$ref['ID']?>">
                            <input type="hidden" name="ref_sect[]" value="<?=$ref['ID']?>">
                            <span class="remove-ref-sect">
                                <i class="fi fi-rs-cross-small"></i>
                            </span>
                            <?=$ref['Name']?>
                        </li>
                    <? endforeach; endif; ?>
                </ul>
            </div>
                <div class="flex">
                <select id="ref-sect-select" name="ref_sects">
                    <? if ($sectList) :?>
                        <option></option>
                        <? foreach ($sectList AS $iSect) : 
                        $isReffed = in_array($iSect['ID'], $refSectIDs);?>
                        <option value="<?=$iSect['ID']?>" <?=($isReffed ? 'title="Already referenced" disabled' : null)?>>
                            <?=$iSect['Name']?>
                        </option>
                    <? endforeach; unset($iSect); endif;?>
                </select>
                <button id="ref-sect-add" class="js-check" type="button">Add</button>
            </div>
        </div>
        </li>
    <? endif;?>

    <li>
    <?php if ($create || $sect['ID']>0) :?>
        <label for="n_page_id">In Page:</label>
        <i class="help icon"><i class="fi fi-rs-interrogation"></i>
            <article class="help-text">
                If you don't see the page you want, make sure that page 
                has 'Multiple Content Sections' enabled in its settings.
            </article>
        </i>
        <select id="n_page_id" name="n_page_id">
            <option value="">None</option>
            <?php foreach($pgList AS $page) : 
                if ($sect['Page_ID']==$page['ID'] || $page['Can_Add_Sect']) : ?>
                <option value="<?show($page['ID']);?>" <?=formCmp($sect['Page_ID'],$page['ID'],'s')?>>
                    <?show($page['Name']);?>
                </option>
            <?php endif;
        endforeach; unset($page);?>
        </select>
    </li>

    <li>
        <div>
            <label for="header_img_upload">Header Image (Optional):</label>
            <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                    <article class="help-text">
                        Must be a .png, .jpg, .gif, or .webp image file.
                    </article>
                </i>
        </div>
        <input type="file" id="header-img-upload" name="header_img_upload" onchange="previewImg('header-img-upload', 'header-img')" value="<?(!isset($_POST['header_img_upload']) ? null : show($_POST['header_img_upload']))?>">
        <input type="hidden" id="header-img-stored" name="header_img_stored" value="<?show($edit ? $sect['Header_Img_Path'] : null);?>">
        <div id="header-img-current" class="page-current-image-wrapper">
            <label>Current:</label> 
                <img id="header-img-visual" class="visual<?=($imgExists ? ' block' : ' invis')?>" src="<?=($imgExists ? $set['dir'].$sect['Header_Img_Path'] : null)?>">
                <input type="hidden" id="header-img-preview" name="header_img_preview" value="">
                <div id="header-img-rmv-info" class="rvm-file-path-info invis">&#10060; File Removed</div>
                <button id="header-img-rmv-btn" type="button" class="small red <?=($imgExists ? null : 'invis')?>" onclick="rmvFilePath(this, 'header-img-stored', 'header-img-current')">Remove Current Image</button>
                <em id="header-img-none" class="<?=(!$imgExists ? null : 'invis')?>">none</em>
        </div>
        <? endif; //end if not section default ?>
        <div>
            <label for="n_show_title">Show header image on the website:</label>
            <input type="hidden" name="n_show_header_img" value="0"> 
            <input type="checkbox" name="n_show_header_img" id="n_show_header_img" value="1" <?=($edit && isset($_POST['n_show_header_img']) && $_POST['n_show_header_img']<1 ? null : 'checked')?>>
        </div>
    </li>

    <?php if ($create || $sect['ID']>0) :?>
    <li>
        <label for="text-editor">Section Text:</label>
        <i class="help icon"><i class="fi fi-rs-interrogation"></i>
            <article class="help-text">
                'Section Text' will also accept HTML and scripts.
            </article>
        </i>
        <div class="text-panel">
            <?include('_components/text-edit-panel.inc.php')?>
            <textarea id="text-editor" name="b_text"><?show($edit ? $sect['Text'] : null)?></textarea>
        </div>
    </li>
    <? endif; //end if not section default ?>

    <li>
        <label for="format">Display Format:</label>
        <p>Select a format for how you would like this <strong>Section</strong> to display on the website.</p>
        <?php if ($sectFormats) :?>
            <select name="format" id="format">
                <?php foreach ($sectFormats AS $sFormat) :?>
                <option value="<?show($sFormat['Path'])?>" <?=($edit && $sect['Format']===$sFormat['Path'] ? 'selected' : null)?>>
                    <?show($sFormat['From'])?> > <?show($sFormat['Name'])?>
                </option>
                <?php endforeach;?>
            </select>
        <?php else:?>
            <i class="red">No valid Section formats were found.</i>
        <?php endif;?>
    </li>

    <?php if ($create || $sect['ID']>0) :?>
    <li>
        <label for="hidden"> Hide this section:</label>
        <i class="help icon"><i class="fi fi-rs-interrogation"></i>
        <article class="help-text">
            Hidden Sections will not display on the live site.
        </article>
        </i>
        <input type="hidden" id="hidden" name="n_hidden" value="0">
        <input type="checkbox" id="hidden" name="n_hidden" value="1" <?=formCmp($sect['Hidden'],1)?>>
    </li>
    <? endif; //end if not section defaults ?>

</ul>
</section>



<? if ($isRef) :
    /// if this is a reference section ?>
    <section>
        <label for="ref-display-sets">
            <h2><i class="fi fi-rs-caret-right"></i> Referenced Item Conditions</h2>
        </label>
        <input type="checkbox" class="chktoggle invis" id="ref-display-sets" name="ref-display-sets">
        <ul class="form-list chktoggle-show">
            <li id="date-cutoff">
                <label for='n_date_cutoff_on'>
                    Enforce Publish-Date Cutoff:
                </label>
                <input type="hidden" name="n_date_cutoff_on" value='0'>
                <input type="checkbox" class="chktoggle" id="n_date_cutoff_on" name="n_date_cutoff_on" value="1" 
                    <?=($edit && $sect['Date_Cutoff_On']>0 ? 'checked' : null)?>>
                <ul class="form-list chktoggle-show">
                    <label for="date_cutoff">
                        Date Cutoff:
                    </label>
                    <li class="flex select-cond-container" style="align-items: baseline; gap:5px;">
                        <div>
                            <label class="invis" for="cutoff-date-expression">
                                Date Cutoff Mode: 
                            </label>
                            <select class="select-cond-master" id="cutoff-date-mode" name="n_date_cutoff_mode">
                                <option value='1' <?=($edit && $sect['Date_Cutoff_Mode']==1 ? 'selected' : null)?>>Calender</option>
                                <option value='2' <?=($edit && $sect['Date_Cutoff_Mode']==2 ? 'selected' : null)?>>Relative</option>
                            </select>
                        </div>
                        <div>
                            <label class="invis" for="date_cutoff_dir">
                                Date Before/After:
                            </label>
                            <select id="n_date_cutoff_dir" name="n_date_cutoff_dir">
                                <option value='1' <?=($edit && $sect['Date_Cutoff_Dir']==1 ? 'selected' : null)?>>After</option>
                                <option value='0' <?=($edit && $sect['Date_Cutoff_Dir']==0 ? 'selected' : null)?>>Before</option>
                            </select>
                        </div>
                        <div class="select-cond" data-sc-conditions="1">
                            <input type="date" id="date_cutoff_strict" name="date_cutoff_strict"
                            value="<?=($edit && $sect['Date_Cutoff'] ? $sect['Date_Cutoff'] : null)?>">
                        </div>
                        <div class="select-cond" data-sc-conditions="2">
                            <div class="flex">
                                <label class="invis" for="date_cutoff">
                                    Date Cutoff:
                                </label>
                                <input type="number" id="date_number" name="n_date_number" min="0" style="width:3em" value="<?=(($sect['Date_Number'] ?? null) ? $sect['Date_Number'] : ($_POST['n_date_number'] ?? null))?>">
                                <select id="date_unit" name="date_unit">
                                    <option value="years" <?=($edit && $sect['Date_Unit']=='years' ? 'selected' : null)?>>Year(s) Ago</option>
                                    <option value="months" <?=($edit && $sect['Date_Unit']=='months' ? 'selected' : null)?>>Month(s) Ago</option>
                                    <option value="weeks" <?=($edit && $sect['Date_Unit']=='weeks' ? 'selected' : null)?>>Weeks(s) Ago</option>
                                    <option value="days" <?=($edit && $sect['Date_Unit']=='days' ? 'selected' : null)?>>Days(s) Ago</option>
                                </select>
                            </div>
                        </div>
                    <li>
                </ul>
            </li>
            <li id="tag-filter">
                <label for='n_tag_filter_on'>
                    Enforce Tag Filter:
                </label>
                <input type="hidden" name="n_tag_filter_on" value="0">
                <input type="checkbox" class="chktoggle" id="n_tag_filter_on" name="n_tag_filter_on" value="1" <?=($edit && $sect['Tag_Filter_On']>0 ? 'checked' : null)?>>
                <ul class="form-list chktoggle-show">
                    <li>
                        <label for="n_tag_filter_mode">
                            Tag Filter Mode:
                            <select id="n_tag_filter_mode" name="n_tag_filter_mode">
                                <option value='1' <?=($edit && $sect['Tag_Filter_Mode']==1 ? 'selected' : null)?>>INCLUDE items with ANY of these tags</option>
                                <option value='2' <?=($edit && $sect['Tag_Filter_Mode']==2 ? 'selected' : null)?>>INCLUDE items with ALL of these tags</option>
                                <option value='3' <?=($edit && $sect['Tag_Filter_Mode']==3 ? 'selected' : null)?>>EXCLUDE items with ANY these tags</option>
                                <option value='0' <?=($edit && $sect['Tag_Filter_Mode']==0 ? 'selected' : null)?>>EXCLUDE items with ALL these tags</option>
                            </select>
                        </label>
                    </li>
                    <li>
                        <label for='tag_filter_list'>
                            Tag Filter List:
                        </label>
                        <p>If more than one tag, separate tags with a comma.</p>
                        <input type="text" name="tag_filter_list" id="tag_filter_list" style="width:90%" value="<?=($edit && $sect['Tag_Filter_List'] ? $sect['Tag_Filter_List'] : null)?>">
                    </li>
                </ul>
            </li>
            <li id="item-limit">
                <label for="item_limit_on">
                    Enforce Item Limit:
                </label>
                <input type="hidden" name="item_limit_on" value="0">
                <input type="checkbox" class="chktoggle" id="item_limit_on" name="item_limit_on" value="1" <?=($edit && $sect['Item_Limit'] ? 'checked' : null)?>>
                <ul class="form-list chktoggle-show">
                    <li>
                        <label for="n_item_limit">
                            Item Limit:
                        </label>
                        <input type="number" id="n_item_limit" name="n_item_limit" style="width:5em;" min='0' value="<?=($edit && $sect['Item_Limit'] ? $sect['Item_Limit'] : null)?>">
                    </li>
                </ul>
            </li>
        </ul>
    </section>
<? endif;?>

<section>
    <label for="section-display-sets">
        <h2><i class="fi fi-rs-caret-right"></i> Item Display Settings</h2>
    </label>
    <input type="checkbox" class="chktoggle invis" id="section-display-sets">
    <ul class="form-list chktoggle-show">

    <li>
        <label for="item_format">Default Item Display Format:</label>
        <p>Select a format for how you would like this <strong>Section's individual items</strong> to display on the website.</p>
        <?php if ($itemFormats) :?>
            <select name="item_format" id="item-format">
                <?php foreach ($itemFormats AS $iFormat) :?>
                <option value="<?show($iFormat['Path'])?>" <?formCmp($sect['Default_Item_Format'],$iFormat['Path'],'s')?>>
                    <?show($iFormat['From'])?> > <?show($iFormat['Name'])?>
                </option>
                <?php endforeach;?>
            </select>
        <?php else :?>
            <i class="red">No valid Item formats were found.</i>
        <?php endif;?>
    </li>

    <li>
        <div>
            <label for="order_by">Order Items By:</label>
            <select id="order_by" name="order_by">
                <option value="Date" <?formCmp($sect['Order_By'],'Date','s')?>>Date</option>
                <option value="Title" <?formCmp($sect['Order_By'],'Title','s')?>>Title</option>
                <option value="Custom" <?formCmp($sect['Order_By'],'Custom','s')?>>Custom</option>
                <option value="ID" <?formCmp($sect['Order_By'],'ID','s')?>>When Added</option>
                <option value="Random" <?formCmp($sect['Order_By'],'Random','s')?>>Random</option>
            </select>
        </div>
        <div>
            <label for="order_dir">Order Direction:</label>
            <select id="order_dir" name="n_order_dir">
                <option value="0" <?=($sect['Order_Dir'] != 1 ? 'selected' : null )?>>Ascending</option>
                <option value="1" <?=($sect['Order_Dir'] == 1 ? 'selected' : null )?>>Descending</option>
            </select>
        </div>
    </li>

    <li>
        <label for="n_show_titles">Show Item Titles:</label>
        <select id="show_titles" name="n_show_titles">
            <option value="0" <?show(!$sect['Show_Item_Titles'] ? 'selected' : null )?>>No</option>
            <option value="1"  <?show($sect['Show_Item_Titles'] ? 'selected' : null )?>>Yes</option>
        </select>
    </li>

    <li>
        <label for="n_show_dates">Show Item Dates:</label>
        <select id="show_dates" name="n_show_dates">
            <option value="0" <?show(!$sect['Show_Item_Dates'] ? 'selected' : null )?>>No</option>
            <option value="1"  <?show($sect['Show_Item_Dates'] ? 'selected' : null )?>>Yes</option>
        </select>
    </li>

    <li>
        <label for="n_show_text">Show Item Text:</label>
        <select id="show_text" name="n_show_text">
            <option value="0" <?formCmp($sect['Show_Item_Text'],0,'s')?>>No</option>
            <option value="1" <?formCmp($sect['Show_Item_Text'],1,'s')?>>Show Truncated Text</option>
            <option value="2" <?formCmp($sect['Show_Item_Text'],2,'s')?>>Show Full Text</option>
        </select>
    </li>

    <li>
        <label for="n_show_images">Show Item Images:</label>
        <select id="show_images" name="n_show_images">
            <option value="0" <?formCmp($sect['Show_Item_Images'],0,'s')?>>No</option>
            <option value="1" <?formCmp($sect['Show_Item_Images'],1,'s')?>>Show Thumbnails</option>
            <option value="2" <?formCmp($sect['Show_Item_Images'],2,'s')?>>Show Full-Sized Images</option>
        </select>
    </li>

    <? if (!$isRef) :?>
    <li>
        <label for="n_create_thumbs">Auto-Create Thumbnails for Images:</label>
        <input type="hidden" name="n_create_thumbs" value="0">
        <input type="checkbox" id="create-thumbs" name="n_create_thumbs" class="chktoggle" value="1" <?=($sect['Auto_Thumbs'] ? "checked=checked" : null );?>>
        <div class="chktoggle-show">
            <ul class="sub-form-list">
                <li>
                    <label for="thumb_size">Choose default thumbnail size:</label>
                    <input type="number" id="thumb_size" name="n_thumb_size" value="<?=($sect['Thumb_Size'] ? $sect['Thumb_Size'] : null );?>">
                </li>
                <li>
                    <label for="thumb_axis">Axis of thumbnail size:</label>
                    <select id="thumb_axis" name="n_thumb_axis">
                        <option value="0" <?formCmp($sect['Thumb_Size_Axis'],0,'s')?>>Width</option>
                        <option value="1" <?formCmp($sect['Thumb_Size_Axis'],1,'s')?>>Height</option>
                    </select>
                </li>
            </ul>
        </div>
    </li>
    <? endif;?>


    <li class="select-cond-container">
        <div>
            <label for="show_files">Show Item Files:</label>
            <select id="show_files" name="n_show_files" class="select-cond-master">
                <option value="0" <?formCmp($sect['Show_Item_Files'],0,'s')?>>No</option>
                <option value="1" <?formCmp($sect['Show_Item_Files'],1,'s')?>>Show Link to File</option>
                <option value="2" <?formCmp($sect['Show_Item_Files'],2,'s')?>>Show File Download</option>
            </select>
        </div>
        <ul class="sub-form-list select-cond" data-sc-conditions="1,2">
            <li>
                <label for="name">Default File Link Text:</label>
                <input type="text" name="file_link_text" id="link-text" max-length="255" value="<?show($edit ? $sect['Default_File_Link_Text'] : "Click here")?>">
            <l/i>
        </ul>
    </li>
    </ul>
    </section>




<section>
    <label for="section-action-sets">
        <h2><i class="fi fi-rs-caret-right"></i> Item Behavior Settings</h2>
    </label>
    <input type="checkbox" class="chktoggle invis" id="section-action-sets">
    <ul class="form-list chktoggle-show">
    <li class="select-cond-container">
        <label for="onclick-action">Item On-Click Actions:</label>
        <p>What should happen when a viewer clicks on an item?</p>
        <select id="onclick-action" class="select-cond-master" name="n_onclick_action">
            <option value="1" <?formCmp($sect['On_Click_Action'],1,'s')?>>Load a single-item viewing page</option>
            <option value="2" <?formCmp($sect['On_Click_Action'],2,'s')?>>Open a lightbox to view item</option>
            <option value="3" <?formCmp($sect['On_Click_Action'],3,'s')?>>Open new window with single-item viewing page</option>
            <option value="0" <?formCmp($sect['On_Click_Action'],0,'s')?>>Nothing; items should not be clickable</option>
        </select>

        <ul class="sub-form-list">
            <li id="item-click-area-sc" class="select-cond" data-sc-conditions="0" data-sc-exclude="true" data-sc-reverse="true">
                <div><label for="click-area">Item Click Area:</label></div>
                <input type="checkbox" class="chktoggle fl-chkbox" id="clk-anywhere" name="item_click_area[1]" value="All" <?=(in_array("All",$sect['Item_Click_Area']) ? "checked" : null )?>>
                <label class="fl-chkbox" for="clk-anywhere">Anywhere</label>
                <div class="chktoggle-hide">
                    <fieldset>
                        <label for="clk-title">
                            <input type="checkbox" id="clk-title" name="item_click_area[2]" value="Title" <?=(in_array("Title",$sect['Item_Click_Area']) ? "checked" : null )?>> Title
                        </label>
                        <label for="clk-image"><input type="checkbox" id="clk-image" name="item_click_area[3]" value="Image" <?=(in_array("Image",$sect['Item_Click_Area']) ? "checked" : null )?>> Image
                        </label>
                        <label for="clk-text">
                            <input type="checkbox" id="clk-text" name="item_click_area[4]" value="Text" <?=(in_array("Text",$sect['Item_Click_Area']) ? "checked" : null )?>> Text
                        </label>
                        <label for="clk-link">
                            <input type="checkbox" id="clk-link" name="item_click_area[5]" value="Link" <?=(in_array("Link",$sect['Item_Click_Area']) ? "checked" : null )?>> Added 'View' Link
                        </label>
                    </fieldset>
                </div>
            </li>
        

            <li id="lightbox-format-sc" class="select-cond" data-sc-conditions="2" data-sc-exclude="" data-sc-reverse="">
                <label for="lightbox-format">Lightbox Format:</label>
                <p>Select a format for how you would like Section's items to display within lightbox.</p>
                <?php if ($lightboxFormats) :?>
                    <select name="lightbox_format" id="lightbox-format">
                        <?php foreach ($lightboxFormats AS $lFormat) :?>
                        <option value="<?show($lFormat['Path'])?>" <?formCmp($sect['Lightbox_Format'],$lFormat['Path'],'s')?>>
                            <?show($lFormat['From'])?> > <?show($lFormat['Name'])?>
                        </option>
                        <?php endforeach;?>
                    </select>
                <?php else :?>
                    <i class="red">No valid Lightbox formats found.</i>
                <?php endif;?>
            </li>


            <li id="viewitem-format-sc" class="select-cond" data-sc-conditions="1,3" data-sc-exclude="" data-sc-reverse="">
                <label for="view-item-format">Default View-Item Page Display Format:</label>
                <p>Select a format for how you would like Section's items to display on their individual 'view' pages on the website.</p>
                <?php if ($viewItemFormats) :?>
                    <select name="view_item_format" id="view-item-format">
                        <?php foreach ($viewItemFormats AS $vFormat) :?>
                        <option value="<?show($vFormat['Path'])?>" <?formCmp($sect['View_Item_Format'],$vFormat['Path'],'s')?>>
                            <?show($vFormat['From'])?> > <?show($vFormat['Name'])?>
                        </option>
                        <?php endforeach;?>
                    </select>
                <?php else :?>
                    <i class="red">No valid View-Item Page formats found.</i>
                <?php endif;?>
            </li>

            <li class="select-cond" data-sc-conditions="0" data-sc-exclude="true" data-sc-reverse="true">
                <label for="paginate-items">
                    Enable pagination between items:
                </label>
                <input type="hidden" name="n_paginate_items" value="0">
                <input type="checkbox" id="paginate-items" name="n_paginate_items" value="1" <?($edit ? formCmp($sect['Paginate_Items'],1) : "checked")?>>
            </li>

        </ul>
    </li>

    </ul>
    </section>
    
    <div class="space-btwn">
        <button type="submit" id="section-submit" name="<?=($create ? "create_section" : "edit_section")?>" formaction="?task=list" onclick="addTarget('_self')">
            <i class="fi fi-rs-check"></i> Submit
        </button>
        <? if ($sect['ID']>0) :?>
            <button type="submit" id="section-preview" class="js-check" name="section_preview" formaction="<?=$baseURL?>/preview/section"  onclick="addTarget('_blank')">
                Preview
            </button>
        <? endif;?>
    </div>
  <div id="modal-home"></div>
</form>

<script src="_js/text-editor.js"></script>
<script src="_js/toggle-on-cond.js"></script>
<script src="_js/preview-img.js"></script>
<script>
    const form = document.getElementById('section-form');
    function addTarget(target) {
        form.target= target;
    }
</script>
<? if ($edit) :?>
<script src="_js/modal.js"></script>
<script src="_js/rmv-file-paths.js"></script>
<script>
let modalHTML = `<h2>Are you sure you want to delete the '<?=$sect['Name']?>' Section?</h2>
                <p>This cannot be undone.</p>
                <div class="flex">
                <button type="submit" class="button red" name="delete_section"/>Yes, delete this section</button>
                <button class="button modal-close" onclick="event.preventDefault()"/>Never mind</button>
                </div>`;
const modalSectDelete = new Modal('modal-sect-delete', modalHTML, false, false);
modalSectDelete.appendToForm('modal-home');

document.getElementById('delete-section').addEventListener('click', function(e) {
    e.preventDefault();
    modalSectDelete.trigger();
}, false);
</script>
<?endif;?>
<? if ($isRef) : ?>
    <script>
        const refSectSlct = document.getElementById('ref-sect-select');
        const refSectOpts = refSectSlct.querySelectorAll('option');
        const refSectList = document.getElementById('ref-sect-list');
        const refSectAdd = document.getElementById('ref-sect-add');
        //var rmvRefSectBtns = document.querySelectorAll('.remove-ref-sect');
        let selectedSect = <?=($refID ?? 'undefined')?>;
        
        function mkBtnLabelHTML(sectID, name) {
            return `<li data-sectid="${sectID}">
                        <input type="hidden" name="ref_sect[]" value="${sectID}">
                        <span class="remove-ref-sect">
                            <i class="fi fi-rs-cross-small"></i>
                        </span>
                        ${name}
                    </li>`;
        }

        // add referenced section
        refSectAdd.addEventListener('click', function() {
            sectID = refSectSlct.value;
            const selected = refSectSlct.options[refSectSlct.selectedIndex];
            const selectedName = selected.text;
            if (!selected.disabled && sectID>0) {
                const btnLabel = mkBtnLabelHTML(sectID, selectedName);
                refSectList.innerHTML += btnLabel;
                for (let i=0;i<refSectOpts.length;i++) {
                    if (refSectOpts[i].value==sectID) {
                        refSectOpts[i].disabled=true;
                    }
                }
                refSectSlct.selectedIndex = 0;
            }
            addRmvFunc();
        })

        // remove referenced section
        function addRmvFunc() {
            const rmvRefSectBtns = document.querySelectorAll('.remove-ref-sect');
            for (let i=0;i<rmvRefSectBtns.length;i++) {
            rmvRefSectBtns[i].addEventListener('click', function() {
                let labelBtn = rmvRefSectBtns[i].parentElement;
                let sectID = labelBtn.dataset.sectid;
                console.log('sectid: '+sectID);
                for (let i2=0;i2<refSectOpts.length;i2++) {
                    if (refSectOpts[i2].value==sectID) {
                        refSectOpts[i2].disabled=false;
                    }
                }
                labelBtn.remove();
            })
        }
    }
    //initialize
    addRmvFunc();

    </script>
<? endif; ?>