<?php

if (!User::isAdmin()) {
	$app->render("404.html");
	$app->stop();
}

require_once("$basePath/classes/Admin.php");

$output = array(
	"adminMissingLossValues" => array()
);


$availableActions = array("", "refetchforlossvalues");
$adminAction = strtolower($adminAction);
if (!in_array($adminAction, $availableActions))
	$adminAction = "";

switch ($adminAction) {
	
	case "refetchforlossvalues":
		$output["adminMissingLossValues"]["action"] = Admin::refetchKillMailsForMissingLossValues();
		break;
	
	default:
		break;
	
}



// Battle reports with missing loss values
$results = $db->row(
	"select count(battleReportID) as brCount " .
	"from brBattles " .
	"where battleReportID in (" .
		"select battleReportID " .
		"from brBattleParties " .
		"where brBattlePartyID in (" .
			"select brBattlePartyID " .
			"from brCombatants " .
			"where died = 1 and priceTag <= 0" .
		")" .
	")"
);
if ($results === NULL)
	$output["adminMissingLossValues"]["error"] = true;
else
	$output["adminMissingLossValues"]["battleReportsCount"] = $results["brCount"];

$app->render("admin/admin.html", $output);
