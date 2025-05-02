<?php
session_start();
require 'database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$userId = $_SESSION["user_id"];

$sql = "SELECT goal_id, title, description, created_at, target_date FROM goals WHERE user_id = ? AND status = 'in_progress'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$goalCards = "";
while ($goal = $result->fetch_assoc()) {
    $goalId = $goal['goal_id'];
    $progress = 0;
    $latestUpdate = "No updates yet.";

    // Get latest progress
    $progressSql = "SELECT progress_percent FROM progress_tracking WHERE goal_id = ? ORDER BY updated_at DESC LIMIT 1";
    $progressStmt = $conn->prepare($progressSql);
    $progressStmt->bind_param("i", $goalId);
    $progressStmt->execute();
    $progressResult = $progressStmt->get_result();
    if ($row = $progressResult->fetch_assoc()) {
        $progress = $row['progress_percent'];
    }

    // Get latest update text
    $updateSql = "SELECT update_text FROM goal_updates WHERE goal_id = ? ORDER BY updated_at DESC LIMIT 1";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("i", $goalId);
    $updateStmt->execute();
    $updateResult = $updateStmt->get_result();
    if ($row = $updateResult->fetch_assoc()) {
        $latestUpdate = htmlspecialchars($row['update_text']);
    }

    $goalCards .= '
    <div class="card p-4">
        <h4>' . htmlspecialchars($goal['title']) . '</h4>
        <p><strong>Description:</strong> ' . htmlspecialchars($goal['description']) . '</p>
        <p><strong>Progress:</strong> ' . $progress . '%</p>
        <p><strong>Latest Update:</strong> ' . $latestUpdate . '</p>
        <p><strong>Start Date:</strong> ' . $goal['created_at'] . '</p>
        <p><strong>Target Date:</strong> ' . $goal['target_date'] . '</p>

        <form action="submit_update.php" method="POST">
            <input type="hidden" name="goal_id" value="' . $goalId . '">
            <div class="mb-2">
                <label class="form-label">Progress Update</label>
                <textarea name="update_text" class="form-control" placeholder="Enter your update..." required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Units Completed</label>
                <input type="number" name="units_completed" class="form-control" min="0" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-2">Submit Update</button>
            <div class="d-flex justify-content-between">
                <a href="seeprogress.php?goal_id=' . $goalId . '" class="btn btn-outline-info w-49">See Progress</a>
                <a href="airecommendation.php?goal_id=' . $goalId . '" class="btn btn-outline-success w-49">AI Recommendation</a>
            </div>
        </form>
    </div>';
}

$html = file_get_contents("activegoals.html");
$html = str_replace("{{active_goals_list}}", $goalCards, $html);
echo $html;

$stmt->close();
$conn->close();
?>
