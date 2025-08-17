<?php
session_start();
$conn = new mysqli("localhost", "root", "", "...");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST["username"];
    $pass = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username=?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashedPassword);
    $stmt->fetch();

    if (password_verify($pass, $hashedPassword)) {
        $_SESSION["user_id"] = $id;
        $_SESSION["username"] = $user;
        echo "Login successful!";
        header("Location: profile.php");
        exit;
    } else {
        echo "Invalid username or password.";
    }
}
?>