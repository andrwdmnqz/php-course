<?php

define("STATUS_CODE_OK", "200");
define("STATUS_CODE_BAD_REQUEST", "400");
define("STATUS_CODE_UNAUTHORIZED", "401");
define("STATUS_CODE_SERVER_ERROR", "500");

define("STATUS_MESSAGE_OK", "OK");
define("STATUS_MESSAGE_BAD_REQUEST", "Bad Request");
define("STATUS_MESSAGE_UNAUTHORIZED", "Unauthorized");
define("STATUS_MESSAGE_SERVER_ERROR", "Internal Server Error");

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
 * @param string $body body of request
 */
function processHttpRequest($method, $uri, $headers, $body) {
    // Define needed headers
    $generatedHeaders = [
        "Server" => "Apache/2.2.14 (Win32)",
        "Content-Length" => "",
        "Connection" => "Closed",
        "Content-Type" => "text/html; charset=utf-8"
    ];

    // If uri or header incorrect - return client error
    if ($uri !== "/api/checkLoginAndPassword" || $headers["Content-Type"] !== "application/x-www-form-urlencoded") {
        $generatedHeaders["Content-Length"] = strlen("not found");
        outputHttpResponse(STATUS_CODE_BAD_REQUEST, STATUS_MESSAGE_BAD_REQUEST, $generatedHeaders, "not found");

        return;
    }

    // If file not exists - 500 server error
    if (!file_exists("passwords.txt")) {
        $generatedHeaders["Content-Length"] = strlen("not found");
        outputHttpResponse(STATUS_CODE_SERVER_ERROR, STATUS_MESSAGE_SERVER_ERROR, $generatedHeaders, "not found");

        return;
    }

    $fileLines = explode(PHP_EOL, file_get_contents("passwords.txt"));
    
    // Parse login and password from request
    parse_str($body, $parsedBody);

    $login = $parsedBody['login'];
    $password = $parsedBody['password'];
        
    foreach ($fileLines as $line) {
        // Try to find match in the file. If found - 
        // assign needed values to respone parts
        $lineData = explode(":", trim($line));

        if ($login === $lineData[0] and $password === $lineData[1]) {
            $responseBody = '<h1 style="color:green">FOUND</h1>';

            $generatedHeaders["Content-Length"] = strlen((string) $responseBody);
            outputHttpResponse(STATUS_CODE_OK, STATUS_MESSAGE_OK, $generatedHeaders, $responseBody);

            return;
        }
    }

    $responseBody = '<h1 style="color:red">NOT FOUND</h1>';

    $generatedHeaders["Content-Length"] = strlen((string) $responseBody);
    outputHttpResponse(STATUS_CODE_UNAUTHORIZED, STATUS_MESSAGE_UNAUTHORIZED, $generatedHeaders, $responseBody);
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