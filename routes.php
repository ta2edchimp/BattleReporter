<?php

/*
 *  Routings, on Error
 */
// 404 - File Not Found
$app->notFound(function () use ($app) {
    $app->render('404.html');
});

// Any Error ...
$app->error(function (\Exception $e) use ($app) {
    include('view/error.php');
});


/*
 *  Default routings
 */
// Homepage
$app->get('/', function () use ($app) {
    
    $app->render("base.html");
    
});

// Creating new battlereports
$app->map('/create', function () use ($app) {
    
    include("views/create.php");
    
})->via('GET', 'POST');


/*
 *  Routings, only available in Debug Mode
 */
if ($BR_DEBUGMODE == true) {
    // Testing playground
    $app->get('/test', function () {
        
        include("views/test.php");
        
    });
}

?>