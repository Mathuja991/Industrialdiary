<?php
session_start();
require_once '../includes/auth_check.php';
checkAuth(['student']);

include '../config/db.php';

$pageTitle = "Student Dashboard";
$student_id = $_SESSION['user_id'];

// Get total diary entries count
$stmt = $conn->prepare("SELECT COUNT(*) as total_reports FROM diaries WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$total_reports = $result->fetch_assoc()['total_reports'];
$stmt->close();

// Fetch start_date to calculate weeks completed
$stmt = $conn->prepare("SELECT start_date FROM student WHERE user_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student_data = $result->fetch_assoc();
$start_date_str = $student_data ? $student_data['start_date'] : null;
$stmt->close();

// Calculate weeks completed based on start date
$weeks_completed = 0;
if ($start_date_str) {
    $start_date = new DateTime($start_date_str);
    $current_date = new DateTime();
    $days_passed = max(0, $current_date->diff($start_date)->days);
    
    // Only count positive days (after start date)
    if ($current_date >= $start_date) {
        $weeks_completed = min(24, ceil($days_passed / 7)); // Cap at 24 weeks
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <?php include 'includes/head_tailwind.php'; ?>
</head>
<body class="bg-light">

<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="ml-64 mt-16 p-8">
    <div class="animate-fade-in">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
            <!-- Stats Cards -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Total Reports Card -->
                <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-secondary flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">Reports Submitted</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $total_reports; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center text-secondary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    </div>
                </div>

                <!-- Weeks Completed Card -->
                <?php 
                    $total_weeks = 24;
                    $progress_percent = min(100, round(($weeks_completed / $total_weeks) * 100));
                ?>
                <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-accent flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">Weeks Completed</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $weeks_completed; ?> <span class="text-sm text-gray-400 font-normal">/ <?php echo $total_weeks; ?></span></h3>
                    </div>
                    <div class="w-12 h-12 bg-orange-50 rounded-full flex items-center justify-center text-accent">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md">
                <h3 class="text-lg font-bold text-primary mb-4">Training Timeline</h3>
                
                <?php
                // Use the start_date already fetched at the top
                if ($start_date_str):
                    $start_date = new DateTime($start_date_str);
                    $current_date = new DateTime();
                    $end_date = clone $start_date;
                    $end_date->modify('+24 weeks');
                    
                    $total_days = $end_date->diff($start_date)->days;
                    $days_passed = max(0, $current_date->diff($start_date)->days);
                    $progress_percent_timeline = min(100, max(0, round(($days_passed / $total_days) * 100)));
                    $current_week = $weeks_completed; // Use the already calculated weeks_completed
                ?>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-50 text-secondary">In Progress</span>
                        <span class="text-xs font-semibold text-secondary"><?php echo $progress_percent_timeline; ?>%</span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div style="width:<?php echo $progress_percent_timeline; ?>%" class="h-full bg-gradient-to-r from-secondary to-blue-400 transition-all duration-1000"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 text-center pt-4">
                        <div>
                            <p class="text-xs text-gray-400 uppercase">Start Date</p>
                            <p class="font-semibold text-gray-700"><?php echo $start_date->format('M d, Y'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase">Current Week</p>
                            <p class="font-semibold text-secondary">Week <?php echo $current_week; ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase">End Date</p>
                            <p class="font-semibold text-gray-700"><?php echo $end_date->format('M d, Y'); ?></p>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="text-center py-8 bg-gray-50 rounded-lg border border-dashed">
                    <p class="text-gray-500 mb-2">Training start date not set.</p>
                    <a href="schedule_week.php" class="text-secondary font-semibold hover:underline">Set Start Date</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
