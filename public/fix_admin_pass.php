<?php
require '../config/db.php';

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Generated hash for 'admin123': " . $hash . "<br>";

// Update the admin user
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $hash);

if ($stmt->execute()) {
    echo "Successfully updated admin password to 'admin123'";
} else {
    echo "Error updating password: " . $conn->error;
}
?>
