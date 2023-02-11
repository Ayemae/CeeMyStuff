<?php 
if (isset($_GET['task'])) {
    $task = $_GET['task'];
}if ($task !== 'pw-reset') {
    $admin_panel = true;
}
include_once '../components/info-head.php';
$page_title = 'Admin Panel : Account';
include '_components/admin-header.inc.php';
?>

<main>

<?
switch ($task) :
case "email" :?>
    <h2>Change Email Address</h2>

    <form method='post'>
        <ul class="form-list">
            <li>
                <label for="email">New Email</label>
                <input type="email" id="email" name="new_email" max-length="255">
            </li>
        </ul>
        <button name="change_email">Submit</button>
    </form>
<? break;
case 'password' :?>
<h2>Change Your Password</h2>

<form class="form-list" method="post" action="?submitted=1">
    <ul>
    <li>
            <label for="password">Your Current Password:</label>
            <input type="password" id="old-password" name="old_password" max-length="255"/>
        </li>
        <li>
            <label for="password">Your New Password:</label>
            <input type="password" id="password" name="password" max-length="255"/>
        </li>
        <li>
            <label for="password2">Confirm Your New Password:</label>
            <input type="password" id="password2" name="password2" max-length="255"/>
        </li>
    </ul>

    <button name="change_password">Submit</button>
</form>
<?break;
case 'pw-reset' :?>

<h2>Password Reset</h2>

<?php if (isset($_GET['key'])) :?>

        <p>Reset your password here.</p>
        <form method="post">
            <input type="hidden" name="key" value="<?=$_GET['key']?>">
            <ul>
                <li>
                    <label for="password">Your New Password:</label>
                    <input type="password" id="password" name="password" max-length="255"/>
                </li>
                <li>
                    <label for="password2">Confirm Your New Password:</label>
                    <input type="password" id="password2" name="password2" max-length="255"/>
                </li>
            </ul>

            <button name="reset_password">Submit</button>
        </form>

<?php else: ?>

    <p>To send an email link to reset your password, please type in the email associated with your account.</p>

    <form method="post">

            <label for="email">Your CeeMyStuff Account Email:</label>
            <input type="email" id="email" name="email"/>

    <button name="send_password_reset">Submit</button>
</form>

<?php endif;
break;
default :?>
<p>This isn't the page you're looking for.</p>
<?break;
endswitch;?>
</main>

<?
include '_components/admin-footer.php';