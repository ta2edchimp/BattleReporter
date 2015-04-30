<?php

if (php_sapi_name() != "cli")
	die("This is a cli script." . PHP_EOL . "Please run it from the commandline using \"php install.php\"!" . PHP_EOL);

$basePath = dirname(__FILE__);

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	//error has been suppressed with "@"
	if (error_reporting() === 0)
		return;
	throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}

// Force all warnings into errors
set_error_handler("exception_error_handler");

// If the config file is present, quit installer
if (file_exists($basePath . "/config.php")) {
	out("|r|BattleReporter has already been installed.");
	out("Please delete" . PHP_EOL . "$basePath/config.php" . PHP_EOL . "if you wish to reinstall.", true, true);
}


out(PHP_EOL . "|w|Welcome the the BattleReporter Installer" . PHP_EOL . "|w|========================================" . PHP_EOL);

out("In order to install and setup you'll be asked a few questions." . PHP_EOL .
	"Questions will always have a default answer specified in []'s." . PHP_EOL .
	"Example: |g|What is 1+1? [2]" . PHP_EOL .
	"|n|Hitting enter will let you select the default answer." . PHP_EOL);

prompt("Please hit enter to continue");
$config = array();


// Ask for some basics
out();
out("Please enter the BattleReporter owner's name." . PHP_EOL . "It will be used in page titles and headlines.");
$config["BR_OWNER"] = prompt("BR Owner's name (can be empty)");

out();
out("Please enter the name of the corporation, BattleReporter will be assigned to." . PHP_EOL .
	"It must be entered |r|exactly|n| as it spelled ingame!");
$config["BR_OWNERCORP_NAME"] = "";
while (empty($config["BR_OWNERCORP_NAME"]))
	$config["BR_OWNERCORP_NAME"] = prompt("BR Corporation's name");

out();
out("Please enter the ID of the entered corporation." . PHP_EOL .
	"You can obtain it by searching for \"" . $config["BR_OWNERCORP_NAME"] . "\" on https://zkillboard.com or" . PHP_EOL .
	"https://beta.eve-kill.net and take the result link's numerical part," . PHP_EOL .
	"e.g. https://zkillboard.com/corporation/|w|98270080|n|/ when searching for |w|Bitslix");
$config["BR_OWNERCORP_ID"] = "";
while (empty($config["BR_OWNERCORP_ID"]))
	$config["BR_OWNERCORP_ID"] = prompt($config["BR_OWNERCORP_NAME"] . "'s ID");


// Ask for Login features
out();
out("Do you want to enable users to login with their EVE Online accounts?");
$br_login_via_eve_sso = prompt("Hit return or enter \"yes\", else enter \"no\"", "yes");
$config["BR_LOGINMETHOD_EVE_SSO"] = "false";
$config["BR_LOGINMETHOD_EVE_SSO_CLIENTID"] = "";
$config["BR_LOGINMETHOD_EVE_SSO_SECRET"] = "";
if (strtolower($br_login_via_eve_sso) == "yes") {
	$config["BR_LOGINMETHOD_EVE_SSO"] = "true";
	out();
	out("You need to register the application at https://developers.eveonline.com" . PHP_EOL .
		"and obtain a Client ID and a Secret Key, in order to be enabled to use" . PHP_EOL .
		"EVE Online Single Sign On (see README.md for instructions)." . PHP_EOL .
		"If you enter empty values, login via EVE SSO will be disabled.");
	$br_eve_sso_clientid = prompt("Enter your Client ID", "");
	if (!empty($br_eve_sso_clientid)) {
		$config["BR_LOGINMETHOD_EVE_SSO_CLIENTID"] = $br_eve_sso_clientid;
		$br_eve_sso_secret = prompt("Enter your Secret Key", "");
		if (!empty($br_eve_sso_secret)) {
			$config["BR_LOGINMETHOD_EVE_SSO_SECRET"] = $br_eve_sso_secret;
		} else {
			$config["BR_LOGINMETHOD_EVE_SSO_CLIENTID"] = "";
			$config["BR_LOGINMETHOD_EVE_SSO_SECRET"] = "";
		}
	} else {
		$config["BR_LOGINMETHOD_EVE_SSO_CLIENTID"] = "";
		$config["BR_LOGINMETHOD_EVE_SSO_SECRET"] = "";
	}
}


// Ask who will be able to login
out();
out("Do you want to enable characters from other corporations to login, too?" . PHP_EOL .
	"Being logged in is required to post comments." . PHP_EOL .
	"Creating and editing battle reports still will be limited to corp members!");
$br_login_othercorps = prompt("Enter \"yes\" if you want to enable other corps' members to login", "yes");
$config["BR_LOGIN_ONLY_OWNERCORP"] = "true";
if (strtolower($br_login_othercorps) == "yes")
	$config["BR_LOGIN_ONLY_OWNERCORP"] = "false";


// Ask, whether to store an api key to check characters' roles within the owner corp
out();
out("If you wish, you may provide an API Key now." . PHP_EOL .
	"It will allow you to enable BattleReporter to permit deleting battle reports" . PHP_EOL .
	"to users who are |w|Directors|n| of |w|" . $config["BR_OWNERCORP_NAME"] . "|n|.");
$apiKeyID = "";
$apiKeyvCode = "";
$apiKeyActive = false;
$apiSetup = strtolower(prompt("Would you like to set it up? Enter \"yes\" or \"no\"", "yes"));
if ($apiSetup == "y" || $apiSetup == "yes") {
	
	out();
	out("Please browse to |b|https://community.eveonline.com/support/api-key/|n| and create" . PHP_EOL .
		"a new API Key for your |w|corporation|n| (NOT character!) with at least access" . PHP_EOL .
		"granted to |w|MemberSecurity|n| (AccessMask = 512)." . PHP_EOL .
		"If you leave a field empty, API Key configuration will be cancelled.");
	
	$apiKeyID = prompt("API Key ID:", "");
	if (!empty($apiKeyID))
		$apiKeyvCode = prompt("API Key vCode", "");
	
	if (!empty($apiKeyID) && !empty($apiKeyvCode)) {
		out();
		$result = strtolower(prompt("Do you want to permit deleting reports to |y|Directors|n|?", "yes"));
		$apiKeyActive = ($result == "y" || $result == "yes");
	}
	
}


// Ask for advanced functions
out();
out("Do you want to enable comments on BattleReports?");
$enableComments = prompt("Enter \"yes\" or \"no\"", "yes");
if (strtolower($enableComments) == "yes")
	$config["BR_COMMENTS_ENABLED"] = "true";
else
	$config["BR_COMMENTS_ENABLED"] = "false";


// Specify where to fetch the killmails from
out();
out("BattleReporter can fetch the killmails to create its reports from either" . PHP_EOL .
	"zKillboard or Eve-Kill.net. It's up to you which one to choose. Their APIs" . PHP_EOL .
	"are identical and usually they are both equally up-to-date." . PHP_EOL .
	"|g|Enter [1] to use zKillboard, or [2] to use Eve-Kill." . PHP_EOL .
	"Hit enter to choose [1] zKillboard.");
$config["BR_FETCH_SOURCE_NAME"] = "nope";
$br_fetch_source_choices = array("", "1", "2");
while (!in_array($config["BR_FETCH_SOURCE_NAME"], $br_fetch_source_choices))
	$config["BR_FETCH_SOURCE_NAME"] = prompt("Select killmail source", "1");
if ($config["BR_FETCH_SOURCE_NAME"] == "2")
	$config["BR_FETCH_SOURCE_NAME"] = "Eve-Kill";
else
	$config["BR_FETCH_SOURCE_NAME"] = "zKillboard";

$config["BR_FETCH_SOURCE_URL"] = "https://zkillboard.com/";
if ($config["BR_FETCH_SOURCE_NAME"] == "2")
	$config["BR_FETCH_SOURCE_URL"] = "https://beta.eve-kill.net/";


// Specify the http communication method to use to fetch killmails etc.
out();
out("Please specify your preferred method to fetch data over networks." . PHP_EOL .
	"Usually, you can stick with the default (curl). Switch to file if" . PHP_EOL .
	"you encounter any problems." . PHP_EOL .
	"|g|Enter [1] to use \"curl\", or [2] to use \"file\"." . PHP_EOL .
	"Hit enter to choose [1] \"curl\".");
$config["BR_FETCH_METHOD"] = "nope";
$br_fetch_method_choices = array("", "1", "2");
while (!in_array($config["BR_FETCH_METHOD"], $br_fetch_method_choices))
	$config["BR_FETCH_METHOD"] = prompt("Select fetch method", "1");
if ($config["BR_FETCH_METHOD"] == "2")
	$config["BR_FETCH_METHOD"] = "file";
else
	$config["BR_FETCH_METHOD"] = "curl";


// Specify database credentials
out();
$config["DB_HOST"] = prompt("Database host (use 127.0.0.1 if localhost causes issues)", "localhost");
$config["DB_NAME"] = "";
$config["DB_USER"] = "";
$config["DB_PASS"] = "";
while (empty($config["DB_NAME"]))
	$config["DB_NAME"] = prompt("Database name");
while (empty($config["DB_USER"]))
	$config["DB_USER"] = prompt("Database user's name");
while (empty($config["DB_PASS"]))
	$config["DB_PASS"] = prompt("Database user's password");


// Ask for a customized theme
out();
out("If you have want to use a custom theme, please specify its name." . PHP_EOL .
	"The theme's name equals is its directory name within the themes directory");
$config["BR_THEME"] = prompt("Theme", "default");


// Ask for the initial administrator user's password
out();
out("By default, a user account named \"admin\" will be created." . PHP_EOL .
	"Please enter its password. This must not be empty.");
$adminPassword = "";
while (empty($adminPassword))
	$adminPassword = prompt("Enter admin password", "");


// Write config to file
$configFile = "$basePath/config.php";
$configFileContents = file_get_contents("$basePath/config.blueprint.php");

foreach ($config as $key => $value)
	$configFileContents = str_replace("%$key%", $value, $configFileContents);

out();
out("Writing config file ... ", false, false);

if (file_put_contents($configFile, $configFileContents) === false)
	out("|r|failed" . PHP_EOL . "Could not write config file $configFile", true, true);

out("|g|success");


// Test the configured settings
out();
out("Testing configured settings ... ");

try {
	require_once($configFile);
} catch (Exception $ex) {
	unlink($configFile);
	out("|r|failed" . PHP_EOL .
		$ex, true, true);
}
out("|g|success");


// Run composer to meet all dependencies
out();
out("Installing dependencies via composer ...");
try {
	// Check if composer isn't already installed
	$location = exec("which composer");
	if(!$location) {
		// Composer isn't installed
		out("Installing composer:");
		chdir($basePath);
		passthru("php -r \"eval('?>'.file_get_contents('https://getcomposer.org/installer'));\"");
		chdir($basePath);
		out();
		out("Installing vendor files");
		passthru("php composer.phar install");
		out();
		out("|g|composer install complete!");
	} else {
		// Composer IS installed
		out("Using already installed composer:");
		chdir($basePath);
		out();
		out("Installing vendor files.");
		passthru("composer install");
		out();
		out("|g|Vendor file installation completed.");
	}
} catch (Exception $ex) {
	unlink($configFile);
	out("|r|failed" . PHP_EOL .
		$ex, true, true);
}
out("|g|success");


// Test the database connection
out();
out("Initializing and testing database connection ... ", false, false);
try {
	chdir($basePath);
	require_once("$basePath/classes/Db.php");
	$db = new Db(DB_NAME, DB_USER, DB_PASS, DB_HOST);
	$one = $db->single("select 1 from dual");
	if ($one != "1") {
		unlink($configFile);
		out("|r|failed" . PHP_EOL .
			"Database connection available, but test statement did not return the expected result.", true, true);
	}
} catch (Exception $ex) {
	unlink($configFile);
	out("|r|failed" . PHP_EOL .
		"Could not connect to database." . PHP_EOL .
		$ex, true, true);
}
out("|g|success");


// Do all the database stuff
out();
out("Setting up the database ...");
try {
	$sqlFiles = scandir("$basePath/database");
	foreach ($sqlFiles as $file) {
		if (substr($file, -strlen(".sql")) != ".sql")
			continue;
		
		$table = str_replace(".sql", "", $file);
		out("Adding table |g|$table|n| ... ", false);
		
		$sqlFile = "$basePath/database/$file";
		
		$db->import($sqlFile);
		
		out("|g|done");
	}
} catch (Exception $ex) {
	unlink($configFile);
	out(PHP_EOL . "|r|Error while database setup." . PHP_EOL .
		$ex, true, true);
}


out();
out("Setting up |w|admin|n| user account ... ", false, false);
$adminPasswordHashed = "";
// ... and finally set up the admin user account
if (!function_exists('password_hash')) {
	try {
		require_once("$basePath/classes/passwordLib.php");
		$adminPasswordHashed = password_hash($adminPassword, PASSWORD_BCRYPT);
	} catch (Exception $ex) {
		unlink($configFile);
		out("|r|failed" . PHP_EOL . "Fatal error" . PHP_EOL .
			"|n|Your server's PHP version is not up-to-date (less than |w|5.5|n|) and " .
			"the library to provide downward compatibility could not be loaded.", true, true);
	}
} else {
	$adminPasswordHashed = password_hash($adminPassword, PASSWORD_BCRYPT);
}
if (empty($adminPasswordHashed)) {
	unlink($configFile);
	out("|r|failed", true, true);
}
try {
	if ($db->query("insert into brUsers (userName, password, isAdmin) values ('admin', :password, 1)", array("password" => $adminPasswordHashed)) == NULL) {
		throw new Exception("Could not insert new account into users table.");
	}
} catch (Exception $ex) {
	unlink($configFile);
	out("|r|failed" . PHP_EOL .
		$ex, true, true);
}
out("|g|success");


// If provided and configured, save the RoleCheck API Key and set it to active
if (($apiSetup == "y" || $apiSetup == "yes") && !empty($apiKeyID) && !empty($apiKeyvCode)) {
	
	$result = $db->query(
		"INSERT INTO brEveApiKeys " .
			"(brApiKeyName, brApiKeyOwnerID, brApiKeyActive, keyID, vCode) " .
		"VALUES " .
			"(:brApiKeyName, :brApiKeyOwnerID, :brApiKeyActive, :keyID, :vCode)",
		array(
			"brApiKeyName" => "RoleCheck",
			"brApiKeyOwnerID" => 0,
			"brApiKeyActive" => $apiKeyActive ? 1 : 0,
			"keyID" => $apiKeyID,
			"vCode" => $apiKeyvCode
		)
	);
	
	if ($result === 1) {
		out("|g|successfully stored API Key in database");
		if ($apiKeyActive === true)
			out("|g|successfully enabled character role check");
	} else {
		out("|r|storing API Key in database failed");
		if ($apiKeyActive === true)
			out("|r|enabling character role check failed");
	}
	
}


out();
out("Creating cache directories ... ", false, false);
@mkdir("$basePath/cache/");
@mkdir("$basePath/cache/pheal/");
out("|g|success");


out();
out("Creating log directory ...", false, false);
@mkdir("$basePath/logs/");
out("|g|success");


out();
out("|g|BattleReporter successfully installed");
out();
out("You may now browse to your BattleReporter's site and login as admin.");


/*
 *	Output and Input Helper Functions
 *	Shamelessly stolen from https://github.com/EVE-KILL/zKillboard
 */

function out($message = "", $die = false, $newline = true) {
	
	$colors = array(
		"|w|" => "1;37",	// White
		"|b|" => "0;34",	// Blue
		"|y|" => "0;33",	// Yellow
		"|g|" => "0;32",	// Green
		"|r|" => "0;31",	// Red
		"|n|" => "0"		// Neutral
	);
	
	$message = "$message|n|";
	
	foreach ($colors as $color => $value)
		$message = str_replace($color, "\033[" . $value . "m", $message);
	
	if ($newline)
		echo $message . PHP_EOL;
	else
		echo $message;
	
	if ($die)
		die();
}

function prompt($prompt, $default = "") {
	
	out("$prompt [$default] ", false, false);
	
	$answer = trim(fgets(STDIN));
	
	if (strlen($answer) == 0)
		return $default;
	
	return $answer;
	
}
