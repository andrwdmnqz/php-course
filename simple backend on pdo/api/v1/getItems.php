<?php

require "../../data/databaseConnection.php";

define("METHOD_NOT_ALLOWED_CODE", "405");
define("SERVER_ERROR_CODE", "500");

// Setup CORS
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

// Takes raw data from the request
$json = file_get_contents('php://input');

// Converts it into a PHP object
$input = json_decode($json, true);

try {
    $conn = openPDOConnection();

    // Create query
    $query = "SELECT * FROM items";
    
    // Fetch result and return it
    $statement = $conn->query($query);
    $items = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (count($items) > 0) {
        // Return items
        echo json_encode(["items" => $items], JSON_PRETTY_PRINT);
    }
    
    closePDOConnection($conn);

} catch (Exception $e) {
    http_response_code(SERVER_ERROR_CODE);
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}