<?php 
include_once '../components/info-head.php';
include '_components/admin-header.inc.php';
?>

<main>
<?php if (empty($_GET)) :

 if (!$loggedIn) {
    include 'login.php';
    }?>
    
<?php elseif (isset($_GET['key'])) : 
$validation = validateEmail($_GET['key']);
if (!$validation || is_string($validation)) : 
    echo '<h2>Account validation failed.</h2><p>'.$validation.'</p>';
else : ?>
    <h2>Account verified!</h2>
    <p><a href="index.php">Click here to login</a>.</p>
<?php endif;
elseif (isset($_GET['pw-reset']) && $_GET['pw-reset']=1) : ?>
<form method="post">
    <p>Click the button below to send an email with a link to reset your password.</p>

    <button name="send_password_reset">Reset my password</button>
</form>
<?php endif;?>
</main>

<?php
include '../components/footer.php';