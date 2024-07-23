<?php

define("BAD_REQUEST_CODE", "400");
define("METHOD_NOT_ALLOWED_CODE", "405");
define("SERVER_ERROR_CODE", "500");

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://frontend.local");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://frontend.local");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Credentials: true");
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(METHOD_NOT_ALLOWED_CODE);
    echo json_encode(['error' => 'Method now allowed!']);
    exit;
}

try {
    // Takes raw data from the request
    $json = file_get_contents('php://input');

    // Converts it into a PHP object
    $input = json_decode($json, true);

    // Chech json complitness
    if (!isset($input['text'])) {
        http_response_code(BAD_REQUEST_CODE);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    $currentId = null;

    // Create lock file
    $lockFile = fopen("../../data/lockfile", "w+");

    if (flock($lockFile, LOCK_EX)) {
        // Get last id and make new one
        $currentId = intval(file_get_contents("../../data/last_id.txt"));

        // Create new item
        $newItem = [
            'id' => $currentId,
            'text' => $input['text'],
            'checked' => false
        ];

        // Get all items and add new one to the end of the array
        $items = json_decode(file_get_contents("../../data/items.json"), true);
        array_push($items['items'], $newItem);

        // Save items and new id
        file_put_contents("../../data/items.json", json_encode($items, JSON_PRETTY_PRINT));
        file_put_contents("../../data/last_id.txt", $currentId + 1);

        flock($lockFile, LOCK_UN);

        // Return json with id
        echo json_encode(['id' => $currentId], JSON_PRETTY_PRINT);
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