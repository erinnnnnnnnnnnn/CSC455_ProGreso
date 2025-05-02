<?php
session_start();
require 'database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $goalId = $_POST["goal_id"];
    $updateText = trim($_POST["update_text"]);
    $unitsCompleted = (int)$_POST["units_completed"];
    $updatedAt = date("Y-m-d H:i:s");

    // Get total units for the goal
    $goalQuery = $conn->prepare("SELECT units FROM goals WHERE goal_id = ?");
    $goalQuery->bind_param("i", $goalId);
    $goalQuery->execute();
    $goalResult = $goalQuery->get_result();
    if ($goalRow = $goalResult->fetch_assoc()) {
        $totalUnits = (int)$goalRow["units"];

        // Calculate percentage
        $progressPercent = ($unitsCompleted / $totalUnits) * 100;
        $progressPercent = min(100, round($progressPercent));  // Cap at 100%
    } else {
        echo "<script>alert('Goal not found.'); window.location.href='activegoals.php';</script>";
        exit();
    }

    // Insert update text
    $stmt1 = $conn->prepare("INSERT INTO goal_updates (goal_id, update_text, updated_at) VALUES (?, ?, ?)");
    $stmt1->bind_param("iss", $goalId, $updateText, $updatedAt);
    $stmt1->execute();

    // Insert progress
    $stmt2 = $conn->prepare("INSERT INTO progress_tracking (goal_id, progress_percent, updated_at) VALUES (?, ?, ?)");
    $stmt2->bind_param("ids", $goalId, $progressPercent, $updatedAt);
    $stmt2->execute();

    $stmt1->close();
    $stmt2->close();
    $goalQuery->close();
    $conn->close();

    echo "<script>alert('Progress updated successfully!'); window.location.href='activegoals.php';</script>";
    exit();
} else {
    header("Location: activegoals.php");
    exit();
}
?>
