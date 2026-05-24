<?php
/**
 * Employee Model
 * Handles employee profile and information management
 */

class EmployeeModel {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create new employee record
     */
    public function createEmployee($data) {
        $sql = "INSERT INTO employees (user_id, employee_code, designation_id, department_id, manager_id, 
                date_of_birth, phone, address, city, state, postal_code, country, join_date, contract_type)
                VALUES (:user_id, :employee_code, :designation_id, :department_id, :manager_id, 
                :date_of_birth, :phone, :address, :city, :state, :postal_code, :country, :join_date, :contract_type)";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':employee_code', $data['employee_code']);
            $stmt->bindParam(':designation_id', $data['designation_id']);
            $stmt->bindParam(':department_id', $data['department_id']);
            $stmt->bindParam(':manager_id', $data['manager_id'] ?? null);
            $stmt->bindParam(':date_of_birth', $data['date_of_birth'] ?? null);
            $stmt->bindParam(':phone', $data['phone'] ?? null);
            $stmt->bindParam(':address', $data['address'] ?? null);
            $stmt->bindParam(':city', $data['city'] ?? null);
            $stmt->bindParam(':state', $data['state'] ?? null);
            $stmt->bindParam(':postal_code', $data['postal_code'] ?? null);
            $stmt->bindParam(':country', $data['country'] ?? null);
            $stmt->bindParam(':join_date', $data['join_date']);
            $stmt->bindParam(':contract_type', $data['contract_type'] ?? 'permanent');
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get employee by ID
     */
    public function getEmployeeById($id) {
        $sql = "SELECT e.*, u.email, u.first_name, u.last_name, u.role,
                d.name as designation_name, dept.name as department_name,
                m.first_name as manager_first_name, m.last_name as manager_last_name
                FROM employees e
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN designations d ON e.designation_id = d.id
                LEFT JOIN departments dept ON e.department_id = dept.id
                LEFT JOIN employees m ON e.manager_id = m.id
                WHERE e.id = :id";
        
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
     * Get employee by user ID
     */
    public function getEmployeeByUserId($user_id) {
        $sql = "SELECT e.*, u.email, u.first_name, u.last_name, u.role,
                d.name as designation_name, dept.name as department_name
                FROM employees e
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN designations d ON e.designation_id = d.id
                LEFT JOIN departments dept ON e.department_id = dept.id
                WHERE e.user_id = :user_id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all employees with filters
     */
    public function getAllEmployees($filters = [], $page = 1, $limit = 20) {
        $sql = "SELECT e.*, u.email, u.first_name, u.last_name,
                d.name as designation_name, dept.name as department_name
                FROM employees e
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN designations d ON e.designation_id = d.id
                LEFT JOIN departments dept ON e.department_id = dept.id
                WHERE 1=1";
        
        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = :department_id";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND e.employment_status = :status";
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (u.email LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search OR e.employee_code LIKE :search)";
        }
        
        $sql .= " ORDER BY e.join_date DESC LIMIT :offset, :limit";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            if (!empty($filters['department_id'])) {
                $stmt->bindParam(':department_id', $filters['department_id'], PDO::PARAM_INT);
            }
            if (!empty($filters['status'])) {
                $stmt->bindParam(':status', $filters['status']);
            }
            if (!empty($filters['search'])) {
                $search = '%' . $filters['search'] . '%';
                $stmt->bindParam(':search', $search);
            }
            
            $offset = ($page - 1) * $limit;
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Update employee information
     */
    public function updateEmployee($id, $data) {
        $fields = [];
        $allowedFields = ['designation_id', 'department_id', 'manager_id', 'date_of_birth', 'phone', 
                         'alternate_phone', 'address', 'city', 'state', 'postal_code', 'country',
                         'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
                         'bank_name', 'bank_account_number', 'ifsc_code', 'pan_number', 'aadhar_number', 'employment_status'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = :$key";
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE employees SET " . implode(", ", $fields) . " WHERE id = :id";
        
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
     * Get employees by department
     */
    public function getEmployeesByDepartment($department_id) {
        $sql = "SELECT e.*, u.email, u.first_name, u.last_name, d.name as designation_name
                FROM employees e
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN designations d ON e.designation_id = d.id
                WHERE e.department_id = :department_id AND e.employment_status = 'active'
                ORDER BY e.employee_code";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get team members under a manager
     */
    public function getTeamMembers($manager_employee_id) {
        $sql = "SELECT e.*, u.email, u.first_name, u.last_name, d.name as designation_name
                FROM employees e
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN designations d ON e.designation_id = d.id
                WHERE e.manager_id = :manager_id AND e.employment_status = 'active'
                ORDER BY u.first_name, u.last_name";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':manager_id', $manager_employee_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Count total employees
     */
    public function countEmployees($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM employees WHERE 1=1";
        
        if (!empty($filters['department_id'])) {
            $sql .= " AND department_id = :department_id";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND employment_status = :status";
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            
            if (!empty($filters['department_id'])) {
                $stmt->bindParam(':department_id', $filters['department_id']);
            }
            if (!empty($filters['status'])) {
                $stmt->bindParam(':status', $filters['status']);
            }
            
            $stmt->execute();
            $result = $stmt->fetch();
            
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }
}
