<?php

namespace Utils\Fetcher;

use Utils\Fetcher\FetcherBase;

class Curl extends FetcherBase {
	
	public function fetch ($url = "", $parameters = array(), $options = array()) {
		
		\Slim\Slim::getInstance()->log->debug("Using cURL to fetch \"$url\".");
		
		if (empty($url))
			return "";
		
		$parametersAsQuerystring = false;
		$parametersAsPostFields = false;
		if (isset($options["queryParams"]))
			$parametersAsQuerystring = $options["queryParams"];
		if (isset($options["postParams"]) && $options["postParams"] === true)
			$parametersAsPostFields = true;
		
		$timeout = 180;
		if (isset($options["timeout"]))
			$timeout = $options["timeout"];
		
		$caching = false;
		$autoCaching = false;
		$cacheLifetime = 600;
		if (isset($options["caching"])) {
			if ($options["caching"] === true)
				$caching = true;
			elseif ($options["caching"] == "auto") {
				$caching = true;
				$autoCaching = true;
			}
		}
		if (isset($options["cacheLifetime"])) {
			$caching = true;
			$cacheLifetime = $options["cacheLifetime"];
		}
		
		$userAgent = "";
		if (isset($options["userAgent"]))
			$userAgent = $options["userAgent"];
		
		if ($parametersAsPostFields !== true)
			$url .= $this->transformParameters($parameters, $parametersAsQuerystring);
		
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
			$curl = curl_init();
			
			curl_setopt($curl, CURLOPT_ENCODING, "");
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			
			$headers = array();
			if (isset($options["headers"]) && count($options["headers"]) > 0)
				$headers = $options["headers"];
			else {
				$headers[] = 'Accept-language: en';
				$headers[] = 'Accept-Encoding: gzip';
			}
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			
			if (substr($url, 0, 5) == "https") {
				if (!isset($options["sslVerify"])) {
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
				} else {
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $options["sslVerify"]);
					if ($options["sslVerify"] === true)
						curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
				}
			}
			
			if (isset($options["postParams"]) && $options["postParams"] === true) {
				$fieldCount = count($parameters);
				$fieldStr = "";
				if ($fieldCount > 0) {
					foreach ($parameters as $key => $value)
						$fieldStr .= $key . "=" . $value . "&";
					
					rtrim($fieldStr, "&");
					
					curl_setopt($curl, CURLOPT_POST, $fieldCount);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $fieldStr);
				}
			}
			
			curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
			
			if (!empty($userAgent))
				curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
			
			
			curl_setopt($curl, CURLOPT_VERBOSE, true);
			curl_setopt($curl, CURLOPT_HEADER, true);
			
			$totalResponse = curl_exec($curl);
			
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$headerText = substr($totalResponse, 0, $header_size);
			$result = substr($totalResponse, $header_size);
			
			$errno  = curl_errno($curl);
			$error  = curl_error($curl);
			
			$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
			$headers = $this->curlHeaderToArray($headerText);
			
			curl_close($curl);
			
			if ($httpCode >= 400)
				throw new \Exception("HTTP-Error #$httpCode with url \"$url\":\n$result", $httpCode);
			
			if ($errno)
				throw new \Exception("Error #$errno while fetching url \"$url\":\n$error", $errno);
			
			if ($caching === true && $cache !== null) {
				if ($autoCaching === true && !empty($headers["Expires"])) {
					$expires = strtotime($headers["Expires"]);
					$cacheLifetime = $expires - time();
				}
				$cache->set($url, $result, $cacheLifetime);
			}
		}
		
		return $result;
		
	}
	
	public static function curlHeaderToArray($headerText = "") {
		
		$headers = array();
		
		if (empty($headerText))
			return $headers;
		
		foreach (explode("\r\n", $headerText) as $i => $line) {
			if ($i == 0) {
				$headers['http_code'] = $line;
			} else {
				$lineArr = explode(': ', $line, 2);
				if (count($lineArr) > 1)
					$headers[$lineArr[0]] = $lineArr[1];
			}
		}
		
		return $headers;
		
	}
	
}
