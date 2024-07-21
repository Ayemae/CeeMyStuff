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
$page['Msg'] = ($msg ?? null);

if (!isset($method)) {
    $method=null;
} else {
    $method=strtolower($method);
}
switch ($method) {
    case 'post':
    case 'put':
    case 'head':
        // do nothing
        break;
    default:
    $method = 'get';
    break;
}


// home
Route::add('/',function(){
    
    printPage(1, 'id');
});

// custom pages
Route::add('/'.$page['Link'], function(){
    global $page;
    printPage($page);
}, $method);

// custom pages with pagination
Route::add('/'.$page['Link'].'/page/([0-9]*)',function($pageNum){
    global $page;
    $pageNum = cleanInt($pageNum);
    printPage($page, $pageNum);
},'get');

// single item displays
Route::add('/view/([0-9]*)',function($id){
    $id = cleanInt($id);
    printPage(false, $id, 'item');
},'get');

// single section index displays
Route::add('/section-index/([0-9]*)',function($id){
    $id = cleanInt($id);
    printPage(false, $id, 'section');
},'get');

// menu submenu index
Route::add("/menu-submenu-index/(([^\/?]+)\/?$)",function($heading){
    printPage(false, $heading, 'submenu');
},'get');

// preview
Route::add("/preview/(([^\/?]+)\/?$)",function($area){
     global $loggedIn;
    if ($loggedIn) {
        printPage(previewPrep($area), $area, 'preview');
    }
},'post');

Route::add("/rss/",function(){
    echo printRSS();
},'get');

Route::run($set['dir']);