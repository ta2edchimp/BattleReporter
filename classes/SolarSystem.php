<?php

class SolarSystem {
    
    public static function getByName($name = "") {
        if (empty($name))
            return null;
        
        global $db;
        
        $system = $db->row(
            "select solarSystemName as name, solarSystemID as id " .
            "from mapSolarSystems " .
            "where solarSystemName = :solarSystemName",
            array(
                "solarSystemName" => $name
            )
        );
        
        if ($system == NULL)
            return null;
        
        return $system;
    }
    
    public static function getAllByPartialName($namePart = "") {
        if (empty($namePart))
            return array();
        
        global $db;
        
        $systems = $db->query(
            "select solarSystemName as name, solarSystemID as id " .
            "from (select solarSystemName, solarSystemID from mapSolarSystems " .
                "where solarSystemName like :solarSystemNameStartsWith " .
                "order by solarSystemName) as drvdtbl1 " .
            "union " .
            "select solarSystemName AS name, solarSystemID as id " .
                "from (select solarSystemName, solarSystemID from mapSolarSystems " .
                "where solarSystemName like :solarSystemNameContains " .
                "order by solarSystemName) as drvdtbl2",
            array(
                "solarSystemNameStartsWith" => $namePart . '%',
                "solarSystemNameContains" => '%' . $namePart . '%'
            )
        );
        
        if ($systems == NULL)
            return array();
        
        return $systems;
    }
    
}