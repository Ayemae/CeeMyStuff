<? 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Settings';
include '_components/admin-header.inc.php';
$settings = fetchSettings();
?>

<main>

<h1><i class="fi fi-rs-settings"></i> Site Settings</h1>

<form id='settings-form' method="post" enctype="multipart/form-data">
    <ul class="form-list">
        <li class="settings-block" id="account-settings-block">
            <input type="checkbox" class="chktoggle invis" id="account-sets">
            <label class="settings-header chktoggle-label" for="account-sets">
                <h3>
                    <i class="fi fi-rs-caret-right"></i><i class="fi fi-rs-caret-down"></i>  
                    Accounts/Security
                </h3>
            </label>
            <div class="chktoggle-show ease">
                <ul class="sub-form-list">
                    <li>
                        <a href="<?show($route)?>/account-settings.php">Change Account Details</a>
                    </li>
                    <? if ($user['Permissions']>=$set['full_permissions']) :?>
                    <li>
                        <a href="<?show($route)?>/manage-accounts.php">Manage Accounts</a>
                    </li>
                    <? endif?>
                </ul>
            </div>
        </li>

    <? foreach ($settings AS $heading => $li) :?>
        <li class="settings-block" id="<?=strtolower($heading)?>-settings-block">
        <input type="checkbox" class="chktoggle invis" id="<?=strtolower($heading)?>_sets" name="<?=strtolower($heading)?>_sets">
        <label class="settings-header chktoggle-label" for="<?=strtolower($heading)?>_sets">
            <h3>
                <i class="fi fi-rs-caret-right"></i><i class="fi fi-rs-caret-down"></i> 
                <?=$heading?>
            </h3>
        </label>
        <div class="chktoggle-show ease">
        <ul class="sub-form-list">
    <? foreach ($li AS $stg) :?>
        <li>
            <label for="<?show($stg['Field']);?>"><?show($stg['Field']);?>:</label>
            
            <p><?show(strDecode($stg['Description']))?></p>

            <? switch ($stg['Type']) :

                case 'select': ?>
                    <select id="<?show($stg['Key']);?>" name="<?show($stg['Key']);?>">
                    <? foreach($stg['Options'] AS $opt) : ?>
                        <option value="<?show($opt);?>" <?echo ($opt != $stg['Value'] ? null : 'selected' )?>><?show($opt);?></option>
                    <? endforeach;?>
                </select>
            <? break;

            case 'function' :
                switch ($stg['Key']) :
                    case ('timezone') :
                        echo selectTimezone($stg['Value']); 
                    break;
                    case ('theme') :
                        echo selectTheme($stg['Value']);
                    break;
                    default:
                    null;
                    break;
                endswitch;
            break;


            case 'img-file' : ?>
                <input type="file" id="<?show($stg['Key']);?>" name="<?show($stg['Key']);?>" value="">
                <br/>Current:
                <? if ($stg['Value']>'') :?>
                    <div id="<?show($stg['Key']);?>_current" class="settings-image-wrapper">
                        <img src="<?show($set['dir'].$stg['Value']);?>" alt="<?show($stg['Field']);?> Current Image" style="height:auto;width:auto;max-width:600px;max-height:300px;">
                    </div>
                    <input type="hidden" id="rmv_<?show($stg['Key']);?>" name="n_rmv_<?show($stg['Key']);?>" value="0">
                    <button type="button" class="small red" onclick="rmvFilePath(this, 'rmv_<?show($stg['Key']);?>', '<?show($stg['Key']);?>_current', 1)">Remove Current Image</button>
                <? else : ?>
                    <i>None</i>
                <? endif;
                break;


                case 'checklist': ?>
                <fieldset class="<?show($stg['Key'])?>-styles">
                    <? $i=1; 
                    foreach($stg['Options'] AS $opt) : ?>
                        <label for="<?show($stg['Key'])?>[<?=$i?>]">
                            <input type="checkbox" name="<?show($stg['Key'])?>[<?=$i?>]" value="<?show($opt);?>" <?=(!in_array($opt,$stg['Value']) ? null : 'checked=checked' )?>>
                            <?show($opt);?>
                        </label>
                        <br/>
                    <?$i++; 
                endforeach;?>
                </fieldset>
            <? break;


             default : 
                $checkbox = '';
                if ($stg['Type']=== 'checkbox') :
                    if ($stg['Value'] === 'checked') {
                        $checkbox = 'checked=checked';
                    }
                    $stg['Value'] = 'checked'; 
                endif;?>
                    <input type="hidden" name="<?show($stg['Key']);?>" value="">
                <input type="<?show($stg['Type']);?>" 
                    id="<?show($stg['Key']);?>" 
                    name="<?show($stg['Key']);?>" 
                    <?=($stg['Type']==='text' ? 'maxlength=85' : null )?>
                    <?=($stg['Type']==='number' && isset($stg['Options'][0]) && isset($stg['Options'][1]) ? 'min="'.$stg['Options'][0].'" max="'.$stg['Options'][1] : null ).'"';?> 
                    value="<?show($stg['Value'])?>" 
                    <?show($checkbox);?>>
            <? break;

        endswitch; ?>
        </li>
        <? endforeach;?>
        </ul>
        </div> <!-- end chktoggle-show -->
    </li> <!-- End settings-block -->
    <? endforeach; ?>
</ul>

<button name="save_settings">Save Settings</button>
</form>

</main>

<script src="_js/rmv-file-paths.js"></script>
<?
include '_components/admin-footer.php';