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
    // Create array
    $product_arr = array(
        'status' => 'success',
        'data' => array(
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'description' => $product->description,
            'price' => $product->price,
            'discount_price' => $product->discount_price,
            'stock_quantity' => $product->stock_quantity,
            'is_active' => $product->is_active,
            'brand_id' => $product->brand_id,
            'category_id' => $product->category_id,
            'main_image_url' => $product->main_image_url,
            'product_type' => $product->product_type,
            'linked_product_id' => $product->linked_product_id,
            'category_name' => $product->category_name,
            'brand_name' => $product->brand_name,
            // Nested data
            'images' => $product->images,
            'attributes' => $product->attributes,
            'linked_product' => $product->linked_product
        )
    );

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
