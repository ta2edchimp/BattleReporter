<?php

if (!User::isAdmin()) {
	$app->render("404.html");
	$app->stop();
}

require_once("$basePath/classes/Admin.php");

$output = array(
	"adminMissingLossValues" => array(),
	"adminCurrentReleaseInfo" => array()
);


$availableActions = array("", "refetchforlossvalues", "refetchfordamagevalues", "repopulatestatistics");
$adminAction = strtolower($adminAction);
if (!in_array($adminAction, $availableActions))
	$adminAction = "";

switch ($adminAction) {
	
	case "refetchforlossvalues":
		$output["adminMissingLossValues"]["action"] = Admin::refetchKillMailsForMissingLossValues();
		break;

	case "refetchfordamagevalues":
		$output["adminMissingDamageValues"]["action"] = Admin::refetchKillMailsForMissingDamageValues();
		break;

	case "repopulatestatistics":
		$output["Miscellanous"] = Admin::repopulateStatistics();
	
	default:
		break;
	
}

// Compare the current installation's version to the latest available
$adminCurrentReleaseResult = \Utils::fetch(
	"https://api.github.com/repos/ta2edchimp/BattleReporter/releases/latest",
	null,
	array(
		"headers" => array("User-Agent: ta2edchimp/BattleReporter-UpdateSearch"),
		"queryParams" => false,
		"caching" => "auto",
		"cachePath" => __DIR__ . '/../cache'
	)
);
if (!empty($adminCurrentReleaseResult)) {
	$decodedInfo = json_decode($adminCurrentReleaseResult);
	$encodedVersion = "";
	if (isset($decodedInfo->tag_name) && !empty($decodedInfo->tag_name)) {
		$encodedVersion = $decodedInfo->tag_name;
	}

	$decodedCurrentVersion = \Utils::parseVersion($encodedVersion);
	$decodedInstalledVersion = \Utils::parseVersion(BR_VERSION);

	$output["adminCurrentReleaseInfo"]["currentVersion"] = $decodedCurrentVersion;
	$output["adminCurrentReleaseInfo"]["installedVersion"] = $decodedInstalledVersion;
	$output["adminCurrentReleaseInfo"]["installedVersionUpToDate"] = (\Utils::compareVersions($decodedCurrentVersion, $decodedInstalledVersion) <= 0);
	$output["adminCurrentReleaseInfo"]["releaseTitle"] = $decodedInfo->name;
	$output["adminCurrentReleaseInfo"]["releaseInfo"] = (new Parsedown())->text($decodedInfo->body);
	$output["adminCurrentReleaseInfo"]["releaseUrl"] = $decodedInfo->html_url;
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

// Battle reports with missing loss damage values
$missingDamageValuesResults = $db->row(
	"select count(battleReportID) as brCount " .
	"from brBattles " .
	"where battleReportID in (" .
		"select battleReportID " .
		"from brBattleParties " .
		"where brBattlePartyID in (" .
			"select brBattlePartyID " .
			"from brCombatants " .
			"where died = 1 and damageTaken <= 0" .
		")" .
	")"
);
if ($missingDamageValuesResults === NULL)
	$output["adminMissingDamageValues"]["error"] = true;
else
	$output["adminMissingDamageValues"]["battleReportsCount"] = $missingDamageValuesResults["brCount"];

$app->render("admin/admin.html", $output);
