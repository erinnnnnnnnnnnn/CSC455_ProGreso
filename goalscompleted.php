<?php
session_start();
require 'database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$userId = $_SESSION["user_id"];

$sql = "SELECT title, target_date FROM goals WHERE user_id = ? AND status = 'completed'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$goalList = "";
while ($row = $result->fetch_assoc()) {
    $goalList .= '<div class="list-group-item">' .
                 htmlspecialchars($row["title"]) .
                 ' <span class="text-muted">[Completed on ' . htmlspecialchars($row["target_date"]) . ']</span>' .
                 '</div>';
}

$html = file_get_contents("completed_goals.html");
$html = str_replace("{{completed_goals_list}}", $goalList, $html);

echo $html;

$stmt->close();
$conn->close();
?>
