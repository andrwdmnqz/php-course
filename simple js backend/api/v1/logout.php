<?php

define("SERVER_ERROR_CODE", "500");

// Setup CORS
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://frontend.local");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://frontend.local");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Credentials: true");
    exit(0);
}

try {
    session_start();
    session_destroy();
    echo json_encode(['ok' => 'true']);
} catch (Exception $e) {
    http_response_code(SERVER_ERROR_CODE);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}