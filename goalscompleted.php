<?php
session_start();
require 'database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$userId = $_SESSION["user_id"];

// Fetch completed goals
$sql = "SELECT goal_id, title, target_date FROM goals WHERE user_id = ? AND status = 'completed'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$goalsOutput = "";

while ($goal = $result->fetch_assoc()) {
    $goalId = $goal["goal_id"];
    $title = htmlspecialchars($goal["title"]);
    $targetDate = $goal["target_date"];

    // Get completion date (when progress reached 100%)
    $completionSql = "SELECT updated_at FROM progress_tracking WHERE goal_id = ? AND progress_percent = 100 ORDER BY updated_at ASC LIMIT 1";
    $completionStmt = $conn->prepare($completionSql);
    $completionStmt->bind_param("i", $goalId);
    $completionStmt->execute();
    $completionStmt->bind_result($completionDate);
    $completionStmt->fetch();
    $completionStmt->close();

    if (!$completionDate) {
        $completionDate = $targetDate; // fallback
    }

    // Date formatting
    $completionText = "Completed on " . htmlspecialchars($completionDate);
    if ($completionDate > $targetDate) {
        $completionText .= " <span style='color:red;'>(completed late)</span>";
    } else {
        $completionText .= " <span style='color:green;'>(on time)</span>";
    }

    // Get progress chart data
    $progressSql = "SELECT progress_percent, updated_at FROM progress_tracking WHERE goal_id = ? ORDER BY updated_at ASC";
    $progressStmt = $conn->prepare($progressSql);
    $progressStmt->bind_param("i", $goalId);
    $progressStmt->execute();
    $progressResult = $progressStmt->get_result();

    $progressDates = [];
    $progressValues = [];

    while ($row = $progressResult->fetch_assoc()) {
        $progressDates[] = $row['updated_at'];
        $progressValues[] = $row['progress_percent'];
    }
    $progressStmt->close();

    // Get update history
    $updateSql = "SELECT update_text, updated_at FROM goal_updates WHERE goal_id = ? ORDER BY updated_at ASC";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("i", $goalId);
    $updateStmt->execute();
    $updateResult = $updateStmt->get_result();

    $updates = [];
    while ($update = $updateResult->fetch_assoc()) {
        $updates[] = $update;
    }
    $updateStmt->close();

    $progressDatesJson = json_encode($progressDates);
    $progressValuesJson = json_encode($progressValues);
    $updatesJson = json_encode($updates);

    $escapedTitle = htmlspecialchars($title, ENT_QUOTES);

    $goalsOutput .= "
    <div class='card p-4 mb-4'>
        <h5>$escapedTitle</h5>
        <p class='text-muted'>$completionText</p>
        <button class='btn btn-sm btn-primary' onclick='generateReport(\"$goalId\", \"$escapedTitle\")'>Download Progress Report (PNG)</button>

        <div id='report_$goalId' style='display:none;'>
            <h4>$escapedTitle</h4>
            <p>$completionText</p>
            <canvas id='chart_$goalId' height='150'></canvas>
            <h5 class='mt-3'>Update History</h5>
            <table class='table table-bordered'>
                <thead><tr><th>Date</th><th>Update</th><th>Progress (%)</th></tr></thead>
                <tbody id='update_body_$goalId'></tbody>
            </table>
        </div>

        <script>
        window['progressDates_$goalId'] = $progressDatesJson;
        window['progressValues_$goalId'] = $progressValuesJson;
        window['updates_$goalId'] = $updatesJson;

        function generateReport(goalId, title) {
            const canvasId = 'chart_' + goalId;
            const updateBodyId = 'update_body_' + goalId;
            const reportId = 'report_' + goalId;

            const updates = window['updates_' + goalId];
            const progressDates = window['progressDates_' + goalId];
            const progressValues = window['progressValues_' + goalId];

            const tbody = document.getElementById(updateBodyId);
            tbody.innerHTML = '';
            updates.forEach((update, index) => {
                const progress = progressValues[index] !== undefined ? progressValues[index] : '';
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>\${update.updated_at}</td><td>\${update.update_text}</td><td>\${progress}</td>`;
                tbody.appendChild(tr);
            });

            const reportElement = document.getElementById(reportId);
            reportElement.style.display = 'block';

            const ctx = document.getElementById(canvasId).getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: progressDates,
                    datasets: [{
                        label: 'Progress (%)',
                        data: progressValues,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        fill: false,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    animation: false,
                    plugins: { legend: { display: true } },
                    scales: { y: { beginAtZero: true, max: 100 } }
                }
            });

            setTimeout(() => {
                html2canvas(reportElement).then(canvas => {
                    const link = document.createElement('a');
                    link.download = title.replace(/\\s+/g, '') + 'ProgressReport.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                    reportElement.style.display = 'none';
                });
            }, 1000);
        }
        </script>
    </div>";
}

$stmt->close();
$conn->close();

$html = file_get_contents("goalscompleted.html");
$html = str_replace("{{completed_goals_list}}", $goalsOutput, $html);
echo $html;
?>
