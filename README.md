# Employee Management System (XAMPP-Ready)

## Overview
A comprehensive full-stack employee management system designed for deployment on XAMPP. This system manages employee lifecycles, attendance, leave requests, payroll, and task allocation.

## System Architecture

### Tech Stack
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript (ES6)
- **Server**: Apache (XAMPP)
- **Authentication**: Session-based with password hashing

### Project Structure
```
employee-management-system/
├── config/
│   ├── database.php
│   └── constants.php
├── database/
│   ├── schema.sql
│   └── migrations/
├── public/
│   ├── index.php
│   ├── css/
│   ├── js/
│   └── assets/
├── app/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   └── middleware/
├── admin/
│   ├── dashboard.php
│   ├── employees/
│   ├── departments/
│   ├── payroll/
│   ├── attendance/
│   ├── leave/
│   └── reports/
├── employee/
│   ├── dashboard.php
│   ├── profile/
│   ├── attendance/
│   ├── leave-requests/
│   ├── tasks/
│   └── announcements/
└── auth/
    ├── login.php
    ├── logout.php
    └── register.php
```

## Installation & Setup

### Prerequisites
- XAMPP installed and running
- MySQL service enabled
- PHP 7.4 or higher

### Steps
1. Clone the repository to `htdocs` folder:
   ```bash
   cd C:\xampp\htdocs  # Windows
   cd /Applications/XAMPP/htdocs  # macOS
   cd /opt/lampp/htdocs  # Linux
   git clone <repo-url> employee-management-system
   ```

2. Import database schema:
   ```bash
   mysql -u root < database/schema.sql
   ```

3. Update database configuration in `config/database.php` (if needed)

4. Access the system:
   - Admin: `http://localhost/employee-management-system/admin/login.php`
   - Employee: `http://localhost/employee-management-system/employee/login.php`

## Default Credentials

**Admin Account**
- Email: `admin@company.com`
- Password: `Admin@123`

## Features

### Admin Dashboard
- ✅ Department Configuration
- ✅ Employee Onboarding & Offboarding
- ✅ Attendance Tracking & Approvals
- ✅ Leave Request Management
- ✅ Payroll Processing
- ✅ Task Allocation
- ✅ Performance Reports
- ✅ User Management

### Employee Portal
- ✅ Profile Management
- ✅ Clock In/Out
- ✅ Timesheet Review
- ✅ Leave Requests
- ✅ Expense Submissions
- ✅ Task Updates
- ✅ Announcements Feed

## Security Features
- Password hashing (bcrypt)
- CSRF protection
- SQL injection prevention (prepared statements)
- Session management
- Role-based access control
- Data encryption for sensitive fields

## Database
Run migrations and seed data:
```bash
mysql -u root employee_management < database/schema.sql
mysql -u root employee_management < database/seeds.sql
```

## Contributing
Follow the existing code structure and naming conventions.

## License
MIT License
