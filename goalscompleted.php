<?php
session_start();
require 'database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$userId = $_SESSION["user_id"];

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

    // Get progress data
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

    $goalsOutput .= "
    <div class='card p-4 mb-4'>
        <h5>$title</h5>
        <p class='text-muted'>Completed on $targetDate</p>
        <button class='btn btn-sm btn-primary' onclick='generateReport(\"$goalId\", \"$title\")'>Download Progress Report (PNG)</button>

        <div id='report_$goalId' style='display:none; background-color: white; padding: 10px;'>
            <h4>$title</h4>
            <p>Completed on $targetDate</p>
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
                const tr = document.createElement('tr');
                const percent = progressValues[index] ?? '';
                tr.innerHTML = `<td>\${update.updated_at}</td><td>\${update.update_text}</td><td>\${percent}</td>`;
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
                    link.download = title.replace(/\\s+/g, '_') + '_Progress_Report.png';
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
