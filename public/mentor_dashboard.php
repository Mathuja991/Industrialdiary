<?php
session_start();
require_once '../includes/auth_check.php';
checkAuth(['mentor']);

include '../config/db.php';

$pageTitle = "Mentor Dashboard";

// Get mentor_id from mentor table
$user_id = $_SESSION['user_id'];
$mentor_query = $conn->query("SELECT mentor_id FROM mentor WHERE user_id = '$user_id'");
$mentor_data = $mentor_query->fetch_assoc();

if (!$mentor_data) {
    die("Error: No mentor profile found.");
}
$mentor_id = $mentor_data['mentor_id'];

// Fetch statistics
$pending_diaries = $conn->query("SELECT COUNT(*) as count FROM diaries d JOIN student s ON d.student_id = s.user_id WHERE d.reviewed = 0 AND s.mentor_id = '$mentor_id'")->fetch_assoc()['count'];

$pending_reports = $conn->query("SELECT COUNT(*) as count FROM overall_reports r JOIN student s ON r.student_id = s.user_id WHERE r.status = 'pending' AND s.mentor_id = '$mentor_id'")->fetch_assoc()['count'];

$total_students = $conn->query("SELECT COUNT(*) as count FROM student WHERE mentor_id = '$mentor_id'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: {
            primary: '#2c3e50', secondary: '#3498db', accent: '#e67e22', dark: '#1a1a1a', light: '#f4f7f6'
        }}}};
    </script>
</head>
<body class="bg-light">

<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="ml-64 mt-16 p-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <!-- Pending Diaries Card -->
        <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-secondary">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase">Pending Diaries</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $pending_diaries; ?></h3>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center text-secondary">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                </div>
            </div>
            <a href="review_diaries.php" class="mt-4 inline-block text-secondary hover:underline text-sm font-semibold">Review Diaries →</a>
        </div>

        <!-- Pending Reports Card -->
        <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-accent">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase">Pending Reports</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $pending_reports; ?></h3>
                </div>
                <div class="w-12 h-12 bg-orange-50 rounded-full flex items-center justify-center text-accent">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <a href="review_overall_reports.php" class="mt-4 inline-block text-accent hover:underline text-sm font-semibold">Review Reports →</a>
        </div>

        <!-- Total Students Card -->
        <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase">Assigned Students</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $total_students; ?></h3>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center text-green-600">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-md p-8">
        <h2 class="text-xl font-bold text-primary mb-6">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="review_diaries.php" class="flex items-center gap-4 p-4 border rounded-lg hover:border-secondary hover:bg-blue-50 transition-all group">
                <div class="w-10 h-10 bg-secondary text-white rounded-full flex items-center justify-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Review Weekly Diaries</h3>
                    <p class="text-sm text-gray-500">Provide feedback on student reports</p>
                </div>
            </a>

            <a href="review_overall_reports.php" class="flex items-center gap-4 p-4 border rounded-lg hover:border-accent hover:bg-orange-50 transition-all group">
                <div class="w-10 h-10 bg-accent text-white rounded-full flex items-center justify-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Review Overall Reports</h3>
                    <p class="text-sm text-gray-500">Sign off on progress reports</p>
                </div>
            </a>
        </div>
    </div>
</div>

</body>
</html>
