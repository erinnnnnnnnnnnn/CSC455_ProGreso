<?php
session_start();
require 'database.php';

// Ensure admin is logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.html");
    exit();
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $userId = intval($_GET['delete']);

    // Delete from goal_updates
    $stmt = $conn->prepare("DELETE FROM goal_updates WHERE goal_id IN (SELECT goal_id FROM goals WHERE user_id = ?)");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // Delete from progress_tracking
    $stmt = $conn->prepare("DELETE FROM progress_tracking WHERE goal_id IN (SELECT goal_id FROM goals WHERE user_id = ?)");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // Delete from goals
    $stmt = $conn->prepare("DELETE FROM goals WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // Delete from ai_recommendation
    $stmt = $conn->prepare("DELETE FROM ai_recommendation WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('User and related data deleted successfully.'); window.location.href='view_users.php';</script>";
    exit();
}

// Fetch all users
$sql = "SELECT user_id, full_name, email, created_at FROM users ORDER BY user_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Users</title>
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
        <h1>ProGreso User Information</h1>
    </div>
</header>

<div class="container mt-5">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>User ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['user_id']) ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <a href="edit_user.php?id=<?= $row['user_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="view_users.php?delete=<?= $row['user_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">Delete</a>
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
<script type="text/javascript">
    function confirmDelete() {
        return confirm("Are you sure you want to delete this user? This action will delete all related data.");
    }
</script>
</body>
</html>
