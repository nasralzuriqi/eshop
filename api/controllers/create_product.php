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

// Get posted data
// The frontend will send a multipart/form-data request
$data = $_POST;
$files = $_FILES;

// --- VALIDATION (A basic example, expand as needed) ---
if (empty($data['name']) || empty($data['price']) || empty($data['sku'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Incomplete data. Name, Price, and SKU are required.']);
    exit();
}

// --- START TRANSACTION ---
$db->beginTransaction();

try {
    // Define the absolute upload path once for reliability
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/eshop/uploads/';

    // Ensure the upload directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // 1. SET MAIN PRODUCT DATA
    $product->name = $data['name'];
    $product->sku = $data['sku'];
    $product->price = $data['price'];
    $product->description = $data['description'] ?? '';
    $product->brand_id = !empty($data['brand_id']) ? $data['brand_id'] : null;
    $product->category_id = !empty($data['category_id']) ? $data['category_id'] : null;
    $product->stock_quantity = $data['stock_quantity'] ?? 0;
    $product->is_active = $data['is_active'] ?? 1;
    $product->product_type = $data['product_type'] ?? 'original';
    $product->linked_product_id = !empty($data['linked_product_id']) ? $data['linked_product_id'] : null;
    $product->discount_price = !empty($data['discount_price']) ? $data['discount_price'] : null;

    // 2. HANDLE MAIN IMAGE UPLOAD
    $product->main_image_url = ''; // Default to empty
    if (isset($files['main_image']) && $files['main_image']['error'] == UPLOAD_ERR_OK) {
        $file_name = uniqid('prod_') . '_' . basename($files['main_image']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($files['main_image']['tmp_name'], $target_file)) {
            $product->main_image_url = 'uploads/' . $file_name;
        }
    }

    // 3. CREATE THE PRODUCT to get the ID
    if (!$product->create()) {
        throw new Exception('Unable to create product.');
    }

    $product_id = $product->id;

    // 4. HANDLE PRODUCT ATTRIBUTES
    if (isset($data['attributes']) && is_array($data['attributes'])) {
        $attr_query = 'INSERT INTO product_attributes (product_id, attribute_key, attribute_value) VALUES (:product_id, :key, :value)';
        $attr_stmt = $db->prepare($attr_query);
        foreach ($data['attributes'] as $attribute) {
            if (!empty($attribute['key']) && !empty($attribute['value'])) {
                $attr_stmt->execute([
                    ':product_id' => $product_id,
                    ':key' => htmlspecialchars(strip_tags($attribute['key'])),
                    ':value' => htmlspecialchars(strip_tags($attribute['value']))
                ]);
            }
        }
    }

    // 5. HANDLE GALLERY IMAGES
    if (isset($files['gallery_images'])) {
        $img_query = 'INSERT INTO product_images (product_id, image_url, alt_text) VALUES (:product_id, :url, :alt)';
        $img_stmt = $db->prepare($img_query);
        
        foreach ($files['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if ($files['gallery_images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = uniqid('gallery_') . '_' . basename($files['gallery_images']['name'][$key]);
                $target_file = $upload_dir . $file_name;
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $image_path = 'uploads/' . $file_name;
                    $img_stmt->execute([
                        ':product_id' => $product_id,
                        ':url' => $image_path,
                        ':alt' => htmlspecialchars(strip_tags($data['name'])) // Use product name as default alt text
                    ]);
                }
            }
        }
    }

    // If everything is successful, commit the transaction
        $db->commit();

    http_response_code(201); // Created
    echo json_encode(['status' => 'success', 'message' => 'Product created successfully.', 'data' => ['product_id' => $product_id]]);

} catch (Exception $e) {
    // If any part fails, roll back the transaction
    $db->rollBack();

    http_response_code(503); // Service Unavailable
    echo json_encode(['status' => 'error', 'message' => 'Product creation failed: ' . $e->getMessage()]);
}
