<?php
session_start();
include '../config/db.php';

/* =========================
   ERROR REPORTING (DEV ONLY)
========================= */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg   = '';

/* =========================
   CSRF TOKEN
========================= */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =========================
   FETCH USER BASE INFO
========================= */
$stmt = $conn->prepare(
    "SELECT role, full_name, username FROM users WHERE id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows === 0) {
    die("User not found");
}

$user = $user_result->fetch_assoc();
$stmt->close();

$role      = strtolower($user['role']);
$full_name = $user['full_name'];
$username  = $user['username'];

$allowed_roles = ['student', 'mentor', 'staff', 'admin'];
if (!in_array($role, $allowed_roles)) {
    die("Invalid role");
}

/* =========================
   TABLE MAP (SECURE)
========================= */
$tableMap = [
    'student' => 'student',
    'mentor'  => 'mentor',
    'staff'   => 'staff'
];

/* =========================
   FETCH PROFILE DETAILS
========================= */
$details = null;

if ($role !== 'admin') {
    $table = $tableMap[$role];
    $stmt = $conn->prepare("SELECT * FROM $table WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $details = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

/* =========================
   HANDLE FORM SUBMIT
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* --- Block admin updates --- */
    if ($role === 'admin') {
        $error_msg = "Admin profile cannot be updated.";
        goto end;
    }

    /* --- CSRF check --- */
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

    /* --- Common Fields --- */
    $email_id = filter_var($_POST['email_id'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone_no = preg_replace('/[^0-9]/', '', $_POST['phone_no'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    if (!empty($email_id) && !filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Invalid email address.";
        goto end;
    }

    if (!empty($phone_no) && strlen($phone_no) < 9) {
        $error_msg = "Invalid phone number.";
        goto end;
    }

    /* =========================
       ROLE BASED UPDATE
    ========================= */
    if ($role === 'student') {

        $reg_no        = trim($_POST['reg_no'] ?? '');
        $academic_year = trim($_POST['academic_year'] ?? '');
        $index_no      = trim($_POST['index_no'] ?? '');

        $stmt = $conn->prepare(
            "INSERT INTO student 
             (user_id, reg_no, academic_year, email_id, phone_no, address, index_no)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
             reg_no = VALUES(reg_no),
             academic_year = VALUES(academic_year),
             email_id = VALUES(email_id),
             phone_no = VALUES(phone_no),
             address = VALUES(address),
             index_no = VALUES(index_no)"
        );
        $stmt->bind_param(
            "issssss",
            $user_id, $reg_no, $academic_year,
            $email_id, $phone_no, $address, $index_no
        );

    } elseif ($role === 'mentor') {

        $organization = trim($_POST['organization'] ?? '');

        $stmt = $conn->prepare(
            "INSERT INTO mentor 
             (user_id, email_id, phone_no, address, working_organization)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
             email_id = VALUES(email_id),
             phone_no = VALUES(phone_no),
             address = VALUES(address),
             working_organization = VALUES(working_organization)"
        );
        $stmt->bind_param(
            "issss",
            $user_id, $email_id, $phone_no, $address, $organization
        );

    } elseif ($role === 'staff') {

        $stmt = $conn->prepare(
            "INSERT INTO staff 
             (user_id, email_id, phone_no, address)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
             email_id = VALUES(email_id),
             phone_no = VALUES(phone_no),
             address = VALUES(address)"
        );
        $stmt->bind_param(
            "isss",
            $user_id, $email_id, $phone_no, $address
        );
    }

    if ($stmt->execute()) {
        $success_msg = "Profile updated successfully!";
    } else {
        $error_msg = "Profile update failed.";
    }

    $stmt->close();

    /* --- Refresh details --- */
    $table = $tableMap[$role];
    $stmt = $conn->prepare("SELECT * FROM $table WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $details = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}


end:
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <?php include 'includes/head_tailwind.php'; ?>
</head>
<body class="bg-light font-sans antialiased">

<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="ml-64 mt-16 p-8 transition-all duration-300">
    <div class="max-w-4xl mx-auto animate-fade-in">
        
        <div class="bg-white rounded-xl shadow-md p-8">
            <!-- Profile Header -->
            <div class="flex items-center gap-6 mb-8 pb-6 border-b">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-secondary to-accent flex items-center justify-center text-white text-3xl font-bold">
                    <?php echo strtoupper(substr($full_name, 0, 1)); ?>
                </div>
                <div>
                    <h2 class="text-3xl font-bold text-primary"><?php echo htmlspecialchars($full_name); ?></h2>
                    <p class="text-gray-600 text-lg"><?php echo ucfirst($role); ?> Profile</p>
                    <p class="text-sm text-gray-500">@<?php echo htmlspecialchars($username); ?></p>
                </div>
            </div>

            <?php if ($success_msg): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg flex items-start gap-3" role="alert">
                    <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="font-medium"><?php echo htmlspecialchars($success_msg); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg flex items-start gap-3" role="alert">
                    <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="font-medium"><?php echo htmlspecialchars($error_msg); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <?php if ($role === 'student'): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Registration Number</label>
                            <input type="text" name="reg_no" value="<?php echo htmlspecialchars($details['reg_no'] ?? '', ENT_QUOTES); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"
                                   placeholder="Enter registration number">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                            <input type="text" name="academic_year" value="<?php echo htmlspecialchars($details['academic_year'] ?? '', ENT_QUOTES); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"
                                   placeholder="e.g., 2023/2024">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Index Number</label>
                            <input type="text" name="index_no" value="<?php echo htmlspecialchars($details['index_no'] ?? '', ENT_QUOTES); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"
                                   placeholder="Enter index number">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email_id" value="<?php echo htmlspecialchars($details['email_id'] ?? '', ENT_QUOTES); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"
                                   placeholder="Enter your email address">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="text" name="phone_no" value="<?php echo htmlspecialchars($details['phone_no'] ?? '', ENT_QUOTES); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"
                                   placeholder="Enter your phone number">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <textarea name="address" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all resize-none"
                                      placeholder="Enter your address"><?php echo htmlspecialchars($details['address'] ?? '', ENT_QUOTES); ?></textarea>
                        </div>
                    </div>

                <?php elseif ($role === 'mentor'): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email_id" value="<?php echo htmlspecialchars($details['email_id'] ?? '', ENT_QUOTES); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"
                                   placeholder="Enter your email address">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="text" name="phone_no" value="<?php echo htmlspecialchars($details['phone_no'] ?? '', ENT_QUOTES); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"
                                   placeholder="Enter your phone number">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Working Organization</label>
                            <input type="text" name="organization" value="<?php echo htmlspecialchars($details['working_organization'] ?? '', ENT_QUOTES); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"
                                   placeholder="Enter your organization name">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <textarea name="address" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all resize-none"
                                      placeholder="Enter your address"><?php echo htmlspecialchars($details['address'] ?? '', ENT_QUOTES); ?></textarea>
                        </div>
                    </div>

                <?php elseif ($role === 'staff'): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email_id" value="<?php echo htmlspecialchars($details['email_id'] ?? '', ENT_QUOTES); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"
                                   placeholder="Enter your email address">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="text" name="phone_no" value="<?php echo htmlspecialchars($details['phone_no'] ?? '', ENT_QUOTES); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"
                                   placeholder="Enter your phone number">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <textarea name="address" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all resize-none"
                                      placeholder="Enter your address"><?php echo htmlspecialchars($details['address'] ?? '', ENT_QUOTES); ?></textarea>
                        </div>
                    </div>
                
                <?php elseif ($role === 'admin'): ?>
                    <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                        <div class="flex items-start gap-3 mb-4">
                            <svg class="w-6 h-6 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-blue-900 mb-2">Administrator Account</p>
                                <p class="text-sm text-blue-700">As an administrator, your profile information is managed separately. You can change your password using the settings menu in the topbar.</p>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-blue-300">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-blue-600 font-medium">Username:</span>
                                    <p class="text-blue-900 mt-1"><?php echo htmlspecialchars($username); ?></p>
                                </div>
                                <div>
                                    <span class="text-blue-600 font-medium">Role:</span>
                                    <p class="text-blue-900 mt-1">Administrator</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="flex gap-4 pt-6 border-t">
                    <a href="<?php echo htmlspecialchars($role); ?>_dashboard.php" class="flex-1 text-center px-6 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all">
                        Back to Dashboard
                    </a>
                    <?php if ($role !== 'admin'): ?>
                    <button type="submit" class="flex-1 bg-secondary hover:bg-blue-600 text-white font-semibold py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all">
                        Save Changes
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

    </div>
</div>

</body>
</html>
