<?php
include '../config/db.php';
$result = $conn->query("SHOW COLUMNS FROM student");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>
