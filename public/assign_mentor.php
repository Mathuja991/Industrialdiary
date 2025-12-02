<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../config/db.php';

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle form submission BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_mentor'])) {
    $studentId = $_POST['student_id'];
    $mentorId = $_POST['mentor_id'];

    // Check if student record exists
    $checkStmt = $conn->prepare("SELECT user_id FROM student WHERE user_id = ?");
    $checkStmt->bind_param("i", $studentId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE student SET mentor_id = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $mentorId, $studentId);
    } else {
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO student (user_id, mentor_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $studentId, $mentorId);
    }

    if ($stmt->execute()) {
        // Redirect to prevent form resubmission
        header("Location: assign_mentor.php?success=1");
        exit;
    } else {
        $error_msg = "Error assigning mentor: " . $stmt->error;
    }

    $checkStmt->close();
    $stmt->close();
}

// Check for success message from redirect
if (isset($_GET['success'])) {
    $success_msg = "Mentor assigned successfully!";
}

// NOW include header and sidebar
// include '../public/header.php'; // Removed old header

// Fetch all students
$students_query = "SELECT u.id, u.full_name, u.username, s.reg_no, s.mentor_id, 
                          m_user.full_name AS mentor_name
                   FROM users u
                   LEFT JOIN student s ON u.id = s.user_id
                   LEFT JOIN mentor m ON s.mentor_id = m.mentor_id
                   LEFT JOIN users m_user ON m.user_id = m_user.id
                   WHERE u.role = 'student'
                   ORDER BY s.reg_no, u.username";
$students = $conn->query($students_query);

// Fetch all mentors
$mentors_query = "SELECT m.mentor_id, u.id AS user_id, u.full_name, m.working_organization,
                         COUNT(s.user_id) AS student_count
                  FROM users u
                  INNER JOIN mentor m ON u.id = m.user_id
                  LEFT JOIN student s ON m.mentor_id = s.mentor_id
                  WHERE u.role = 'mentor'
                  GROUP BY m.mentor_id, u.id, u.full_name, m.working_organization
                  ORDER BY u.full_name";
$mentors_result = $conn->query($mentors_query);
$mentors = [];
while ($row = $mentors_result->fetch_assoc()) {
    $mentors[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Mentor to Student</title>
    <?php include 'includes/head_tailwind.php'; ?>
</head>
<body class="bg-light font-sans antialiased">

<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="ml-64 mt-16 p-8 transition-all duration-300">
    <div class="max-w-6xl mx-auto animate-fade-in">
        <h1 class="text-3xl font-bold text-primary mb-8">Assign Mentor to Student</h1>

        <!-- Assignment Form -->
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-lg p-8 mb-8 border border-gray-100">
            <div class="flex items-center gap-4 mb-6 pb-4 border-b-2 border-secondary/20">
                <div class="w-14 h-14 bg-gradient-to-br from-secondary to-blue-600 text-white rounded-xl flex items-center justify-center shadow-md">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <polyline points="17 11 19 13 23 9"></polyline>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-primary">Assign Mentor</h2>
                    <p class="text-gray-600 text-sm">Select a student and assign them to a mentor</p>
                </div>
            </div>

            <?php if (isset($success_msg)): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg flex items-start gap-3" role="alert">
                    <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="font-medium"><?php echo $success_msg; ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($error_msg)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg flex items-start gap-3" role="alert">
                    <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="font-medium"><?php echo $error_msg; ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="student_id" class="block text-sm font-semibold text-gray-700">
                            <span class="flex items-center gap-2">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                Select Student
                            </span>
                        </label>
                        <select name="student_id" id="student_id" required 
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all bg-white hover:border-gray-300">
                            <option value="">-- Choose a student --</option>
                            <?php 
                            $students->data_seek(0);
                            while ($student = $students->fetch_assoc()): 
                            ?>
                                <option value="<?php echo htmlspecialchars($student['id']); ?>">
                                    <?php echo htmlspecialchars($student['reg_no'] ?? $student['username']); ?> - <?php echo htmlspecialchars($student['full_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="mentor_id" class="block text-sm font-semibold text-gray-700">
                            <span class="flex items-center gap-2">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                Select Mentor
                            </span>
                        </label>
                        <select name="mentor_id" id="mentor_id" required 
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all bg-white hover:border-gray-300">
                            <option value="">-- Choose a mentor --</option>
                            <?php foreach ($mentors as $mentor): ?>
                                <option value="<?php echo htmlspecialchars($mentor['mentor_id']); ?>">
                                    <?php echo htmlspecialchars($mentor['full_name']); ?> - <?php echo htmlspecialchars($mentor['working_organization']); ?> (<?php echo $mentor['student_count']; ?> students)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t-2 border-gray-100">
                    <button type="submit" name="assign_mentor" 
                            class="bg-gradient-to-r from-secondary to-blue-600 hover:from-blue-600 hover:to-secondary text-white font-semibold py-3 px-8 rounded-lg shadow-md hover:shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Assign Mentor
                    </button>
                </div>
            </form>
        </div>

        <!-- Current Assignments Table -->
        <div class="bg-white rounded-xl shadow-md p-8">
            <h2 class="text-xl font-bold text-primary mb-6">Current Student-Mentor Assignments</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 uppercase text-xs tracking-wider">
                            <th class="p-4 font-semibold rounded-tl-lg">Registration No</th>
                            <th class="p-4 font-semibold">Student Name</th>
                            <th class="p-4 font-semibold">Assigned Mentor</th>
                            <th class="p-4 font-semibold rounded-tr-lg">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-600 divide-y divide-gray-100">
                        <?php 
                        $students->data_seek(0); // Reset pointer
                        while ($student = $students->fetch_assoc()): 
                        ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 font-medium text-gray-800"><?php echo htmlspecialchars($student['reg_no'] ?? $student['username']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td class="p-4">
                                    <?php if ($student['mentor_name']): ?>
                                        <span class="text-gray-800"><?php echo htmlspecialchars($student['mentor_name']); ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400 italic">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4">
                                    <?php if ($student['mentor_id']): ?>
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">
                                            Assigned
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs font-semibold">
                                            Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../public/footer.php'; ?>

</body>
</html>
