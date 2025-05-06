<?php
session_start();
require 'database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

if (!isset($_GET["goal_id"])) {
    echo "Goal ID missing!";
    exit();
}

$goalId = intval($_GET["goal_id"]);
$userId = $_SESSION["user_id"];

// Verify the goal belongs to the user
$check = $conn->prepare("SELECT title FROM goals WHERE goal_id = ? AND user_id = ?");
$check->bind_param("ii", $goalId, $userId);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    echo "Invalid goal!";
    exit();
}

$check->bind_result($goalTitle);
$check->fetch();
$check->close();

// Fetch progress data
$stmt = $conn->prepare("SELECT progress_percent, updated_at FROM progress_tracking WHERE goal_id = ? ORDER BY updated_at ASC");
$stmt->bind_param("i", $goalId);
$stmt->execute();
$result = $stmt->get_result();

$dates = [];
$percentages = [];
while ($row = $result->fetch_assoc()) {
    $dates[] = $row["updated_at"];
    $percentages[] = $row["progress_percent"];
}

$stmt->close();
$conn->close();

// Inject into the HTML
$html = file_get_contents("seeprogress.html");
$html = str_replace("{{goal_title}}", htmlspecialchars($goalTitle), $html);
$html = str_replace("{{labels}}", json_encode($dates), $html);
$html = str_replace("{{data}}", json_encode($percentages), $html);
echo $html;
?>
