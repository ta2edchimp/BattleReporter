<?php

class KBFetch {
    
    private static $availableParams = array("corporationID", "solarSystemID", "startTime", "endTime");
    
    /**
     * Fetches combined kill mails from zKillboard and returns them as a new Battle instance
     * @param  array  $params parameters for kill mail fetching (should be of `corporationID`, `solarSystemID`, `startTime`, `endTime`)
     * @return Battle         a new Battle instance
     */
    public static function fetchBattle(array $params = array()) {
        
        $battle = new Battle();
        $battle->import(self::fetchKills($params));
        
        return $battle;
        
    }
	
	/**
	 * Fetches combined kill mails from zKillboard
	 * @param  array  $params parameters for kill mail fetching (should be of `corporationID`, `solarSystemID`, `startTime`, `endTime`)
	 * @return array          fetched kill mails
	 */
	public static function fetchKills($params = array()) {
		
		$parameters = array();
		
		foreach ($params as $key => $val) {
			if (in_array($key, self::$availableParams))
				$parameters[$key] = $val;
		}
		
		$fetchedResult = Utils::fetch(
			BR_FETCH_SOURCE_URL . "api/combined",
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
	
	/**
	 * Fetches the kill mail specified by its id
	 * @param  string $killID id of the fill to (re)fetch
	 * @return object         the (re)fetched kill mail
	 */
	public static function fetchKill($killID = "") {
		
		if (empty($killID) || $killID <= 0)
			return;
		
		$fetchedResult = Utils::fetch(
			BR_FETCH_SOURCE_URL . "api/kills",
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
    
    /**
     * Test if the specified string matches the required timespan pattern
     * @param  string $timespan the string to test
     * @return bool             whether the timespan matches or not
     */
    public static function testTimespanPattern($timespan) {
        $didMatch = preg_match('/^([0-9]{2}){1,2}-([0-1][0-2]|[0]{0,1}[1-9])-[0-3]{0,1}[0-9] [0-2]{0,1}[0-9]:[0-5][0-9] - [0-2]{0,1}[0-9]:[0-5][0-9]$/', $timespan, $reMatches, PREG_OFFSET_CAPTURE);
        
        if ($didMatch === FALSE)
            throw new Exception("Something bad happened when trying to check the given battleTimespan.");
        
        if ($didMatch == 1)
            return true;
        else
            return false;
    }
    
    /**
     * Returns a datetime object out of the specified timespan string, either start or end
     * @param  string  $timespan the timespan string to parse
     * @param  boolean $endTime  whether to return the timespan's end
     * @return DateTime          the parsed datetime object
     */
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
	
	/**
	 * Transforms the specified timespan string's start time into zKillboard's format
	 * @param  string $timespan the timespan string to parse
	 * @return string           the parsed zKillboard start time equivalent
	 */
	public static function getZKBStartTime($timespan) {
		return self::getDateTime($timespan)->format("YmdHi");
	}
	
	/**
	 * Transforms the specified timespan string's end time into zKillboard's format
	 * @param  string $timespan the timespan string to parse
	 * @return string           the parsed zKillboard end time equivalent
	 */
	public static function getZKBEndTime($timespan) {
		return self::getDateTime($timespan, true)->format("YmdHi");
	}

}
