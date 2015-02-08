<?php

$params = $app->request->post();

if (isset($params["query"])) {
    
    $suggestions = array();
    
    $namePart = $params["query"];
    if (!empty($namePart)) {
        
        $results = $db->query(
            "select distinct allianceName as value, allianceID as data " .
            "from (select allianceName, allianceID from brCombatants " .
                "where allianceName like :nameStartsWith " .
                "order by allianceName) as drvdtbl1 " .
            "union " .
            "select distinct allianceName as value, allianceID as data " .
                "from (select allianceName, allianceID from brCombatants " .
                "where allianceName like :nameContains " .
                "order by allianceName) as drvdtbl2",
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