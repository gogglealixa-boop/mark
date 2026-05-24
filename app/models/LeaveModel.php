<?php
/**
 * Leave Request Model
 * Handles leave applications and approvals
 */

class LeaveModel {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Submit leave request
     */
    public function submitLeaveRequest($data) {
        $sql = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, no_of_days, reason)
                VALUES (:employee_id, :leave_type, :start_date, :end_date, :no_of_days, :reason)";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindParam(':employee_id', $data['employee_id']);
            $stmt->bindParam(':leave_type', $data['leave_type']);
            $stmt->bindParam(':start_date', $data['start_date']);
            $stmt->bindParam(':end_date', $data['end_date']);
            $stmt->bindParam(':no_of_days', $data['no_of_days']);
            $stmt->bindParam(':reason', $data['reason']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get leave request by ID
     */
    public function getLeaveRequestById($id) {
        $sql = "SELECT lr.*, e.employee_code, u.first_name, u.last_name, u.email,
                approver.first_name as approver_first_name, approver.last_name as approver_last_name
                FROM leave_requests lr
                LEFT JOIN employees e ON lr.employee_id = e.id
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN users approver ON lr.approved_by = approver.id
                WHERE lr.id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get employee leave requests
     */
    public function getEmployeeLeaveRequests($employee_id, $status = null) {
        $sql = "SELECT lr.*, e.employee_code, u.first_name, u.last_name
                FROM leave_requests lr
                LEFT JOIN employees e ON lr.employee_id = e.id
                LEFT JOIN users u ON e.user_id = u.id
                WHERE lr.employee_id = :employee_id";
        
        if ($status) {
            $sql .= " AND lr.status = :status";
        }
        
        $sql .= " ORDER BY lr.start_date DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            
            if ($status) {
                $stmt->bindParam(':status', $status);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get pending leave requests for approval
     */
    public function getPendingLeaveRequests($department_id = null) {
        $sql = "SELECT lr.*, e.employee_code, e.department_id, u.first_name, u.last_name, u.email, d.name as designation_name
                FROM leave_requests lr
                LEFT JOIN employees e ON lr.employee_id = e.id
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN designations d ON e.designation_id = d.id
                WHERE lr.status = 'pending'";
        
        if ($department_id) {
            $sql .= " AND e.department_id = :department_id";
        }
        
        $sql .= " ORDER BY lr.created_at ASC";
        
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
     * Approve leave request
     */
    public function approveLeaveRequest($id, $approved_by) {
        $sql = "UPDATE leave_requests SET status = 'approved', approved_by = :approved_by 
                WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':approved_by', $approved_by, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Reject leave request
     */
    public function rejectLeaveRequest($id, $approved_by, $reason) {
        $sql = "UPDATE leave_requests SET status = 'rejected', approved_by = :approved_by, 
                rejection_reason = :reason WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':approved_by', $approved_by, PDO::PARAM_INT);
            $stmt->bindParam(':reason', $reason);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get leave balance
     */
    public function getLeaveBalance($employee_id, $year = null) {
        if (!$year) {
            $year = date('Y');
        }
        
        $sql = "SELECT * FROM leave_balance WHERE employee_id = :employee_id AND leave_year = :year";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Initialize leave balance for employee
     */
    public function initializeLeaveBalance($employee_id, $year) {
        $sql = "INSERT INTO leave_balance (employee_id, leave_year, sick_leave, casual_leave, medical_leave, annual_leave)
                VALUES (:employee_id, :year, 5, 8, 3, 20)
                ON DUPLICATE KEY UPDATE leave_year = :year";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Deduct leave balance
     */
    public function deductLeaveBalance($employee_id, $leave_type, $days, $year) {
        $leaveColumn = $leave_type . '_leave';
        
        $sql = "UPDATE leave_balance SET $leaveColumn = $leaveColumn - :days 
                WHERE employee_id = :employee_id AND leave_year = :year";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
