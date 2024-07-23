<?php

require "../../data/databaseConnection.php";

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

// Takes raw data from the request
$json = file_get_contents('php://input');

// Converts it into a PHP object
$input = json_decode($json, true);

// Chech json complitness
if (!isset($input['id'], $input['text'], $input['checked'])) {
    http_response_code(BAD_REQUEST_CODE);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Get needed fields
$inputId = $input['id'];
$text = htmlspecialchars($input['text'], ENT_QUOTES, 'UTF-8');
$checked = boolval($input['checked']);

echo "id $inputId, text $text, checked $checked";

try {
    $conn = openPDOConnection();

    // Prepare query
    $statement = $conn->prepare("UPDATE items SET text = :text, checked = :checked WHERE id = :inputId");

    $statement->bindParam(':text', $text);
    $statement->bindParam(':checked', $checked);
    $statement->bindParam(':inputId', $inputId);

    // Execute query
    if ($statement->execute()) {
        // Return result
        echo json_encode(['ok' => true], JSON_PRETTY_PRINT);
    }

    closePDOConnection($conn);

} catch (Exception $e) {
    $error = $e->getMessage();
    echo $error;
    http_response_code(SERVER_ERROR_CODE);
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}