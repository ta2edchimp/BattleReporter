<?php

define('BR_VERSION', '0.5.7');

require_once('vendor/autoload.php');

require_once('classes/Db.php');

// PHP 5.5-like password hash functions in prior versions
if (!function_exists('password_hash') || !function_exists('password_verify')
	|| !function_exists('password_needs_rehash') || !function_exists('password_get_info'))
	require_once('classes/passwordLib.php');
require_once('classes/User.php');
require_once('classes/Session.php');

require_once('classes/Fetcher/FetcherInterface.php');
require_once('classes/Fetcher/FetcherBase.php');
require_once('classes/Fetcher/Curl.php');
require_once('classes/Fetcher/File.php');
require_once('classes/Utils.php');

require_once('classes/KBFetch.php');
require_once('classes/SolarSystem.php');
require_once('classes/Item.php');

require_once('classes/Battle.php');
require_once('classes/BattleParty.php');
require_once('classes/Kill.php');
require_once('classes/Combatant.php');


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
