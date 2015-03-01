<?php

class KBFetch {
    
    private static $availableParams = array("corporationID", "solarSystemID", "startTime", "endTime");
    
    public static function fetchBattle(array $params = array()) {
        
        $battle = new Battle();
        $battle->import(self::fetchKills($params));
        
        return $battle;
        
    }
	
	public static function fetchKills($params = array()) {
		
		$parameters = array();
		
		foreach ($params as $key => $val) {
			if (in_array($key, self::$availableParams))
				$parameters[$key] = $val;
		}
		
		$fetchedResult = Utils::curl(
			BR_FETCH_SOURCE_URL . "api",
			$parameters,
			array(
				"queryParams" => false,
				"caching" => "auto",
				"cachePath" => __DIR__ . '/../cache'
			)
		);
		
		if (empty($fetchedResult))
			$fetchedResult = "[]";
		
		return json_decode($fetchedResult);
		
	}
	
	public static function fetchKill($killID = "") {
		
		if (empty($killID) || $killID <= 0)
			return;
		
		$fetchedResult = Utils::curl(
			BR_FETCH_SOURCE_URL . "api",
			array(
				"killID" => $killID
			),
			array(
				"queryParams" => false,
				"caching" => "auto",
				"cachePath" => __DIR__ . '/../cache'
			)
		);
		
		if (empty($fetchedResult))
			return null;
		
		return json_decode($fetchedResult);
		
	}
    
    public static function testTimespanPattern($timespan) {
        $didMatch = preg_match('/^([0-9]{2}){1,2}-([0-1][0-2]|[0]{0,1}[1-9])-[0-3]{0,1}[0-9] [0-2]{0,1}[0-9]:[0-5][0-9] - [0-2]{0,1}[0-9]:[0-5][0-9]$/', $timespan, $reMatches, PREG_OFFSET_CAPTURE);
        
        if ($didMatch === FALSE)
            throw new Exception("Something bad happened when trying to check the given battleTimespan.");
        
        if ($didMatch == 1)
            return true;
        else
            return false;
    }
    
	public static function getDateTime($timespan, $endTime = false) {
		
		// Fetch datetime parts from timespan string ...
		$dtStr = preg_replace(
			'/^([0-9]{2,4})-([0-1][0-2]|[0]{0,1}[1-9])-([0-3]{0,1}[0-9]) ([0-2]{0,1}[0-9]):([0-5][0-9]) - ([0-2]{0,1}[0-9]):([0-5][0-9])$/',
			'$1-$2-$3 ' . ($endTime === true ? '$6:$7' : '$4:$5'),
			$timespan
		);
		
		// Convert into real datetime
		$dt = new DateTime($dtStr);
		
		// Correct datetime when being called for endTime ...
		if ($endTime === true) {
			$dtStart = self::getDateTime($timespan);
			// ... according to possible newday
			// if endTime < startTime
			if ($dt->getTimestamp() < $dtStart->getTimestamp())
				$dt->modify("+1 day");
		}
		
		// Return the result ...
		return $dt;
		
	}
	
	public static function getZKBStartTime($timespan) {
		return self::getDateTime($timespan)->format("YmdHi");
	}
	
	public static function getZKBEndTime($timespan) {
		return self::getDateTime($timespan, true)->format("YmdHi");
	}

}
