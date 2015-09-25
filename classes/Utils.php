<?php

/*
 *  Reusable Collection of Utility Methods
 */

class Utils {
	
	/**
	 * Converts an object to an array
	 * @param  object $d input object
	 * @return array     output array
	 */
	public static function objectToArray($d) {
		
		if (is_object($d))
			$d = get_object_vars($d);
		
		if (is_array($d))
			return array_map("Utils::objectToArray", $d);
		else
			return $d;
		
	}
	
	/**
	 * Converts an array to an object
	 * @param  array $d input array
	 * @return object   output object
	 */
	public static function arrayToObject($d) {
		
		if (is_array($d))
			return (object) array_map("Utils::objectToArray", $d);
		else
			return $d;
		
	}
	
	private static $fetcher = null;
	
	/**
	 * Sets the general Fetcher to be retrieved by Utils::getFetcher
	 * @param FetcherInterface $fetcher an instance of a class implementing `FetcherInterface`
	 */
	public static function setFetcher($fetcher) {
		
		self::$fetcher = $fetcher;
		
	}
	
	/**
	 * Returns the general Fetcher instance previously set by Utils::setFetcher
	 * @return FetcherInterface an instance of a class implementing `FetcherInterface`
	 */
	public static function getFetcher() {
		
		if (self::$fetcher === null) {
			self::setFetcher(new \Utils\Fetcher\Curl());
		}
		
		return self::$fetcher;
		
	}
	
	/**
	 * Fetches the file at a given url, using the (optional) parameters and additional options
	 * @param  string $url        url of file to fetch
	 * @param  array  $parameters the optional parameters (e.g. query string variables)
	 * @param  array  $options    options to apply when fetching
	 * @return string             the file's content
	 */
	public static function fetch($url = "", $parameters = array(), $options = array()) {
		
		return self::getFetcher()->fetch($url, $parameters, $options);
		
	}

	/**
	 * @deprecated Fetches the file at a given url, using the (optional) parameters and additional options.
	 * Deprecated since the implementation of different types of Fetchers (previously only curl was supported).
	 * @param  string $url        url of file to fetch
	 * @param  array  $parameters the optional parameters (e.g. query string variables)
	 * @param  array  $options    options to apply when fetching
	 * @return string             the file's content
	 */
	public static function curl($url = "", $parameters = array(), $options = array()) {
		
		return self::fetch($url, $parameters, $options);
		
	}

	/**
	 * Parses a string for a version like [major].[minor].[patch] and returns a corresponding object.
	 * The returned array contains the version parts as numerical items. Additionally it contains a boolean flag *valid*.
	 * @param  string $versionString a string containing a version info, like '1.2.3' or 'v1.2.3', etc.
	 * @return array                 an array containing the numerical version info
	 */
	public static function parseVersion($versionString = "") {

		$version = array(
			"major" => 0,
			"minor" => 0,
			"patch" => 0,
			"valid" => false
		);

		if (!empty($versionString) && preg_match("/^.*?(?P<major>\d*)\.(?P<minor>\d*)\.(?P<patch>\d*)$/", $versionString, $results) === 1) {
			$version["major"] = intval($results["major"]);
			$version["minor"] = intval($results["minor"]);
			$version["patch"] = intval($results["patch"]);
			$version["valid"] = true;
		}

		return $version;

	}
	
}
