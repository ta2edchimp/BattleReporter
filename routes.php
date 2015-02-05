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

// Posting edited (and newly created) battlereports
$app->post('/edit/:brid', function ($brID) use ($app) {
    
    include("views/edit.php");
    
});

// Fetching solar systems for input suggestions
$app->post('/autocomplete/solarSystems', function () use ($app, $db) {
    
    include("views/autocomplete/solarSystems.php");
    
});


?>