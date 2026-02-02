<?php
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../models/AdminUser.php';

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'super_admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Super admin access required.']);
    exit();
}

$database = Database::getInstance();
$db = $database->getConnection();
$user = new AdminUser($db);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $user->read();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $users]);
        break;

    case 'POST': // Handles create and update
        $data = $_POST;
        $user->id = $data['id'] ?? null;
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->full_name = $data['full_name'];
        $user->role = $data['role'];
        if (!empty($data['password'])) {
            $user->password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if ($user->id) {
            $result = $user->update();
                        $message = 'User updated.';
        } else {
            $result = $user->create();
                        $message = 'User created.';
        }

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => $message]);
        } else {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => 'Failed to save user.']);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        $user->id = $data->id;
        if ($user->delete()) {
                        echo json_encode(['status' => 'success', 'message' => 'User deleted.']);
        } else {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete user or user is protected.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        break;
}
