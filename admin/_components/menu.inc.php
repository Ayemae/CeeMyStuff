<a href="<?show($route)?>/settings.php">Settings</a>
<a href="<?show($route)?>/categories.php">Categories</a>
<?php if ($loggedIn) :?>
    <form method="post">
        <button name="logout">Logout</button>
    </form>
<?php  endif; ?>