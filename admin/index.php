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
    
    <?php else :?>
        <?php include '_components/login.inc.php';?>
        
<?php endif; ?>

</main>

<?php
include '_components/admin-footer.php';