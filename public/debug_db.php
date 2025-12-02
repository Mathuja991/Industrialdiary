<?php
include 'config/db.php';

echo "<h2>Users</h2>";
$result = $conn->query("SELECT * FROM users");
while ($row = $result->fetch_assoc()) {
    print_r($row);
    echo "<br>";
}

echo "<h2>Mentors</h2>";
$result = $conn->query("SELECT * FROM mentor");
while ($row = $result->fetch_assoc()) {
    print_r($row);
    echo "<br>";
}

echo "<h2>Students</h2>";
$result = $conn->query("SELECT * FROM student");
while ($row = $result->fetch_assoc()) {
    print_r($row);
    echo "<br>";
}
?>
