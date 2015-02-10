<?php

// Set default timezone to "EVE Time", aka "UTC"
date_default_timezone_set("UTC");

// Global base path holder
$basePath = dirname(__FILE__);

// Global database access wrapper object
$db = new Db(DB_NAME, DB_USER, DB_PASS, DB_HOST);

// Global (Slim) application object
$app = new \Slim\Slim();

$app->config('debug', $BR_DEBUGMODE);

// Theming ...
if (!defined('BR_THEME'))
    $theme = BR_THEME;
if(!isset($theme) || empty($theme))
    $theme = "default";
elseif(!is_dir(__DIR__ . "/public/themes/$theme"))
    $theme = "default";
$app->config(array("templates.path" => __DIR__ ."/public/themes/$theme"));

$app->view(new \Slim\Views\Twig());

// Give us pretty error messages ... (if in debug mode)
if ($BR_DEBUGMODE == true)
    $app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware);

?>