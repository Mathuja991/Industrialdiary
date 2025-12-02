<?php
include 'config/db.php';

$username = 'mentor_test';
$password = 'password123';
$role = 'mentor';
$full_name = 'Test Mentor';
$working_org = 'Test Org';

// Check if exists
$check = $conn->query("SELECT id FROM users WHERE username = '$username'");
if ($check->num_rows > 0) {
    echo "User $username already exists. Resetting password...<br>";
    $user = $check->fetch_assoc();
    $user_id = $user['id'];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password = '$hash' WHERE id = $user_id");
    echo "Password reset to $password<br>";
} else {
    echo "Creating user $username...<br>";
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, role, full_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $hash, $role, $full_name);
    $stmt->execute();
    $user_id = $conn->insert_id;
    
    $stmt2 = $conn->prepare("INSERT INTO mentor (user_id, working_organization) VALUES (?, ?)");
    $stmt2->bind_param("is", $user_id, $working_org);
    $stmt2->execute();
    echo "User created.<br>";
}
?>
