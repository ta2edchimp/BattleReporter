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

// Show certain battle report
$app->get('/show/:battleReportID', function($battleReportID) use ($app) {
    
    include("views/show.php");
    
});

// Creating new battle reports
$app->map('/create', function () use ($app) {
    
    include("views/create.php");
    
})->via('GET', 'POST');

// Editing existing (and newly created) battle reports
$app->map('/edit/:battleReportID', function ($battleReportID) use ($app) {
    
    include("views/edit.php");
    
})->via('GET', 'POST');

// Fetching solar systems for input suggestions
$app->post('/autocomplete/solarSystems', function () use ($app, $db) {
    
    include("views/autocomplete/solarSystems.php");
    
});


?>