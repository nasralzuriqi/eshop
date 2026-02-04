<?php
// Required headers
header("Content-Type: application/json; charset=UTF-8");

// Include database and object files
include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../models/Product.php';

// Instantiate DB & connect
$database = Database::getInstance();
$db = $database->getConnection();

// Initialize product object
$product = new Product($db);

// Get ID from URL
$product->id = isset($_GET['id']) ? $_GET['id'] : die();

// Read the details of the product to be edited
$product_details = $product->readOne();

if ($product_details) {
    // The readOne method now returns the complete data array.
    $product_arr = [
        'status' => 'success',
        'data' => $product_details
    ];

    // Set response code - 200 OK
    http_response_code(200);

    // Make it json format
    echo json_encode($product_arr);
} else {
    // Set response code - 404 Not found
    http_response_code(404);

    // Tell the user product does not exist
    echo json_encode(array('status' => 'error', 'message' => 'Product not found.'));
}
