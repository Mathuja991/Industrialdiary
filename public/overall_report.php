<?php
session_start();
require_once '../includes/auth_check.php';
checkAuth(['student']); // Only students can access this page

include '../config/db.php';
// include '../public/header.php';

// Handle Overall Process Report Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['overall_report'])) {
    $student_id = $_SESSION['user_id'];
    $summary = $_POST['summary'];
    $challenges = $_POST['challenges'];
    $improvements = $_POST['improvements'];

    // Check if overall report already exists
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM overall_reports WHERE student_id = ?");
    $checkStmt->bind_param("i", $student_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        $error_msg = "Overall Process Report has already been submitted.";
    } else {
        $stmt = $conn->prepare("INSERT INTO overall_reports (student_id, summary, challenges, improvements) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $student_id, $summary, $challenges, $improvements);

        if ($stmt->execute()) {
            $success_msg = "Overall Process Report submitted successfully.";
        } else {
            $error_msg = "Failed to submit the report. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overall Report</title>
    <?php include 'includes/head_tailwind.php'; ?>
</head>
<body class="bg-light font-sans antialiased">

<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="ml-64 pt-24 p-8 transition-all duration-300">
    <div class="bg-white rounded-xl shadow-md p-8 max-w-4xl mx-auto animate-fade-in">
        <div class="flex items-center gap-4 mb-8 border-b pb-4">
            <div class="w-12 h-12 bg-secondary text-white rounded-full flex items-center justify-center">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-primary">Overall Progress Report</h2>
                <p class="text-gray-500">Submit your final training summary</p>
            </div>
        </div>

        <?php if (isset($success_msg)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p><?php echo $success_msg; ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($error_msg)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?php echo $error_msg; ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <input type="hidden" name="overall_report" value="1">
            
            <div>
                <label for="summary" class="block text-sm font-medium text-gray-700 mb-2">Conduct in General</label>
                <textarea name="summary" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all bg-gray-50 resize-none"></textarea>
            </div>
            
            <div>
                <label for="challenges" class="block text-sm font-medium text-gray-700 mb-2">Involvement in the Project</label>
                <textarea name="challenges" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all bg-gray-50 resize-none"></textarea>
            </div>
            
            <div>
                <label for="improvements" class="block text-sm font-medium text-gray-700 mb-2">Any Other Comments</label>
                <textarea name="improvements" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all bg-gray-50 resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="student_dashboard.php" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all transform hover:-translate-y-0.5">Cancel</a>
                <button type="submit" class="bg-secondary hover:bg-blue-600 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">Submit Report</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
