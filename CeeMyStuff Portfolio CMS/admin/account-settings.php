<?php 
$task = null;
$header = '';
if (isset($_GET['task'])) {
    $task = $_GET['task'];
}
if ($task !== 'pw-reset') {
    $admin_panel = true;
    $loginArea = false;
} else {
    $admin_panel = false;
    $loginArea = true;
    $header = '<h1>Password Reset</h1>';
}
include_once '../components/info-head.php';
$page_title = 'Admin Panel : Account Settings';
include '_components/admin-header.inc.php';

$pageExists = true;
if (!$loginArea) {
    $id = ($_SESSION['UserID'] ?? null);
    $adminInputs = null;
    $pullRank = false;
    if ((isset($_SESSION['Permissions']) && $_SESSION['Permissions']>=$set['full_permissions']) && isset($_GET['id'])) {
        $_GET['id'] = cleanInt($_GET['id']);
        if ($_GET['id']) {
            $id = $_GET['id'];
            $adminInputs = "<input type='hidden' name='n_user_id' value='".$id."'/>";
        }
    }
    $account = getAdminAccounts($id);
    if ($id != $_SESSION['UserID']) {
        $pullRank = true;
        $adminInputs .="\n <input type='hidden' name='account_username' value='".$account['Username']."'/>";
    }
    $header = '<h1>Account Settings : '.$account['Username'].'</h1>';
}
?>


<main>
    <div class="space-btwn">
        <?=$header?>
    </div>

<?
switch ($task) :
    case 'email' :
        if ($account['Email_Valid']) {
            include '_components/accounts-email.inc.php';
        } else {$pageExists=false;}
        break;
    case 'password' :
        if ($account['Email_Valid']) {
            include '_components/accounts-password.inc.php';
        } else {$pageExists=false;}
        break;
    case 'username' :
        if ($account['Email_Valid']) {
            include '_components/accounts-username.inc.php';
        } else {$pageExists=false;}
        break;
    case 'permissions' :
        if ($pullRank && $account['Email_Valid']) {
            include '_components/accounts-permissions.inc.php';
        } else {$pageExists=false;}
        break;
    case 'delete' :
        if ($pullRank && $account['Email_Valid']) {
            include '_components/accounts-delete.inc.php';
        } else {$pageExists=false;}
        break;
case null :

    if ($pullRank && !$account['Email_Valid']) :
        //for pending accounts:
        include '_components/accounts-pending.inc.php';
    else :?>
        <hr>
        <? include '_components/accounts-username.inc.php';?>
        <hr>
        <? include '_components/accounts-icon.inc.php';?>
        <hr>
        <? include '_components/accounts-email.inc.php';?>
        <hr>
        <? include '_components/accounts-password.inc.php';?>

        <?if ($pullRank) :?>
            <hr>
            <? include '_components/accounts-permissions.inc.php';?>
            <hr>
            <? include '_components/accounts-delete.inc.php';?>
        <?endif;?>

        <hr>

        <?endif; // endif account is activated?>
    <?break;
    case 'pw-reset' :
        $key = ($_GET['key'] ?? null);
        include '_components/accounts-password-reset.inc.php';
    break;
    default :
    $pageExists=false;
    break;
endswitch; 

if (!$pageExists) :?>
    <p>This isn't the page you're looking for.</p>
    <script>window.location.replace("<?=$baseURL?>/admin/settings.php");</script>
<?endif;?>
</main>

<? if ($pageExists) :?>
<script src="_js/preview-img.js"></script>
<script src="_js/rmv-file-paths.js"></script>
<?endif;

include '_components/admin-footer.php';?>
