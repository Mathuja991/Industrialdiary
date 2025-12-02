<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

include '../config/db.php';
// include '../public/header.php'; // Removed old header

// Fetch students for the dropdown
$students = [];
$studentsQuery = "SELECT id, username, full_name FROM users WHERE role = 'student'";
$result = $conn->query($studentsQuery);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Initialize variables
$results_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $studentId = $_POST['student_id'];

    // Fetch inspection marks (1 report)
    $inspectionQuery = $conn->prepare("SELECT inspection_marks FROM inspection_results WHERE student_id = ?");
    $inspectionQuery->bind_param("i", $studentId);
    $inspectionQuery->execute();
    $inspection_result = $inspectionQuery->get_result();
    $inspection_row = $inspection_result->fetch_assoc();
    $inspection_marks = $inspection_row ? $inspection_row['inspection_marks'] : 0;
    $inspectionQuery->close();

    // Fetch diary marks (24 reports)
    $diaryQuery = $conn->prepare("SELECT SUM(mentor_mark) FROM diaries WHERE student_id = ?");
    $diaryQuery->bind_param("i", $studentId);
    $diaryQuery->execute();
    $diary_result = $diaryQuery->get_result();
    $diary_row = $diary_result->fetch_row();
    $diary_marks = $diary_row[0] ? $diary_row[0] : 0;
    $diaryQuery->close();

    // Fetch process marks (1 report)
    $processQuery = $conn->prepare("SELECT overallpro_mark FROM overall_reports WHERE student_id = ?");
    $processQuery->bind_param("i", $studentId);
    $processQuery->execute();
    $process_result = $processQuery->get_result();
    $process_row = $process_result->fetch_assoc();
    $process_marks = $process_row ? $process_row['overallpro_mark'] : 0;
    $processQuery->close();

    // Calculate total marks and grade
    $total_marks = $inspection_marks + $diary_marks + $process_marks;
    $grade = calculateGrade($total_marks);

    // Store the results data for display
    $results_data = [
        'inspection_marks' => $inspection_marks,
        'diary_marks' => $diary_marks,
        'process_marks' => $process_marks,
        'total_marks' => $total_marks,
        'grade' => $grade
    ];

    // Insert results into the student_results table
    // Check if result already exists to avoid duplicates or update
    $checkQuery = $conn->prepare("SELECT id FROM student_results WHERE student_id = ?");
    $checkQuery->bind_param("i", $studentId);
    $checkQuery->execute();
    if ($checkQuery->get_result()->num_rows == 0) {
        $insertQuery = $conn->prepare("INSERT INTO student_results (student_id, inspection_mark, diary_mark, process_mark, total_mark, grade) VALUES (?, ?, ?, ?, ?, ?)");
        $insertQuery->bind_param("idddds", $studentId, $inspection_marks, $diary_marks, $process_marks, $total_marks, $grade);
        $insertQuery->execute();
        $insertQuery->close();
    } else {
        // Optional: Update existing result
        $updateQuery = $conn->prepare("UPDATE student_results SET inspection_mark=?, diary_mark=?, process_mark=?, total_mark=?, grade=? WHERE student_id=?");
        $updateQuery->bind_param("ddddsi", $inspection_marks, $diary_marks, $process_marks, $total_marks, $grade, $studentId);
        $updateQuery->execute();
        $updateQuery->close();
    }
    $checkQuery->close();
}

// Function to calculate grade based on total marks
function calculateGrade($totalMarks) {
    // Define maximum possible marks based on assumptions
    $maxInspectionMarks = 100;  // Assuming max marks for inspection report
    $maxDiaryMarks = 240;       // Assuming max marks for 24 diaries (10 marks each)
    $maxProcessMarks = 100;     // Assuming max marks for overall process report

    // Total maximum marks
    $maxTotalMarks = $maxInspectionMarks + $maxDiaryMarks + $maxProcessMarks;

    // Calculate the percentage
    if ($maxTotalMarks > 0) {
        $percentage = ($totalMarks / $maxTotalMarks) * 100;
    } else {
        $percentage = 0;
    }

    // Determine grade based on percentage
    if ($percentage >= 90) return "A";
    elseif ($percentage >= 80) return "B";
    elseif ($percentage >= 70) return "C";
    elseif ($percentage >= 60) return "D";
    else return "F";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Results</title>
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
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-primary">Student Results</h2>
                    <p class="text-gray-500">Calculate and view final student grades</p>
                </div>
            </div>

            <!-- Form to select a student -->
            <form method="POST" action="" class="mb-8">
                <div class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Select Student (Reg No)</label>
                        <select id="student_id" name="student_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none">
                            <option value="" disabled selected>Select a student</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>" <?php echo isset($_POST['student_id']) && $_POST['student_id'] == $student['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['username']) . " - " . htmlspecialchars($student['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="bg-secondary hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition-all h-[42px]">
                        View Results
                    </button>
                </div>
            </form>

            <!-- Display results table if data is available -->
            <?php if ($results_data): ?>
                <div class="border-t pt-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Results for Selected Student</h3>
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-700 uppercase text-xs tracking-wider">
                                    <th class="p-4 font-semibold border-b">Inspection Report Marks</th>
                                    <th class="p-4 font-semibold border-b">Diary Report Marks</th>
                                    <th class="p-4 font-semibold border-b">Overall Process Report Marks</th>
                                    <th class="p-4 font-semibold border-b">Total Marks</th>
                                    <th class="p-4 font-semibold border-b">Grade</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-gray-600">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-4 border-b"><?php echo htmlspecialchars($results_data['inspection_marks']); ?></td>
                                    <td class="p-4 border-b"><?php echo htmlspecialchars($results_data['diary_marks']); ?></td>
                                    <td class="p-4 border-b"><?php echo htmlspecialchars($results_data['process_marks']); ?></td>
                                    <td class="p-4 border-b font-bold text-gray-800"><?php echo htmlspecialchars($results_data['total_marks']); ?></td>
                                    <td class="p-4 border-b">
                                        <span class="px-3 py-1 rounded-full text-sm font-bold 
                                            <?php echo $results_data['grade'] == 'A' ? 'bg-green-100 text-green-700' : 
                                                       ($results_data['grade'] == 'B' ? 'bg-blue-100 text-blue-700' : 
                                                       ($results_data['grade'] == 'C' ? 'bg-yellow-100 text-yellow-700' : 
                                                       'bg-red-100 text-red-700')); ?>">
                                            <?php echo htmlspecialchars($results_data['grade']); ?>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php elseif (isset($_POST['student_id'])): ?>
                <!-- Message if no results are available for the selected student -->
                <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <p>No results found for the selected student.</p>
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
