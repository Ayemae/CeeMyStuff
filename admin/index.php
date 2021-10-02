<?php 
$admin_panel = true;
$loginArea = true;
include_once '../components/info-head.php';
$page_title = 'Admin Panel';
include '_components/admin-header.inc.php';
?>

<main>

<?php 
if (isset($_GET['key'])) : 
    $validation = validateEmail($_GET['key']);
    if (!$validation || is_string($validation)) : 
        echo '<h2>Account validation failed.</h2><p>'.$validation.'</p>';
    else : ?>
        <h2>Account verified. Login below!</h2>
    <?php 
    include '_components/login.inc.php';
    endif;
elseif ($loggedIn) :?>
    <p>Logged in!</p>
    <script>
        window.location.replace("<?show($route)?>/pages.php");
    </script>
<?php elseif (isset($_GET['pw-reset']) && $_GET['pw-reset']=1) : ?>
    <form method="post">
        <p>Click the button below to send an email with a link to reset your password.</p>
    
        <button name="send_password_reset">Reset my password</button>
    </form>

    <?php else :?>
        <h2>Login</h2>
        <?php include '_components/login.inc.php';?>
        
<?php endif; ?>

</main>

<?php
include '../components/footer.php';