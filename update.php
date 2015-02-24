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
