<?php

$output = array();

$params = array();

$battleList = $db->query(
	"select br.battleReportID, br.brTitle as title, br.brStartTime as startTime, br.brEndTime as endTime, br.brPublished as published, " .
		"br.brCreatorUserID as creatorUserID, ifnull(u.userName, '') as creatorUserName, " .
		"br.brUniquePilotsTeamA, br.brUniquePilotsTeamB, br.brUniquePilotsTeamC, " .
		"ifnull((select sum(c.priceTag) from brCombatants as c where c.brHidden = 0 and c.brBattlePartyID = (" .
			"select bp.brBattlePartyID from brBattleParties as bp where bp.battleReportID = br.battleReportID and bp.brTeamName = 'teamA' limit 1" .
		")), 0.0) as iskLostTeamA, " .
		"ifnull((select sum(c.priceTag) from brCombatants as c where c.brHidden = 0 and c.brBattlePartyID = (" .
			"select bp.brBattlePartyID from brBattleParties as bp where bp.battleReportID = br.battleReportID and bp.brTeamName = 'teamB' limit 1" .
		")), 0.0) as iskLostTeamB, " .
		"ifnull((select sum(c.priceTag) from brCombatants as c where c.brHidden = 0 and c.brBattlePartyID = (" .
			"select bp.brBattlePartyID from brBattleParties as bp where bp.battleReportID = br.battleReportID and bp.brTeamName = 'teamC' limit 1" .
		")), 0.0) as iskLostTeamC, " .
		"sys.solarSystemName, " .
		"(select count(commentID) from brComments as cm where cm.battleReportID = br.battleReportID and cm.commentDeleteTime is NULL) as commentCount, " .
		"(select count(videoID) from brVideos as v where v.battleReportID = br.battlereportID) as footageCount " .
	"from brBattles as br inner join mapSolarSystems as sys " .
		"on br.solarSystemID = sys.solarSystemID " .
		"left outer join brUsers as u on u.userID = br.brCreatorUserID " .
	"where br.brDeleteTime is NULL " .
	(User::isLoggedIn() && User::can("edit") ? "" : "and br.brPublished = 1 ") .
	"order by br.brStartTime desc"
);

if ($battleList != NULL) {
	
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
