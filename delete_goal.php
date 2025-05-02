<?php
session_start();
require 'database.php';

if (!isset($_SESSION["user_id"]) || $_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit();
}

$goalId = $_POST["goal_id"];

// Delete from goal_updates
$conn->query("DELETE FROM goal_updates WHERE goal_id = $goalId");

// Delete from progress_tracking
$conn->query("DELETE FROM progress_tracking WHERE goal_id = $goalId");

// Delete from ai_recommendation
$conn->query("DELETE FROM ai_recommendation WHERE user_id = " . $_SESSION["user_id"] . " AND recommended_goal IN (
    SELECT title FROM goals WHERE goal_id = $goalId
)");

// Delete from goals
$stmt = $conn->prepare("DELETE FROM goals WHERE goal_id = ?");
$stmt->bind_param("i", $goalId);

if ($stmt->execute()) {
    echo "<script>alert('Goal deleted successfully.'); window.location.href='activegoals.php';</script>";
} else {
    echo "<script>alert('Failed to delete goal.'); window.location.href='activegoals.php';</script>";
}

$stmt->close();
$conn->close();
?>
