<?php
session_start();
require_once '../includes/auth_check.php';
checkAuth(['student']); // Only students can access this page

include '../config/db.php';
// include '../public/header.php';

// Fetch the overall report for the student
$student_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT summary, challenges, improvements FROM overall_reports WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$report = null;
if ($result->num_rows > 0) {
    $report = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Overall Report</title>
    <?php include 'includes/head_tailwind.php'; ?>
</head>
<body class="bg-light font-sans antialiased">

<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="ml-64 pt-24 p-8 transition-all duration-300">
    <div class="bg-white rounded-xl shadow-md p-8 animate-fade-in">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-secondary text-white rounded-full flex items-center justify-center">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-primary">Overall Progress Report</h2>
                    <p class="text-gray-500">View your submitted overall report</p>
                </div>
            </div>
            <?php if ($report): ?>
                <a href="../public/generate_overall_report.php" target="_blank">
                    <button type="button" class="bg-green-500 hover:bg-green-600 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow hover:shadow-md transition-all">Download PDF</button>
                </a>
            <?php endif; ?>
        </div>
        
        <?php if ($report): ?>
            <div class="space-y-6">
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="w-8 h-8 bg-secondary text-white rounded-full flex items-center justify-center text-sm">1</span>
                        Conduct in General
                    </h3>
                    <p class="text-gray-600 leading-relaxed pl-10"><?php echo nl2br(htmlspecialchars($report['summary'])); ?></p>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="w-8 h-8 bg-secondary text-white rounded-full flex items-center justify-center text-sm">2</span>
                        Involvement in the Project
                    </h3>
                    <p class="text-gray-600 leading-relaxed pl-10"><?php echo nl2br(htmlspecialchars($report['challenges'])); ?></p>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="w-8 h-8 bg-secondary text-white rounded-full flex items-center justify-center text-sm">3</span>
                        Any Other Comments
                    </h3>
                    <p class="text-gray-600 leading-relaxed pl-10"><?php echo nl2br(htmlspecialchars($report['improvements'])); ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-16 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-lg font-medium mb-2">No report submitted yet</p>
                <p class="text-sm mb-4">Submit your overall progress report to view it here</p>
                <a href="overall_report.php" class="inline-block bg-secondary hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg transition-all">
                    Submit Overall Report
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
