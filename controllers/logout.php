<?php
session_start();
session_destroy();
header("Location: ../controllers/login.php");
exit();
?>
