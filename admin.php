<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.html?error=session_expired");
    exit();
}

$adminName = $_SESSION["admin_full_name"];
$html = file_get_contents("admin.html");
$html = str_replace("{{admin_name}}", htmlspecialchars($adminName), $html);

echo $html;
?>
