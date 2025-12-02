<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
session_start();
include '../config/db.php';

// Ensure the user is a staff member
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'staff') {
    echo "<p class='error-msg'>Access Denied. Only staff members can access this page.</p>";
    exit;
}

// Fetch lecturer's name from the database based on the session ID
$lecturer_name = "";
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($lecturer_name);
    $stmt->fetch();
    $stmt->close();
}

// Fetch counts for dashboard metrics
$total_students = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetch_row()[0];
$total_inspections = $conn->query("SELECT COUNT(*) FROM inspection_reports")->fetch_row()[0];
$pending_results = $conn->query("SELECT COUNT(DISTINCT ir.student_id) FROM inspection_reports ir LEFT JOIN inspection_results res ON ir.student_id = res.student_id WHERE res.student_id IS NULL")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <?php include 'includes/head_tailwind.php'; ?>
</head>
<body class="bg-light font-sans antialiased">

<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="ml-64 mt-16 p-8 transition-all duration-300">
    <div class="max-w-7xl mx-auto animate-fade-in">
        
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-primary mb-2">Welcome, <?php echo htmlspecialchars($lecturer_name); ?>!</h1>
            <p class="text-gray-600">Manage inspection reports and student results</p>
        </div>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Students -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm uppercase tracking-wide">Total Students</p>
                        <h3 class="text-4xl font-bold mt-2"><?php echo $total_students; ?></h3>
                    </div>
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Inspections -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm uppercase tracking-wide">Total Inspections</p>
                        <h3 class="text-4xl font-bold mt-2"><?php echo $total_inspections; ?></h3>
                    </div>
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pending Results -->
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm uppercase tracking-wide">Pending Results</p>
                        <h3 class="text-4xl font-bold mt-2"><?php echo $pending_results; ?></h3>
                    </div>
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-md p-8 mb-8">
            <h2 class="text-xl font-bold text-primary mb-6">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="upload_inspection.php" class="group bg-gradient-to-br from-secondary to-blue-600 hover:from-blue-600 hover:to-secondary text-white rounded-lg p-6 text-center transition-all transform hover:scale-105 shadow-md hover:shadow-xl">
                    <svg class="w-12 h-12 mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <span class="font-semibold">Upload Inspection</span>
                </a>

                <a href="view_inspections.php" class="group bg-gradient-to-br from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-500 text-white rounded-lg p-6 text-center transition-all transform hover:scale-105 shadow-md hover:shadow-xl">
                    <svg class="w-12 h-12 mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span class="font-semibold">View Inspections</span>
                </a>

                <a href="assign_inspectionresults.php" class="group bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-500 text-white rounded-lg p-6 text-center transition-all transform hover:scale-105 shadow-md hover:shadow-xl">
                    <svg class="w-12 h-12 mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                    <span class="font-semibold">Assign Results</span>
                </a>

                <a href="student_results.php" class="group bg-gradient-to-br from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-500 text-white rounded-lg p-6 text-center transition-all transform hover:scale-105 shadow-md hover:shadow-xl">
                    <svg class="w-12 h-12 mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span class="font-semibold">Calculate Results</span>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-md p-8">
            <h2 class="text-xl font-bold text-primary mb-6">Recent Inspection Reports</h2>
            
            <?php
            // Fetch recent inspection reports
            $recent_reports = [];
            $stmt = $conn->prepare("SELECT ir.id, ir.inspection_date, ir.inspector_name, u.full_name, u.username 
                                    FROM inspection_reports ir 
                                    JOIN users u ON ir.student_id = u.id 
                                    ORDER BY ir.inspection_date DESC 
                                    LIMIT 5");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $recent_reports[] = $row;
            }
            $stmt->close();
            ?>

            <?php if (empty($recent_reports)): ?>
                <div class="text-center py-12 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-lg font-medium mb-2">No inspection reports yet</p>
                    <p class="text-sm mb-4">Upload your first inspection report to get started</p>
                    <a href="upload_inspection.php" class="inline-block bg-secondary hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg transition-all">
                        Upload Inspection Report
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-700 uppercase text-xs tracking-wider">
                                <th class="p-4 font-semibold border-b">Date</th>
                                <th class="p-4 font-semibold border-b">Inspector</th>
                                <th class="p-4 font-semibold border-b">Student</th>
                                <th class="p-4 font-semibold border-b">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-600">
                            <?php foreach ($recent_reports as $report): ?>
                                <tr class="hover:bg-gray-50 transition-colors border-b">
                                    <td class="p-4"><?php echo htmlspecialchars($report['inspection_date']); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($report['inspector_name']); ?></td>
                                    <td class="p-4">
                                        <div class="font-medium text-gray-800"><?php echo htmlspecialchars($report['full_name']); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($report['username']); ?></div>
                                    </td>
                                    <td class="p-4">
                                        <a href="view_inspections.php" class="text-secondary hover:text-blue-700 font-medium transition-colors">
                                            View Details →
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-center">
                    <a href="view_inspections.php" class="text-secondary hover:text-blue-700 font-medium transition-colors">
                        View All Inspection Reports →
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>
