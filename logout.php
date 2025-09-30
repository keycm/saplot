<?php
session_start();

// Destroy lahat ng session data
session_unset();
session_destroy();

// Optional: para siguradong wala na kahit cookies
setcookie(session_name(), '', time() - 3600, '/');

// Redirect balik login page
header("Location: login.php");
exit();
?>