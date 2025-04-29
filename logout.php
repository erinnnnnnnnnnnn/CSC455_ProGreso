<?php
session_start();
session_unset();
session_destroy();
header("Location: landingpage.html?logout=1");
exit();
?>
