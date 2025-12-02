<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../config/db.php';

echo "<h1>Testing Admin Dashboard</h1>";

// Test 1: Check session
echo "<h2>1. Session Check</h2>";
echo "admin_id: " . (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'NOT SET') . "<br>";
echo "user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . "<br>";
echo "role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'NOT SET') . "<br>";

// Test 2: Check database connection
echo "<h2>2. Database Connection</h2>";
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Database connected successfully<br>";

// Test 3: Count students
echo "<h2>3. Count Students</h2>";
try {
    $result = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'student'");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "Student count: " . $count . "<br>";
    } else {
        echo "Query failed: " . $conn->error . "<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test 4: Count mentors
echo "<h2>4. Count Mentors</h2>";
try {
    $result = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'mentor'");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "Mentor count: " . $count . "<br>";
    } else {
        echo "Query failed: " . $conn->error . "<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test 5: Count staff
echo "<h2>5. Count Staff</h2>";
try {
    $result = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'staff'");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "Staff count: " . $count . "<br>";
    } else {
        echo "Query failed: " . $conn->error . "<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<h2>All tests completed!</h2>";
echo "<p><a href='admin_dashboard.php'>Go to Admin Dashboard</a></p>";
?>
