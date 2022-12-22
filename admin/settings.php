<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Settings';
include '_components/admin-header.inc.php';
$settings = fetchSettings();
?>

<main>

<h1><i class="fi fi-rs-settings"></i> Site Settings</h1>

<form method="post">
<ul class="form-list">
    <?php foreach ($settings AS $stg) :?>
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

             default : 
                $checkbox = '';
                if ($stg['Type']=== 'checkbox') :
                    if ($stg['Value'] === 'checked') {
                        $checkbox = 'checked=checked';
                    }
                    $stg['Value'] = 'checked'; ?>
                    <input type="hidden" name="<?show($stg['Key']);?>" value="">
                <?php elseif ($stg['Type']=== 'file') : ?>
                    <input type="hidden" name="<?show($stg['Key']);?>_stored" value="<?show($stg['Value']);?>">
                <?php endif; ?>
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