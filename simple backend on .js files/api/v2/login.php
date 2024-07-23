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

session_start();
$conn = NULL;

try {
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
    $password = $input['pass'];

    $conn = openConnection();
    // Create query
    $query = "SELECT id, password FROM USERS WHERE login = '$login'";

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['userId'] = $row['id'];
            echo json_encode(['ok' => 'true']);
        } else {
            http_response_code(BAD_REQUEST_CODE);
            echo json_encode(['error' => 'Invalid login or password']);
        }
    } else {
        
        echo json_encode(['error' => 'User not found']);
    }

    closeConnection($conn);
} catch (Exception $e) {
    closeConnection($conn);
    http_response_code(SERVER_ERROR_CODE);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}