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
    $unitsCompleted = (int) $_POST["units_completed"];
    $updatedAt = date("Y-m-d H:i:s");

    // Get total units for the goal
    $stmt = $conn->prepare("SELECT units FROM goals WHERE goal_id = ?");
    $stmt->bind_param("i", $goalId);
    $stmt->execute();
    $stmt->bind_result($totalUnits);
    $stmt->fetch();
    $stmt->close();

    $progressPercent = 0;
    if ($totalUnits > 0) {
        $progressPercent = round(($unitsCompleted / $totalUnits) * 100);
    }

    // Insert into goal_updates
    $stmt1 = $conn->prepare("INSERT INTO goal_updates (goal_id, update_text, updated_at) VALUES (?, ?, ?)");
    $stmt1->bind_param("iss", $goalId, $updateText, $updatedAt);
    $stmt1->execute();
    $stmt1->close();

    // Insert into progress_tracking
    $stmt2 = $conn->prepare("INSERT INTO progress_tracking (goal_id, progress_percent, total_units_completed, updated_at) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("iiis", $goalId, $progressPercent, $unitsCompleted, $updatedAt);
    $stmt2->execute();
    $stmt2->close();

    echo "<script>alert('Update submitted successfully!'); window.location.href='activegoals.php';</script>";
    exit();
}
?>
