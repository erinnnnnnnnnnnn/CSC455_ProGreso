<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

require 'database.php';

$userId = $_SESSION["user_id"];
$fullName = $_SESSION["full_name"] ?? "User";
$profilePic = $_SESSION["profile_pic"] ?? "mew.jpg";

// Fetch goal stats
$completed = 0;
$active = 0;
$streak = 0;

$sql = "SELECT status, created_at FROM goals WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$today = date('Y-m-d');
while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'completed') $completed++;
    if ($row['status'] === 'in_progress') {
        $active++;
        if (date('Y-m-d', strtotime($row['created_at'])) <= $today) {
            $streak++;
        }
    }
}

// Load HTML template
$html = file_get_contents("home.html");

// Replace placeholders
$html = str_replace("{{user_name}}", htmlspecialchars($fullName), $html);
$html = str_replace("{{profile_pic}}", htmlspecialchars($profilePic), $html);
$html = str_replace("{{completed_goals}}", $completed, $html);
$html = str_replace("{{active_goals}}", $active, $html);
$html = str_replace("{{streak_days}}", $streak, $html);

// Add goal added alert if redirected with success
if (isset($_GET['goal_added']) && $_GET['goal_added'] == 1) {
    $html .= "<script>alert('Goal added successfully!');</script>";
}

echo $html;
?>
