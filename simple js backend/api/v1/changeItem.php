<?php

define("BAD_REQUEST_CODE", "400");
define("METHOD_NOT_ALLOWED_CODE", "405");
define("SERVER_ERROR_CODE", "500");

// Setup CORS
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://frontend.local");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://frontend.local");
    header("Access-Control-Allow-Methods: PUT, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Credentials: true");
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(METHOD_NOT_ALLOWED_CODE);
    echo json_encode(['error' => 'Method now allowed!']);
    exit;
}

try {
    // Get raw data from the request
    $json = file_get_contents('php://input');

    // Converts it into a PHP object
    $input = json_decode($json, true);

    // Check json complitness
    if (!isset($input['id'], $input['text'], $input['checked'])) {
        http_response_code(BAD_REQUEST_CODE);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    // Create lock file
    $lockFile = fopen("../../data/lockfile", "w+");
    
    if (flock($lockFile, LOCK_EX)) {
        // Get all items and edit needed
        $items = json_decode(file_get_contents("../../data/items.json"), true);
        foreach ($items['items'] as &$item) {
            if ($item['id'] === $input['id']) {
                $item['text'] = $input['text'];
                $item['checked'] = $input['checked'];
            }
        }

        // Save items
        file_put_contents("../../data/items.json", json_encode($items, JSON_PRETTY_PRINT));

        flock($lockFile, LOCK_UN);

        // Return json with ok field
        echo json_encode(['ok' => true], JSON_PRETTY_PRINT);
    } else {
        fclose($lockFile);
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