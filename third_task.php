<?php

/**
 * Function to read input data
 */
function readHttpLikeInput() {
    $f = fopen( 'php://stdin', 'r' );
    $store = "";
    $toread = 0;
    while( $line = fgets( $f ) ) {
        $store .= preg_replace("/\r/", "", $line);
        if (preg_match('/Content-Length: (\d+)/',$line,$m)) 
            $toread=$m[1]*1; 
        if ($line == "\r\n") 
              break;
    }
    if ($toread > 0) 
        $store .= fread($f, $toread);
    return $store;
}

$contents = readHttpLikeInput();

/**
 * Function simplifies results printing by formatting it to needed look
 * 
 * @param string $statuscode status of response
 * @param string $statusMessage status message of response
 * @param array $headers headers of response
 * @param string $body body of response
 */
function outputHttpResponse($statuscode, $statusmessage, $headers, $body) {
    
    echo "HTTP/1.1 $statuscode $statusmessage\n";

    foreach($headers as $key => $value) {
        echo "$key: $value\n";
    }
    echo "\n$body";
}

/**
 * Function works with http request to define
 * what response should be given
 * 
 * @param string $method method of request
 * @param string $uri uri of request
 * @param string $headers headers of request
 * @param array $body body of request
 */
function processHttpRequest($method, $uri, $headers, $body) {
    $statuscode = "";
    $statusMessage = "";
    $responseBody = "";
    $body = "";
    // Define needed headers
    $generatedHeaders = array(
        "Server" => "Apache/2.2.14 (Win32)",
        "Content-Length" => "",
        "Connection" => "Closed",
        "Content-Type" => "text/html; charset=utf-8"
    );
    
    // If uri and header is correct
    if ($method === "GET" and str_starts_with($uri, "/sum?nums=")) {
        $numbers = explode(",", substr($uri, strlen("/sum?nums=")));
        $statuscode = "200";
        $statusMessage = "OK";
        $responseBody = array_sum($numbers);
    }

    if (!str_starts_with($uri, "/sum")) {
        // If uri doesn't starts with sum - return client error
        $statuscode = "404";
        $statusMessage = "Not Found";
        $responseBody = "not found";
    }
    if (!strpos($uri, "?nums=") or $method != "GET") {
        // If uri or header incorrect - return client error
        $statuscode = "400";
        $statusMessage = "Bad Request";
        $responseBody = "bad request";
    }

    $generatedHeaders["Content-Length"] = strlen((string)$responseBody);
    outputHttpResponse($statuscode, $statusMessage, $generatedHeaders, $responseBody);
}

function parseTcpStringAsHttpRequest($string) {

    $method = substr($string, 0, strpos($string, ' '));
    $string = substr($string, strlen($method) + 1);

    $uri = substr($string, 0, strpos($string, " "));
    $string = substr($string, strpos($string, "\n") + 1);

    $headers = array();
    $lines = explode("\n", $string);

    foreach ($lines as $line) {
        if ($line != "") {
            $header_name = substr($line, 0, strpos($line, ":"));
            $header_value = substr($line, strlen($header_name) + 2);
            $headers[$header_name] = $header_value;
        } else {
            break;
        }
    }
    
    return array(
        "method" => $method,
        "uri" => $uri,
        "headers" => $headers,
        "body" => end($lines),
    );
}

$http = parseTcpStringAsHttpRequest($contents);
processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);