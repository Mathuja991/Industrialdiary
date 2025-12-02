<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include 'config/db.php';

echo "<h2>Login Simulation Debug</h2>";

// 1. Simulate Login for 'kamal'
$username = 'kamal';
$password = 'password123'; // Assuming this is the password, but we will check hash

echo "Attempting to find user: $username<br>";

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    echo "User found: " . print_r($user, true) . "<br>";
    
    // Manually set session for testing
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    
    echo "Session set: " . print_r($_SESSION, true) . "<br>";
    
    // 2. Simulate Mentor Dashboard Logic
    echo "<h3>Testing Mentor Dashboard Logic</h3>";
    
    $user_id = $_SESSION['user_id'];
    echo "Querying mentor table for user_id: $user_id<br>";
    
    $mentor_query = $conn->query("SELECT mentor_id FROM mentor WHERE user_id = '$user_id'");
    
    if ($mentor_query) {
        $mentor_data = $mentor_query->fetch_assoc();
        if ($mentor_data) {
            echo "Mentor Data Found: " . print_r($mentor_data, true) . "<br>";
            $mentor_id = $mentor_data['mentor_id'];
            echo "Mentor ID: $mentor_id<br>";
        } else {
            echo "<span style='color:red'>Error: No mentor profile found for this user.</span><br>";
        }
    } else {
        echo "Query failed: " . $conn->error . "<br>";
    }
    
} else {
    echo "User not found.<br>";
}
?>
