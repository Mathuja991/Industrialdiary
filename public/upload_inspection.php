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

// Fetch lecturer's name
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

// Fetch the list of registered students
$students = [];
$stmt = $conn->prepare("SELECT u.id, u.full_name, u.username, s.reg_no FROM users u LEFT JOIN student s ON u.id = s.user_id WHERE u.role = 'student' ORDER BY s.reg_no, u.username");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();

// Handle inspection report submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inspection_date = filter_var($_POST['inspection_date'], FILTER_SANITIZE_STRING);
    $inspector_name = filter_var($_POST['inspector_name'], FILTER_SANITIZE_STRING);
    $student_id = filter_var($_POST['student_id'], FILTER_SANITIZE_NUMBER_INT);
    $remarks_supervisor = filter_var($_POST['Remarks_Supervisor'], FILTER_SANITIZE_STRING);
    $remarks_student = filter_var($_POST['Remarks_Student'], FILTER_SANITIZE_STRING);

    // Check if an inspection report already exists for this student
    $check_stmt = $conn->prepare("SELECT id FROM inspection_reports WHERE student_id = ?");
    $check_stmt->bind_param("i", $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $error_msg = "An inspection report has already been submitted for this student.";
    } else {
        $stmt = $conn->prepare("INSERT INTO inspection_reports (inspection_date, inspector_name, student_id, supervisor_remarks, student_remarks) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $inspection_date, $inspector_name, $student_id, $remarks_supervisor, $remarks_student);

        if ($stmt->execute()) {
            $success_msg = "Inspection report submitted successfully.";
        } else {
            $error_msg = "Failed to submit the report. Please try again.";
        }
        $stmt->close();
    }
    $check_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Inspection Report</title>
    <?php include 'includes/head_tailwind.php'; ?>
</head>
<body class="bg-light font-sans antialiased">

<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="ml-64 mt-16 p-8 transition-all duration-300">
    <div class="bg-white rounded-xl shadow-md p-8 max-w-4xl mx-auto animate-fade-in">
        <div class="flex items-center gap-4 mb-8 border-b pb-4">
            <div class="w-12 h-12 bg-secondary text-white rounded-full flex items-center justify-center">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-primary">Upload Inspection Report</h2>
                <p class="text-gray-500">Submit inspection report for a student</p>
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
            <div>
                <label for="lecturer_name" class="block text-sm font-medium text-gray-700 mb-2">Inspector Name</label>
                <input type="text" id="lecturer_name" name="inspector_name" value="<?php echo htmlspecialchars($lecturer_name); ?>" readonly required class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 outline-none">
            </div>

            <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Select Student</label>
                <select id="student_id" name="student_id" onchange="fillStudentName()" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none">
                    <option value="" disabled selected>Select a student</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>" data-name="<?php echo htmlspecialchars($student['full_name']); ?>">
                            <?php echo htmlspecialchars($student['reg_no'] ?? $student['username']) . ' - ' . htmlspecialchars($student['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="student_name" class="block text-sm font-medium text-gray-700 mb-2">Student Name</label>
                <input type="text" id="student_name" name="student_name" readonly required class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 outline-none">
            </div>

            <div>
                <label for="inspection_date" class="block text-sm font-medium text-gray-700 mb-2">Inspection Date</label>
                <input type="date" id="inspection_date" name="inspection_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none">
            </div>

            <div>
                <label for="Remarks_Supervisor" class="block text-sm font-medium text-gray-700 mb-2">Remarks By Supervisor</label>
                <textarea id="Remarks_Supervisor" name="Remarks_Supervisor" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none resize-none"></textarea>
            </div>

            <div>
                <label for="Remarks_Student" class="block text-sm font-medium text-gray-700 mb-2">Remarks By Student</label>
                <textarea id="Remarks_Student" name="Remarks_Student" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="staff_dashboard.php" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all">Cancel</a>
                <button type="submit" class="bg-secondary hover:bg-blue-600 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md hover:shadow-lg transition-all">
                    Submit Report
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function fillStudentName() {
    const studentSelect = document.getElementById("student_id");
    const selectedStudent = studentSelect.options[studentSelect.selectedIndex];
    const studentNameField = document.getElementById("student_name");
    studentNameField.value = selectedStudent.dataset.name;
}
</script>

</body>
</html>
