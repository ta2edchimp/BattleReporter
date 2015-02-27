<?php

$output = array();

$params = array();

$battleList = Battle::getList(array(
	"onlyPublished" => !(User::isLoggedIn() && User::can("edit"))
));

if ($battleList !== NULL && $battleList !== FALSE) {
	
	foreach ($battleList as &$battle) {
		
		$battle["date"] = date("Y-m-d", $battle["startTime"]);
		
		$totalLost = $battle["iskLostTeamA"] + $battle["iskLostTeamB"] + $battle["iskLostTeamC"];
		if ($totalLost > 0.0)
			$battle["efficiency"] = 1.0 - $battle["iskLostTeamA"] / $totalLost;
		else
			$battle["efficiency"] = 0.0;
	
	}
	
	$output["battleList"] = $battleList;
	
}

$app->render("index.html", $output);
