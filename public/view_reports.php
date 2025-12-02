<?php
session_start();
require_once '../includes/auth_check.php';
checkAuth(['student']); // Only students can access this page

include '../config/db.php';
// include '../public/header.php';

// Fetch progress reports for the student
$student_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, week_number, report, month, year, upload_date, reviewed,
                               IFNULL(feedback, 'Pending Review') AS feedback 
                        FROM diaries
                        WHERE student_id = ?
                        ORDER BY year DESC, month DESC, week_number DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$progress_reports = [];
while ($row = $result->fetch_assoc()) {
    $progress_reports[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports</title>
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
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-primary">Diary Reports</h2>
                    <p class="text-gray-500">View all your submitted weekly reports</p>
                </div>
            </div>
            <?php if (!empty($progress_reports)): ?>
                <a href="generate_diary_pdf.php" target="_blank">
                    <button type="button" class="bg-green-500 hover:bg-green-600 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow hover:shadow-md transition-all">Download PDF</button>
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($progress_reports)): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 uppercase text-xs tracking-wider">
                            <th class="p-4 font-semibold rounded-tl-lg">Year</th>
                            <th class="p-4 font-semibold">Month</th>
                            <th class="p-4 font-semibold">Week</th>
                            <th class="p-4 font-semibold">Upload Date</th>
                            <th class="p-4 font-semibold">Report</th>
                            <th class="p-4 font-semibold">Status</th>
                            <th class="p-4 font-semibold">Mentor Feedback</th>
                            <th class="p-4 font-semibold rounded-tr-lg">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-600 divide-y divide-gray-100">
                        <?php foreach ($progress_reports as $report): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4"><?php echo htmlspecialchars($report['year']); ?></td>
                                <td class="p-4"><?php echo date("F", mktime(0, 0, 0, $report['month'], 1)); ?></td>
                                <td class="p-4 font-medium text-gray-800">Week <?php echo htmlspecialchars($report['week_number']); ?></td>
                                <td class="p-4 text-xs text-gray-500"><?php echo date('M d, Y', strtotime($report['upload_date'])); ?></td>
                                <td class="p-4 max-w-md">
                                    <div class="line-clamp-2" title="<?php echo htmlspecialchars($report['report']); ?>">
                                        <?php echo htmlspecialchars($report['report']); ?>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <?php if ($report['reviewed'] == 0): ?>
                                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs font-semibold">
                                            Not Reviewed
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">
                                            Reviewed
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4">
                                    <span class="<?php echo $report['feedback'] == 'Pending Review' ? 'bg-gray-100 text-gray-600' : 'bg-blue-100 text-blue-700'; ?> px-2 py-1 rounded-full text-xs font-semibold">
                                        <?php echo htmlspecialchars($report['feedback']); ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <?php if ($report['reviewed'] == 0): ?>
                                        <div class="flex gap-2">
                                            <a href="upload_diary.php?edit=1&id=<?php echo $report['id']; ?>" 
                                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-semibold transition-all"
                                               title="Edit">
                                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <a href="upload_diary.php?delete=1&id=<?php echo $report['id']; ?>" 
                                               class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold transition-all"
                                               onclick="return confirm('Are you sure you want to delete this diary entry? This action cannot be undone.');"
                                               title="Delete">
                                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs italic">Locked</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-16 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-lg font-medium mb-2">No diary reports available</p>
                <p class="text-sm mb-4">Start by submitting your first weekly report</p>
                <a href="upload_diary.php" class="inline-block bg-secondary hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg transition-all">
                    Upload Diary
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
