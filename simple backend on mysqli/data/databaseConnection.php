<?php

function openConnection() {
    $conn = new mysqli("localhost", "root", "", "todo-list") or die("Connection failed: %s\n". $conn ->
    error);

    return $conn;
}

function closeConnection($conn) {
    $conn -> close();
}