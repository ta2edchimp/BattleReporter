<?php

namespace Utils\Fetcher;

use Utils\Fetcher\FetcherInterface;

class FetcherBase implements FetcherInterface {
	
	public function fetch ($url = "", $parameters = array(), $options = array()) {

		return "";

	}

	public static function transformParameters($parameters = array(), $parametersAsQuerystring = true) {
		
		if (!is_array($parameters) || empty($parameters))
			return "";
		
		$queryps = array();
		$keys = array_keys($parameters);
		
		if ($parametersAsQuerystring === true) {
			foreach ($keys as $key) {
				$queryps[] = $key . "=" . $parameters[$key];
			}
			if (count($queryps) > 0)
				return "?" . implode("&", $queryps);
		} else {
			foreach ($keys as $key) {
				$queryps[] = $key;
				$queryps[] = $parameters[$key];
			}
			if (count($queryps) > 0)
				return "/" . implode("/", $queryps) . "/";
		}
		
		return "";
		
	}
	
}
