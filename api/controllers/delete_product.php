<?php
// Required headers
header("Content-Type: application/json; charset=UTF-8");


// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Admin access required.']);
    exit();
}

// Include database and object files
include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../models/Product.php';

// Instantiate DB & connect
$database = Database::getInstance();
$db = $database->getConnection();

// Initialize product object
$product = new Product($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

// Set product ID to be deleted
if (!empty($data->id)) {
    $product->id = $data->id;

    // Delete the product
    if ($product->delete()) {
            // Set response code - 200 ok
        http_response_code(200);

        // Tell the user
        echo json_encode(array('status' => 'success', 'message' => 'Product was deleted.'));
    } else {
        // Set response code - 503 service unavailable
        http_response_code(503);

        // Tell the user
        echo json_encode(array('status' => 'error', 'message' => 'Unable to delete product.'));
    }
} else {
    // Set response code - 400 bad request
    http_response_code(400);

    // Tell the user
    echo json_encode(array('status' => 'error', 'message' => 'Unable to delete product. Product ID is missing.'));
}
