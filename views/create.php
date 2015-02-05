<?php


if (!User::can('create'))
    $app->redirect('/');

$output = array();

// Get all POST variables
$parameters = $app->request->post();

if ($parameters != null) {
    
    $battleTimespan         = null;
    $battleSolarSystem      = null;
    
    /*
     *  Interpret specified timespan
     */
    if (isset($parameters["battleTimespan"])) {
        $inputBattleTimespan = $parameters["battleTimespan"];
        
        $output["inputBattleTimespan"] = $inputBattleTimespan;
        
        if (KBFetch::testTimespanPattern($inputBattleTimespan)) {
            $battleTimespan = $inputBattleTimespan;
            $output["battleTimespan"] = $inputBattleTimespan;
        } else {
            $output["battleTimespanError"] = true;
        }
    }
    
    /*
     *  Interpret specified system
     */
    if (isset($parameters["battleSolarSystemName"])) {
        $inputBattleSolarSystemName = $parameters["battleSolarSystemName"];
        
        $battleSolarSystem = SolarSystem::getByName($inputBattleSolarSystemName);
        
        $output["inputBattleSolarSystemName"] = $inputBattleSolarSystemName;
        
        if ($battleSolarSystem == null) {
            $output["battleSolarSystemError"] = true;
        } else {
            $output["battleSolarSystem"] = $battleSolarSystem;
        }
    }
    
    /*
     *  Fetch corresponding kills ... if existing ...
     */
    if ($battleTimespan != null && $battleSolarSystem != null) {
        try {
            $battle = KBFetch::fetchBattle(
                array(
                    "corporationID" => BR_OWNERCORP_ID,
                    "solarSystemID" => $battleSolarSystem["id"],
                    "startTime"     => KBFetch::getZKBStartTime($battleTimespan),
                    "endTime"       => KBFetch::getZKBEndTime($battleTimespan)
                )
            );
            $battle->savePreparation();
            
            $output["battleReport"] = $battle;
        } catch (Exception $e) {
            $output["battleReportError"] = $e->getMessage();
        }
    }

}

$app->render("create.html", $output);