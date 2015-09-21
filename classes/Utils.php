<?php

/*
 *  Reusable Collection of Utility Methods
 */

class Utils {
	
    
    public static function objectToArray($d) {
        
        if (is_object($d))
            $d = get_object_vars($d);
        
        if (is_array($d))
            return array_map("Utils::objectToArray", $d);
        else
            return $d;
        
    }
    
    public static function arrayToObject($d) {
        
        if (is_array($d))
            return (object) array_map("Utils::objectToArray", $d);
        else
            return $d;
        
    }
	
	private static $fetcher = null;
	
	public static function setFetcher($fetcher) {
		
		self::$fetcher = $fetcher;
		
	}
	
	public static function getFetcher() {
		
		if (self::$fetcher === null) {
			self::setFetcher(new \Utils\Fetcher\Curl());
		}
		
		return self::$fetcher;
		
	}
	
	public static function fetch($url = "", $parameters = array(), $options = array()) {
		
		return self::getFetcher()->fetch($url, $parameters, $options);
		
	}

    public static function curl($url = "", $parameters = array(), $options = array()) {
		
		return self::fetch($url, $parameters, $options);
        
    }
    
}
