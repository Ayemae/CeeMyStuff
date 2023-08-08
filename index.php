<?php
include 'components/info-head.php';
// Include router class
require_once('library/Route.php');

$request = cleanRequest();
if (!$request) {
    // if no page request, request home page
    $page = getPage(1, 'id', ($_GET['tag'] ?? null));
} else {
    // if page request exists, get page by the link
    $page = getPage($request, 'link', ($_GET['tag'] ?? null));
}

// home
Route::add('/',function(){
    global $set;global $db;
    printPage(1, 'id');
});

// custom pages
Route::add('/'.$page['Link'], function(){
    global $set;global $db;global $page;
    printPage($page);
});

// custom pages with pagination
Route::add('/'.$page['Link'].'/page/([0-9]*)',function($pageNum){
    global $set;global $db;global $page;
    $pageNum = cleanInt($pageNum);
    printPage($page, $pageNum);
},'get');

// single item displays
Route::add('/view/([0-9]*)',function($id){
    global $set;global $db;
    $id = cleanInt($id);
    printPage(false, $id, 'item');
},'get');

// menu submenu index
Route::add("/menu-submenu-index/(([^\/?]+)\/?$)",function($heading){
    global $set;global $db;
    printPage(false, $heading, 'submenu');
},'get');

// preview
Route::add("/preview/(([^\/?]+)\/?$)",function($area){
    global $set;global $db; global $loggedIn;global $_SESSION;
    if ($loggedIn) {
        printPage(previewPrep($area), $area, 'preview');
    }
},'post');

Route::add("/rss/",function(){
    echo printRSS();
},'get');

Route::run($set['dir']);