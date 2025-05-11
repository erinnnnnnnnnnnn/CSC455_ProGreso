<?php
session_start();
require 'database.php';

// Ensure admin is logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.html");
    exit();
}

// Ensure the goal id is provided for editing
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('Invalid goal ID.'); window.location.href='view_goals.php';</script>";
    exit();
}

$goalId = intval($_GET['id']);

// Fetch goal details
$stmt = $conn->prepare("SELECT goal_id, user_id, title, description, status, target_date FROM goals WHERE goal_id = ?");
$stmt->bind_param("i", $goalId);
$stmt->execute();
$result = $stmt->get_result();

// Check if goal exists
if ($result->num_rows === 0) {
    echo "<script>alert('Goal not found.'); window.location.href='view_goals.php';</script>";
    exit();
}

$goal = $result->fetch_assoc();
$stmt->close();

// Handle form submission for goal update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $status = trim($_POST["status"]);
    $targetDate = $_POST["target_date"];

    // Validate inputs
    if (empty($title) || empty($description) || empty($status) || empty($targetDate)) {
        echo "<script>alert('All fields are required.'); window.location.href = 'edit_goal.php?id={$goalId}';</script>";
        exit();
    }

    // Update goal information in the database
    $stmt = $conn->prepare("UPDATE goals SET title = ?, description = ?, status = ?, target_date = ? WHERE goal_id = ?");
    $stmt->bind_param("ssssi", $title, $description, $status, $targetDate, $goalId);

    if ($stmt->execute()) {
        echo "<script>alert('Goal updated successfully!'); window.location.href = 'view_goals.php';</script>";
    } else {
        echo "<script>alert('Failed to update goal. Try again.'); window.location.href = 'edit_goal.php?id={$goalId}';</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Goal - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .hero-section {
            background: url('progreso.jpg') no-repeat center center/cover;
            height: 30vh;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
        }
        .hero-overlay {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }
        .hero-content {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="landingpage.html">
        <img src="Logo.png" alt="Logo" width="50" height="50" class="d-inline-block align-text-top">
        <a class="navbar-brand fs-3" href="landingpage.html">ProGreso Admin</a>
    </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="admin.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<header class="hero-section">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1>Edit ProGreso Goal</h1>
    </div>
</header>

<div class="container mt-5">
    <form action="edit_goal.php?id=<?= $goal['goal_id'] ?>" method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($goal['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($goal['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
                <option value="in_progress" <?= ($goal['status'] == 'in_progress') ? 'selected' : '' ?>>In Progress</option>
                <option value="completed" <?= ($goal['status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="target_date" class="form-label">Target Date</label>
            <input type="date" class="form-control" id="target_date" name="target_date" value="<?= htmlspecialchars($goal['target_date']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Goal</button>
    </form>
</div>

<footer class="bg-primary text-white text-center py-3 mt-5">
    <p>&copy; 2025 ProGreso. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
