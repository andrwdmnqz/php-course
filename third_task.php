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
    
    echo "HTTP/1.1 $statuscode $statusmessage" . PHP_EOL;

    foreach($headers as $key => $value) {
        echo "$key: $value" . PHP_EOL;
    }

    echo PHP_EOL . $body;
}

/**
 * Function works with http request to define
 * what response should be given
 * 
 * @param string $method method of request
 * @param string $uri uri of request
 * @param array $headers headers of request
 * @param array $body body of request
 */
function processHttpRequest($method, $uri, $headers, $body) {

    // Define needed headers
    $generatedHeaders = [
        "Server" => "Apache/2.2.14 (Win32)",
        "Connection" => "Closed",
        "Content-Type" => "text/html; charset=utf-8",
        "Content-Length" => "",
    ];

    if (!str_starts_with($uri, "/sum"))     {
        // If uri doesn't starts with sum - return client error
        $statuscode = "404";
        $statusMessage = "Not Found";
        $responseBody = "not found";

        $generatedHeaders["Content-Length"] = strlen((string)$responseBody);
        outputHttpResponse($statuscode, $statusMessage, $generatedHeaders, $responseBody);

        return;
    }

    if (!strpos($uri, "?nums=") || $method !== "GET") {
        // If uri or header incorrect - return client error
        $statuscode = "400";
        $statusMessage = "Bad Request";
        $responseBody = "bad request";

        $generatedHeaders["Content-Length"] = strlen((string)$responseBody);
        outputHttpResponse($statuscode, $statusMessage, $generatedHeaders, $responseBody);

        return;
    }
    
    // If uri and header are correct
    $numbers = explode(",", substr($uri, strlen("/sum?nums=")));
    $statuscode = "200";
    $statusMessage = "OK";
    $responseBody = array_sum($numbers);

    $generatedHeaders["Content-Length"] = strlen((string)$responseBody);
    outputHttpResponse($statuscode, $statusMessage, $generatedHeaders, $responseBody);
}

/**
 * Function parces given string to http request
 * 
 * @param string given tcp string
 */
function parseTcpStringAsHttpRequest($string) {
    $splittedFirstLine = explode(" ", $string);
    $method = $splittedFirstLine[0];
    $uri = $splittedFirstLine[1];

    $string = substr($string, strpos($string, "\n") + strlen("\n"));

    $headers = [];
    // Split string by lines
    $lines = explode("\n", $string);

    foreach ($lines as $line) {

        if (strpos($line, ":")) {
            // If line isn't empty - extract header from it
            $header_name = substr($line, 0, strpos($line, ":"));
            $headers[$header_name] = substr($line, strlen($header_name) + 2);
        }
    }
    
    return [
        "method" => $method,
        "uri" => $uri,
        "headers" => $headers,
        "body" => end($lines),
    ];
}

$http = parseTcpStringAsHttpRequest($contents);
processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);