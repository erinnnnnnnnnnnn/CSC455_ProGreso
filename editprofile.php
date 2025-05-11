<?php
session_start();
require 'database.php';

// Check if the user or admin is logged in
if (!isset($_SESSION["user_id"]) && !isset($_SESSION["admin_id"])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Safely access POST variables only after checking method
    $userId = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : $_SESSION["admin_id"];
    $fullName = trim($_POST["full_name"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $inputPassword = $_POST["current_password"] ?? '';

    if (!$fullName || !$email || !$inputPassword) {
        echo "<script>alert('All fields are required.'); window.location.href = 'editprofile.html';</script>";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.'); window.location.href = 'editprofile.html';</script>";
        exit();
    }

    // Determine whether it's a user or admin
    if (isset($_SESSION["user_id"])) {
        // Fetch stored password for user
        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
    } elseif (isset($_SESSION["admin_id"])) {
        // Fetch stored password for admin
        $stmt = $conn->prepare("SELECT password_hash FROM admins WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
    }

    $stmt->execute();
    $stmt->bind_result($storedPassword);
    $stmt->fetch();
    $stmt->close();

    // Check hashed or plain password
    $isValid = password_verify($inputPassword, $storedPassword) || $inputPassword === $storedPassword;

    if (!$isValid) {
        echo "<script>alert('Incorrect password. Profile not updated.'); window.location.href = 'editprofile.html';</script>";
        exit();
    }

    // Update profile for user or admin
    if (isset($_SESSION["user_id"])) {
        // Update user profile
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $fullName, $email, $userId);
    } elseif (isset($_SESSION["admin_id"])) {
        // Update admin profile
        $stmt = $conn->prepare("UPDATE admins SET full_name = ?, email = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $fullName, $email, $userId);
    }

    if ($stmt->execute()) {
        $_SESSION["full_name"] = $fullName;
        $_SESSION["admin_full_name"] = $fullName;
        $_SESSION["email"] = $email;

        if (isset($_SESSION["admin_id"])) {
            echo "<script>alert('Profile updated successfully!'); window.location.href = 'admin.php';</script>";
        } else {
            echo "<script>alert('Profile updated successfully!'); window.location.href = 'home.php';</script>";
        }
    } else {
        echo "<script>alert('Failed to update profile. Try again.'); window.location.href = 'editprofile.html';</script>";
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: editprofile.html");
    exit();
}
?>
