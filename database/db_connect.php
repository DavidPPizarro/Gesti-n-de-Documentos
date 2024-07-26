<?php

// Database connection parameters
$dbHost = "localhost"; // Replace with your database host
$dbUsername = "root"; // Replace with your database username
$dbPassword = "password"; // Replace with your database password
$dbName = "documentos"; // Replace with your database name

// Create connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to close the database connection
