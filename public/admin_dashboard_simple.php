<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../config/db.php';
require_once '../includes/auth_check.php';

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    checkAuth(['admin']);
}

include '../public/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body class="bg-light">

<?php include 'sidebar.php'; ?>

<div class="ml-64 pt-24 p-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-primary mb-8">Admin Dashboard</h1>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <?php
            // Get counts
            $studentCount = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'student'")->fetch_assoc()['count'] ?? 0;
            $mentorCount = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'mentor'")->fetch_assoc()['count'] ?? 0;
            $staffCount = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'staff'")->fetch_assoc()['count'] ?? 0;
            ?>
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-gray-500 text-sm font-medium">Total Students</h3>
                <p class="text-4xl font-bold text-primary mt-2"><?php echo $studentCount; ?></p>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-gray-500 text-sm font-medium">Total Mentors</h3>
                <p class="text-4xl font-bold text-secondary mt-2"><?php echo $mentorCount; ?></p>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-gray-500 text-sm font-medium">Total Staff</h3>
                <p class="text-4xl font-bold text-accent mt-2"><?php echo $staffCount; ?></p>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-primary mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="manage_users.php" class="bg-secondary hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg text-center transition-all">
                    Manage Users
                </a>
                <a href="assign_mentor.php" class="bg-accent hover:bg-orange-600 text-white font-semibold py-3 px-6 rounded-lg text-center transition-all">
                    Assign Mentors
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../public/footer.php'; ?>

</body>
</html>
