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
<ul class="form-list">
    <?php foreach ($settings AS $stg) :?>
        <li>
            <label for="<?show($stg['Field']);?>"><b><?show($stg['Field']);?>:</b></label>
            
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
                <br/><img src="<?show($set['dir'].$stg['Value']);?>" alt="Header image" style="height:auto;width:auto;max-width:600px;max-height:300px;">
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
                <input type="<?show($stg['Type']);?>" id="<?show($stg['Key']);?>" name="<?show($stg['Key']);?>" value="<?show($stg['Value'])?>" <?show($checkbox);?>>
            <?php break;

        endswitch; ?>
        </li>
    <?php endforeach; ?>
<ul>

<button name="save_settings">Save Settings</button>
</form>

</main>

<?php
include '_components/admin-footer.php';