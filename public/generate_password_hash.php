<?php
/**
 * Password Hash Generator
 * Use this script to generate a password hash for admin accounts
 * 
 * Usage:
 * 1. Run this file in your browser: http://localhost:8000/generate_password_hash.php
 * 2. Enter your desired password
 * 3. Copy the generated hash
 * 4. Use it in the create_admin.sql script
 */

$password_hash = '';
$password_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password_input = $_POST['password'];
    $password_hash = password_hash($password_input, PASSWORD_DEFAULT);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Hash Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg p-8 max-w-2xl w-full">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Password Hash Generator</h1>
        <p class="text-gray-600 mb-6">Generate a secure password hash for admin account creation.</p>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Enter Password</label>
                <input type="text" name="password" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                       placeholder="Enter the password you want to hash">
            </div>
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-all">
                Generate Hash
            </button>
        </form>

        <?php if ($password_hash): ?>
            <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <h3 class="font-semibold text-green-800 mb-2">Generated Hash:</h3>
                <div class="bg-white p-3 rounded border border-green-300 mb-3">
                    <code class="text-sm break-all text-gray-800"><?php echo htmlspecialchars($password_hash); ?></code>
                </div>
                <p class="text-sm text-green-700 mb-3">
                    <strong>Password:</strong> <?php echo htmlspecialchars($password_input); ?>
                </p>
                
                <div class="bg-blue-50 border border-blue-200 rounded p-3 mt-4">
                    <h4 class="font-semibold text-blue-800 mb-2">SQL Query to Create Admin:</h4>
                    <code class="text-xs block bg-white p-2 rounded border border-blue-300 overflow-x-auto">
INSERT INTO users (username, password, role, full_name) <br>
VALUES ('admin', '<?php echo htmlspecialchars($password_hash); ?>', 'admin', 'System Administrator');
                    </code>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h3 class="font-semibold text-yellow-800 mb-2">⚠️ Security Notes:</h3>
            <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                <li>Use a strong password (mix of letters, numbers, symbols)</li>
                <li>Never share admin credentials</li>
                <li>Delete this file after creating your admin account</li>
                <li>Change the default admin password after first login</li>
            </ul>
        </div>
    </div>
</body>
</html>
