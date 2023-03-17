<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Settings';
include '_components/admin-header.inc.php';
$settings = fetchSettings();
?>

<main>

<h1><i class="fi fi-rs-settings"></i> Site Settings</h1>


<form id='settings-form' method="post" enctype="multipart/form-data">
        <div class="settings-block" id="account-settings-block">
            <label class="settings-header" for="account-sets">
                <h2><i class="fi fi-rs-caret-right"></i> Account/Security Settings</h2>
            </label>
            <input type="checkbox" class="chktoggle invis" id="account-sets">
            <div class="chktoggle-show ease">
                <ul class="form-list">
                    <li>
                        <a href="<?show($route)?>/account-settings.php?task=email">Change Email</a>
                    </li>
                    <li>
                        <a href="<?show($route)?>/account-settings.php?task=password">Change Password</a>
                    </li>
                </ul>
            </div>
        </div>

    <?php foreach ($settings AS $heading => $li) :?>
        <div class="settings-block" id="<?=strtolower($heading)?>-settings-block">
        <label class="settings-header" for="<?=strtolower($heading)?>-sets">
            <h2><i class="fi fi-rs-caret-right"></i> <?=$heading?> Settings</h2>
        </label>
        <input type="checkbox" class="chktoggle invis" id="<?=strtolower($heading)?>-sets">
        <div class="chktoggle-show ease">
        <ul class="form-list">
    <? foreach ($li AS $stg) :?>
        <li>
            <label for="<?show($stg['Field']);?>"><?show($stg['Field']);?>:</label>
            
            <p><?show($stg['Description'])?></p>

            <?php switch ($stg['Type']) :

                case 'select': ?>
                    <select id="<?show($stg['Key']);?>" name="<?show($stg['Key']);?>">
                    <?php foreach($stg['Options'] AS $opt) : ?>
                        <option value="<?show($opt);?>" <?echo ($opt != $stg['Value'] ? null : 'selected' )?>><?show($opt);?></option>
                    <?php endforeach;?>
                </select>
            <?php break;

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
                <?php if ($stg['Value']>'') :?>
                    <div id="<?show($stg['Key']);?>_current" class="settings-image-wrapper">
                        <img src="<?show($set['dir'].$stg['Value']);?>" alt="<?show($stg['Field']);?> Current Image" style="height:auto;width:auto;max-width:600px;max-height:300px;">
                    </div>
                    <input type="hidden" id="rmv_<?show($stg['Key']);?>" name="n_rmv_<?show($stg['Key']);?>" value="0">
                    <button type="button" class="small red" onclick="rmvFilePath(this, 'rmv_<?show($stg['Key']);?>', '<?show($stg['Key']);?>_current', 1)">Remove Current Image</button>
                <? else : ?>
                    <i>None</i>
                <? endif;
                break;


             default : 
                $checkbox = '';
                if ($stg['Type']=== 'checkbox') :
                    if ($stg['Value'] === 'checked') {
                        $checkbox = 'checked=checked';
                    }
                    $stg['Value'] = 'checked'; 
                endif;?>
                    <input type="hidden" name="<?show($stg['Key']);?>" value="">
                <input type="<?show($stg['Type']);?>" id="<?show($stg['Key']);?>" name="<?show($stg['Key']);?>" <?=($stg['Type']==='text' ? 'maxlength=85' : null )?> value="<?show($stg['Value'])?>" <?show($checkbox);?>>
            <?php break;

        endswitch; ?>
        </li>
    <?php endforeach;?>
    </div></ul></div>
    <?php endforeach; ?>

<button name="save_settings">Save Settings</button>
</form>

</main>

<script src="_js/rmv-file-paths.js"></script>
<?php
include '_components/admin-footer.php';