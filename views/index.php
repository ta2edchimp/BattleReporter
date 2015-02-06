<?php

$output = array();


global $db;

$battleList = $db->query(
    "select br.battleReportID, br.brTitle as title, br.brStartTime as startTime, br.brEndTime as endTime, " .
        "(select count(c.brCombatantID) from brCombatants as c where c.brHidden = 0 and c.brBattlePartyID = (" .
            "select bp.brBattlePartyID from brBattleParties as bp where bp.battleReportID = br.battleReportID and bp.brTeamName = 'teamA' limit 1" .
        ")) as pilotCountTeamA, " .
        "(select count(c.brCombatantID) from brCombatants as c where c.brHidden = 0 and c.brBattlePartyID = (" .
            "select bp.brBattlePartyID from brBattleParties as bp where bp.battleReportID = br.battleReportID and bp.brTeamName = 'teamB' limit 1" .
        ")) as pilotCountTeamB, " .
        "(select count(c.brCombatantID) from brCombatants as c where c.brHidden = 0 and c.brBattlePartyID = (" .
            "select bp.brBattlePartyID from brBattleParties as bp where bp.battleReportID = br.battleReportID and bp.brTeamName = 'teamC' limit 1" .
        ")) as pilotCountTeamC, " .
        "ifnull((select sum(c.priceTag) from brCombatants as c where c.brHidden = 0 and c.brBattlePartyID = (" .
            "select bp.brBattlePartyID from brBattleParties as bp where bp.battleReportID = br.battleReportID and bp.brTeamName = 'teamA' limit 1" .
        ")), 0.0) as iskLostTeamA, " .
        "ifnull((select sum(c.priceTag) from brCombatants as c where c.brHidden = 0 and c.brBattlePartyID = (" .
            "select bp.brBattlePartyID from brBattleParties as bp where bp.battleReportID = br.battleReportID and bp.brTeamName = 'teamB' limit 1" .
        ")), 0.0) as iskLostTeamB, " .
        "ifnull((select sum(c.priceTag) from brCombatants as c where c.brHidden = 0 and c.brBattlePartyID = (" .
            "select bp.brBattlePartyID from brBattleParties as bp where bp.battleReportID = br.battleReportID and bp.brTeamName = 'teamC' limit 1" .
        ")), 0.0) as iskLostTeamC, " .
        "sys.solarSystemName " .
    "from brBattles as br inner join mapSolarSystems as sys " .
        "on br.solarSystemID = sys.solarSystemID " .
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