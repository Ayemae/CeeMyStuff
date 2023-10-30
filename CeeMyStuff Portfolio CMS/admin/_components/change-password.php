<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Settings';
include '_components/admin-header.inc.php';
?>

<section>

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
</section>
<? include '_components/admin-footer.inc.php'; ?>