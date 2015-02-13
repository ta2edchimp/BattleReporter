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

    public static function curl($url = "", $parameters = array(), $options = array()) {
        
        if (empty($url))
            return "";
        
        $parametersAsQuerystring = false;
		$parametersAsPostFields = false;
        if (isset($options["queryParams"]))
            $parametersAsQuerystring = $options["queryParams"];
        if (isset($options["postParams"]) && $options["postParams"] == true)
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
        
		if ($parametersAsPostFields != true)
			$url .= self::transformParameters($parameters, $parametersAsQuerystring);
        
        $result = null;
        $cache = null;
        
        if ($caching == true) {
            if (isset($options["cachePath"]))
                $cache = new phpFastCache("files", array("path" => $options["cachePath"]));
            else
                $cache = new phpFastCache("files");
            if ($cache != null)
                $result = $cache->get($url);
        }
        
        if ($result == null) {
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
					if ($options["sslVerify"] == true)
						curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
				}
			}
			
			if (isset($options["postParams"]) && $options["postParams"] == true) {
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
            
            $headers = self::curlHeaderToArray($headerText);
            
            curl_close($curl);
            
            if ($httpCode >= 400)
                throw new Exception("HTTP-Error #$httpCode with url \"$url\":\n$result", $httpCode);
            
            if ($errno)
                throw new Exception("Error #$errno while fetching url \"$url\":\n$error", $errno);
            
            if ($caching == true && $cache != null) {
                if ($autoCaching == true && !empty($headers["Expires"])) {
                    $expires = strtotime($headers["Expires"]);
                    $cacheLifetime = $expires - time();
                }
                $cache->set($url, $result, $cacheLifetime);
            }
        }
        
        return $result;
        
    }
    
    private static function transformParameters($parameters = array(), $parametersAsQuerystring = true) {
        
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