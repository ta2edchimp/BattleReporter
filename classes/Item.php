<?php

class Item {
    
    private static $nameIDs = array();
    
    public static function getNameByID($id = "") {
        if (empty($id))
            return "";
        
        if (isset(self::$nameIDs["id#" . $id]))
            return self::$nameIDs["id#" . $id];
        
        global $db;
        
        $result = $db->single(
            "select typeName " .
            "from invTypes " .
            "where typeID = :id",
            array(
                "id" => $id
            )
        );
        
        if ($result == NULL)
            return "";
        
        self::$nameIDs["id#" . $id] = $result;
        
        return $result;
    }
    
    public static function getIDByName($name = "") {
        if (empty($name))
            return "";
        
        if (isset(self::$nameIDs["name#" . $name]))
            return self::$nameIDs["name#" . $name];
        
        global $db;
        
        $result = $db->single(
            "select typeID " .
            "from invTypes " .
            "where typeName = :name",
            array(
                "name" => $name
            )
        );
        
        if ($result == NULL)
            return "";
        
        self::$nameIDs["name#" . $name] = $result;
        
        return $result;
    }
    
    public static function getAllShipsByPartialName($namePart = "") {
        
        if (empty($namePart))
            return array();
        
        global $db;
        
        $ships = $db->query(
            "select typeName as name, typeID as id " .
            "from (select typeName, typeID from invTypes " .
                "where typeName like :shipNameStartsWith " .
                    "and groupID in (select groupID from invGroups where categoryID = 6) " .
                "order by typeName) as drvdtbl1 " .
            "union " .
            "select typeName AS name, typeID as id " .
                "from (select typeName, typeID from invTypes " .
                "where typeName like :shipNameContains " .
                    "and groupID in (select groupID from invGroups where categoryID = 6) " .
                "order by typeName) as drvdtbl2",
            array(
                "shipNameStartsWith" => $namePart . '%',
                "shipNameContains" => '%' . $namePart . '%'
            )
        );
        
        if ($ships == NULL)
            return array();
        
        return $ships;
    }
    
}