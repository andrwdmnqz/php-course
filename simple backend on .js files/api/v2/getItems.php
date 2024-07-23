<?php

define("METHOD_NOT_ALLOWED_CODE", "405");
define("SERVER_ERROR_CODE", "500");

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://frontend.local");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://frontend.local");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Credentials: true");
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(METHOD_NOT_ALLOWED_CODE);
    echo json_encode(['error' => 'Method now allowed!']);
    exit;
}

try {
    // Create lock file
    $lockFile = fopen("../../data/lockfile", "w+");
    if (flock($lockFile, LOCK_EX)) {
        // Read items and response with them
        $data = json_decode(file_get_contents("../../data/items.json"), JSON_PRETTY_PRINT);

        $itemsArray = array_values($data['items']);
        
        // Return items
        echo json_encode(['items' => $itemsArray], JSON_PRETTY_PRINT);

        flock($lockFile, LOCK_UN);
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