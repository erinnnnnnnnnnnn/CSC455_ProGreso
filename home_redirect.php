<?php
session_start();

// Check if user or admin session exists
if (isset($_SESSION["user_id"])) {
    header("Location: home.php");
} elseif (isset($_SESSION["admin_id"])) {
    header("Location: admin.php");
} else {
    header("Location: login.html"); 
}
exit();
?>
