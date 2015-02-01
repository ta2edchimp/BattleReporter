<?php

require_once('vendor/autoload.php');
require_once('classes/Db.php');
require_once('classes/DAO.php');
require_once('classes/User.php');
require_once('classes/Session.php');
require_once('classes/Utils.php');

// By default, not in debug mode
$BR_DEBUGMODE = false;

// Load individual configuration
include('config.php');

// Initialize all the things ...
include('init.php');

// User session
$session = new Session();
session_set_save_handler($session, true);
session_cache_limiter(false);
session_start();

if (!User::isLoggedIn())
    User::checkAutoLogin();

include('twig.php');

// Set up routing
include('routes.php');

$app->run();

?>