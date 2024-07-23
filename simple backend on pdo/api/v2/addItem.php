<?php

require "../../data/databaseConnection.php";

define("BAD_REQUEST_CODE", "400");
define("METHOD_NOT_ALLOWED_CODE", "405");
define("SERVER_ERROR_CODE", "500");

// Setup CORS
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

$text = htmlspecialchars($input['text'], ENT_QUOTES, 'UTF-8');
$checked = 0;

try {
    $conn = openPDOConnection();

    // Prepare query
    $statement = $conn->prepare("INSERT INTO items (text, checked) VALUES (:text, :checked)");

    // Execute statement
    if ($statement->execute([':text' => $text, ':checked' => $checked])) {
        // Return json with id
        $currentId = $conn->lastInsertId();
        echo json_encode(['id' => $currentId], JSON_PRETTY_PRINT);
    }

    closePDOConnection($conn);

} catch (Exception $e) {
    http_response_code(SERVER_ERROR_CODE);
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}