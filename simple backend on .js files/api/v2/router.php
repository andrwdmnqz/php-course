<?php

define("BAD_REQUEST_CODE", "400");

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://frontend.local");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://frontend.local");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Credentials: true");
    exit(0);
}

if (!isset($_GET['action'])) {
    http_response_code(BAD_REQUEST_CODE);
    echo json_encode(['error' => 'No action determined']);
}

$action = $_GET['action'];

switch ($action) {
    case 'register':
        require_once 'register.php';
        break;
    case 'login': 
        require_once 'login.php';
        break;
    case 'logout':
        require_once 'logout.php';
        break;
    case 'getItems':
        require_once 'getItems.php';
        break;
    case 'addItem':
        require_once 'addItem.php';
        break;
    case 'changeItem':
        require_once 'changeItem.php';
        break;
    case 'deleteItem':
        require_once 'deleteItem.php';
        break;
    default: 
        http_response_code(BAD_REQUEST_CODE);
        echo json_encode(['error' => 'Undefined action']);
        exit();    
}