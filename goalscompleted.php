<?php
session_start();
require 'database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$userId = $_SESSION["user_id"];

// Get completed goals
$sql = "SELECT goal_id, title, target_date FROM goals WHERE user_id = ? AND status = 'completed'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$goalsOutput = "";

while ($goal = $result->fetch_assoc()) {
    $goalId = $goal["goal_id"];
    $title = htmlspecialchars($goal["title"]);
    $targetDate = htmlspecialchars($goal["target_date"]);

    // Fetch update texts
    $updatesSql = "SELECT update_text, updated_at FROM goal_updates WHERE goal_id = ? ORDER BY updated_at ASC";
    $updatesStmt = $conn->prepare($updatesSql);
    $updatesStmt->bind_param("i", $goalId);
    $updatesStmt->execute();
    $updatesResult = $updatesStmt->get_result();

    $updateList = "";
    if ($updatesResult->num_rows > 0) {
        $updateList .= "<ul class='update-list'>";
        while ($update = $updatesResult->fetch_assoc()) {
            $updateText = htmlspecialchars($update['update_text']);
            $updateDate = htmlspecialchars($update['updated_at']);
            $updateList .= "<li><strong>$updateDate:</strong> $updateText</li>";
        }
        $updateList .= "</ul>";
    } else {
        $updateList = "<p class='text-muted'>No text updates available.</p>";
    }
    $updatesStmt->close();

    // Fetch progress tracking
    $progressSql = "SELECT progress_percent, updated_at FROM progress_tracking WHERE goal_id = ? ORDER BY updated_at ASC";
    $progressStmt = $conn->prepare($progressSql);
    $progressStmt->bind_param("i", $goalId);
    $progressStmt->execute();
    $progressResult = $progressStmt->get_result();

    $progressList = "";
    if ($progressResult->num_rows > 0) {
        $progressList .= "<ul class='progress-list'>";
        while ($progress = $progressResult->fetch_assoc()) {
            $percent = htmlspecialchars($progress['progress_percent']);
            $progressDate = htmlspecialchars($progress['updated_at']);
            $progressList .= "<li><strong>$progressDate:</strong> $percent%</li>";
        }
        $progressList .= "</ul>";
    } else {
        $progressList = "<p class='text-muted'>No progress updates available.</p>";
    }
    $progressStmt->close();

    $goalsOutput .= "
    <div class='card p-4'>
        <h5 class='mb-2'>$title</h5>
        <p class='text-muted'>Completed on $targetDate</p>
        <h6 class='mt-3'>Update History:</h6>
        $updateList
        <h6 class='mt-3'>Progress History:</h6>
        $progressList
    </div>
    ";
}

$stmt->close();
$conn->close();

$html = file_get_contents("goalscompleted.html");
$html = str_replace("{{completed_goals_list}}", $goalsOutput, $html);

echo $html;
?>
