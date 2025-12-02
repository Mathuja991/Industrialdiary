<?php
/**
 * Authentication and Role-Based Access Control Utility
 * Include this file at the top of pages that require authentication
 * 
 * Usage:
 * require_once '../includes/auth_check.php';
 * checkAuth(['student']); // Only students can access
 * checkAuth(['student', 'mentor']); // Students and mentors can access
 */

/**
 * Check if user is authenticated and has the required role
 * @param array $allowedRoles Array of allowed roles (e.g., ['student', 'mentor'])
 * @param string $redirectUrl URL to redirect if not authenticated (default: login.php)
 */
function checkAuth($allowedRoles = [], $redirectUrl = '../public/login.php') {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        // Not logged in, redirect to login page
        header('Location: ' . $redirectUrl);
        exit();
    }
    
    // If specific roles are required, check if user has one of them
    if (!empty($allowedRoles)) {
        $userRole = $_SESSION['role'];
        
        // Check for admin using admin_id
        if (isset($_SESSION['admin_id'])) {
            $userRole = 'admin';
        }
        
        if (!in_array($userRole, $allowedRoles)) {
            // User doesn't have required role
            http_response_code(403);
            echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg max-w-md text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Access Denied</h1>
        <p class="text-gray-600 mb-6">You do not have permission to access this page.</p>
        <a href="../public/logout.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
            Go to Login
        </a>
    </div>
</body>
</html>';
            exit();
        }
    }
    
    return true;
}

