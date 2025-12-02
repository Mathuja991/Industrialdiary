<?php
session_start();
include '../config/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $organization = trim($_POST['organization']);
    $role = 'mentor'; // Fixed role for mentor registration only
    
    // Validation
    if (empty($full_name) || empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($phone) || empty($address) || empty($organization)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username already exists. Please choose another.";
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Hash password and insert user as mentor
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, role, full_name) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $hashed_password, $role, $full_name);
                $stmt->execute();
                
                // Get the newly created user_id
                $user_id = $conn->insert_id;
                
                // Insert into mentor table
                $stmt = $conn->prepare("INSERT INTO mentor (user_id, email_id, phone_no, address, working_organization) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $user_id, $email, $phone, $address, $organization);
                $stmt->execute();
                
                // Commit transaction
                $conn->commit();
                $success = "Mentor registration successful! You can now login with your credentials.";
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                $error = "Registration failed. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Registration - Industrial Diary Management System</title>
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
    
    <!-- Registration Container -->
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden max-w-5xl w-full grid md:grid-cols-2 animate-slide-up">
        
        <!-- Left Panel - Branding -->
        <div class="gradient-panel p-10 flex flex-col justify-between relative overflow-hidden">
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
                    Mentor<br/>Registration
                </h1>
                <p class="text-white/85 text-base">University of Jaffna</p>
            </div>
            
            <div class="relative z-10">
                <p class="text-white/70 text-sm">
                    Register as a mentor to guide students through their industrial training journey. Students and staff accounts are created by administrators.
                </p>
            </div>
        </div>
        
        <!-- Right Panel - Registration Form -->
        <div class="p-8 flex items-center justify-center bg-white overflow-y-auto max-h-[90vh]">
            <div class="w-full max-w-md">
                <!-- Form Header -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">Mentor Registration</h2>
                    <p class="text-gray-600 text-sm">Create your mentor account</p>
                </div>
                
                <!-- Success Message -->
                <?php if (!empty($success)): ?>
                    <div class="flex items-start gap-2 p-3 mb-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                        <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="flex-1">
                            <span class="text-green-800 text-xs font-medium"><?php echo $success; ?></span>
                            <div class="mt-2">
                                <a href="login.php" class="text-green-700 font-semibold hover:text-green-900 hover:underline text-xs">
                                    Go to Login →
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Error Message -->
                <?php if (!empty($error)): ?>
                    <div class="flex items-start gap-2 p-3 mb-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                        <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span class="text-red-800 text-xs font-medium"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                
                <!-- Registration Form -->
                <form method="POST" class="space-y-3.5">
                    <!-- Full Name Field -->
                    <div>
                        <label for="full_name" class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
                            Full Name
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                id="full_name" 
                                name="full_name" 
                                required
                                value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                class="w-full pl-10 pr-3 py-2.5 text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 outline-none"
                                placeholder="Full name"
                            >
                        </div>
                    </div>
                    
                    <!-- Username Field -->
                    <div>
                        <label for="username" class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
                            Username
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                required
                                maxlength="12"
                                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                class="w-full pl-10 pr-3 py-2.5 text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 outline-none"
                                placeholder="Username (max 12 chars)"
                            >
                        </div>
                    </div>
                    
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                class="w-full pl-10 pr-3 py-2.5 text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 outline-none"
                                placeholder="email@example.com"
                            >
                        </div>
                    </div>
                    
                    <!-- Phone Field -->
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
                            Phone Number
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                required
                                value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                class="w-full pl-10 pr-3 py-2.5 text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 outline-none"
                                placeholder="Phone number"
                            >
                        </div>
                    </div>
                    
                    <!-- Address Field -->
                    <div>
                        <label for="address" class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
                            Address
                        </label>
                        <div class="relative">
                            <div class="absolute top-2.5 left-0 pl-3 flex items-start pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <textarea 
                                id="address" 
                                name="address" 
                                required
                                rows="2"
                                class="w-full pl-10 pr-3 py-2.5 text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 outline-none resize-none"
                                placeholder="Address"
                            ><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Organization Field -->
                    <div>
                        <label for="organization" class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
                            Working Organization
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                id="organization" 
                                name="organization" 
                                required
                                value="<?php echo isset($_POST['organization']) ? htmlspecialchars($_POST['organization']) : ''; ?>"
                                class="w-full pl-10 pr-3 py-2.5 text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 outline-none"
                                placeholder="Organization name"
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
                                minlength="6"
                                class="w-full pl-10 pr-3 py-2.5 text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 outline-none"
                                placeholder="Password (min. 6 chars)"
                            >
                        </div>
                    </div>
                    
                    <!-- Confirm Password Field -->
                    <div>
                        <label for="confirm_password" class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
                            Confirm Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                required
                                minlength="6"
                                class="w-full pl-10 pr-3 py-2.5 text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 outline-none"
                                placeholder="Confirm password"
                            >
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-accent hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 uppercase text-xs tracking-wide shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 mt-4"
                    >
                        <span>Create Account</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                </form>
                
                <!-- Form Footer -->
                <div class="mt-6 pt-4 border-t border-gray-200 text-center">
                    <p class="text-gray-600 text-xs">
                        Already have an account? 
                        <a href="login.php" class="text-accent font-semibold hover:text-blue-700 hover:underline transition-colors">
                            Sign in here
                        </a>
                    </p>
                    <p class="text-gray-500 text-xs mt-2">
                        <strong>Note:</strong> This registration is for mentors only. Student and staff accounts are created by administrators.
                    </p>
                </div>
            </div>
        </div>
        
    </div>
    
</body>
</html>
