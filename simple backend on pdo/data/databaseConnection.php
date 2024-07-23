<?php

function openPDOConnection() {
    try {
        $hostName = "localhost";
        $dbName = "todo-list";

        $conn = new PDO("mysql:host=$hostName;dbname=$dbName", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    return $conn;
}

function closePDOConnection($conn) {
    $conn = null;
}