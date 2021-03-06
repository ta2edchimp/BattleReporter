<?php

// Set default timezone to "EVE Time", aka "UTC"
date_default_timezone_set("UTC");

// Global base path holder
$basePath = dirname(__FILE__);

// Global caching settings
phpFastCache::setup(array(
	'storage' => 'file',
	'htaccess' => false,
	'path' => "$basePath/cache",
	'securityKey' => 'battleReporter'
));

// Global database access wrapper object
Db::setCredentials(DB_NAME, DB_USER, DB_PASS, DB_HOST);
$db = Db::getInstance();

// Global (Slim) application object
$app = new \Slim\Slim(array(
	'mode' => $BR_DEBUGMODE === true ? 'development' : 'production',
	'debug' => ($BR_DEBUGMODE === true),
	'log.enabled' => true,
	'log.level' => $BR_DEBUGMODE === true ? \Slim\Log::DEBUG : \Slim\Log::WARN,
	'log.writer' => new \Slim\Logger\DateTimeFileWriter(array(
		'path' => "$basePath/logs"
	))
));

$app->config('debug', $BR_DEBUGMODE);

// Theming ...
if (defined('BR_THEME'))
	$theme = BR_THEME;
if(!isset($theme) || empty($theme))
	$theme = "default";
elseif(!is_dir("$basePath/public/themes/$theme"))
	$theme = "default";
$app->config(array("templates.path" => "$basePath/public/themes/$theme"));

$app->view(new \Slim\Views\Twig());

// Give us pretty error messages ... (if in debug mode)
if ($BR_DEBUGMODE === true) {
	$app->config('whoops.editor', 'textmate');
	$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware);
}

// Configuring PhealNG
// Enable Caching -- ATTENTION: Directory MUST exist already!!
\Pheal\Core\Config::getInstance()->cache = new \Pheal\Cache\FileStorage("$basePath/cache/pheal/");
// Enable AccessMask Check
\Pheal\Core\Config::getInstance()->access = new \Pheal\Access\StaticCheck();

// Take care of environments without curl support
if (defined('BR_FETCH_METHOD')) {
	if (strtolower(BR_FETCH_METHOD) == "file") {
		// Set the killmail / sso fetcher to type "file"
		Utils::setFetcher(new \Utils\Fetcher\File());
		// Set PhealNG's fetcher to type "file"
		\Pheal\Core\Config::getInstance()->fetcher = new \Pheal\Fetcher\Curl();
	}
}
