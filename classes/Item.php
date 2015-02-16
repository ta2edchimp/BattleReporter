<?php

class Item {
    
    private static $nameIDs = array();
    
    public static function getNameByID($id = "") {
        if (empty($id))
            return "";
        
        if (isset(self::$nameIDs["id#" . $id]))
            return self::$nameIDs["id#" . $id];
        
        $db = Db::getInstance();
        
        $result = $db->single(
            "select typeName " .
            "from invTypes " .
            "where typeID = :id",
            array(
                "id" => $id
            )
        );
        
        if ($result === NULL)
            return "";
        
        self::$nameIDs["id#" . $id] = $result;
        
        return $result;
    }
    
    public static function getIDByName($name = "") {
        if (empty($name))
            return "";
        
        if (isset(self::$nameIDs["name#" . $name]))
            return self::$nameIDs["name#" . $name];
        
        $db = Db::getInstance();
        
        $result = $db->single(
            "select typeID " .
            "from invTypes " .
            "where typeName = :name",
            array(
                "name" => $name
            )
        );
        
        if ($result === NULL)
            return "";
        
        self::$nameIDs["name#" . $name] = $result;
        
        return $result;
    }
    
    public static function getAllShipsByPartialName($namePart = "") {
        
        if (empty($namePart))
            return array();
        
        $db = Db::getInstance();
        
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
        
        if ($ships === NULL)
            return array();
        
        return $ships;
    }
	
	public static function getGroupIDByName($name = "") {
		
		if (empty($name))
			return "";
		
		$db = Db::getInstance();
		
		$groupID = $db->single(
			"select groupID from invGroups where groupName = :groupName",
			array(
				"groupName" => $name
			)
		);
		
		if ($groupID === NULL)
			return "";
		
		return $groupID;
		
	}
	
	public static function getGroupIDofTypeID($id = "") {
		
		if (empty($id))
			return "";
		
		$db = Db::getInstance();
		
		$groupID = $db->single(
			"select groupID from invTypes where typeID = :typeID",
			array(
				"typeID" => $id
			)
		);
		
		if ($groupID === NULL)
			return "";
		
		return $groupID;
		
	}
	
	private static $shipTypeIDgroupIDs = array();
	private static $podGroupID = "";
	
	public static function isCapsule($shipTypeID = "") {
		
		if (empty($shipTypeID))
			return false;
		
		if (isset(self::$shipTypeIDgroupIDs[$shipTypeID])) {
			$shipTypeGroupID = self::$shipTypeIDgroupIDs[$shipTypeID];
		} else {
			$temp = self::getGroupIDofTypeID($shipTypeID);
			if (empty($temp))
				return false;
			
			self::$shipTypeIDgroupIDs[$shipTypeID] = $temp;
			$shipTypeGroupID = self::$shipTypeIDgroupIDs[$shipTypeID];
		}
		
		if (empty(self::$podGroupID)) {
			$temp = self::getGroupIDByName("Capsule");
			if (empty($temp))
				return false;
			
			self::$podGroupID = $temp;
		}
		
		return $shipTypeGroupID == self::$podGroupID;
		
	}
    
}
