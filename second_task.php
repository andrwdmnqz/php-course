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
    // Get method and cut string
    $method = substr($string, 0, strpos($string, ' '));
    $string = substr($string, strlen($method) + 1);

    // Get uri and cut string
    $uri = substr($string, 0, strpos($string, " "));
    $string = substr($string, strpos($string, "\n") + 1);

    $headers = array();
    // Split string by lines
    $lines = explode("\n", $string);

    foreach ($lines as $line) {
        if ($line != "") {
            // If line isn't empty - extract header from it
            $header_name = substr($line, 0, strpos($line, ":"));
            $headers[] = array($header_name, substr($line, strlen($header_name) + 2));
        } else {
            // Break if line is empty
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
echo(json_encode($http, JSON_PRETTY_PRINT));