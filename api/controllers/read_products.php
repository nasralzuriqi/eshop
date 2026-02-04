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

// Collect filter parameters from the request
$params = [
    'type' => $_GET['type'] ?? 'all',
    'category' => $_GET['category'] ?? 'all',
    'brand' => $_GET['brand'] ?? 'all',
    'search' => $_GET['search'] ?? '',
    'sort' => $_GET['sort'] ?? 'created_at_desc'
];

// Query products
$stmt = $product->read($params);
$num = $stmt->rowCount();

// Check if any products found
if ($num > 0) {
    // Products array
    $products_arr = array();
    $products_arr['status'] = 'success';
    $products_arr['data'] = array();

    // Retrieve our table contents
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $product_item = array(
            'id' => $id,
            'name' => $name,
            'sku' => $sku,
            'product_type' => $product_type,
            'price' => $price,
            'stock_quantity' => $stock_quantity,
            'is_active' => $is_active,
            'main_image_url' => $main_image_url,
            'category_id' => $category_id,
            'category_name' => $category_name,
            'brand_name' => $brand_name,
            'brand_id' => $brand_id,
            'created_at' => $created_at
        );
        array_push($products_arr['data'], $product_item);
    }

    // Set response code - 200 OK
    http_response_code(200);

    // Show products data in json format
    echo json_encode($products_arr);
} else {
    // Set response code - 404 Not found
    http_response_code(404);

    // Tell the user no products found
    echo json_encode(
        array('status' => 'error', 'message' => 'No products found.')
    );
}
