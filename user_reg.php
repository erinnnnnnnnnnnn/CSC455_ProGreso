<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST["first_name"]);
    $lastName = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];
    $userType = $_POST["user_type"];

    // Validate inputs
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword) || empty($userType)) {
        header("Location: user_reg.html?error=empty");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: user_reg.html?error=email");
        exit();
    }

    if ($password !== $confirmPassword) {
        header("Location: user_reg.html?error=nomatch");
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $fullName = $firstName . " " . $lastName;

    if ($userType === "user") {
        // Check if user already exists
        $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            header("Location: user_reg.html?error=exists");
            exit();
        }

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullName, $email, $hashedPassword);

    } elseif ($userType === "admin") {
        // Check if admin already exists
        $check = $conn->prepare("SELECT * FROM admins WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            header("Location: user_reg.html?error=exists");
            exit();
        }

        // Insert new admin
        $stmt = $conn->prepare("INSERT INTO admins (full_name, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullName, $email, $hashedPassword);
    } else {
        header("Location: user_reg.html?error=server");
        exit();
    }

    if ($stmt->execute()) {
        header("Location: login.html?success=1");
        exit();
    } else {
        header("Location: user_reg.html?error=server");
        exit();
    }
}
?>

