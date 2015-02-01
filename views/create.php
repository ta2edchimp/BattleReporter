<?php

function getZKBStartTime($timespan) {
    // YmdHi
    return preg_replace(
        '/^([0-9]{4})-([0-1][0-2]|0[1-9])-([0-3][0-9]) ([0-2][0-9]):([0-5][0-9]) - ([0-2][0-9]:[0-5][0-9])$/',
        '$1$2$3$4$5',
        $timespan
    );
}

function getZKBEndTime($timespan) {
    // YmdHi
    return preg_replace(
        '/^([0-9]{4})-([0-1][0-2]|0[1-9])-([0-3][0-9]) ([0-2][0-9]):([0-5][0-9]) - ([0-2][0-9]:[0-5][0-9])$/',
        '$1$2$3$6$7',
        $timespan
    );
}

function parseSystems($kills) {
    
    $systems = array();
    
    if (count($kills) == 0)
        return $systems;
    
    foreach ($kills as $kill) {
        if (isset($kill["solarSystemID"])) {
            $id = $kill["solarSystemID"];
            if (!isset($systems[$kill["solarSystemID"]])) {
                $systems[$id]["id"] = $id;
                $systems[$id]["name"] = $id;
                $systems[$id]["kills"] = 0;
            }
            $systems[$id]["kills"] = $systems[$id]["kills"] + 1;
        }
    }
    
    return $systems;
}


if (!User::can('create'))
    $app->redirect('/');

$output = array();

// Get all POST variables
$parameters = $app->request->post();

if ($parameters != null) {
    
    $battleTimespan         = null;
    $fetchedKills           = null;
    $battledSolarSystems    = null;
    
    /*
     *  1 - Specify Timespan
     */
    if (isset($parameters["battleTimespan"])) {
        $inpBattleTimespan = $parameters["battleTimespan"];
        $didMatch = preg_match('/^[0-9]{4}-([0-1][0-2]|0[1-9])-[0-3][0-9] [0-2][0-9]:[0-5][0-9] - [0-2][0-9]:[0-5][0-9]$/', $inpBattleTimespan, $reMatches, PREG_OFFSET_CAPTURE);
        
        if ($didMatch === FALSE)
            throw new Exception("Something bad happened when trying to check the given battleTimespan.");
        if ($didMatch == 1) {
            $battleTimespan = $inpBattleTimespan;
            $output["battleTimespan"] = $inpBattleTimespan;
        } else {
            $output["battleTimespanError"] = true;
        }
    }
    
    /*
     *  2 - Specify Solar System
     */
    
    if ($battleTimespan != null) {
        $output["battlesFound"] = false;
        
        $startTimeZKB   = getZKBStartTime($battleTimespan);
        $endTimeZKB     = getZKBEndTime($battleTimespan);
        
        $fetchedKills = Utils::curl(
            "https://zkillboard.com/api",
            array(
                "corporationID" => "98270080",
                "startTime" => $startTimeZKB,
                "endTime" => $endTimeZKB
            ),
            array(
                "queryParams" => false,
                "caching" => "auto",
                "cachePath" => __DIR__ . '/../cache'
            )
        );
        
        // Must not be an empty string
        if (empty($fetchedKills)) {
            $output["battlesFetchingError"] = true;
        } else {
            $fetchedKills = json_decode($fetchedKills, true);
            $battledSolarSystems = parseSystems($fetchedKills);
        }
    }
    
    if ($battledSolarSystems == null || !isset($parameters["battledSolarSystem"]) || empty($parameters["battledSolarSystem"])) {
        //echo "<p>" . count($fetchedKills) . "<br>" . json_encode($fetchedKills) . "</p>";
        
        if (count($battledSolarSystems) > 0) {
            $output["battledSolarSystems"] = $battledSolarSystems;
            $output["battlesFound"] = true;
        }
    } else {
        $output["battledSolarSystem"] = $battledSolarSystems[$parameters["battledSolarSystem"]];
    }

}

$app->render("create.html", $output);