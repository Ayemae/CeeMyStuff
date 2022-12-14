<nav id="site-menu">
    <ul class="site-menu">
        <?php foreach ($menu as $link) :?>
            <li class="site-menu-item"><?=$link?></li>
        <?php endforeach; unset($link);?>
    </ul>
</nav>