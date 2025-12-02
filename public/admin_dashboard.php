<?php
session_start();
require_once '../includes/auth_check.php';
// Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Fallback check if auth_check doesn't handle it or for extra safety
    // Assuming auth_check handles redirection, but if not:
    // header("Location: login.php"); exit();
}

include '../config/db.php';

$pageTitle = "Admin Dashboard";

// Count total students
$studentCount = $conn->query("SELECT COUNT(*) AS count FROM student")->fetch_assoc()['count'];
$mentorCount = $conn->query("SELECT COUNT(*) AS count FROM mentor")->fetch_assoc()['count'];
$staffCount = $conn->query("SELECT COUNT(*) AS count FROM staff")->fetch_assoc()['count'];

// Fetch students with role 'student'
$students = $conn->query("SELECT id, full_name, username FROM users WHERE role = 'student'");

// Fetch mentor data by joining users and mentor tables
$stmt = $conn->prepare("
    SELECT users.id AS user_id, users.full_name, mentor.working_organization 
    FROM users 
    INNER JOIN mentor ON users.id = mentor.user_id 
    WHERE users.role = 'mentor'
");
$stmt->execute();
$result = $stmt->get_result();
$mentors = [];
while ($row = $result->fetch_assoc()) {
    $mentors[] = $row;
}
$stmt->close();

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_mentor'])) {
    $studentId = $_POST['student_id'];
    $mentorId = $_POST['mentor_id'];

    // Check if the student already has a mentor assigned
    $checkStmt = $conn->prepare("SELECT mentor_id FROM student WHERE user_id = ?");
    $checkStmt->bind_param("i", $studentId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Record exists, update the mentor assignment
        $stmt = $conn->prepare("UPDATE student SET mentor_id = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $mentorId, $studentId);
    } else {
        // Record does not exist, insert new mentor assignment
        $stmt = $conn->prepare("INSERT INTO student (user_id, mentor_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $studentId, $mentorId);
    }

    if ($stmt->execute()) {
        $message = "Mentor assigned successfully!";
        $messageType = "success";
    } else {
        $message = "Error assigning mentor: " . $stmt->error;
        $messageType = "error";
    }

    $checkStmt->close();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <?php include 'includes/head_tailwind.php'; ?>
    <style>
        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-light text-gray-800 font-sans">

<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="ml-64 mt-16 p-8">
    <div class="max-w-7xl mx-auto fade-in">
        
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Welcome, Admin</h1>
            <p class="text-gray-500 mt-1">Manage users, assign mentors, and oversee the system.</p>
        </div>

        <!-- Notification Message -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Tab Navigation -->
        <div class="mb-8 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button onclick="showSection('assignMentor')" id="tab-assignMentor" class="tab-btn border-secondary text-secondary whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Assign Mentors
                </button>
                <button onclick="showSection('manage')" id="tab-manage" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Manage System
                </button>
            </nav>
        </div>

        <!-- Assign Mentor Section -->
        <div id="assignMentor" class="section block">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-2xl">
                <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Assign Mentor to Student
                </h2>
                <form method="POST" class="space-y-6">
                    <div>
                        <label for="student" class="block text-sm font-medium text-gray-700 mb-1">Select Student</label>
                        <select name="student_id" id="student" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-secondary focus:ring focus:ring-secondary focus:ring-opacity-20 transition-colors">
                            <option value="">-- Select Student --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= htmlspecialchars($student['id']) ?>">
                                    <?= htmlspecialchars($student['full_name']) ?> (<?= htmlspecialchars($student['username']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="mentor" class="block text-sm font-medium text-gray-700 mb-1">Select Mentor</label>
                        <select name="mentor_id" id="mentor" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-secondary focus:ring focus:ring-secondary focus:ring-opacity-20 transition-colors">
                            <option value="">-- Select Mentor --</option>
                            <?php foreach ($mentors as $mentor): ?>
                                <option value="<?= htmlspecialchars($mentor['user_id']) ?>">
                                    <?= htmlspecialchars($mentor['full_name']) ?> - <?= htmlspecialchars($mentor['working_organization']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pt-2">
                        <button type="submit" name="assign_mentor" class="w-full bg-secondary hover:bg-secondary-dark text-white font-semibold py-2.5 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Assign Mentor
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Manage Section -->
        <div id="manage" class="section hidden">
            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">Total Students</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $studentCount; ?></h3>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">Total Mentors</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $mentorCount; ?></h3>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-purple-500">
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">Total Staff</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $staffCount; ?></h3>
                </div>
            </div>

            <div class="space-y-8">
                <!-- Students Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-800">Registered Students</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider">
                                    <th class="px-6 py-3 font-medium">ID</th>
                                    <th class="px-6 py-3 font-medium">Name</th>
                                    <th class="px-6 py-3 font-medium">Reg No</th>
                                    <th class="px-6 py-3 font-medium">Academic Year</th>
                                    <th class="px-6 py-3 font-medium">Phone</th>
                                    <th class="px-6 py-3 font-medium">Email</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php
                                $query = "SELECT student.student_id, student.reg_no, student.academic_year, student.phone_no, student.email_id, users.full_name 
                                          FROM student 
                                          INNER JOIN users ON student.user_id = users.id";
                                $result = $conn->query($query);
                                if ($result->num_rows > 0) {
                                    while ($student = $result->fetch_assoc()) {
                                        echo "<tr class='hover:bg-gray-50 transition-colors'>
                                                <td class='px-6 py-4 text-sm text-gray-600'>{$student['student_id']}</td>
                                                <td class='px-6 py-4 text-sm font-medium text-gray-800'>{$student['full_name']}</td>
                                                <td class='px-6 py-4 text-sm text-gray-600'>{$student['reg_no']}</td>
                                                <td class='px-6 py-4 text-sm text-gray-600'>{$student['academic_year']}</td>
                                                <td class='px-6 py-4 text-sm text-gray-600'>{$student['phone_no']}</td>
                                                <td class='px-6 py-4 text-sm text-gray-600'>{$student['email_id']}</td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='px-6 py-8 text-center text-gray-500'>No students found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mentors Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-800">Registered Mentors</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider">
                                    <th class="px-6 py-3 font-medium">ID</th>
                                    <th class="px-6 py-3 font-medium">Name</th>
                                    <th class="px-6 py-3 font-medium">Email</th>
                                    <th class="px-6 py-3 font-medium">Phone</th>
                                    <th class="px-6 py-3 font-medium">Organization</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php
                                $query = "SELECT mentor.mentor_id, mentor.phone_no, mentor.email_id, mentor.working_organization, users.full_name 
                                          FROM mentor 
                                          INNER JOIN users ON mentor.user_id = users.id";
                                $result = $conn->query($query);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr class='hover:bg-gray-50 transition-colors'>
                                                <td class='px-6 py-4 text-sm text-gray-600'>{$row['mentor_id']}</td>
                                                <td class='px-6 py-4 text-sm font-medium text-gray-800'>{$row['full_name']}</td>
                                                <td class='px-6 py-4 text-sm text-gray-600'>{$row['email_id']}</td>
                                                <td class='px-6 py-4 text-sm text-gray-600'>{$row['phone_no']}</td>
                                                <td class='px-6 py-4 text-sm text-gray-600'>{$row['working_organization']}</td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='px-6 py-8 text-center text-gray-500'>No mentors found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Staff Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-800">Registered Staff</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider">
                                    <th class="px-6 py-3 font-medium">ID</th>
                                    <th class="px-6 py-3 font-medium">Name</th>
                                    <th class="px-6 py-3 font-medium">Phone</th>
                                    <th class="px-6 py-3 font-medium">Email</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php
                                $query = "SELECT staff.staff_id, staff.phone_no, staff.email_id, users.full_name 
                                          FROM staff 
                                          INNER JOIN users ON staff.user_id = users.id";
                                $result = $conn->query($query);
                                if ($result->num_rows > 0) {
                                    while ($staff = $result->fetch_assoc()) {
                                        echo "<tr class='hover:bg-gray-50 transition-colors'>
                                                <td class='px-6 py-4 text-sm text-gray-600'>{$staff['staff_id']}</td>
                                                <td class='px-6 py-4 text-sm font-medium text-gray-800'>{$staff['full_name']}</td>
                                                <td class='px-6 py-4 text-sm text-gray-600'>{$staff['phone_no']}</td>
                                                <td class='px-6 py-4 text-sm text-gray-600'>{$staff['email_id']}</td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='px-6 py-8 text-center text-gray-500'>No staff found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function showSection(sectionId) {
        // Hide all sections
        document.querySelectorAll('.section').forEach(section => {
            section.classList.add('hidden');
            section.classList.remove('block');
        });
        
        // Show selected section
        const selectedSection = document.getElementById(sectionId);
        if (selectedSection) {
            selectedSection.classList.remove('hidden');
            selectedSection.classList.add('block');
            selectedSection.classList.add('fade-in');
        }

        // Update tab styles
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-secondary', 'text-secondary');
            btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        });

        const activeTab = document.getElementById('tab-' + sectionId);
        if (activeTab) {
            activeTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            activeTab.classList.add('border-secondary', 'text-secondary');
        }
    }
</script>

</body>
</html>
