<?php

namespace App\Models;

class User {
    protected $table = 'users';
    protected $id;
    protected $email;
    protected $password;
    protected $role;
    protected $status;
    protected $last_login;
    protected $login_attempts;
    protected $locked_until;
    protected $db;

    public function __construct($db = null) {
        $this->db = $db;
    }

    /**
     * Create a new user
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                  (email, password, role, status, created_at)
                  VALUES
                  (:email, :password, :role, :status, NOW())";

        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', password_hash($data['password'], PASSWORD_BCRYPT));
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':status', $data['status']);

        return $stmt->execute();
    }

    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Find user by ID
     */
    public function findById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Verify password
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Update last login
     */
    public function updateLastLogin($id) {
        $query = "UPDATE " . $this->table . "
                  SET last_login = NOW(), login_attempts = 0
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    /**
     * Increment login attempts
     */
    public function incrementLoginAttempts($email) {
        $query = "UPDATE " . $this->table . "
                  SET login_attempts = login_attempts + 1
                  WHERE email = :email";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);

        return $stmt->execute();
    }

    /**
     * Lock user account
     */
    public function lockAccount($email, $minutes = 15) {
        $query = "UPDATE " . $this->table . "
                  SET locked_until = DATE_ADD(NOW(), INTERVAL :minutes MINUTE)
                  WHERE email = :email";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':minutes', $minutes, \PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Check if account is locked
     */
    public function isAccountLocked($email) {
        $query = "SELECT locked_until FROM " . $this->table . "
                  WHERE email = :email AND locked_until > NOW()";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
