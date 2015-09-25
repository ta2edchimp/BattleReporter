<?php

$output = array();

$params = array();

$battleList = Battle::getList(array(
	"onlyPublished" => !(User::isLoggedIn() && User::can("edit"))
));

$previewBattlesTotal = 0;
$previewStartTime = 0;
$previewEndTime = 0;
$previewISKdestroyed = 0;
$previewEfficiencyAvg = 0;

if ($battleList !== NULL && $battleList !== FALSE) {
	
	foreach ($battleList as &$battle) {
		
		$previewBattlesTotal = $previewBattlesTotal + 1;
		
		$battle["date"] = date("Y-m-d", $battle["startTime"]);
		if ($previewStartTime == 0 || $previewStartTime > $battle["startTime"])
			$previewStartTime = $battle["startTime"];
		if ($previewEndTime < $battle["endTime"])
			$previewEndTime = $battle["endTime"];
		
		$totalLost = $battle["iskLostTeamA"] + $battle["iskLostTeamB"] + $battle["iskLostTeamC"];
		if ($totalLost > 0.0)
			$battle["efficiency"] = 1.0 - $battle["iskLostTeamA"] / $totalLost;
		else
			$battle["efficiency"] = 0.0;
		
		$previewISKdestroyed = $previewISKdestroyed + $totalLost;
		$previewEfficiencyAvg = $previewEfficiencyAvg + $battle["efficiency"];

		$battle["hasAAR"] = !empty($battle["summary"]);
	
	}
	
	$output["battleList"] = $battleList;
	
	$previewEfficiencyAvg = ($previewEfficiencyAvg * 100) / $previewBattlesTotal;
	
	if ($previewISKdestroyed < 1000000000000) {
		if ($previewISKdestroyed < 1000000000) {
			$previewISKdestroyed = number_format($previewISKdestroyed / 1000000, 2, '.', ',') . " million";
		} else {
			$previewISKdestroyed = number_format($previewISKdestroyed / 1000000000, 2, '.', ',') . " billion";
		}
	} else {
		$previewISKdestroyed = number_format($previewISKdestroyed / 1000000000000, 2, '.', ',') . " trillion";
	}
	
}

// Compile preview data
$output['previewMeta'] = array();

$output['previewMeta']['title'] = (BR_OWNER == "" ? "" : (BR_OWNER . "'s")) . " BattleReporter Index";
$output['previewMeta']['description'] = BR_OWNERCORP_NAME . " fought " . $previewBattlesTotal . " battles, with an average efficiency of " . number_format($previewEfficiencyAvg, 1, '.', ',') . "%. " . $previewISKdestroyed . " ISK have been destroyed.";
$output['previewMeta']['image'] = "//image.eveonline.com/corporation/" . BR_OWNERCORP_ID . "_128.png";


$app->render("index.html", $output);
