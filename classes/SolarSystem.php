<?php

class SolarSystem {
    
    private static $nameIDs = array();
    
    /**
     * Returns the solar system object with the given name
     * @param  string $name solar system name to lookup
     * @return object       key value pair of solar system's name and id
     */
    public static function getByName($name = "") {
        if (empty($name))
            return null;
        
        $db = Db::getInstance();
        
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
    
    /**
     * Returns the solar system object with the given id
     * @param  string $id solar system id to lookup
     * @return object     key value pair of solar system's name and id
     */
    public static function getByID($id = "") {
        if (empty($id))
            return "";
        if ($id <= 0)
            return "";
        
        if (isset(self::$nameIDs["id#" . $id]))
            return self::$nameIDs["id#" . $id];
        
        $db = Db::getInstance();
        
        $result = $db->single(
            "select solarSystemName " .
            "from mapSolarSystems " .
            "where solarSystemID = :id",
            array(
                "id" => $id
            )
        );
        
        if ($result == NULL)
            return "";
        
        self::$nameIDs["id#" . $id] = $result;
        
        return $result;
    }
    
    /**
     * Returns all solar systems starting with or containing the given name part
     * @param  string $namePart solar system name part to lookup
     * @return array            array of objects corresponding to matching solar systems
     */
    public static function getAllByPartialName($namePart = "") {
        if (empty($namePart))
            return array();
        
        $db = Db::getInstance();
        
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
