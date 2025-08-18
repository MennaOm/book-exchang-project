<?php
session_start();

$conn = new mysqli("localhost", "root", "982004", "book_exchange");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // نجيب اليوزر من الداتابيز
    $stmt = $conn->prepare("SELECT user_id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashedPassword);
        $stmt->fetch();

        // نتحقق من الباسورد
        if (password_verify($password, $hashedPassword)) {
            $_SESSION["user_id"] = $user_id;
            $_SESSION["username"] = $username;

            echo "✅ Login successful!<br>";
            echo "Welcome, " . htmlspecialchars($username);

            // ممكن نوجهه لصفحة تانية
            // header("Location: profile.php");
            // exit;
        } else {
            echo "❌ Invalid password.";
        }
    } else {
        echo "❌ No user found with that username.";
    }
}
?>

<!-- فورم تسجيل دخول -->
<form method="post">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Login</button>
</form>
