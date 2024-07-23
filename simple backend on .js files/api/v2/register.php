
<?php

require "../../data/databaseConnection.php";

define("MYSQL_DUPLICATE_ENTRY_CODE", 1062);
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

try {
    session_start();

    // Takes raw data from the request
    $json = file_get_contents('php://input');
    
    // Converts it into a PHP object
    $input = json_decode($json, true);
    
    // Chech json complitness
    if (!isset($input['login']) || !isset($input['pass'])) {
        http_response_code(BAD_REQUEST_CODE);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }
    
    $login = $input['login'];
    $password = password_hash($input['pass'], PASSWORD_DEFAULT);
    // Create query
    $query = "INSERT INTO users (login, password) VALUES ('$login', '$password')";
    
    $conn = openConnection();
    
    try {
        $conn->query($query);

        echo json_encode(['ok' => 'true']);
    } catch (mysqli_sql_exception $e) {

        if ($e->getCode() == MYSQL_DUPLICATE_ENTRY_CODE) {
            http_response_code(BAD_REQUEST_CODE);
            echo json_encode(['error' => 'User already exists']);
        } else {
            http_response_code(BAD_REQUEST_CODE);
            echo json_encode(["error" => "Error: " . $e->getMessage()]);
        }
    }
    
    closeConnection($conn);
} catch (Exception $e) {
    closeConnection($conn);
    http_response_code(SERVER_ERROR_CODE);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}