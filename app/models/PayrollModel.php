<?php
/**
 * Payroll Model
 * Handles payroll processing and salary calculations
 */

class PayrollModel {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create payroll record
     */
    public function createPayroll($data) {
        $sql = "INSERT INTO payroll (employee_id, payroll_month, base_salary, allowances, deductions, overtime_hours, overtime_amount, gross_salary, net_salary, tax, status)
                VALUES (:employee_id, :payroll_month, :base_salary, :allowances, :deductions, :overtime_hours, :overtime_amount, :gross_salary, :net_salary, :tax, 'draft')";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindParam(':employee_id', $data['employee_id']);
            $stmt->bindParam(':payroll_month', $data['payroll_month']);
            $stmt->bindParam(':base_salary', $data['base_salary']);
            $stmt->bindParam(':allowances', $data['allowances'] ?? 0);
            $stmt->bindParam(':deductions', $data['deductions'] ?? 0);
            $stmt->bindParam(':overtime_hours', $data['overtime_hours'] ?? 0);
            $stmt->bindParam(':overtime_amount', $data['overtime_amount'] ?? 0);
            $stmt->bindParam(':gross_salary', $data['gross_salary']);
            $stmt->bindParam(':net_salary', $data['net_salary']);
            $stmt->bindParam(':tax', $data['tax'] ?? 0);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get payroll record
     */
    public function getPayrollById($id) {
        $sql = "SELECT p.*, e.employee_code, u.first_name, u.last_name, u.email,
                d.name as designation_name, dept.name as department_name,
                processor.first_name as processor_first_name, processor.last_name as processor_last_name
                FROM payroll p
                LEFT JOIN employees e ON p.employee_id = e.id
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN designations d ON e.designation_id = d.id
                LEFT JOIN departments dept ON e.department_id = dept.id
                LEFT JOIN users processor ON p.processed_by = processor.id
                WHERE p.id = :id";
        
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
     * Get employee payroll history
     */
    public function getEmployeePayrollHistory($employee_id, $limit = 12) {
        $sql = "SELECT * FROM payroll 
                WHERE employee_id = :employee_id
                ORDER BY payroll_month DESC
                LIMIT :limit";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get payroll for specific month
     */
    public function getMonthPayroll($payroll_month, $status = null) {
        $sql = "SELECT p.*, e.employee_code, u.first_name, u.last_name, u.email,
                d.name as designation_name, dept.name as department_name
                FROM payroll p
                LEFT JOIN employees e ON p.employee_id = e.id
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN designations d ON e.designation_id = d.id
                LEFT JOIN departments dept ON e.department_id = dept.id
                WHERE DATE_FORMAT(p.payroll_month, '%Y-%m') = :payroll_month";
        
        if ($status) {
            $sql .= " AND p.status = :status";
        }
        
        $sql .= " ORDER BY e.employee_code ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':payroll_month', $payroll_month);
            
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
     * Update payroll
     */
    public function updatePayroll($id, $data) {
        $fields = [];
        $allowedFields = ['base_salary', 'allowances', 'deductions', 'overtime_hours', 'overtime_amount', 'gross_salary', 'net_salary', 'tax', 'status', 'notes'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = :$key";
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE payroll SET " . implode(", ", $fields) . " WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $stmt->bindParam(':' . $key, $data[$key]);
                }
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Approve payroll
     */
    public function approvePayroll($id, $processed_by) {
        $sql = "UPDATE payroll SET status = 'approved', processed_by = :processed_by WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':processed_by', $processed_by, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark payroll as paid
     */
    public function markPayrollAsPaid($id, $processed_by) {
        $sql = "UPDATE payroll SET status = 'paid', processed_by = :processed_by, payment_date = NOW() WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':processed_by', $processed_by, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Calculate payroll summary for department
     */
    public function getDepartmentPayrollSummary($department_id, $payroll_month) {
        $sql = "SELECT 
                COUNT(*) as total_employees,
                SUM(p.base_salary) as total_base_salary,
                SUM(p.allowances) as total_allowances,
                SUM(p.deductions) as total_deductions,
                SUM(p.gross_salary) as total_gross_salary,
                SUM(p.net_salary) as total_net_salary,
                SUM(p.tax) as total_tax
                FROM payroll p
                LEFT JOIN employees e ON p.employee_id = e.id
                WHERE e.department_id = :department_id 
                AND DATE_FORMAT(p.payroll_month, '%Y-%m') = :payroll_month";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
            $stmt->bindParam(':payroll_month', $payroll_month);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get pending payroll approvals
     */
    public function getPendingPayrollApprovals() {
        $sql = "SELECT p.*, e.employee_code, u.first_name, u.last_name, u.email,
                d.name as designation_name, dept.name as department_name
                FROM payroll p
                LEFT JOIN employees e ON p.employee_id = e.id
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN designations d ON e.designation_id = d.id
                LEFT JOIN departments dept ON e.department_id = dept.id
                WHERE p.status = 'pending'
                ORDER BY p.payroll_month DESC, e.employee_code ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
