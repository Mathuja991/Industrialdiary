<?php
session_start();
include '../config/db.php';

echo "<h1>Mentor Login Test</h1>";

// Check if mentor exists
$username = ""; // PUT YOUR MENTOR USERNAME HERE
if (isset($_GET['username'])) {
    $username = $_GET['username'];
}

if ($username) {
    $stmt = $conn->prepare("SELECT u.*, m.mentor_id, m.working_organization FROM users u LEFT JOIN mentor m ON u.id = m.user_id WHERE u.username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    echo "<h2>User Data:</h2>";
    echo "<pre>";
    print_r($user);
    echo "</pre>";
    
    echo "<h2>Session Data:</h2>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

echo '<form method="GET">';
echo '<input type="text" name="username" placeholder="Enter mentor username" value="' . htmlspecialchars($username) . '">';
echo '<button type="submit">Check</button>';
echo '</form>';
?>
