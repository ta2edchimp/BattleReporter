<?php

$params = $app->request->post();

if (isset($params["query"])) {
    
    $suggestions = array();
    
    $namePart = $params["query"];
    if (!empty($namePart)) {
        
        $results = $db->query(
            "select corporationName as value, ifnull(allianceName, '') as data " .
            "from (select c1.corporationName, a1.allianceName " .
				"from brCorporations as c1 left outer join brAlliances as a1 ".
					"on c1.allianceID = a1.allianceID " .
				"where c1.corporationName like :nameStartsWith " .
				"order by c1.corporationName) as drvdtbl1 " .
            "union " .
            "select corporationName as value, ifnull(allianceName, '') as data " .
            "from (select distinct c2.corporationName, a2.allianceName " .
				"from brCorporations as c2 left outer join brAlliances as a2 " .
					"on c2.allianceID = a2.allianceID " .
                "where c2.corporationName like :nameContains " .
                "order by c2.corporationName) as drvdtbl2",
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
