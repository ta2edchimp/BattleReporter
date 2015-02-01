<?php

/*
 * Testing Playground
 */

require_once(__DIR__ . '/../classes/Utils.php');

echo "<p>lolwo0tumad?</p>";

if (1 == 1) {
    
    $arr = Utils::curlHeaderToArray("HTTP/1.1 200 OK\r\nDate: Sun, 01 Feb 2015 17:36:04 GMT\r\nServer: Apache\r\nX-Powered-By: PHP/5.4.36-1~dotdeb.1\r\nSet-Cookie: PHPSESSID=16nnaujaqh6ttv7d04o93sps45; path=/\r\nX-Bin-Request-Count: 4\r\nX-Bin-Max-Requests: 360\r\nAccess-Control-Allow-Origin: *\r\nAccess-Control-Allow-Methods: GET\r\nEtag: \"40cd750bba9870f18aada2478b24840a\"\r\nExpires: Sun, 01 Feb 2015 18:36:04 GMT\r\nConnection: close
Transfer-Encoding: chunked\r\nExpires: Sun, 01 Feb 2015 18:45:20 GMT\r\nContent-Type: application/json; charset=utf-8");
    
    echo "<p>" . json_encode($arr) . "</p>";
    
    echo "<p>" . $arr["Expires"] . "</p>";
    
    echo "<p>" . strtotime($arr["Expires"]) . "</p>";
    
} else {
    
    $url = "https://zkillboard.com/api";
    echo "<p><b>Fetching from</b>: $url</p>";
    
    echo Utils::curl($url, array(
        "corporationID" => "98270080",
        "limit" => "2"
    ), array(
        "queryParams" => false,
        "caching" => true,
        "cacheLifetime" => 300,
        "cachePath" => __DIR__ . '/../cache'
    ));

}

//echo htmlentities(Utils::curl($url));
//$url = "https://zkillboard.com/api/corporationID/98270080/limit/10/";
