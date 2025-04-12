<?php
session_start();
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        header("Location: login.html?error=empty");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $hashed = $user["password_hash"];
        $plainOK = ($password === $hashed); // check for plain text
        $hashOK = password_verify($password, $hashed); // check if hashed

        if ($plainOK || $hashOK) {
            // Password is correct
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["full_name"] = $user["full_name"];
            header("Location: home.php");
            exit();
        }
    }

    // Failed login
    header("Location: login.html?error=invalid");
    exit();
}
?>
