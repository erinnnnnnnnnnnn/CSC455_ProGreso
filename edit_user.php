<?php
session_start();
require 'database.php';

// Ensure admin is logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.html");
    exit();
}

// Ensure the user id is provided for editing
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('Invalid user ID.'); window.location.href='view_users.php';</script>";
    exit();
}

$userId = intval($_GET['id']);

// Fetch user details
$stmt = $conn->prepare("SELECT user_id, full_name, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('User not found.'); window.location.href='view_users.php';</script>";
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);

    // Validate inputs
    if (empty($fullName) || empty($email)) {
        echo "<script>alert('All fields are required.'); window.location.href = 'edit_user.php?id={$userId}';</script>";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.'); window.location.href = 'edit_user.php?id={$userId}';</script>";
        exit();
    }

    // Update user information
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $fullName, $email, $userId);

    if ($stmt->execute()) {
        echo "<script>alert('User updated successfully!'); window.location.href = 'view_users.php';</script>";
    } else {
        echo "<script>alert('Failed to update user. Try again.'); window.location.href = 'edit_user.php?id={$userId}';</script>";
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
    <title>Edit User - Admin</title>
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
        <h1>Edit ProGreso User</h1>
    </div>
</header>

<div class="container mt-5">
    <form method="POST" action="edit_user.php?id=<?= $userId ?>">
        <div class="mb-3">
            <label for="full_name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update User</button>
        <a href="admin_users.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

<footer class="bg-primary text-white text-center py-3 mt-5">
    <p>&copy; 2025 ProGreso. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
