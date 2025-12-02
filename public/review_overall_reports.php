<?php
session_start();
require_once '../includes/auth_check.php';
checkAuth(['mentor']);

include '../config/db.php';

// Get mentor_id and user_id from mentor table
$user_id = $_SESSION['user_id'];
$mentor_query = $conn->query("SELECT mentor_id, user_id FROM mentor WHERE user_id = '$user_id'");
$mentor_data = $mentor_query->fetch_assoc();

if (!$mentor_data) {
    die("Error: No mentor profile found.");
}
$mentor_id = $mentor_data['mentor_id'];
$mentor_user_id = $mentor_data['user_id']; // This is what we need for the FK

$feedback_marks = [
    'Outstanding performance' => 100,
    'Excellent work' => 80,
    'Consistent performance' => 70,
    'Good progress' => 60,
    'Needs improvement' => 50,
    'Unsatisfactory performance' => 30,
    'Lack of effort' => 20,
    'Failure to meet requirements' => 0
];

// Handle sign-off
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sign_off_report'])) {
    $report_id = $_POST['report_id'];
    $mentor_feedback = $_POST['mentor_feedback'];
    $overallpro_mark = $feedback_marks[$mentor_feedback] ?? null;

    $stmt = $conn->prepare("UPDATE overall_reports SET status = 'signed', mentor_feedback = ?, overallpro_mark = ?, mentor_id = ? WHERE report_id = ?");
    $stmt->bind_param("siid", $mentor_feedback, $overallpro_mark, $mentor_user_id, $report_id);

    if ($stmt->execute()) {
        $success_msg = "Report signed successfully!";
    } else {
        $error_msg = "Failed to sign report.";
    }
}

// Fetch pending reports
$reports = $conn->query("SELECT r.report_id, r.student_id, u.full_name, r.submission_date, r.summary, r.challenges, r.improvements FROM overall_reports r JOIN users u ON r.student_id = u.id JOIN student s ON r.student_id = s.user_id WHERE r.status = 'pending' AND s.mentor_id = '$mentor_id' ORDER BY r.submission_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Overall Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: {
                primary: '#2c3e50', secondary: '#3498db', accent: '#e67e22', dark: '#1a1a1a', light: '#f4f7f6'
            }}}
        }
    </script>
</head>
<body class="bg-light">

<?php include 'sidebar.php'; ?>

<div class="ml-64 pt-24 p-8">
    <div class="bg-white rounded-xl shadow-md p-8">
        <h2 class="text-2xl font-bold text-primary mb-6">Review Overall Reports</h2>
        
        <?php if (isset($success_msg)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if ($reports->num_rows > 0): ?>
            <div class="space-y-6">
                <?php while ($report = $reports->fetch_assoc()): ?>
                    <div class="border rounded-lg p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-bold"><?php echo htmlspecialchars($report['full_name']); ?></h3>
                            <p class="text-sm text-gray-500">Submitted: <?php echo $report['submission_date']; ?></p>
                        </div>
                        
                        <div class="space-y-4 mb-6">
                            <div class="bg-gray-50 p-4 rounded">
                                <h4 class="font-semibold mb-2">Conduct in General</h4>
                                <p><?php echo nl2br(htmlspecialchars($report['summary'])); ?></p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded">
                                <h4 class="font-semibold mb-2">Involvement in Project</h4>
                                <p><?php echo nl2br(htmlspecialchars($report['challenges'])); ?></p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded">
                                <h4 class="font-semibold mb-2">Other Comments</h4>
                                <p><?php echo nl2br(htmlspecialchars($report['improvements'])); ?></p>
                            </div>
                        </div>
                        
                        <form method="POST" class="flex gap-4 border-t pt-4">
                            <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                            <select name="mentor_feedback" required class="flex-1 px-4 py-2 border rounded-lg">
                                <option value="">Select Feedback</option>
                                <option value="Outstanding performance">Outstanding (100)</option>
                                <option value="Excellent work">Excellent (80)</option>
                                <option value="Consistent performance">Consistent (70)</option>
                                <option value="Good progress">Good (60)</option>
                                <option value="Needs improvement">Needs Improvement (50)</option>
                                <option value="Unsatisfactory performance">Unsatisfactory (30)</option>
                                <option value="Lack of effort">Lack of Effort (20)</option>
                                <option value="Failure to meet requirements">Failure (0)</option>
                            </select>
                            <button type="submit" name="sign_off_report" class="bg-secondary text-white px-6 py-2 rounded-lg hover:bg-blue-600">Sign Off</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-500 py-8">No pending reports for review</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
