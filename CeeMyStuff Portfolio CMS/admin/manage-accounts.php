<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Admin Panel : Manage Accounts';
include '_components/admin-header.inc.php';
$task = null;
if (isset($_GET['task'])) {
    $task = $_GET['task'];
}
?>

<main>

<?
if (is_null($user['Permissions']) || $user['Permissions']<$set['full_permissions']) :?>
    <p>This isn't the page you're looking for.</p>
    <script>window.location.replace("<?=$baseURL?>/admin/settings.php");</script>
<? else :
    switch ($task) :
        case 'add-account' :?>
        <h2>Add New Account</h2>
        <p>Enter the email address of the person you'd like to invite to create an admin account. Make sure it's entered correctly!
            <br/>An email will be sent to this person's address to confirm their new account &mdash; <strong>tell them to check their spam/junk folder if they don't see it after a few minutes</strong>.</p>
            <p>Keep in mind that <strong>the account activation key will expire 48 hours after it is sent</strong>.</p>
            <form method="post">
                <ul class="form-list">
                    <li>
                        <label for="account_email">Invite From Email Address:</label><br class="mobile-only"/>
                        <input class="width-300px" type="email" id="email" name="account_email"/>
                    </li>
                </ul>
            <button name="send_account_invite">Invite to Create Account</button>
        </form>
        <? break;

        case 'manage-accounts' :
            // default should inherit 'manage-accounts'
        default :
        $accounts = getAdminAccounts();?>
            <div class="space-btwn">
                <h1>Manage Accounts</h1>
                <a class="button" href="<?show($route)?>/manage-accounts.php?task=add-account">
                    <i class="fi fi-rs-plus"></i> Add New Account
                </a>
            </div>

        <div class="admin-table manage-accounts">
            <ul class="table-head">
                <li>User</li>
                <li>Email</li>
                <li>Icon</li>
                <li>Permissions</li>
                <li>Last Login</li>
                <li>Creation Date</li>
                <li><!--Tools--></li>
            </ul>
        <ul class="table-index">
            <?php foreach ($accounts AS $account) :?>
            <li class="account-item<?=(!$account['Permissions'] ? ' gradient greyed-out' : '')?>">
                <input type="hidden" name="account_id" value="<?=$account['ID']?>">
                <div>
                    <?show($account['Username'])?>
                </div>
                <div class="flex column">
                    <div>
                        <?=($account['Email_Valid'] 
                        ? '<i class="fi fi-rs-check green" title="Activated"></i> ' 
                        : ($account['Activation_Expired'] 
                        ?  '<i class="fi fi-rs-cross red" title="Expired"></i> ' 
                        : '<i class="fi fi-rs-question" title="Pending"></i> '))?>
                        
                        &nbsp;<?show($account['Email']);?>
                    </div>
                    <?if (!$account['Email_Valid'] && $account['Activation_Expired']) :?>
                        <div class="text-small">
                            [Activation Expired]
                        </div>
                    <?endif;?>
                </div>
                <div class="image">
                    <? if ($account['Icon_Path']) :?>
                    <img src="<?show($set['dir'].insertFilenameTag($account['Icon_Path'], '50px'))?>" alt="<?show($account['Username'])?>">
                    <? else : ?>
                        n/a
                    <? endif;?>
                </div>
                <div>
                    <?show($account['Title'])?>
                </div>
                <div class="flex column">
                    <?show($account['Last_Login_Date'])?>
                </div>
                <div class="flex column">
                    <?show($account['Account_Created_Date'])?>
                </div>
                <div>
                    <a href="<?=$set['dir']?>/admin/account-settings.php?id=<?=$account['ID']?>">Manage</a>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        </div>
    
        <? break;
    endswitch;
endif; // end "if $user['Permissions']>0"
?>

</main>

<?
include '_components/admin-footer.php';