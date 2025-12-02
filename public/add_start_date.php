<?php
include '../config/db.php';

// SQL to add start_date column if it doesn't exist
$sql = "ALTER TABLE student ADD COLUMN start_date DATE NULL DEFAULT NULL";

if ($conn->query($sql) === TRUE) {
    echo "Column start_date added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}

$conn->close();
?>
