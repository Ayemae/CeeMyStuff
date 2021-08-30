<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Admin Panel';
include '_components/admin-header.inc.php';
?>

<main>

<?php if (!$loggedIn) :
include 'login.php';
 else :?>
    <p>Logged in!</p>
    <script>
        window.location.replace("<?show($route)?>/categories.php");
    </script>
<?php endif; ?>

</main>

<?php
include '../components/footer.php';