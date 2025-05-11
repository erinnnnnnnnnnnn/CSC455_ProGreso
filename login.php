<?php
session_start();
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $user_type = $_POST["user_type"];

    if (empty($email) || empty($password) || empty($user_type)) {
        header("Location: login.html?error=empty");
        exit();
    }

    if ($user_type === "user") {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    } elseif ($user_type === "admin") {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    } else {
        header("Location: login.html?error=invalid");
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($account = $result->fetch_assoc()) {
        $storedPassword = $account["password_hash"]; // changed to password_hash as per your schema
        $plainOK = ($password === $storedPassword);
        $hashOK = password_verify($password, $storedPassword);

        if ($plainOK || $hashOK) {
            if ($user_type === "user") {
                $_SESSION["user_id"] = $account["user_id"];
                $_SESSION["full_name"] = $account["full_name"];
                header("Location: home.php");
            } else {
                $_SESSION["admin_id"] = $account["user_id"]; // admin_id is not present, so I used user_id
                $_SESSION["admin_full_name"] = $account["full_name"]; // full_name instead of username
                header("Location: admin.html");
            }
            exit();
        }
    }

    // Failed login
    header("Location: login.html?error=invalid");
    exit();
}
?>

