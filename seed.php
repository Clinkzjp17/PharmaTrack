<?php
// seed.php — run this ONCE to create the default admin account
// Then DELETE this file afterward!

require_once 'config.php';

$username = 'admin';
$password = 'admin123';
$role     = 'admin';
$hash     = password_hash($password, PASSWORD_BCRYPT);

// Remove any broken seeded admin first
$conn->query("DELETE FROM users WHERE username = 'admin' AND role = 'admin'");

$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $hash, $role);

if ($stmt->execute()) {
    echo "
    <style>body{font-family:sans-serif;padding:40px;background:#f0fdf4;}</style>
    <h2 style='color:#16a34a'>✓ Admin account created successfully!</h2>
    <p>Username: <strong>admin</strong></p>
    <p>Password: <strong>admin123</strong></p>
    <br>
    <a href='index.php' style='background:#0f5c63;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;'>Go to Login →</a>
    <br><br>
    <strong style='color:red'>⚠ Delete seed.php from your folder now!</strong>
    ";
} else {
    echo "<h2 style='color:red'>✗ Failed: " . $stmt->error . "</h2>";
}

$stmt->close();
?>
