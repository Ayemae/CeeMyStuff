
<?php
$request = $_SERVER['REQUEST_URI'];
include_once 'components/info-head.php';
include 'components/header.php';?>

</main>

<?php
switch ($request) {
    case $set['dir'].'/' :
    case $set['dir'] :
        echo 'hello, test.';
        // require __DIR__ . '/views/index.php';
        break;
    case '/about' :
        // require __DIR__ . '/views/about.php';
        echo 'test home';
        break;
    default:
        http_response_code(404);
        // require __DIR__ . '/views/404.php';
        break;
}?>

</main>

<?php
include 'components/footer.php';