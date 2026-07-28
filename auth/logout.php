<?php
session_start();
session_destroy();
header("Location: /waste-project/auth/login.php");
exit;
?>
