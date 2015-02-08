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
    
    include("views/index.php");
    
});

// Show certain battle report
$app->get('/show/:battleReportID(/:battleReportDetail)', function($battleReportID, $battleReportDetail = "") use ($app) {
    
    include("views/show.php");
    
});

// Creating new battle reports
$app->map('/create', function () use ($app) {
    
    include("views/create.php");
    
})->via('GET', 'POST');

// Editing existing (and newly created) battle reports
$app->map('/edit/:battleReportID(/:unpublish)', function ($battleReportID, $battleReportEditAction = "") use ($app) {
    
    include("views/edit.php");
    
})->via('GET', 'POST');

// Log in and out
$app->map('/login', function () use ($app) {
    
    include("views/login.php");
    
})->via('GET', 'POST');
$app->get('/logout', function () use ($app) {
    
    include("views/logout.php");
    
});

// Autocomplete Suggestions:
$app->group('/autocomplete', function () use ($app, $db) {
    
    // Fetching solar systems for input suggestions
    $app->post('/solarSystems', function () use ($app, $db) {
        include("views/autocomplete/solarSystems.php");
    });
    
    // Fetching ship names for input suggestions
    $app->post('/shipNames', function () use ($app, $db) {
        include("views/autocomplete/shipNames.php");
    });
    
    // Fetching known corporation and alliance names
    $app->post('/corpNames', function () use ($app, $db) {
        include("views/autocomplete/corpNames.php");
    });
    $app->post('/alliNames', function () use ($app, $db) {
        include("views/autocomplete/alliNames.php");
    });
        
});


?>