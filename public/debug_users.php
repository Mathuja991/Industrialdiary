<?php
include '../config/db.php';
$result = $conn->query("SELECT id, username, role FROM users");
while ($row = $result->fetch_assoc()) {
    echo "User: " . $row['username'] . " Role: " . $row['role'] . "\n";
}
?>
