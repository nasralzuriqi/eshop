<?php
// Required headers
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/AuthController.php';

$auth = new AuthController();
$data = json_decode(file_get_contents("php://input"));

if (empty($data->email) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
    exit();
}

$result = $auth->customerLogin($data);

if ($result['status'] === 'success') {
    http_response_code(200);
} else {
    http_response_code(401); // Unauthorized
}

echo json_encode($result);
