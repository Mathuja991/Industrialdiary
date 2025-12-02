<?php
session_start();
require_once '../includes/auth_check.php';
checkAuth(['student']); // Only students can access this page

include '../config/db.php';
// include '../public/header.php';

$user_id = $_SESSION['user_id'];

// Fetch current start date and check if student record exists
$stmt = $conn->prepare("SELECT start_date FROM student WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student_data = $result->fetch_assoc();
$current_start_date = ($student_data && isset($student_data['start_date'])) ? $student_data['start_date'] : '';
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    
    if (!empty($start_date)) {
        // Check if student record exists
        $check_stmt = $conn->prepare("SELECT user_id FROM student WHERE user_id = ?");
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing record
            $update_stmt = $conn->prepare("UPDATE student SET start_date = ? WHERE user_id = ?");
            $update_stmt->bind_param("si", $start_date, $user_id);
            
            if ($update_stmt->execute()) {
                $success_msg = "Training schedule updated successfully!";
                $current_start_date = $start_date;
            } else {
                $error_msg = "Failed to update schedule: " . $update_stmt->error;
            }
            $update_stmt->close();
        } else {
            // Insert new record
            $insert_stmt = $conn->prepare("INSERT INTO student (user_id, start_date) VALUES (?, ?)");
            $insert_stmt->bind_param("is", $user_id, $start_date);
            
            if ($insert_stmt->execute()) {
                $success_msg = "Training schedule set successfully!";
                $current_start_date = $start_date;
            } else {
                $error_msg = "Failed to set schedule: " . $insert_stmt->error;
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } else {
        $error_msg = "Please select a valid date.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Week</title>
    <?php include 'includes/head_tailwind.php'; ?>
</head>
<body class="bg-light font-sans antialiased">

<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="ml-64 pt-24 p-8 transition-all duration-300">
    <div class="bg-white rounded-xl shadow-md p-8 max-w-2xl mx-auto animate-fade-in">
        <div class="flex items-center gap-4 mb-8 border-b pb-4">
            <div class="w-12 h-12 bg-secondary text-white rounded-full flex items-center justify-center">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-primary">Schedule Training Week</h2>
                <p class="text-gray-500">Set your industrial training start date</p>
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

        <?php if (!empty($current_start_date)): ?>
            <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <p class="font-semibold">Current Start Date: <?php echo date('F d, Y', strtotime($current_start_date)); ?></p>
                        <p class="text-sm mt-1">You can update this date if it was entered incorrectly.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6" id="scheduleForm">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <?php echo !empty($current_start_date) ? 'Update Training Start Date' : 'Training Start Date'; ?>
                </label>
                <div class="relative">
                    <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($current_start_date); ?>" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all bg-gray-50 pl-10">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    <?php if (!empty($current_start_date)): ?>
                        ⚠️ Changing this date will recalculate your entire training timeline.
                    <?php else: ?>
                        Your training timeline and weekly reports will be calculated based on this date.
                    <?php endif; ?>
                </p>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="student_dashboard.php" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all transform hover:-translate-y-0.5">Cancel</a>
                <button type="submit" id="submitBtn" class="bg-secondary hover:bg-blue-600 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                    <?php echo !empty($current_start_date) ? 'Update Schedule' : 'Save Schedule'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Add confirmation when updating existing date
document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    const currentDate = '<?php echo $current_start_date; ?>';
    const newDate = document.getElementById('start_date').value;
    
    if (currentDate && currentDate !== newDate) {
        if (!confirm('Are you sure you want to change your training start date? This will recalculate your entire training timeline.')) {
            e.preventDefault();
        }
    }
});
</script>

</body>
</html>
