<?php

if (!User::isAdmin()) {
	$app->render("404.html");
	$app->stop();
}

require_once("$basePath/classes/Admin.php");

$output = array(
	"adminMissingLossValues" => array()
	"adminCurrentReleaseInfo" => array()
);


$availableActions = array("", "refetchforlossvalues");
$adminAction = strtolower($adminAction);
if (!in_array($adminAction, $availableActions))
	$adminAction = "";

switch ($adminAction) {
	
	case "refetchforlossvalues":
		$output["adminMissingLossValues"]["action"] = Admin::refetchKillMailsForMissingLossValues();
	
	default:
		break;
	
}



// Battle reports with missing loss values
$missingLossValuesResults = $db->row(
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
if ($missingLossValuesResults === NULL)
	$output["adminMissingLossValues"]["error"] = true;
else
	$output["adminMissingLossValues"]["battleReportsCount"] = $missingLossValuesResults["brCount"];

$adminCurrentReleaseResult = \Utils::fetch(
	"https://api.github.com/repos/ta2edchimp/BattleReporter/releases/latest",
	null,
	array(
		"headers": array("User-Agent: ta2edchimp/BattleReporter-UpdateSearch"),
		"queryParams" => false,
		"caching" => "auto",
		"cachePath" => __DIR__ . '/../cache'
	)
);
if (!empty($adminCurrentReleaseResult)) {
	$decodedInfo = json_decode($adminCurrentReleaseResult)
	$encodedVersion = "";
	if (isset($decodedInfo["tag_name"]) && !empty($decodedInfo["tag_name"])) {
		$encodedVersion = $decodedInfo["tag_name"];
	}
	$output["adminCurrentReleaseInfo"]["raw"] = json_encode($decodedInfo, JSON_PRETTY_PRINT);
	$output["adminCurrentReleaseInfo"]["currentVersion"] = \Utils::parseVersion($encodedVersion);
	$output["adminCurrentReleaseInfo"]["installedVersion"] = \Utils::parseVersion(BR_VERSION);
}

$app->render("admin/admin.html", $output);
