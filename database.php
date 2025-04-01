<?php
$host = "localhost"; 
$user = "root";
$password = ""; 
$database = "progreso";

$conn = new mysqli($host, $user, $password);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (!$conn->select_db($database)) {
    die("Database selection failed: " . $conn->error);
}

echo "Connected to: " . $database;
?>