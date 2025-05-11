<?php
session_start();
require 'database.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.html");
    exit();
}

// Handle delete
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $deleteId = intval($_POST["delete_id"]);
    $stmt = $conn->prepare("DELETE FROM progress_tracking WHERE track_id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Progress entry deleted successfully.');</script>";
}

// Fetch progress entries
$sql = "SELECT track_id, goal_id, progress_percent, updated_at FROM progress_tracking ORDER BY updated_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin - Progress Tracking</title>
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
    <h1>All ProGreso Progress Tracks</h1>
  </div>
</header>

<div class="container mt-5">
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Track ID</th>
        <th>Goal ID</th>
        <th>Progress (%)</th>
        <th>Updated At</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['track_id']) ?></td>
        <td><?= htmlspecialchars($row['goal_id']) ?></td>
        <td><?= htmlspecialchars($row['progress_percent']) ?>%</td>
        <td><?= htmlspecialchars($row['updated_at']) ?></td>
        <td>
          <a href="edit_progress.php?id=<?= $row['track_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
          <form method="POST" onsubmit="return confirm('Delete this entry?');" style="display:inline;">
            <input type="hidden" name="delete_id" value="<?= $row['track_id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>

<footer class="bg-primary text-white text-center py-3 mt-5">
  <p>&copy; 2025 ProGreso. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
