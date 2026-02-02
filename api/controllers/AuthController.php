<?php
// Include database and object files
include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../models/User.php';
include_once __DIR__ . '/../models/AdminUser.php'; // This model will be created next

class AuthController {
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    // Customer Login
    public function customerLogin($data) {
        $user = new User($this->db);
        $user->email = $data->email;

        if ($user->findByEmail() && password_verify($data->password, $user->password_hash)) {
            // Session is already started in routes.php
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_role'] = 'customer';

            return ['status' => 'success', 'message' => 'Customer login successful.', 'data' => ['id' => $user->id, 'full_name' => $user->full_name, 'email' => $user->email]];
        } else {
            return ['status' => 'error', 'message' => 'Invalid credentials.'];
        }
    }

    // Admin Login
    public function adminLogin($data) {
        $admin = new AdminUser($this->db);

        if ($admin->findByUsername($data->username) && password_verify($data->password, $admin->password_hash)) {
            // Session is already started in routes.php
            $_SESSION['admin_id'] = $admin->id;
            $_SESSION['admin_role'] = $admin->role;

            return ['status' => 'success', 'message' => 'Admin login successful.', 'data' => ['id' => $admin->id, 'username' => $admin->username, 'role' => $admin->role]];
        } else {
            return ['status' => 'error', 'message' => 'Invalid admin credentials.'];
        }
    }
}
