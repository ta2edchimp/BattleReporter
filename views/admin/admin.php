<?php

if (!User::isAdmin()) {
	$app->render("404.html");
	$app->stop();
}

$output = array(
	"adminMissingLossValues" => array()
);


$availableActions = array("", "refetchforlossvalues");
$adminAction = strtolower($adminAction);
if (!in_array($adminAction, $availableActions))
	$adminAction = "";

switch ($adminAction) {
	
	case "refetchforlossvalues":
		$output["adminMissingLossValues"]["action"] = refetchKillMailsForMissingLossValues();
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


function refetchKillMailsForMissingLossValues() {
	
	$result = array(
		"success" => true
	);
	$missingCount = -1;
	$refetchCount = 0;
	
	global $db;
	
	$combatants = $db->query("select * from brCombatants where died = 1 and priceTag <= 0");
	if ($combatants === NULL) {
		return array(
			"success" => false,
			"message" => "An error occurred when trying to collect the kills missing their loss values from database."
		);
	} else {
		$missingCount = count($combatants);
	}
	
	foreach ($combatants as $combatant) {
		
		$combatant = new Combatant($combatant);
		// Maybe track the count of omitted combatants?
		if ($combatant == null)
			continue;
		
		$killArray = KBFetch::fetchKill($combatant->killID);
		foreach ($killArray as $kill) {
			
			if ($kill->killID != $combatant->killID ||
				!isset($kill->zkb) || !isset($kill->zkb->totalValue))
				continue;
			
			$combatant->priceTag = floatVal($kill->zkb->totalValue);
			$combatant->save();
			
			$refetchCount++;
			
		}
		
	}
	
	if ($missingCount <= 0)
		return null;
	
	if ($refetchCount > 0) {
		$result["message"] = "$refetchCount kills completed by adding the missing loss values.";
	} else {
		$result = array(
			"success" => false,
			"message" => "No kill mail refetched in order to add missing loss values."
		);
	}
	
	return $result;
	
}
