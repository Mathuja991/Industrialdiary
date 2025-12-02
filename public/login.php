<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hardcoded Admin Login
    if ($username === 'admin' && $password === 'admin123') {
        session_unset(); // Clear previous session
        $_SESSION['user_id'] = 1;
        $_SESSION['admin_id'] = 1;
        $_SESSION['role'] = 'admin';
        header('Location: admin_dashboard.php');
        exit();
    }

    // Check the users table for all user types
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Clear any previous session data
        session_unset();
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = trim($user['role']);

        // Admin specific session
        if ($_SESSION['role'] === 'admin') {
            $_SESSION['admin_id'] = $user['id'];
        }

        // Redirect based on role
        if ($user['role'] === 'student') {
            header('Location: student_dashboard.php');
        } elseif ($user['role'] === 'mentor') {
            header('Location: mentor_dashboard.php');
        } elseif ($user['role'] === 'staff') {
            header('Location: staff_dashboard.php');
        } elseif ($user['role'] === 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit();
    } else {
        $error = "Invalid username or password.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Industrial Diary Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e3c72',
                        secondary: '#2a5298',
                        accent: '#0066cc',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
        }
        .gradient-panel {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }
        .grid-pattern {
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 50px 50px;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-slide-up {
            animation: slideUp 0.6s ease-out;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    
    <!-- Login Container -->
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden max-w-4xl w-full grid md:grid-cols-5 animate-slide-up">
        
        <!-- Left Panel - Branding -->
        <div class="gradient-panel md:col-span-3 p-10 flex flex-col justify-between relative overflow-hidden">
            <!-- Grid Pattern Overlay -->
            <div class="absolute inset-0 grid-pattern opacity-30"></div>
            
            <!-- Decorative Circles -->
            <div class="absolute -top-20 -right-20 w-80 h-80 border-[40px] border-white/5 rounded-full"></div>
            <div class="absolute -bottom-16 -left-16 w-64 h-64 border-[30px] border-white/10 rounded-full"></div>
            <div class="absolute top-1/2 right-1/4 w-32 h-32 border-2 border-white/20 rounded-full"></div>
            
            <!-- Content -->
            <div class="relative z-10">
                <!-- Logo -->
                <div class="w-14 h-14 bg-white/10 backdrop-blur-sm border-2 border-white/20 rounded-xl flex items-center justify-center mb-6 hover:bg-white/15 transition-all duration-300">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                
                <!-- Title -->
                <h1 class="text-3xl font-bold text-white mb-3 leading-tight">
                    Industrial Diary<br/>Management System
                </h1>
                <p class="text-white/85 text-base">University of Jaffna</p>
            </div>
            
            <div class="relative z-10">
                <p class="text-white/70 text-sm">
                    Streamline your industrial training documentation and mentor feedback process.
                </p>
            </div>
        </div>
        
        <!-- Right Panel - Login Form -->
        <div class="md:col-span-2 p-8 flex items-center justify-center bg-white">
            <div class="w-full max-w-xs">
                <!-- Form Header -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">Welcome</h2>
                    <p class="text-gray-600 text-sm">Login to continue</p>
                </div>
                
                <!-- Error Message -->
                <?php if (isset($error)): ?>
                    <div class="flex items-start gap-2 p-3 mb-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                        <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span class="text-red-800 text-xs font-medium"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form method="POST" class="space-y-4">
                    <!-- Username Field -->
                    <div>
                        <label for="username" class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
                            Username
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                required
                                maxlength="12"
                                class="w-full pl-10 pr-3 py-2.5 text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 outline-none"
                                placeholder="Username"
                            >
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                maxlength="12"
                                class="w-full pl-10 pr-3 py-2.5 text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 outline-none"
                                placeholder="Password"
                            >
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-accent hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 uppercase text-xs tracking-wide shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 mt-5"
                    >
                        <span>Sign In</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                </form>
                
                <!-- Form Footer -->
                <div class="mt-6 pt-4 border-t border-gray-200 text-center">
                    <p class="text-gray-600 text-xs">
                        Mentor? 
                        <a href="register.php" class="text-accent font-semibold hover:text-blue-700 hover:underline transition-colors">
                            Register here
                        </a>
                    </p>
                </div>
            </div>
        </div>
        
    </div>
    
</body>
</html>
