<?php
session_start();

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Kung walang login session, redirect balik login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>