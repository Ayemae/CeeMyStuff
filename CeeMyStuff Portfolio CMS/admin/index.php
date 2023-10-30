<?php 
$admin_panel = true;
$loginArea = true;
include '../components/info-head.php';
$page_title = 'Admin Panel';
include '_components/admin-header.inc.php';

$task = "login";
if (isset($_GET['key']) && $_GET['key']>'') {
    $key = htmlspecialchars($_GET['key']);
} else {
    $key = null;
}
if (!is_null($key)) {
    $task = "validate-email";
}

?>

<main>

<?php 
if ($task === "validate-email") : 

    $keyHash = hash("sha256", $key);
    $creds = validateEmail($key);
    $valid = ($creds['Validated'] ?? false);
    echoIfTesting($keyHash);
    if (!$valid) : ?>
         <h2>Email validation failed.</h2><p><?=($msg ?? null)?></p>
   <? else : ?>
    <h2>Activate Your Account</h2>
        <p>Enter a username<?=($creds['ID']>1 ? " and password " : " ")?>to confirm, and then you can login!</p>

        <form method="post" action="<?=$set['dir']?>/admin/" autocomplete="off">
            <input type="hidden" name="key" value="<?=$key?>">
            <ul class="form-list">
                <li>
                    <label for="username">Username:</label>
                    <input type="text" maxlength="80" name="username" autocomplete="off"/>
                </li>
                <? if ($creds['ID']>1) :?>
                    <li>
                        <label for="password">Password:</label>
                        <input type="password" name="password" autocomplete="off"/>
                    </li>
                    <li>
                        <label for="password">Confirm Password:</label>
                        <input type="password" name="password2" autocomplete="off"/>
                    </li>
                <? endif;?>
                <button type="submit" name="verify_account">Submit</button>
            </ul>
        </form>
    <?php 
    endif;?>

   <?php else : 
        if (!$loggedIn) :
            include('_components/login.inc.php');
            if ($loginSuccess ?? null) :?>
                <script>
                    if ( window.history.replaceState ) {
                            window.history.replaceState( null, null, window.location.href );
                        }
                        window.location = window.location.href;
                </script>
            <? unset($loginSuccess);
        endif;?>
        <? else : 
        $sectList = getSectList();
        $pageList = getPageList();
            include('_components/shortcuts.inc.php');?>
        <? endif;

endif; ?>

</main>

<?php
include '_components/admin-footer.php';