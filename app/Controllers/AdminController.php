<?php

namespace App\Controllers;

use App\Models\Department;
use App\Models\Employee;

class AdminController {
    protected $db;
    protected $department_model;
    protected $employee_model;

    public function __construct($db) {
        $this->db = $db;
        $this->department_model = new Department($db);
        $this->employee_model = new Employee($db);
    }

    /**
     * Create department
     */
    public function createDepartment($data) {
        try {
            $result = $this->department_model->create($data);

            if ($result) {
                return ['success' => true, 'message' => 'Department created successfully'];
            }

            return ['success' => false, 'message' => 'Failed to create department'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get all departments
     */
    public function getDepartments() {
        try {
            $departments = $this->department_model->getAll();
            return ['success' => true, 'data' => $departments];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update department
     */
    public function updateDepartment($id, $data) {
        try {
            $result = $this->department_model->update($id, $data);

            if ($result) {
                return ['success' => true, 'message' => 'Department updated successfully'];
            }

            return ['success' => false, 'message' => 'Failed to update department'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete department
     */
    public function deleteDepartment($id) {
        try {
            $result = $this->department_model->delete($id);

            if ($result) {
                return ['success' => true, 'message' => 'Department deleted successfully'];
            }

            return ['success' => false, 'message' => 'Failed to delete department'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        try {
            $stats = [];

            // Total employees
            $query = "SELECT COUNT(*) as total FROM employees WHERE status = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['total_employees'] = $stmt->fetch()['total'];

            // Total departments
            $query = "SELECT COUNT(*) as total FROM departments WHERE status = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['total_departments'] = $stmt->fetch()['total'];

            // Present today
            $query = "SELECT COUNT(DISTINCT employee_id) as total FROM attendance 
                      WHERE attendance_date = CURDATE() AND status = 'Present'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['present_today'] = $stmt->fetch()['total'];

            // Absent today
            $query = "SELECT COUNT(DISTINCT employee_id) as total FROM attendance 
                      WHERE attendance_date = CURDATE() AND status = 'Absent'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['absent_today'] = $stmt->fetch()['total'];

            // Pending leave requests
            $query = "SELECT COUNT(*) as total FROM leaves WHERE status = 'Pending'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['pending_leaves'] = $stmt->fetch()['total'];

            return ['success' => true, 'data' => $stats];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
