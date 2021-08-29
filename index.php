
<?php
include_once 'components/info-head.php';
// Include router class
include('library/Route.php');

$request = str_replace($set['dir'].'/','',$_SERVER['REQUEST_URI']);

$page = getPage($request);

// Add base route (startpage)
Route::add('/',function(){
    echo 'This is a homepage';
});

// custom pages route!
Route::add('/'.$page['Page_Name'],function(){
    global $set;global $db;global $page;
    $admin_panel = false;
    include_once 'components/info-head.php';
    $page_title = $page['Page_Name'];
    include_once 'components/header.php';
    echo '<h1>'.$page['Page_Header'].'</h1>';
    include_once 'components/footer.php';
});



Route::run($set['dir']);