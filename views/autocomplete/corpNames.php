<?php

$params = $app->request->post();

if (isset($params["query"])) {
    
    $suggestions = array();
    
    $namePart = $params["query"];
    if (!empty($namePart)) {
        
        $results = $db->query(
            "select corporationName as value, allianceName as data " .
            "from (select distinct corporationName, allianceName from brCombatants " .
                "where corporationName like :nameStartsWith " .
                "order by corporationName) as drvdtbl1 " .
            "union " .
            "select corporationName as value, allianceName as data " .
                "from (select distinct corporationName, allianceName from brCombatants " .
                "where corporationName like :nameContains " .
                "order by corporationName) as drvdtbl2",
            array(
                "nameStartsWith" => $namePart . '%',
                "nameContains" => '%' . $namePart . '%'
            )
        );
        
        if ($results != NULL)
            $suggestions = $results;
        
    }
    
    $app->status(200);
    $app->contentType('application/json');
    
    echo '{"suggestions":' . json_encode($suggestions) . '}';
    
}