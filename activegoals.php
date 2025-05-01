<?php
session_start();
require 'database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$userId = $_SESSION["user_id"];

$sql = "SELECT title, description, created_at, target_date FROM goals WHERE user_id = ? AND status = 'in_progress'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$goalCards = "";
while ($row = $result->fetch_assoc()) {
    $goalCards .= '
    <div class="card p-4">
        <h4>Goal: ' . htmlspecialchars($row['title']) . '</h4>
        <p><strong>Description:</strong> ' . htmlspecialchars($row['description']) . '</p>
        <p><strong>Start:</strong> ' . htmlspecialchars($row['created_at']) . '</p>
        <p><strong>Target:</strong> ' . htmlspecialchars($row['target_date']) . '</p>
    </div>';
}

$html = file_get_contents("active_goals.html");
$html = str_replace("{{active_goals_list}}", $goalCards, $html);

echo $html;

$stmt->close();
$conn->close();
?>
