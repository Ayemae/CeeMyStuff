<?php 
include_once '../library/functions.php';
$page_title = 'Reset Password';
include '_components/admin-header.inc.php';
?>


<main>
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

<?php endif;?>
</main>

<?php
include '_components/footer.php';