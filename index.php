
<?php
include 'components/info-head.php';
// Include router class
include('library/Route.php');
$request = str_replace($set['dir'].'/','',$_SERVER['REQUEST_URI']);
if (strpos($request,'/')) {
    $request = substr($request,0,strpos($request,'/'));
}
$page = getPage(strtolower($request), 'link');

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


Route::run($set['dir']);