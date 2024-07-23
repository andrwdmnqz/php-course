<?php

require "databaseConnection.php";

header('Content-Type: application/json');

$conn = openConnection();

$itemsQuery = "CREATE TABLE IF NOT EXISTS items (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    text VARCHAR(255) NOT NULL,
    checked TINYINT(1) NOT NULL DEFAULT 0
)";

if ($conn->query($itemsQuery)) {
    echo json_encode(["message" => "Table 'items' created successfully."], JSON_PRETTY_PRINT);
} else {
    echo json_encode(["error" => "Error creating table: " . $conn->error], JSON_PRETTY_PRINT);
}

$usersQuery = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)";

if ($conn->query($usersQuery)) {
    echo json_encode(["message" => "Table 'users' created successfully."], JSON_PRETTY_PRINT);
} else {
    echo json_encode(["error" => "Error creating table: " . $conn->error], JSON_PRETTY_PRINT);
}

closeConnection($conn);