<?php
/**
 * User Model
 * Handles user database operations
 */

class UserModel {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create new user account
     */
    public function createUser($data) {
        $sql = "INSERT INTO users (email, password_hash, first_name, last_name, role, status) 
                VALUES (:email, :password, :first_name, :last_name, :role, :status)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
            
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':role', $data['role'] ?? 'employee');
            $stmt->bindParam(':status', $data['status'] ?? 'active');
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        
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
     * Verify password against hash
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Update user information
     */
    public function updateUser($id, $data) {
        $fields = [];
        $allowedFields = ['first_name', 'last_name', 'email', 'status', 'role', 'last_login'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = :$key";
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = :id";
        
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
     * Change user password
     */
    public function changePassword($id, $newPassword) {
        $sql = "UPDATE users SET password_hash = :password WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all users with filters
     */
    public function getAllUsers($filters = [], $page = 1, $limit = 20) {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
        if (!empty($filters['role'])) {
            $sql .= " AND role = :role";
            $params[':role'] = $filters['role'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT :offset, :limit";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindParam($key, $value);
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
     * Count total users
     */
    public function countUsers($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM users WHERE 1=1";
        
        if (!empty($filters['role'])) {
            $sql .= " AND role = :role";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            
            if (!empty($filters['role'])) {
                $stmt->bindParam(':role', $filters['role']);
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
    
    /**
     * Delete user
     */
    public function deleteUser($id) {
        $sql = "DELETE FROM users WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
