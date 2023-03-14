<?php
include 'components/info-head.php';
// Include router class
include('library/Route.php');
$request = str_replace($set['dir'].'/','',$_SERVER['REQUEST_URI']);
if (strpos($request,'/')) {
    $request = substr($request,0,strpos($request,'/'));
}
if ($request==='') {
    $page = getPage(0, 'id');
} else {
    $page = getPage(strtolower($request), 'link');
}

// home
Route::add('/',function(){
    global $set;global $db;
    printPage();
});

// custom pages
Route::add('/'.$page['Link'], function(){
    global $set;global $db;global $page;
    printPage($page);
});

// custom pages with pagination
Route::add('/'.$page['Link'].'/page/([0-9]*)',function($pageNum){
    global $set;global $db;global $page;
    $pageNum = filter_var($pageNum, FILTER_SANITIZE_NUMBER_INT);
    printPage($page, $pageNum);
},'get');

// single item displays
Route::add('/view/([0-9]*)',function($id){
    global $set;global $db;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    printPage(false, $id, true);
},'get');

// menu dropdown index
Route::add("/menu-dropdown-index/(([^\/?]+)\/?$)",function($heading){
    global $set;global $db;
    printPage(false, 1, false, $heading);
},'get');

Route::run($set['dir']);