<?php
    if (isset($_GET['sent']) && $_GET['sent']==='true') {
        $resetSent=true;
    } else {
        $resetSent=false;
    }
?>

<section>

<?php if ($key) :?>

        <p>Reset your password here.</p>
        <form method="post" action="<?=$set['dir']?>/admin/account-settings.php?task=pw-reset&sent=true">
            <input type="hidden" name="key" value="<?=$key?>">
            <ul class="form-list">
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

<?php else :
    if (!$resetSent) :?>
    <p>To send an email link to reset your password, please type in the email associated with your account.</p>

    <form method="post">
        <ul class="form-list">
            <li>
                <label for="email">Your CeeMyStuff Account Email:</label>
                <input type="email" id="email" name="email"/>
            </li>
        </ul>
    <button name="send_password_reset">Submit</button>
    </form>
    <? endif; // endif $resetSent
    endif; // endif $key?>
</section>