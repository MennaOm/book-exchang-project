<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

echo "<h1>Welcome, " . $_SESSION["username"] . "!</h1>";
echo "<p>Your contact info is private.</p>";
echo '<a href="logout.php">Logout</a>';
