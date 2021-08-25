<?php 
$root = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'], 2);
include_once $root.'/components/info-head.php';
$admin_panel = true;
if (!$loggedIn && $admin_panel) {
    kickOut();
    exit();
}
$page_title = 'Settings';
include $root.'/components/header.php';
$settings = fetchSettings();
?>

<main>

<form method="post">
<ul class="form-list">
    <?php foreach ($settings AS $stg) :?>
        <li>
            <label for="<?show($stg['Field']);?>"><?show($stg['Field']);?>:</label>
            <?php if ($stg['Type'] != 'select') : 
                $checkbox = '';
                if ($stg['Type']=== 'checkbox') :
                    if ($stg['Value'] === 'checked') {
                        $checkbox = 'checked=checked';
                    }
                    $stg['Value'] = 'checked'; ?>
                    <input type="hidden" name="<?show($stg['Key']);?>" value="">
                <?php endif; ?>
                <input type="<?show($stg['Type']);?>" id="<?show($stg['Key']);?>" name="<?show($stg['Key']);?>" value="<?show($stg['Value'])?>" <?show($checkbox);?>>
            <?php else : ?>
                <select id="<?show($stg['Key']);?>" name="<?show($stg['Key']);?>">
                    <?php foreach($stg['Options'] AS $opt) : ?>
                        <option value="<?show($opt);?>" <?echo ($opt != $stg['Value'] ? null : 'selected' )?>><?show($opt);?></option>
                    <?php endforeach;?>
                </select>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
<ul>

<button name="save_settings">Save Settings</button>
</form>

</main>

<?php
include $root.'/components/footer.php';