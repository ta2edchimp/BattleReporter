<?php

$params = $app->request->post();

if (isset($params["query"])) {
	
	$suggestions = array();
	
	$namePart = $params["query"];
	if (!empty($namePart)) {
		
		$results = $db->query(
			"select value, data from (" .
				"select characterName as value, brCombatantID as data " .
				"from brCombatants " .
				"where brBattlePartyID in (" .
					"select brBattlePartyID " .
					"from brBattleParties " .
					"where battleReportID = :battleReportID1" .
				") and characterName like :nameStartsWith " .
				"group by characterName" .
			") as strtswtbl " .
			"union select value, data from (" .
				"select characterName as value, brCombatantID as data " .
				"from brCombatants " .
				"where brBattlePartyID in (" .
					"select brBattlePartyID " .
					"from brBattleParties " .
					"where battleReportID = :battleReportID2" .
				") and characterName like :nameContains " .
				"group by characterName" .
			") as contnstbl",
			array(
				"battleReportID1" => $battleReportID,
				"nameStartsWith" => $namePart . '%',
				"battleReportID2" => $battleReportID,
				"nameContains" => '%' . $namePart . '%'
			)
		);
		
		if ($results !== NULL && $results !== FALSE)
			$suggestions = $results;
		
	}
	
	$app->status(200);
	$app->contentType('application/json');
	
	echo '{"suggestions":' . json_encode($suggestions) . '}';
	
}
