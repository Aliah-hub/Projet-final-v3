<?php 
session_start();
session_destroy();
header("Location: ../PAGES/login.php");
?>