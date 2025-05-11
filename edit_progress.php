<?php
session_start();
require 'database.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('Invalid progress ID.'); window.location.href='view_progress.php';</script>";
    exit();
}

$progressId = intval($_GET['id']);

// Fetch current data
$stmt = $conn->prepare("SELECT track_id, goal_id, progress_percent FROM progress_tracking WHERE track_id = ?");
$stmt->bind_param("i", $progressId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Entry not found.'); window.location.href='view_progress.php';</script>";
    exit();
}

$entry = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $progress = intval($_POST["progress_percent"]);
    if ($progress < 0 || $progress > 100) {
        echo "<script>alert('Progress must be between 0 and 100.'); window.location.href='edit_progress.php?id={$progressId}';</script>";
        exit();
    }

    $stmt = $conn->prepare("UPDATE progress_tracking SET progress_percent = ?, updated_at = NOW() WHERE track_id = ?");
    $stmt->bind_param("ii", $progress, $progressId);

    if ($stmt->execute()) {
        echo "<script>alert('Progress updated successfully!'); window.location.href='view_progress.php';</script>";
    } else {
        echo "<script>alert('Failed to update.'); window.location.href='edit_progress.php?id={$progressId}';</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Progress - Admin</title>
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
    <h1>Edit ProGreso Progress Entry</h1>
  </div>
</header>

<div class="container mt-5">
  <form action="edit_progress.php?id=<?= $entry['track_id'] ?>" method="POST">
    <div class="mb-3">
      <label class="form-label">Goal ID</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($entry['goal_id']) ?>" disabled />
    </div>
    <div class="mb-3">
      <label for="progress_percent" class="form-label">Progress (%)</label>
      <input type="number" name="progress_percent" id="progress_percent" class="form-control" min="0" max="100" value="<?= $entry['progress_percent'] ?>" required />
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="view_progress.php" class="btn btn-secondary ms-2">Cancel</a>
  </form>
</div>

<footer class="bg-primary text-white text-center py-3 mt-5">
  <p>&copy; 2025 ProGreso. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
