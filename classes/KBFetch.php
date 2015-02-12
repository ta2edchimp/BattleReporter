<?php

class KBFetch {
    
    private static $availableParams = array("corporationID", "solarSystemID", "startTime", "endTime");
    
    public static function fetchBattle($params = array()) {
        
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
        
        $fetchedResult = json_decode($fetchedResult);
        
        $battle = new Battle();
        $battle->import($fetchedResult);
        
        return $battle;
        
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
        $didMatch = preg_match('/^[0-9]{4}-([0-1][0-2]|0[1-9])-[0-3][0-9] [0-2][0-9]:[0-5][0-9] - [0-2][0-9]:[0-5][0-9]$/', $timespan, $reMatches, PREG_OFFSET_CAPTURE);
        
        if ($didMatch === FALSE)
            throw new Exception("Something bad happened when trying to check the given battleTimespan.");
        
        if ($didMatch == 1)
            return true;
        else
            return false;
    }
    
    public static function getZKBStartTime($timespan) {
        return preg_replace(
            '/^([0-9]{4})-([0-1][0-2]|0[1-9])-([0-3][0-9]) ([0-2][0-9]):([0-5][0-9]) - ([0-2][0-9]:[0-5][0-9])$/',
            '$1$2$3$4$5',
            $timespan
        );
    }
    
    public static function getZKBEndTime($timespan) {
        return preg_replace(
            '/^([0-9]{4})-([0-1][0-2]|0[1-9])-([0-3][0-9]) ([0-2][0-9]):([0-5][0-9]) - ([0-2][0-9]:[0-5][0-9])$/',
            '$1$2$3$6$7',
            $timespan
        );
    }
    
}