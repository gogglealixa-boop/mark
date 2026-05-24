<?php
/**
 * Attendance Model
 * Handles attendance tracking and management
 */

class AttendanceModel {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Record attendance check-in
     */
    public function checkIn($employee_id) {
        $today = date('Y-m-d');
        
        $sql = "INSERT INTO attendance (employee_id, attendance_date, check_in_time, status)
                VALUES (:employee_id, :date, NOW(), 'present')
                ON DUPLICATE KEY UPDATE check_in_time = NOW()";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':date', $today);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Record attendance check-out
     */
    public function checkOut($employee_id) {
        $today = date('Y-m-d');
        
        $sql = "UPDATE attendance SET check_out_time = NOW(), 
                working_hours = TIMESTAMPDIFF(HOUR, check_in_time, NOW())
                WHERE employee_id = :employee_id AND attendance_date = :date";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':date', $today);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get attendance for a specific date
     */
    public function getAttendanceByDate($employee_id, $date) {
        $sql = "SELECT * FROM attendance WHERE employee_id = :employee_id AND attendance_date = :date";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get monthly attendance report
     */
    public function getMonthlyAttendance($employee_id, $month, $year) {
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $sql = "SELECT * FROM attendance 
                WHERE employee_id = :employee_id 
                AND attendance_date BETWEEN :start_date AND :end_date
                ORDER BY attendance_date ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get attendance summary for a date range
     */
    public function getAttendanceSummary($employee_id, $start_date, $end_date) {
        $sql = "SELECT 
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_day,
                SUM(working_hours) as total_hours
                FROM attendance
                WHERE employee_id = :employee_id 
                AND attendance_date BETWEEN :start_date AND :end_date";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark attendance manually
     */
    public function markAttendance($employee_id, $date, $status, $notes = '') {
        $sql = "INSERT INTO attendance (employee_id, attendance_date, status, notes)
                VALUES (:employee_id, :date, :status, :notes)
                ON DUPLICATE KEY UPDATE status = :status, notes = :notes";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':notes', $notes);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Approve attendance
     */
    public function approveAttendance($attendance_id, $approved_by) {
        $sql = "UPDATE attendance SET approved_by = :approved_by WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':approved_by', $approved_by, PDO::PARAM_INT);
            $stmt->bindParam(':id', $attendance_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get pending attendance approvals
     */
    public function getPendingApprovals($department_id = null) {
        $sql = "SELECT a.*, e.employee_code, u.first_name, u.last_name, u.email
                FROM attendance a
                LEFT JOIN employees e ON a.employee_id = e.id
                LEFT JOIN users u ON e.user_id = u.id
                WHERE a.approved_by IS NULL";
        
        if ($department_id) {
            $sql .= " AND e.department_id = :department_id";
        }
        
        $sql .= " ORDER BY a.attendance_date DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            if ($department_id) {
                $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get department attendance report
     */
    public function getDepartmentAttendanceReport($department_id, $date) {
        $sql = "SELECT a.*, e.employee_code, u.first_name, u.last_name, d.name as designation_name
                FROM attendance a
                LEFT JOIN employees e ON a.employee_id = e.id
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN designations d ON e.designation_id = d.id
                WHERE e.department_id = :department_id AND a.attendance_date = :date
                ORDER BY u.first_name, u.last_name";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
