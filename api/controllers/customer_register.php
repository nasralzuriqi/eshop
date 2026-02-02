<?php
// Required headers
header("Content-Type: application/json; charset=UTF-8");

// Include database and object files
include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../models/User.php';

// Instantiate DB & connect
$database = Database::getInstance();
$db = $database->getConnection();

// Initialize user object
$user = new User($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

// Basic validation
if (
    empty($data->full_name) ||
    empty($data->email) ||
    empty($data->password)
) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Incomplete data. Full name, email, and password are required.']);
    exit();
}

// Set user property values
$user->full_name = $data->full_name;
$user->email = $data->email;
$user->password = $data->password; // The model will hash this
$user->phone = $data->phone ?? null;
$user->address = $data->address ?? null;

// Check if email already exists
if ($user->emailExists()) {
    http_response_code(409); // Conflict
    echo json_encode(['status' => 'error', 'message' => 'This email address is already registered.']);
    exit();
}

// Create the user
if ($user->create()) {
    http_response_code(201);
    echo json_encode(['status' => 'success', 'message' => 'User was created successfully.']);
} else {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Unable to create user.']);
}
