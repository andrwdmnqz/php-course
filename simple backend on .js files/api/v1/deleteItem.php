<?php

define("BAD_REQUEST_CODE", "400");
define("METHOD_NOT_ALLOWED_CODE", "405");
define("SERVER_ERROR_CODE", "500");

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://frontend.local");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://frontend.local");
    header("Access-Control-Allow-Methods: DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Credentials: true");
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(METHOD_NOT_ALLOWED_CODE);
    echo json_encode(['error' => 'Method now allowed!']);
    exit;
}

try {
    // Take raw data from the request
    $json = file_get_contents('php://input');

    // Convert it into a PHP object
    $input = json_decode($json, true);

    // Check json complitness
    if (!isset($input['id'])) {
        http_response_code(BAD_REQUEST_CODE);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    $inputId = $input['id'];

    // Create lock file
    $lockFile = fopen("../../data/lockfile", "w+");
    
    if (flock($lockFile, LOCK_EX)) {
        // Get all items and delete one with needed id
        $items = json_decode(file_get_contents("../../data/items.json"), true);
        $items['items'] = array_filter($items['items'], function($item) use ($inputId) {
            return $item['id'] != $inputId;
        });

        // Save items
        file_put_contents("../../data/items.json", json_encode($items, JSON_PRETTY_PRINT));

        flock($lockFile, LOCK_UN);

        // Return json with ok field
        echo json_encode(['ok' => true], JSON_PRETTY_PRINT);
    } else {
        // If unable to lock file - return error
        http_response_code(SERVER_ERROR_CODE);
        echo json_encode(['error' => "Could not lock the file"]);
        exit;
    }

    fclose($lockFile);

} catch (Exception $e) {
    http_response_code(SERVER_ERROR_CODE);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}