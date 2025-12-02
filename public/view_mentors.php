<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../config/db.php';
require_once '../includes/auth_check.php';

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    checkAuth(['admin']);
}

// include '../public/header.php'; // Removed old header

// Fetch all mentors with their details
$query = "SELECT u.id, u.full_name, u.username, m.mentor_id, m.working_organization, m.phone_no, m.email_id,
                 COUNT(DISTINCT s.user_id) AS student_count
          FROM users u
          INNER JOIN mentor m ON u.id = m.user_id
          LEFT JOIN student s ON m.user_id = s.mentor_id
          WHERE u.role = 'mentor'
          GROUP BY u.id, u.full_name, u.username, m.mentor_id, m.working_organization, m.phone_no, m.email_id
          ORDER BY u.full_name";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Mentors</title>
    <?php include 'includes/head_tailwind.php'; ?>
</head>
<body class="bg-light">

<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="ml-64 mt-16 p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-primary">Mentor Details</h1>
            <a href="admin_dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-all">
                Back to Dashboard
            </a>
        </div>
        
        <!-- Mentors Table -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="flex items-center gap-4 mb-6 pb-4 border-b-2 border-secondary/20">
                <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl flex items-center justify-center shadow-md">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-primary">All Mentors</h2>
                    <p class="text-gray-600 text-sm">Complete list of registered mentors</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 uppercase text-xs tracking-wider">
                            <th class="p-4 font-semibold rounded-tl-lg">ID</th>
                            <th class="p-4 font-semibold">Full Name</th>
                            <th class="p-4 font-semibold">Username</th>
                            <th class="p-4 font-semibold">Working Organization</th>
                            <th class="p-4 font-semibold">Phone</th>
                            <th class="p-4 font-semibold">Email</th>
                            <th class="p-4 font-semibold rounded-tr-lg">Students Assigned</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-600 divide-y divide-gray-100">
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($mentor = $result->fetch_assoc()) {
                                echo "<tr class='hover:bg-gray-50 transition-colors'>
                                        <td class='p-4 font-medium text-gray-800'>{$mentor['id']}</td>
                                        <td class='p-4 font-semibold text-gray-800'>" . htmlspecialchars($mentor['full_name']) . "</td>
                                        <td class='p-4'>" . htmlspecialchars($mentor['username']) . "</td>
                                        <td class='p-4'>" . htmlspecialchars($mentor['working_organization'] ?? 'Not specified') . "</td>
                                        <td class='p-4'>" . htmlspecialchars($mentor['phone_no'] ?? 'N/A') . "</td>
                                        <td class='p-4'>" . htmlspecialchars($mentor['email_id'] ?? 'N/A') . "</td>
                                        <td class='p-4'>
                                            <span class='bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold'>
                                                {$mentor['student_count']} students
                                            </span>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='p-8 text-center text-gray-500'>
                                    <div class='flex flex-col items-center gap-3'>
                                        <svg width='48' height='48' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.5' class='text-gray-400'>
                                            <circle cx='12' cy='12' r='10'></circle>
                                            <line x1='12' y1='8' x2='12' y2='12'></line>
                                            <line x1='12' y1='16' x2='12.01' y2='16'></line>
                                        </svg>
                                        <p class='text-lg font-medium'>No mentors found</p>
                                        <p class='text-sm'>Mentors can register through the public registration page</p>
                                    </div>
                                  </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../public/footer.php'; ?>

</body>
</html>
