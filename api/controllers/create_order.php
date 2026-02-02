<?php
// Required headers
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/OrderController.php';

$order_controller = new OrderController();
$data = json_decode(file_get_contents("php://input"));

if (empty($data->shipping_name) || empty($data->shipping_address)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Shipping name and address are required.']);
    exit();
}

$result = $order_controller->createOrder($data);

if ($result['status'] === 'success') {
    http_response_code(201);
} else {
    http_response_code(503);
}

echo json_encode($result);
