<?php
session_start();
require 'database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Safely access POST variables only after checking method
    $userId = $_SESSION["user_id"];
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

    // Fetch stored password
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
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

    // Update profile
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $fullName, $email, $userId);

    if ($stmt->execute()) {
        $_SESSION["full_name"] = $fullName;
        $_SESSION["email"] = $email;
        echo "<script>alert('Profile updated successfully!'); window.location.href = 'home.php';</script>";
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
