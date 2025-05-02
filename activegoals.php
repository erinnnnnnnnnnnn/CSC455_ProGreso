<?php
session_start();
require 'database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$userId = $_SESSION["user_id"];
$today = date("Y-m-d");

$sql = "SELECT goal_id, title, description, units, unit_type, created_at, target_date FROM goals WHERE user_id = ? AND status = 'in_progress'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$goalCards = "";
while ($goal = $result->fetch_assoc()) {
    $goalId = $goal['goal_id'];
    $units = $goal['units'];
    $unitType = $goal['unit_type'];
    $targetDate = $goal['target_date'];

    // Fetch latest progress
    $progress = 0;
    $latestUpdate = '';
    $progressSql = "SELECT progress_percent FROM progress_tracking WHERE goal_id = ? ORDER BY updated_at DESC LIMIT 1";
    $progressStmt = $conn->prepare($progressSql);
    $progressStmt->bind_param("i", $goalId);
    $progressStmt->execute();
    $progressResult = $progressStmt->get_result();
    if ($row = $progressResult->fetch_assoc()) {
        $progress = $row['progress_percent'];
    }

    // Fetch latest update text
    $updateSql = "SELECT update_text FROM goal_updates WHERE goal_id = ? ORDER BY updated_at DESC LIMIT 1";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("i", $goalId);
    $updateStmt->execute();
    $updateResult = $updateStmt->get_result();
    if ($row = $updateResult->fetch_assoc()) {
        $latestUpdate = htmlspecialchars($row['update_text']);
    }

    // If progress is 100%, auto-complete the goal
    if ($progress >= 100) {
        $updateStatus = $conn->prepare("UPDATE goals SET status = 'completed' WHERE goal_id = ?");
        $updateStatus->bind_param("i", $goalId);
        $updateStatus->execute();
        continue;
    }

    // Deadline alert 
    $deadlineAlert = "";
    $daysLeft = (strtotime($targetDate) - strtotime($today)) / (60 * 60 * 24);
    if ($daysLeft <= 3 && $progress < 100) {
        $deadlineAlert = '<div class="deadline-alert">⚠️Hurry up! Only ' . max(0, (int)$daysLeft) . ' day(s) left to complete this goal!</div>';
    }

    $goalCards .= '
    <div class="card p-4">
        <h4>' . htmlspecialchars($goal['title']) . '</h4>
        ' . $deadlineAlert . '
        <p><strong>Description:</strong> ' . htmlspecialchars($goal['description']) . '</p>
        <p><strong>Progress:</strong> ' . $progress . '%</p>
        <p><strong>Last Update:</strong> ' . ($latestUpdate ?: "No updates yet") . '</p>
        <p><strong>Start Date:</strong> ' . $goal['created_at'] . '</p>
        <p><strong>Target Date:</strong> ' . $targetDate . '</p>
        <p><strong>Total Units:</strong> ' . htmlspecialchars($goal['units']) . ' ' . htmlspecialchars($goal['unit_type']) . '</p>

        <form action="submit_update.php" method="POST">
            <input type="hidden" name="goal_id" value="' . $goalId . '">
            <div class="mb-2">
                <label class="form-label">Progress Update</label>
                <textarea name="update_text" class="form-control" placeholder="Enter your update..." required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Units Completed (' . $unitType . ')</label>
                <input type="number" name="units_completed" class="form-control" min="0" max="' . $units . '" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-2">Submit Update</button>
            <div class="d-flex justify-content-between">
                <a href="seeprogress.php?goal_id=' . $goalId . '" class="btn btn-info w-49">See Progress</a>
                <a href="airecommendation.php?goal_id=' . $goalId . '" class="btn btn-success w-49">AI Recommendation</a>
            </div>
        </form>

        <form action="delete_goal.php" method="POST" class="mt-2 text-center" onsubmit="return confirmDelete();">
            <input type="hidden" name="goal_id" value="' . $goalId . '">
            <button type="submit" class="btn btn-danger btn-sm">Delete Goal</button>
        </form>

    </div>';
}

$html = file_get_contents("activegoals.html");
$html = str_replace("{{active_goals_list}}", $goalCards, $html);
echo $html;

$stmt->close();
$conn->close();
?>

