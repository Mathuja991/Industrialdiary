<?php
$host = 'localhost';
$db = 'group14';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
} else {
    echo "Connected successfully to database '$db'";
}
$conn->close();
?>
