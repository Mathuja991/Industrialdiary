<?php
session_start();
require_once '../includes/auth_check.php';
// Allow both staff and admin to access this page
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'staff' && !isset($_SESSION['admin_id']))) {
    header("Location: login.php");
    exit;
}

include '../config/db.php';
include '../config/db.php';
// include '../public/header.php'; // Removed old header

// Fetch all inspection reports
$inspection_reports = [];
$stmt = $conn->prepare("SELECT ir.id AS inspection_report_id, ir.inspection_date, ir.inspector_name, 
                               ir.supervisor_remarks, ir.student_remarks, ir.student_id, u.full_name, u.username
                        FROM inspection_reports ir
                        JOIN users u ON ir.student_id = u.id
                        ORDER BY ir.inspection_date DESC");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $inspection_reports[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Inspection Reports</title>
    <?php include 'includes/head_tailwind.php'; ?>
</head>
<body class="bg-light font-sans antialiased">

<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="ml-64 mt-16 p-8 transition-all duration-300">
    <div class="bg-white rounded-xl shadow-md p-8 animate-fade-in">
        <div class="flex items-center gap-4 mb-8 border-b pb-4">
            <div class="w-12 h-12 bg-secondary text-white rounded-full flex items-center justify-center">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-primary">All Inspection Reports</h2>
                <p class="text-gray-500">View all submitted inspection reports</p>
            </div>
        </div>

        <?php if (!empty($inspection_reports)): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 uppercase text-xs tracking-wider">
                            <th class="p-4 font-semibold rounded-tl-lg">Inspection Date</th>
                            <th class="p-4 font-semibold">Inspector Name</th>
                            <th class="p-4 font-semibold">Student</th>
                            <th class="p-4 font-semibold">Supervisor Remarks</th>
                            <th class="p-4 font-semibold rounded-tr-lg">Student Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-600 divide-y divide-gray-100">
                        <?php foreach ($inspection_reports as $report): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 font-medium text-gray-800"><?php echo htmlspecialchars($report['inspection_date']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($report['inspector_name']); ?></td>
                                <td class="p-4">
                                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars($report['full_name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($report['username']); ?></div>
                                </td>
                                <td class="p-4 max-w-md">
                                    <div class="line-clamp-2">
                                        <?php echo htmlspecialchars($report['supervisor_remarks']); ?>
                                    </div>
                                </td>
                                <td class="p-4 max-w-md">
                                    <div class="line-clamp-2">
                                        <?php echo htmlspecialchars($report['student_remarks']); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-16 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <p class="text-lg font-medium mb-2">No inspection reports found</p>
                <p class="text-sm mb-4">Upload your first inspection report to get started</p>
                <a href="upload_inspection.php" class="inline-block bg-secondary hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg transition-all">
                    Upload Inspection Report
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
