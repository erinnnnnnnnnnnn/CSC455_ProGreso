<?php
require_once 'tcpdf/tcpdf.php';
require 'database.php';

if (!isset($_POST['goal_id'])) {
    die('Goal ID is required.');
}

$goalId = intval($_POST['goal_id']);

// Fetch goal info
$goalSql = "SELECT title, target_date FROM goals WHERE goal_id = ?";
$goalStmt = $conn->prepare($goalSql);
$goalStmt->bind_param("i", $goalId);
$goalStmt->execute();
$goalResult = $goalStmt->get_result()->fetch_assoc();

$title = $goalResult['title'];
$targetDate = $goalResult['target_date'];

// Fetch updates and progress
$updates = [];
$sql = "SELECT gu.update_text, gu.updated_at, pt.progress_percent 
        FROM goal_updates gu 
        LEFT JOIN progress_tracking pt 
        ON gu.goal_id = pt.goal_id AND DATE(gu.updated_at) = DATE(pt.updated_at) 
        WHERE gu.goal_id = ? ORDER BY gu.updated_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $goalId);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $updates[] = $row;
}

$stmt->close();

// Generate chart image (reuse same chart logic from seeprogress, or hardcode sample)

// Create PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);
$pdf->Write(0, "Progress Report for Goal: $title");
$pdf->Ln();
$pdf->Write(0, "Target Date: $targetDate");
$pdf->Ln();
$pdf->Ln();
$pdf->Write(0, "Progress History:");
$pdf->Ln();

// Table Header
$html = '<table border="1" cellpadding="5"><tr><th>Date</th><th>Update</th><th>Progress %</th></tr>';

foreach ($updates as $update) {
    $date = $update['updated_at'];
    $text = htmlspecialchars($update['update_text']);
    $percent = $update['progress_percent'] !== null ? $update['progress_percent'] . '%' : '';
    $html .= "<tr><td>$date</td><td>$text</td><td>$percent</td></tr>";
}

$html .= '</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Save and force download
$pdf->Output("Progress_Report_{$goalId}.pdf", 'D'); // 'D' forces download

$conn->close();
?>
