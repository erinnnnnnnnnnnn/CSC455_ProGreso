<?php
session_start();
session_destroy();
header("Location: landingpage.html?logout=1");
exit();
?>
