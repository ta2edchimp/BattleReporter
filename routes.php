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
$app->map('/create', function () use ($app, $db) {
    
    include("views/create.php");
    
})->via('GET', 'POST');

// Fetching solar systems for input suggestions
$app->post('/autocomplete/solarSystems', function () use ($app, $db) {
    
    include("views/autocomplete/solarSystems.php");
    
});


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