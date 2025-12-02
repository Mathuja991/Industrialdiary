<?php
include 'config/db.php';

echo "<h2>Orphan Mentor Check</h2>";
$query = "SELECT u.id, u.username, u.role 
          FROM users u 
          LEFT JOIN mentor m ON u.id = m.user_id 
          WHERE u.role = 'mentor' AND m.mentor_id IS NULL";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "Found orphan mentor users (no profile in mentor table):<br>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
        echo "<br>";
    }
} else {
    echo "All mentor users have profiles.<br>";
}
?>
