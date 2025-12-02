<div class="fixed left-0 top-0 w-64 h-full bg-gradient-to-b from-primary to-gray-900 text-white flex flex-col shadow-xl z-50 transition-all duration-300" id="sidebar">
    <div class="p-6 bg-black/10 text-center border-b border-white/5">
        <div class="flex flex-col items-center gap-3">
            <!-- Placeholder Logo -->
            <div class="w-14 h-14 bg-white/10 rounded-full flex items-center justify-center text-secondary mb-1">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>
            <h2 class="m-0 text-xl font-bold text-white tracking-wide uppercase">Industrial Diary</h2>
        </div>
    </div>
    <ul class="flex-1 py-4 m-0 list-none overflow-y-auto">
        <?php 
        $current_page = basename($_SERVER['PHP_SELF']);
        function getLinkClass($page, $current) {
            $base = "flex items-center px-6 py-4 text-gray-300 no-underline transition-all duration-300 text-[0.95rem] border-l-4 border-transparent hover:bg-white/5 hover:text-white hover:border-secondary hover:pl-8";
            if ($page == $current) {
                return $base . " bg-white/5 text-white border-secondary pl-8";
            }
            return $base;
        }
        ?>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'student'): ?>
            <li><a href="student_dashboard.php" class="<?php echo getLinkClass('student_dashboard.php', $current_page); ?>">Dashboard</a></li>
            <li><a href="schedule_week.php" class="<?php echo getLinkClass('schedule_week.php', $current_page); ?>">Schedule Week</a></li>
            <li><a href="upload_diary.php" class="<?php echo getLinkClass('upload_diary.php', $current_page); ?>">Upload Diary</a></li>
            <li><a href="view_reports.php" class="<?php echo getLinkClass('view_reports.php', $current_page); ?>">View Reports</a></li>
            <li><a href="overall_report.php" class="<?php echo getLinkClass('overall_report.php', $current_page); ?>">Upload Overall Report</a></li>
            <li><a href="view_overall_report.php" class="<?php echo getLinkClass('view_overall_report.php', $current_page); ?>">View Overall Report</a></li>
            <li><a href="inspection_reports.php" class="<?php echo getLinkClass('inspection_reports.php', $current_page); ?>">View Inspection Reports</a></li>

        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'mentor'): ?>
            <li><a href="mentor_dashboard.php" class="<?php echo getLinkClass('mentor_dashboard.php', $current_page); ?>">Dashboard</a></li>
            <li><a href="review_diaries.php" class="<?php echo getLinkClass('review_diaries.php', $current_page); ?>">Review Diaries</a></li>
            <li><a href="review_overall_reports.php" class="<?php echo getLinkClass('review_overall_reports.php', $current_page); ?>">Review Overall Reports</a></li>
        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'staff'): ?>
            <li><a href="staff_dashboard.php" class="<?php echo getLinkClass('staff_dashboard.php', $current_page); ?>">Dashboard</a></li>
            <li><a href="upload_inspection.php" class="<?php echo getLinkClass('upload_inspection.php', $current_page); ?>">Upload Inspection</a></li>
            <li><a href="view_inspections.php" class="<?php echo getLinkClass('view_inspections.php', $current_page); ?>">View Inspections</a></li>
            <li><a href="assign_inspectionresults.php" class="<?php echo getLinkClass('assign_inspectionresults.php', $current_page); ?>">Assign Results</a></li>
            <li><a href="student_results.php" class="<?php echo getLinkClass('student_results.php', $current_page); ?>">Calculate Results</a></li>
        <?php elseif (isset($_SESSION['admin_id'])): ?>
            <li><a href="admin_dashboard.php" class="<?php echo getLinkClass('admin_dashboard.php', $current_page); ?>">Dashboard</a></li>
            <li><a href="manage_users.php" class="<?php echo getLinkClass('manage_users.php', $current_page); ?>">Manage Users</a></li>
            <li><a href="assign_mentor.php" class="<?php echo getLinkClass('assign_mentor.php', $current_page); ?>">Assign Mentor</a></li>
            <li><a href="view_mentors.php" class="<?php echo getLinkClass('view_mentors.php', $current_page); ?>">View Mentors</a></li>
            <!-- Staff Functionalities for Admin -->
            <li><a href="upload_inspection.php" class="<?php echo getLinkClass('upload_inspection.php', $current_page); ?>">Upload Inspection</a></li>
            <li><a href="view_inspections.php" class="<?php echo getLinkClass('view_inspections.php', $current_page); ?>">View Inspections</a></li>
            <li><a href="assign_inspectionresults.php" class="<?php echo getLinkClass('assign_inspectionresults.php', $current_page); ?>">Assign Results</a></li>
            <li><a href="student_results.php" class="<?php echo getLinkClass('student_results.php', $current_page); ?>">Calculate Results</a></li>
        <?php endif; ?>
    </ul>
    <div class="p-5 border-t border-white/5 text-center text-xs text-gray-500">
        &copy; <?php echo date('Y'); ?> UoJ IDMS
    </div>
</div>
