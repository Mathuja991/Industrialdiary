<?php
session_start();
require_once '../includes/auth_check.php';
checkAuth(['mentor']);

include '../config/db.php';

// Get mentor_id from mentor table using user_id
$user_id = $_SESSION['user_id'];
$mentor_query = $conn->query("SELECT mentor_id FROM mentor WHERE user_id = '$user_id'");
$mentor_data = $mentor_query->fetch_assoc();

if (!$mentor_data) {
    die("Error: No mentor profile found.");
}
$mentor_id = $mentor_data['mentor_id'];

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

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    $student_id = $_POST['student_id'];
    $week_number = $_POST['week_number'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    $feedback = $_POST['feedback'];
    $mentor_mark = $feedback_marks[$feedback] ?? null;

    $stmt = $conn->prepare("UPDATE diaries SET reviewed = 1, feedback = ?, mentor_mark = ? WHERE student_id = ? AND week_number = ? AND month = ? AND year = ?");
    $stmt->bind_param("siisii", $feedback, $mentor_mark, $student_id, $week_number, $month, $year);

    if ($stmt->execute()) {
        $success_msg = "Feedback submitted successfully!";
    } else {
        $error_msg = "Failed to submit feedback.";
    }
}

// Fetch pending diaries
$diaries = $conn->query("SELECT d.*, u.full_name FROM diaries d JOIN student s ON d.student_id = s.user_id JOIN users u ON d.student_id = u.id WHERE d.reviewed = 0 AND s.mentor_id = '$mentor_id' ORDER BY d.upload_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Diaries</title>
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
        <h2 class="text-2xl font-bold text-primary mb-6">Review Student Diaries</h2>
        
        <?php if (isset($success_msg)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if ($diaries->num_rows > 0): ?>
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-4">Student</th>
                        <th class="p-4">Week</th>
                        <th class="p-4">Month/Year</th>
                        <th class="p-4">Report</th>
                        <th class="p-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $diaries->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="p-4"><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td class="p-4">Week <?php echo $row['week_number']; ?></td>
                            <td class="p-4"><?php echo date("F", mktime(0, 0, 0, $row['month'], 1)) . ' ' . $row['year']; ?></td>
                            <td class="p-4 max-w-md"><?php echo nl2br(htmlspecialchars($row['report'])); ?></td>
                            <td class="p-4">
                                <form method="POST" class="space-y-2">
                                    <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                                    <input type="hidden" name="week_number" value="<?php echo $row['week_number']; ?>">
                                    <input type="hidden" name="month" value="<?php echo $row['month']; ?>">
                                    <input type="hidden" name="year" value="<?php echo $row['year']; ?>">
                                    <select name="feedback" required class="w-full px-3 py-2 border rounded-lg">
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
                                    <button type="submit" name="submit_feedback" class="w-full bg-secondary text-white py-2 rounded-lg hover:bg-blue-600">Submit</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-gray-500 py-8">No pending diaries for review</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
