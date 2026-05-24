<?php

namespace App\Models;

class Attendance {
    protected $table = 'attendance';
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Clock In
     */
    public function clockIn($employee_id, $check_in_time) {
        $attendance_date = date('Y-m-d');
        
        // Check if employee already clocked in today
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE employee_id = :employee_id AND attendance_date = :attendance_date";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->bindParam(':attendance_date', $attendance_date);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Already clocked in today'];
        }

        // Create attendance record
        $query = "INSERT INTO " . $this->table . "
                  (employee_id, attendance_date, check_in_time, status, created_at)
                  VALUES
                  (:employee_id, :attendance_date, :check_in_time, 'Present', NOW())";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->bindParam(':attendance_date', $attendance_date);
        $stmt->bindParam(':check_in_time', $check_in_time);

        return $stmt->execute();
    }

    /**
     * Clock Out
     */
    public function clockOut($employee_id, $check_out_time) {
        $attendance_date = date('Y-m-d');

        $query = "UPDATE " . $this->table . "
                  SET check_out_time = :check_out_time, updated_at = NOW()
                  WHERE employee_id = :employee_id AND attendance_date = :attendance_date";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->bindParam(':attendance_date', $attendance_date);
        $stmt->bindParam(':check_out_time', $check_out_time);

        return $stmt->execute();
    }

    /**
     * Get employee attendance records
     */
    public function getEmployeeAttendance($employee_id, $month = null, $year = null) {
        if (!$month) $month = date('m');
        if (!$year) $year = date('Y');

        $query = "SELECT * FROM " . $this->table . "
                  WHERE employee_id = :employee_id 
                  AND MONTH(attendance_date) = :month 
                  AND YEAR(attendance_date) = :year
                  ORDER BY attendance_date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':year', $year);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all attendance for approval
     */
    public function getPendingApprovals($department_id = null) {
        $query = "SELECT a.*, e.employee_id, e.first_name, e.last_name, d.name as department
                  FROM " . $this->table . " a
                  JOIN employees e ON a.employee_id = e.id
                  JOIN departments d ON e.department_id = d.id
                  WHERE a.approved_by IS NULL";

        if ($department_id) {
            $query .= " AND d.id = :department_id";
        }

        $query .= " ORDER BY a.attendance_date DESC";

        $stmt = $this->db->prepare($query);
        
        if ($department_id) {
            $stmt->bindParam(':department_id', $department_id);
        }

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Approve attendance
     */
    public function approve($attendance_id, $approved_by) {
        $query = "UPDATE " . $this->table . "
                  SET approved_by = :approved_by, approval_date = NOW(), updated_at = NOW()
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $attendance_id);
        $stmt->bindParam(':approved_by', $approved_by);

        return $stmt->execute();
    }
}
