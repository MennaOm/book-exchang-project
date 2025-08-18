<?php
session_start();
if (isset($_SESSION["username"])) {
    echo "👋 Welcome, " . $_SESSION["username"] . "! You are logged in.";
    echo "<br><a href='logout.php'>Logout</a>";
} else {
    echo "⚠️ You must login first! <a href='login.php'>Login</a>";
}
?>