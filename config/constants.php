<?php
/**
 * Application Constants
 */

// Application Settings
define('APP_NAME', 'Employee Management System');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', true);
define('BASE_URL', 'http://localhost/employee-management-system/');

// Session Timeout (in seconds)
define('SESSION_TIMEOUT', 1800); // 30 minutes

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_HR', 'hr');
define('ROLE_MANAGER', 'manager');
define('ROLE_EMPLOYEE', 'employee');

// Attendance Status
define('ATTENDANCE_PRESENT', 'present');
define('ATTENDANCE_ABSENT', 'absent');
define('ATTENDANCE_LATE', 'late');
define('ATTENDANCE_HALF_DAY', 'half_day');

// Leave Status
define('LEAVE_PENDING', 'pending');
define('LEAVE_APPROVED', 'approved');
define('LEAVE_REJECTED', 'rejected');
define('LEAVE_CANCELLED', 'cancelled');

// Leave Types
define('LEAVE_SICK', 'sick');
define('LEAVE_CASUAL', 'casual');
define('LEAVE_MEDICAL', 'medical');
define('LEAVE_ANNUAL', 'annual');

// Payroll Status
define('PAYROLL_DRAFT', 'draft');
define('PAYROLL_PENDING', 'pending');
define('PAYROLL_APPROVED', 'approved');
define('PAYROLL_PAID', 'paid');

// Task Status
define('TASK_PENDING', 'pending');
define('TASK_IN_PROGRESS', 'in_progress');
define('TASK_COMPLETED', 'completed');
define('TASK_CANCELLED', 'cancelled');

// Pagination
define('ITEMS_PER_PAGE', 20);

// Email Configuration
define('MAIL_FROM', 'noreply@company.com');
define('MAIL_FROM_NAME', 'Employee Management System');

// File Upload Limits
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'jpg', 'png', 'gif']);
