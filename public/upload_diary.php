<?php
session_start();
require_once '../includes/auth_check.php';
checkAuth(['student']); // Only students can access this page

include '../config/db.php';
// include '../public/header.php';

$user_id = $_SESSION['user_id'];

// Handle diary deletion (only if not reviewed)
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $diary_id = $_GET['id'];
    
    // Check if diary is not reviewed
    $check_stmt = $conn->prepare("SELECT reviewed FROM diaries WHERE id = ? AND student_id = ?");
    $check_stmt->bind_param("ii", $diary_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $diary = $check_result->fetch_assoc();
    
    if ($diary && $diary['reviewed'] == 0) {
        $delete_stmt = $conn->prepare("DELETE FROM diaries WHERE id = ? AND student_id = ?");
        $delete_stmt->bind_param("ii", $diary_id, $user_id);
        if ($delete_stmt->execute()) {
            $success_msg = "Diary entry deleted successfully.";
        } else {
            $error_msg = "Failed to delete diary entry.";
        }
    } else {
        $error_msg = "Cannot delete a reviewed diary entry.";
    }
}

// Handle diary edit
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit']) && isset($_GET['id'])) {
    $diary_id = $_GET['id'];
    
    $edit_stmt = $conn->prepare("SELECT * FROM diaries WHERE id = ? AND student_id = ? AND reviewed = 0");
    $edit_stmt->bind_param("ii", $diary_id, $user_id);
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();
    
    if ($edit_result->num_rows > 0) {
        $edit_data = $edit_result->fetch_assoc();
        $edit_mode = true;
    } else {
        $error_msg = "Cannot edit a reviewed diary entry.";
    }
}

// Handle diary submission or update
if (isset($_POST['submit_diary'])) {
    $week_number = $_POST['week_number'];
    $report = $_POST['report'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    $diary_id = isset($_POST['diary_id']) ? $_POST['diary_id'] : null;

    if ($diary_id) {
        // Update existing diary (only if not reviewed)
        $check_stmt = $conn->prepare("SELECT reviewed FROM diaries WHERE id = ? AND student_id = ?");
        $check_stmt->bind_param("ii", $diary_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $diary = $check_result->fetch_assoc();
        
        if ($diary && $diary['reviewed'] == 0) {
            $update_stmt = $conn->prepare("UPDATE diaries SET week_number = ?, report = ?, month = ?, year = ? WHERE id = ? AND student_id = ?");
            $update_stmt->bind_param("isiiii", $week_number, $report, $month, $year, $diary_id, $user_id);
            
            if ($update_stmt->execute()) {
                $success_msg = "Diary entry updated successfully!";
                $edit_mode = false;
                $edit_data = null;
            } else {
                $error_msg = "Failed to update diary entry.";
            }
        } else {
            $error_msg = "Cannot update a reviewed diary entry.";
        }
    } else {
        // Check if an entry for the selected week already exists
        $checkQuery = "SELECT * FROM diaries WHERE student_id = ? AND week_number = ? AND month = ? AND year = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("iiii", $user_id, $week_number, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_msg = "You have already submitted a report for Week $week_number in " . date("F", mktime(0, 0, 0, $month, 1)) . " $year.";
        } else {
            // Insert new diary entry
            $upload_date = date('Y-m-d');
            $insertQuery = "INSERT INTO diaries (student_id, upload_date, report, week_number, month, year) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("issiii", $user_id, $upload_date, $report, $week_number, $month, $year);

            if ($stmt->execute()) {
                $success_msg = "Report submitted successfully for Week $week_number in " . date("F", mktime(0, 0, 0, $month, 1)) . " $year!";
            } else {
                $error_msg = "Failed to submit the report. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Edit Diary' : 'Upload Diary'; ?></title>
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
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-primary"><?php echo $edit_mode ? 'Edit Weekly Diary' : 'Weekly Diary Submission'; ?></h2>
                <p class="text-gray-500"><?php echo $edit_mode ? 'Update your weekly training report' : 'Submit your weekly training report'; ?></p>
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

        <?php if ($edit_mode): ?>
            <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
                <p class="font-semibold">Editing Mode</p>
                <p class="text-sm">You are editing your diary for Week <?php echo $edit_data['week_number']; ?>, <?php echo date("F", mktime(0, 0, 0, $edit_data['month'], 1)); ?> <?php echo $edit_data['year']; ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="week_number" class="block text-sm font-medium text-gray-700 mb-2">Select Week</label>
                    <select name="week_number" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all bg-gray-50">
                        <?php for ($i = 1; $i <= 24; $i++): 
                            $selected = ($edit_mode && $edit_data['week_number'] == $i) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $i; ?>" <?php echo $selected; ?>>Week <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Select Month</label>
                    <select name="month" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all bg-gray-50">
                        <?php 
                        $months = [
                            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                        ];
                        $current_month = date('n');
                        foreach ($months as $num => $name):
                            $selected = ($edit_mode && $edit_data['month'] == $num) ? 'selected' : ($num == $current_month ? 'selected' : '');
                        ?>
                            <option value="<?php echo $num; ?>" <?php echo $selected; ?>><?php echo $name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Select Year</label>
                    <select name="year" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all bg-gray-50">
                        <?php 
                        $current_year = date('Y');
                        for ($i = $current_year; $i >= $current_year - 1; $i--):
                            $selected = ($edit_mode && $edit_data['year'] == $i) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div>
                <label for="report" class="block text-sm font-medium text-gray-700 mb-2">Report</label>
                <textarea name="report" rows="8" placeholder="Describe the work done this week..." required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all bg-gray-50 resize-none"><?php echo $edit_mode ? htmlspecialchars($edit_data['report']) : ''; ?></textarea>
            </div>
            
            <div class="flex justify-end gap-4 pt-4 border-t">
                <?php if ($edit_mode): ?>
                    <a href="upload_diary.php" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all">Cancel Edit</a>
                <?php else: ?>
                    <a href="student_dashboard.php" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all">Cancel</a>
                <?php endif; ?>
                <button type="submit" name="submit_diary" class="bg-secondary hover:bg-blue-600 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md hover:shadow-lg transition-all">
                    <?php echo $edit_mode ? 'Update Report' : 'Submit Report'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
