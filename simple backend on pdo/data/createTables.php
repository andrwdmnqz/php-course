<?php

require "databaseConnectionPDO.php";

header('Content-Type: application/json');

$conn = openPDOConnection();

$itemsQuery = "CREATE TABLE IF NOT EXISTS items (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    text VARCHAR(255) NOT NULL,
    checked TINYINT(1) NOT NULL DEFAULT 0
)";

try {
    $statement = $conn->query($query);
    echo json_encode(["message" => "Table 'items' created successfully."], JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error creating table: " . $e->getMessage()]);
}

$conn = openPDOConnection();

$usersQuery = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)";

try {
    $statement = $conn->query($query);
    echo json_encode(["message" => "Table 'users' created successfully."], JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error creating table: " . $e->getMessage()]);
}

closePDOConnection($conn);