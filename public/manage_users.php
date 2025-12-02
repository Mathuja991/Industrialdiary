<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../config/db.php';

// Check if user is admin BEFORE including header
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// NOW include header after authentication
// include '../public/header.php'; // Removed old header

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $default_password = 'password123'; // Default password for new users
    
    // Check if username exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $error_msg = "Username already exists.";
    } else {
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, full_name, must_change_password) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("ssss", $username, $hashed_password, $role, $full_name);
        
        if ($stmt->execute()) {
            $success_msg = "User created successfully! Default password: <strong>password123</strong>";
        } else {
            $error_msg = "Failed to create user.";
        }
    }
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->bind_param("i", $user_id);
    if ($delete_stmt->execute()) {
        $success_msg = "User deleted successfully.";
    }
}

// Handle password reset
if (isset($_GET['reset'])) {
    $user_id = $_GET['reset'];
    $default_password = 'password123';
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
    
    $reset_stmt = $conn->prepare("UPDATE users SET password = ?, must_change_password = 1 WHERE id = ?");
    $reset_stmt->bind_param("si", $hashed_password, $user_id);
    
    if ($reset_stmt->execute()) {
        $success_msg = "Password reset successfully! New password: <strong>password123</strong>";
    }
}

// Fetch all users
$users_query = "SELECT id, username, full_name, role, must_change_password FROM users ORDER BY role, full_name";
$users = $conn->query($users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <?php include 'includes/head_tailwind.php'; ?>
</head>
<body class="bg-light font-sans antialiased">

<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="ml-64 mt-16 p-8 transition-all duration-300">
    <div class="max-w-7xl mx-auto animate-fade-in">
        <h1 class="text-3xl font-bold text-primary mb-8">User Management</h1>

        <!-- Create User Form -->
        <div class="bg-white rounded-xl shadow-md p-8 mb-8">
            <div class="flex items-center gap-4 mb-6 border-b pb-4">
                <div class="w-12 h-12 bg-secondary text-white rounded-full flex items-center justify-center">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-primary">Create New User</h2>
                    <p class="text-gray-500">Add a new student or staff account</p>
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

            <form method="POST" class="space-y-6" id="createUserForm">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" name="full_name" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none"
                               placeholder="e.g., John Doe">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" id="usernameLabel">
                            Username
                        </label>
                        <input type="text" name="username" id="usernameInput" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none"
                               placeholder="Enter username">
                        <p class="text-xs text-gray-500 mt-1" id="usernameHint"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <select name="role" id="roleSelect" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none bg-white">
                            <option value="student">Student</option>
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <p class="text-sm text-blue-700">
                        <strong>Note:</strong> New users will be created with default password: <code class="bg-blue-100 px-2 py-1 rounded">password123</code>
                        <br>They will be required to change it on first login.
                    </p>
                </div>

                <div class="flex justify-end pt-4 border-t">
                    <button type="submit" name="create_user" 
                            class="bg-secondary hover:bg-blue-600 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md hover:shadow-lg transition-all">
                        Create User
                    </button>
                </div>
            </form>

            <script>
                // Update username label and placeholder based on role
                document.getElementById('roleSelect').addEventListener('change', function() {
                    const role = this.value;
                    const label = document.getElementById('usernameLabel');
                    const input = document.getElementById('usernameInput');
                    const hint = document.getElementById('usernameHint');
                    
                    if (role === 'student') {
                        label.textContent = 'Registration Number';
                        input.placeholder = 'e.g., 2020/CSC/001';
                        hint.textContent = 'This will be used as the login username';
                    } else if (role === 'staff') {
                        label.textContent = 'Staff ID / NIC';
                        input.placeholder = 'e.g.,7568945';
                        hint.textContent = 'Unique identifier for staff member';
                    } else if (role === 'admin') {
                        label.textContent = 'Username';
                        input.placeholder = 'e.g., admin_john';
                        hint.textContent = 'Admin login username';
                    }
                });
                
                // Trigger on page load for default selection
                document.getElementById('roleSelect').dispatchEvent(new Event('change'));
            </script>
        </div>

        <!-- Users List -->
        <div class="bg-white rounded-xl shadow-md p-8">
            <h2 class="text-xl font-bold text-primary mb-6">All Users</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 uppercase text-xs tracking-wider">
                            <th class="p-4 font-semibold rounded-tl-lg">Full Name</th>
                            <th class="p-4 font-semibold">Username</th>
                            <th class="p-4 font-semibold">Role</th>
                            <th class="p-4 font-semibold">Status</th>
                            <th class="p-4 font-semibold rounded-tr-lg">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-600 divide-y divide-gray-100">
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 font-medium text-gray-800"><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        <?php echo $user['role'] == 'student' ? 'bg-blue-100 text-blue-700' : 
                                                   ($user['role'] == 'mentor' ? 'bg-green-100 text-green-700' : 
                                                   ($user['role'] == 'staff' ? 'bg-purple-100 text-purple-700' : 
                                                   'bg-red-100 text-red-700')); ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <?php if ($user['must_change_password'] == 1): ?>
                                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs font-semibold">
                                            Must Change Password
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">
                                            Active
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4">
                                    <div class="flex gap-2">
                                        <a href="?reset=<?php echo $user['id']; ?>" 
                                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs font-semibold transition-all"
                                           onclick="return confirm('Reset password to default (password123)?');"
                                           title="Reset Password">
                                            Reset
                                        </a>
                                        <a href="?delete=<?php echo $user['id']; ?>" 
                                           class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold transition-all"
                                           onclick="return confirm('Are you sure you want to delete this user?');"
                                           title="Delete User">
                                            Delete
                                        </a>
                                    </div>
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
