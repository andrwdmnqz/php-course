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
            $headers[] = array($header_name, substr($line, strlen($header_name) + 2));
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
echo(json_encode($http, JSON_PRETTY_PRINT));