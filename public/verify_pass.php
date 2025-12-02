<?php
$hash = '$2y$10$/lhWl5Z.1TavBVNYf9YvoeakbKRimsAymQ404lUGZqUE9c1ACrs2O';
$password = 'password123';

if (password_verify($password, $hash)) {
    echo "Password 'password123' is CORRECT for kamal.";
} else {
    echo "Password 'password123' is INCORRECT for kamal.";
}
?>
