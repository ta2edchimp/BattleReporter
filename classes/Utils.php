<?php

/*
 *  Reusable Collection of Utility Methods
 */

class Utils {
    
    public static function curl($url = "", $parameters = array(), $parametersAsQuerystring = true, $headers = array()) {
        
        if (empty($url))
            return "";
        
        $url .= self::transformParameters($parameters, $parametersAsQuerystring);
        
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        // TODO: merge these
        //if (count($headers) > 0)
        //    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Accept-language: en\r\n' .
            'Accept-Encoding: gzip\r\n'
        ));
        
        if(substr($url, 0, 5) == "https")
           curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        
        // set timeout
        //curl_setopt(self::$curl, CURLOPT_TIMEOUT, self::$TIMEOUT);
        
        curl_setopt($curl, CURLOPT_USERAGENT, "BRTest");
        
        
        $result = curl_exec($curl);
        $errno  = curl_errno($curl);
        $error  = curl_error($curl);
        
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        if ($httpCode >= 400)
            throw new Exception("HTTP-Error #$httpCode with url \"$url\"", $httpCode);
        
        if ($errno)
            throw new Exception("Error #$errno while fetching url \"$url\":\n$error", $errno);
        else
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
    
}