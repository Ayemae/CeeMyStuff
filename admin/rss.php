<?php
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Admin Panel: RSS';
include '_components/admin-header.inc.php';
$rss = getSectInfo(0);
$refSectIDs = explode(',', $rss['Ref_Sect_IDs']);
$refSects = getRefSectsInfo($refSectIDs);
$sectList = getSectList(false, true);
$refSectsLen = count($refSects);
if ($refSectsLen<1) {
    for ($i=0;$i<count($sectList);$i++) {
        if (in_array($sectList[$i]['ID'], $refSectIDs)) {
            $refSects[] = array($sectList[$i]);
        }
    }
}
?>

<h1>RSS Feed Settings</h1>

<form id="rss-form" method="post" enctype="multipart/form-data" action="<?=$baseURL?>/admin/socials.php">
    <ul class="form-list">
        <li>
            <label for="name">Output from Section(s):</label>
            <i class="help icon"><i class="fi fi-rs-interrogation"></i>
            <article class="help-text">
                Which section(s) is this feed referencing?
            </article>
        </i>
        <noscript>
            <p class="red">Enable Javascript to add more than one section.</p>
        </noscript>
        <? if (isset($rss['Ref_Sect_IDs'])) :?>
            <input type="hidden" name="ref_sect_list" value="<?show($rss['Ref_Sect_IDs'])?>">
        <? endif;?>
        <div id="ref-select-area">
            <div class="ref-sect-list js-check">
                <ul id="ref-sect-list" class="button-labels ref-sects">
                    <? if ($refSectsLen>0) :
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

    <section>
        <label for="ref-display-sets">
            <h2><i class="fi fi-rs-caret-right"></i> Item Reference Filters</h2>
        </label>
        <input type="checkbox" class="chktoggle invis" id="ref-display-sets" name="ref-display-sets">
        <ul class="form-list chktoggle-show">
            <li id="date-cutoff">
                <label for='n_date_cutoff_on'>
                    Enforce Publish-Date Cutoff:
                </label>
                <input type="hidden" name="n_date_cutoff_on" value='0'>
                <input type="checkbox" class="chktoggle" id="n_date_cutoff_on" name="n_date_cutoff_on" value="1" 
                    <?=($rss['Date_Cutoff_On']>0 ? 'checked' : null)?>>
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
                                <option value='1' <?=($rss['Date_Cutoff_Mode']==1 ? 'selected' : null)?>>Calender</option>
                                <option value='2' <?=($rss['Date_Cutoff_Mode']==2 ? 'selected' : null)?>>Relative</option>
                            </select>
                        </div>
                        <div>
                            <label class="invis" for="date_cutoff_dir">
                                Date Before/After:
                            </label>
                            <select id="n_date_cutoff_dir" name="n_date_cutoff_dir">
                                <option value='1' <?=($rss['Date_Cutoff_Dir']==1 ? 'selected' : null)?>>After</option>
                                <option value='0' <?=($rss['Date_Cutoff_Dir']==0 ? 'selected' : null)?>>Before</option>
                            </select>
                        </div>
                        <div class="select-cond" data-sc-conditions="1">
                            <input type="date" id="date_cutoff_strict" name="date_cutoff_strict"
                            value="<?=($rss['Date_Cutoff'] ? $rss['Date_Cutoff'] : null)?>">
                        </div>
                        <div class="select-cond" data-sc-conditions="2">
                            <div class="flex">
                                <label class="invis" for="date_cutoff">
                                    Date Cutoff:
                                </label>
                                <input type="number" id="date_number" name="n_date_number" min="0" style="width:3em" value="<?=(($rss['Date_Number'] ?? null) ? $rss['Date_Number'] : ($_POST['n_date_number'] ?? null))?>">
                                <select id="date_unit" name="date_unit">
                                    <option value="years" <?=($rss['Date_Unit']=='years' ? 'selected' : null)?>>Year(s) Ago</option>
                                    <option value="months" <?=($rss['Date_Unit']=='months' ? 'selected' : null)?>>Month(s) Ago</option>
                                    <option value="weeks" <?=($rss['Date_Unit']=='weeks' ? 'selected' : null)?>>Weeks(s) Ago</option>
                                    <option value="days" <?=($rss['Date_Unit']=='days' ? 'selected' : null)?>>Days(s) Ago</option>
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
                <input type="checkbox" class="chktoggle" id="n_tag_filter_on" name="n_tag_filter_on" value="1" <?=($rss['Tag_Filter_On']>0 ? 'checked' : null)?>>
                <ul class="form-list chktoggle-show">
                    <li>
                        <label for="n_tag_filter_mode">
                            Tag Filter Mode:
                            <select id="n_tag_filter_mode" name="n_tag_filter_mode">
                                <option value='1' <?=($rss['Tag_Filter_Mode']==1 ? 'selected' : null)?>>INCLUDE items with ANY of these tags</option>
                                <option value='2' <?=($rss['Tag_Filter_Mode']==2 ? 'selected' : null)?>>INCLUDE items with ALL of these tags</option>
                                <option value='3' <?=($rss['Tag_Filter_Mode']==3 ? 'selected' : null)?>>EXCLUDE items with ANY these tags</option>
                                <option value='0' <?=($rss['Tag_Filter_Mode']==0 ? 'selected' : null)?>>EXCLUDE items with ALL these tags</option>
                            </select>
                        </label>
                    </li>
                    <li>
                        <label for='tag_filter_list'>
                            Tag Filter List:
                        </label>
                        <p>If more than one tag, separate tags with a comma.</p>
                        <input type="text" name="tag_filter_list" id="tag_filter_list" style="width:90%" value="<?=($rss['Tag_Filter_List'] ? $rss['Tag_Filter_List'] : null)?>">
                    </li>
                </ul>
            </li>
            <li id="item-limit">
                <label for="item_limit_on">
                    Enforce Item Limit:
                </label>
                <input type="hidden" name="item_limit_on" value="0">
                <input type="checkbox" class="chktoggle" id="item_limit_on" name="item_limit_on" value="1" <?=($rss['Item_Limit'] ? 'checked' : null)?>>
                <ul class="form-list chktoggle-show">
                    <li>
                        <label for="n_item_limit">
                            Item Limit:
                        </label>
                        <input type="number" id="n_item_limit" name="n_item_limit" style="width:5em;" min='0' value="<?=($rss['Item_Limit'] ? $rss['Item_Limit'] : null)?>">
                    </li>
                </ul>
            </li>
        </ul>
    </section>
    
    <div class="space-btwn">
        <button type="submit" id="rss-submit" name="edit_rss" formaction="?task=list">
            <i class="fi fi-rs-check"></i> Submit
        </button>
    </div>
</form>

<script src="_js/toggle-on-cond.js"></script>
<script>
    const form = document.getElementById('rss-form');
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
    </main>

<?php
include '_components/admin-footer.php';

