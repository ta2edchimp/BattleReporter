<?php
/**

	Updater for BattleReporter Database Tables.

**/

if (php_sapi_name() != "cli")
	die("This is a cli script." . PHP_EOL . "Please run it from the commandline using \"php update.php\"!" . PHP_EOL);

$basePath = dirname(__FILE__);

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	//error has been suppressed with "@"
	if (error_reporting() === 0)
		return;
	throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}

// Force all warnings into errors
set_error_handler("exception_error_handler");

// If the config file is not present, quit updater
if (!file_exists($basePath . "/config.php"))
	out("|r|BattleReporter has not been installed properly.", true, true);


out(PHP_EOL . "|w|Welcome the the BattleReporter Updater" . PHP_EOL . "|w|======================================" . PHP_EOL);

out("The updater will make an update of the existing BattleReporter database," . PHP_EOL .
	"import the current database schemas and reinsert the previous contents." . PHP_EOL);

prompt("Please hit enter to you are ready to proceed");


out();
out("Checking for required cache and log directories ... ", false, false);
$requiredFolders = array("$basePath/cache/", "$basePath/cache/pheal/", "$basePath/logs/");
foreach ($requiredFolders as $folder) {
	if (!file_exists($folder))
		@mkdir($folder);
}
out("|g|done");


// Updating Composer ...
out();
out("Updating Composer ... ");
try {
	chdir($basePath);
	passthru("php composer.phar self-update");
} catch (Exception $ex) {
	out("|r|failed");
	out("Could not update Composer:" . PHP_EOL . $ex->getMessage(), true, true);
}
out("|g|success");


// ... and any registered dependencies
out();
out("Updating dependencies via Composer ... ");
try {
	passthru("php composer.phar update --optimize-autoloader");
} catch (Exception $ex) {
	out("|r|failed");
	out("Could not update dependencies via Composer:" . PHP_EOL . $ex->getMessage(), true, true);
}
out("|g|success");


// Load configuration
out("Trying to include the current configuration ... ", false, false);
try {
	require_once("$basePath/config.php");
} catch (Exception $ex) {
	out("|r|failed");
	out("The configuration could not be initialized:" . PHP_EOL . $ex->getMessage(), true, true);
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


/*
** Setting any kind of maintenance mode or sth.
** should go here.
*/


// Do all the database stuff
out();
out("Updating the database ...");
try {
	$sqlFiles = scandir("$basePath/database");
	foreach ($sqlFiles as $file) {
		if (substr($file, -strlen(".sql")) != ".sql")
			continue;
		
		$table = str_replace(".sql", "", $file);
		
		$sqlFile = "$basePath/database/$file";
		
		if (substr($table, 0, 2) == 'br') {
			
			// This is BattleReporter's own stuff
			out("Updating table |g|$table|n| ...", false, false);
			
			// Look, if table exists, if not its either a new one
			// or the update process has somehow been interrupted
			// after backing it up
			$result = $db->query("show tables like '$table'");
			if ($result !== FALSE && count($result) > 0) {
				// Backup existing tables
				out(" |y|backup", false, false);
				$db->query("alter table $table rename bak_$table");
				out(",", false, false);
			}
			
			
			// Import sql file
			out(" |y|import", false, false);
			$db->import($sqlFile);
			
			// Reinsert from backup, if existing
			$result = $db->query("show tables like 'bak_$table'");
			if ($result !== FALSE && count($result) > 0) {
				
				out(" & |y|reinsert", false, false);
				
				// Get currently present column names
				$colsCurrent = $db->query("show columns from $table");
				// Get previously present column names
				$colsPrevious = $db->query("show columns from bak_$table");
				$colsToInsert = array();
				
				// Collect all the still used columns in their correct order
				// to later build the proper update (insert into) statement
				foreach ($colsPrevious as &$column) {
					$column = $column["Field"];
				}
				foreach ($colsCurrent as &$column) {
					if (in_array($column["Field"], $colsPrevious) === TRUE) {
						$colsToInsert[] = "b." . $column["Field"];
					} else {
						$colsToInsert[] = empty($column["Default"]) ? "NULL" : $column["Default"];
					}
				}
				$colsToInsert = implode(", ", $colsToInsert);
				if (empty($colsToInsert))
					$colsToInsert = "b.*";
				
				// Reinsert old values into the new table ...
				try {
					$db->query("insert ignore into $table (select $colsToInsert from bak_$table as b)");
					$db->query("drop table bak_$table");
				} catch (Exception $ex) {
					out("|r|failed" . PHP_EOL .
						"Reverting table $table back to previous state:" . PHP_EOL . $ex);
					$db->query("drop table $table");
					$db->query("alter table bak_$table rename $table");
					continue;
				}
				
			}
			
		} elseif (substr($table, 0, 2) == 'z_') {
			
			// This is also BattleReporter's own stuff,
			// but mostly prepopulation stuff.
			// Silently omit this ...
			continue;
			
		} else {
			
			// This usually is ccp stuff (EVE sql dumps).
			// Whatever it is exactly, import it ...
			$result = $db->query("show tables like '$table'");
			if ($result !== FALSE && count($result) > 0)
				out("Updating table |g|$table|n| ... |y|update", false, false);
			 else
				out("Importing table |g|$table|n| ... |y|import", false, false);
			
		}
		
		out(" |g|done");
	}
} catch (Exception $ex) {
	out(" |r|failure" . PHP_EOL .
		"|r|Error while database setup." . PHP_EOL .
		$ex, true, true);
}


// Check, if there's a Corporation API Key for checking character roles saved ...
out();
out("Checking for API Key to check character's corp roles ... ", false, false);
try {
	
	$result = $db->row("SELECT * FROM brEveApiKeys WHERE brApiKeyName = 'RoleCheck' AND brApiKeyOwnerID = 0");
	
	if ($result === FALSE) {
		
		out("|y|not found");
		
		out();
		out("You may enter an API Key now." . PHP_EOL .
			"It will allow you to enable BattleReporter to permit deleting battle reports" . PHP_EOL .
			"to users who are |w|Directors|n| of |w|" . BR_OWNERCORP_NAME . "|n|.");
		
		$setupNow = strtolower(prompt("Would you like to set it up now? (y)es or (n)o", "y"));
		
		if ($setupNow == "y" || $setupNow == "yes") {
			
			out();
			out("Please browse to |b|https://community.eveonline.com/support/api-key/|n| and create" . PHP_EOL .
				"a new API Key for your |w|corporation|n| (NOT character!) with at least access" . PHP_EOL .
				"granted to |w|MemberSecurity|n| (AccessMask = 512)." . PHP_EOL .
				"If you leave a field empty, API Key configuration will be cancelled.");
			
			$keyID = "";
			$vCode = "";
			$keyActive = false;
			
			$keyID = prompt("API Key ID:", "");
			if (!empty($keyID))
				$vCode = prompt("API Key vCode", "");
			
			if (!empty($keyID) && !empty($vCode)) {
				
				out();
				$result = strtolower(prompt("Do you want to permit deleting reports to |y|Directors|n|?", "y"));
				$keyActive = ($result == "y" || $result == "yes");
				
				$result = $db->query(
					"INSERT INTO brEveApiKeys " .
						"(brApiKeyName, brApiKeyOwnerID, brApiKeyActive, keyID, vCode) " .
					"VALUES " .
						"(:brApiKeyName, :brApiKeyOwnerID, :brApiKeyActive, :keyID, :vCode)",
					array(
						"brApiKeyName" => "RoleCheck",
						"brApiKeyOwnerID" => 0,
						"brApiKeyActive" => $keyActive ? 1 : 0,
						"keyID" => $keyID,
						"vCode" => $vCode
					)
				);
				
				if ($result === 1) {
					out("|g|successfully stored API Key in database");
					if ($keyActive === true)
						out("|g|successfully enabled character role check");
				} else {
					out("|r|storing API Key in database failed");
					if ($keyActive === true)
						out("|r|enabling character role check failed");
				}
				
			} else {
				out("|y|API Key configuration and character role check setup cancelled");
			}
			
		}
		
	} elseif ($result === NULL) {
		out("|r|failed");
	} else {
		out("|g|success");
	}
	
} catch (Exception $ex) {
	out(" |r|failure" . PHP_EOL .
		$ex);
}


/*
** Disabling any kind of maintenance mode or sth.
** should go here.
*/


out();
out("|g|BattleReporter successfully updated");
out();


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
