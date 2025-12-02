<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

// Database connection
include '../config/db.php';
// include '../public/header.php'; // Removed old header

$students = []; // Initialize student list
$selected_student_results = null;
$student_id = null; // Initialize student ID
$success_message = ''; // To store success message for marks submission
$error_message = ''; // To store error messages

// Fetch all students from the 'users' table where role is 'student'
$query = "SELECT id, username, full_name FROM users WHERE role = 'student'";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
} else {
    $error_message = "Error retrieving students: " . $conn->error; // Error handling
}

// Check if a student username was submitted
if (isset($_POST['selected_student'])) {
    $username = $_POST['selected_student'];

    // Retrieve student ID from the users table based on the selected username
    $student_query = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($student_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $student_result = $stmt->get_result();
    
    if ($student_result->num_rows > 0) {
        $student_data = $student_result->fetch_assoc();
        $student_id = $student_data['id'];

        // Retrieve the inspection report from inspection_reports table based on student ID
        $report_query = "SELECT inspection_date, inspector_name, supervisor_remarks, student_remarks FROM inspection_reports WHERE student_id = ?";
        $report_stmt = $conn->prepare($report_query);
        $report_stmt->bind_param("i", $student_id);
        $report_stmt->execute();
        $selected_student_results = $report_stmt->get_result()->fetch_assoc();
    }
}

// Check if inspection marks were submitted
if (isset($_POST['submit_results'])) {
    // Retrieve student ID from POST data
    $student_id = $_POST['student_id'];
    $inspection_marks = $_POST['inspection_marks'];

    // Check if marks already exist for this student
    $check_query = "SELECT id FROM inspection_results WHERE student_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $error_message = "Error: Marks have already been assigned for this student.";
    } else {
        // Insert the inspection marks into the inspection_results table
        $insert_query = "INSERT INTO inspection_results (student_id, inspection_marks) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("ii", $student_id, $inspection_marks);
        
        if ($insert_stmt->execute()) {
            $success_message = "Results submitted successfully.";
        } else {
            $error_message = "Error submitting results: " . $conn->error; // Error handling
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspection Reports Results</title>
    <?php include 'includes/head_tailwind.php'; ?>
</head>
<body class="bg-light font-sans antialiased">

<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="ml-64 mt-16 p-8 transition-all duration-300">
    <div class="max-w-4xl mx-auto animate-fade-in">
        
        <div class="bg-white rounded-xl shadow-md p-8 mb-8">
            <div class="flex items-center gap-4 mb-8 border-b pb-4">
                <div class="w-12 h-12 bg-secondary text-white rounded-full flex items-center justify-center">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-primary">Assign Marks</h2>
                    <p class="text-gray-500">Assign marks to inspection reports</p>
                </div>
            </div>

            <!-- Display Messages -->
            <?php if ($success_message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>

            <!-- Student Selection Form -->
            <form method="POST" id="studentForm" class="mb-8">
                <div class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Select Student</label>
                        <select id="student_id" name="selected_student" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none">
                            <option value="" disabled selected>Select a student</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo htmlspecialchars($student['username']); ?>" <?php echo isset($_POST['selected_student']) && $_POST['selected_student'] == $student['username'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['username']) . " - " . htmlspecialchars($student['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" id="showReportButton" class="bg-secondary hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition-all h-[42px]">
                        Show Report
                    </button>
                </div>
            </form>

            <!-- Display Inspection Report if Available -->
            <?php if ($selected_student_results): ?>
                <div id="inspectionReport" class="border-t pt-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Inspection Report Details</h3>
                    <div class="bg-gray-50 rounded-lg p-6 mb-6 border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <span class="text-sm text-gray-500 uppercase block">Inspection Date</span>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($selected_student_results['inspection_date']); ?></span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 uppercase block">Inspector Name</span>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($selected_student_results['inspector_name']); ?></span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <span class="text-sm text-gray-500 uppercase block">Supervisor Remarks</span>
                            <p class="text-gray-700 mt-1"><?php echo htmlspecialchars($selected_student_results['supervisor_remarks']); ?></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500 uppercase block">Student Remarks</span>
                            <p class="text-gray-700 mt-1"><?php echo htmlspecialchars($selected_student_results['student_remarks']); ?></p>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-800 mb-4">Enter Results</h3>
                    <form method="POST" class="bg-blue-50 p-6 rounded-lg border border-blue-100">
                        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>">
                        <div class="mb-4">
                            <label for="inspection_marks" class="block text-sm font-medium text-gray-700 mb-2">Inspection Report Marks</label>
                            <input type="number" name="inspection_marks" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" name="submit_results" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition-all">
                                Submit Results
                            </button>
                        </div>
                    </form>
                </div>
            <?php elseif (isset($_POST['selected_student'])): ?>
                <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <p>No inspection report available for the selected student.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div>
            <button type="button" onclick="window.history.back();" class="text-gray-500 hover:text-gray-700 font-medium flex items-center gap-2 transition-colors">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back
            </button>
        </div>
    </div>
</div>

</body>
</html>
