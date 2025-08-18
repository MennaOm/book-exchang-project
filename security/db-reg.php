<?php
require_once __DIR__ . '/../_inc/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username   = $_POST["username"];
    $email      = $_POST["email"];
    $password   = $_POST["password"];
    $first_name = $_POST["first_name"];
    $last_name  = $_POST["last_name"];
    $phone      = $_POST["phone"];
    $city       = $_POST["city"];
    $state      = $_POST["state"];

    // هاش للباسورد
    $hashed = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare(
        "INSERT INTO users (username, email, password_hash, first_name, last_name, phone, city, state, registration_date, is_active, privacy_contact_info, email_verified) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1, 'public', 1)"
    );
    $stmt->bind_param("ssssssss", $username, $email, $hashed, $first_name, $last_name, $phone, $city, $state);

    if ($stmt->execute()) {
        echo "✅ Registration successful!";
    } else {
        echo "❌ Error: " . $stmt->error;
    }
}
?>

<!-- فورم بسيط للتجربة -->
<form method="post">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <input type="text" name="first_name" placeholder="First name"><br>
    <input type="text" name="last_name" placeholder="Last name"><br>
    <input type="text" name="phone" placeholder="Phone"><br>
    <input type="text" name="city" placeholder="City"><br>
    <input type="text" name="state" placeholder="State"><br>
    <button type="submit">Register</button>
</form>
