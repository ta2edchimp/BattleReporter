<?php

namespace Utils\Fetcher;

use Utils\Fetcher\FetcherBase;

class File extends FetcherBase {
	
	public function fetch ($url = "", $parameters = array(), $options = array()) {
		
		\Slim\Slim::getInstance()->log->debug("Using File to fetch \"$url\".");
		
		if (empty($url))
			return "";
		
		$opts = array();
		
		$opts["http"] = array();
		$opts["http"]["ignore_errors"] = true;
		
		$parametersAsQuerystring = false;
		$parametersAsPostFields = false;
		if (isset($options["queryParams"]))
			$parametersAsQuerystring = $options["queryParams"];
		if (isset($options["postParams"]) && $options["postParams"] === true)
			$parametersAsPostFields = true;
		
		$timeout = 180;
		if (isset($options["timeout"]))
			$timeout = $options["timeout"];
		$opts["http"]["timeout"] = $timeout;
		
		$caching = false;
		$cacheLifetime = 600;
		if (isset($options["caching"])) {
			if ($options["caching"] === true || $options["caching"] == "auto") {
				$caching = true;
			}
		}
		if (isset($options["cacheLifetime"])) {
			$caching = true;
			$cacheLifetime = $options["cacheLifetime"];
		}
		
		if (isset($options["userAgent"]))
			$opts["http"]["user_agent"] = $options["userAgent"];
		
		if (substr($url, 0, 5) == "https") {
			if (!isset($options["sslVerify"])) {
				$opts["ssl"]["verify_peer"] = false;
			} else {
				$opts["ssl"]["verify_peer"] = $options["sslVerify"];
			}
		}
		
		if (count($parameters)) {
			if ($parametersAsPostFields !== true) {
				$url .= $this->transformParameters($parameters, $parametersAsQuerystring);
			} else {
				$opts["http"]["method"] = "POST";
				$opts["http"]["content"] = http_build_query($parameters, '', '&');
			}
		}
		
		$opts["http"]["header"] =	"Connection: close\r\n" .
									"Content-Type: application/x-www-form-urlencoded\r\n";
		if (isset($options["headers"]) && count($options["headers"]) > 0)
			$opts["http"]["header"] .= implode("\r\n", $options["headers"]) . "\r\n";
		
		$result = null;
		$cache = null;
		
		if ($caching === true) {
			// `cachePath` option now deprecated,
			// use application's default cache path for phpFastCache
			$cache = phpFastCache();
			if ($cache !== null)
				$result = $cache->get($url);
		}
		
		if ($result === null) {
			
			// As PPetermann said:
			// "initialize this php abomination"
			$php_errormsg = null;
			
			// save "track_errors" setting, but temporarily
			// set it to true, for $php_errormsg
			$oldTrackErrors = ini_get('track_errors');
			ini_set('track_errors', true);
			
			if (count($opts)) {
				$context = stream_context_create($opts);
				$result = file_get_contents($url, false, $context);
			} else {
				$result = file_get_contents($url);
			}
			

			$httpCode = 200;
			$httpVersion = '';
			$httpMsg = '';
			if (isset($http_response_header[0])) {
				list($httpVersion, $httpCode, $httpMsg) = explode(' ', $http_response_header[0], 3);
			}
			
			if (is_numeric($httpCode) && $httpCode >= 400)
				throw new \Exception("HTTP ($httpVersion) Error #$httpCode with url \"$url\":\n$httpMsg\nResult:\n$result", $httpCode);
			
			if ($result === false)
				throw new \Exception("Error while fetching url \"$url\":\n" . ($php_errormsg ? $php_errormsg : "HTTP Request Failed"), 666);
			
			// reset "track_errors" setting
			ini_set('track_errors', $oldTrackErrors);
			
			if ($caching === true && $cache !== null) {
				$cache->set($url, $result, $cacheLifetime);
			}
		}
		
		return $result;
		
	}
	
}
