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
    
    private static function getIDByName($name = "") {
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
    
}