<?php
session_start();
require 'database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $startDate = $_POST["start_date"];
    $targetDate = $_POST["target_date"];

    if (empty($title) || empty($description) || empty($startDate) || empty($targetDate)) {
        echo "<script>alert('Please fill out all fields.'); window.location.href='home.php';</script>";
        exit();
    }

    $createdAt = date("Y-m-d");

    $stmt = $conn->prepare("INSERT INTO goals (user_id, title, description, status, target_date, created_at) VALUES (?, ?, ?, 'in_progress', ?, ?)");
    $stmt->bind_param("issss", $userId, $title, $description, $targetDate, $createdAt);

    if ($stmt->execute()) {
        header("Location: home.php?goal_added=1");
        exit();
    } else {
        echo "<script>alert('Failed to add goal. Please try again.'); window.location.href='home.php';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: home.php");
    exit();
}
?>
