<?php
session_start();

// Check if the user is NOT logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

// Load the HTML template
$html = file_get_contents("home.html");

// Replace placeholders
$userName = $_SESSION["full_name"] ?? "User";
$profilePic = $_SESSION["profile_pic"] ?? "mew.jpg";

$html = str_replace("{{user_name}}", htmlspecialchars($userName), $html);
$html = str_replace("{{profile_pic}}", htmlspecialchars($profilePic), $html);

// Show the final page
echo $html;
?>
