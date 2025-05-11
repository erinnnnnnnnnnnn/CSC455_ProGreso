<?php
session_start();
require 'database.php';

// Ensure admin is logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.html");
    exit();
}

// Validate update ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('Invalid update ID.'); window.location.href='view_updates.php';</script>";
    exit();
}

$updateId = intval($_GET['id']);

// Fetch update data
$stmt = $conn->prepare("SELECT update_id, goal_id, update_text FROM goal_updates WHERE update_id = ?");
$stmt->bind_param("i", $updateId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Update not found.'); window.location.href='aview_updates.php';</script>";
    exit();
}

$update = $result->fetch_assoc();
$stmt->close();

// Handle update form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $updateText = trim($_POST["update_text"]);

    if (empty($updateText)) {
        echo "<script>alert('Update text cannot be empty.'); window.location.href='edit_update.php?id={$updateId}';</script>";
        exit();
    }

    $stmt = $conn->prepare("UPDATE goal_updates SET update_text = ?, updated_at = NOW() WHERE update_id = ?");
    $stmt->bind_param("si", $updateText, $updateId);

    if ($stmt->execute()) {
        echo "<script>alert('Update edited successfully!'); window.location.href='view_updates.php';</script>";
    } else {
        echo "<script>alert('Failed to update. Try again.'); window.location.href='edit_update.php?id={$updateId}';</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Goal Update - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
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
    <h1>Edit ProGreso Goal Update</h1>
  </div>
</header>

<div class="container mt-5">
  <form action="edit_update.php?id=<?= $update['update_id'] ?>" method="POST">
    <div class="mb-3">
      <label class="form-label">Goal ID</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($update['goal_id']) ?>" disabled />
    </div>
    <div class="mb-3">
      <label for="update_text" class="form-label">Update Text</label>
      <textarea class="form-control" name="update_text" id="update_text" rows="4" required><?= htmlspecialchars($update['update_text']) ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="view_updates.php" class="btn btn-secondary ms-2">Cancel</a>
  </form>
</div>

<footer class="bg-primary text-white text-center py-3 mt-5">
  <p>&copy; 2025 ProGreso. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
