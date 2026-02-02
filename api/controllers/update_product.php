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
$data = $_POST;
unset($data['action'], $data['resource']); // Prevent routing params from being treated as columns
$files = $_FILES;

// Get the product ID from the posted data
$product->id = $data['id'] ?? null;

if (!$product->id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Product ID is required for update.']);
    exit();
}

// --- START TRANSACTION ---
$db->beginTransaction();

try {
    // Define the absolute upload path once for reliability
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/eshop/uploads/';
    // First, get the existing product to check the old image path
    $existing_product = new Product($db);
    $existing_product->id = $product->id;
    $existing_product->readOne();
    $current_main_image = $existing_product->main_image_url;

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

    // 2. HANDLE MAIN IMAGE UPLOAD (if a new one is provided)
    $product->main_image_url = $current_main_image; // Keep old image by default
    if (isset($files['main_image']) && $files['main_image']['error'] == UPLOAD_ERR_OK) {
        $file_name = uniqid('prod_') . '_' . basename($files['main_image']['name']);
        $target_file = $upload_dir . $file_name;
        // Attempt to move the new file first
        if (move_uploaded_file($files['main_image']['tmp_name'], $target_file)) {
            // If move is successful, delete the old image
            if ($current_main_image && file_exists($upload_dir . basename($current_main_image))) {
                unlink($upload_dir . basename($current_main_image));
            }
            // Set the new image URL for the database update
            $product->main_image_url = 'uploads/' . $file_name;
        }
    }

    // 3. UPDATE THE PRODUCT RECORD
    if (!$product->update()) {
        throw new Exception('Unable to update product core details.');
    }

    // 4. HANDLE PRODUCT ATTRIBUTES (Delete old ones, insert new ones)
    $delete_attr_query = 'DELETE FROM product_attributes WHERE product_id = :product_id';
    $del_attr_stmt = $db->prepare($delete_attr_query);
    $del_attr_stmt->bindParam(':product_id', $product->id);
    $del_attr_stmt->execute();

    if (isset($data['attributes']) && is_array($data['attributes'])) {
        $attr_query = 'INSERT INTO product_attributes (product_id, attribute_key, attribute_value) VALUES (:product_id, :key, :value)';
        $attr_stmt = $db->prepare($attr_query);
        foreach ($data['attributes'] as $attribute) {
            if (!empty($attribute['key']) && !empty($attribute['value'])) {
                $attr_stmt->execute([
                    ':product_id' => $product->id,
                    ':key' => htmlspecialchars(strip_tags($attribute['key'])),
                    ':value' => htmlspecialchars(strip_tags($attribute['value']))
                ]);
            }
        }
    }

    // 5. HANDLE NEW GALLERY IMAGES (does not delete old ones, just adds new ones)
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
                        ':product_id' => $product->id,
                        ':url' => $image_path,
                        ':alt' => htmlspecialchars(strip_tags($data['name']))
                    ]);
                }
            }
        }
    }

    // If everything is successful, commit the transaction
    $db->commit();

    // Log the activity only after the transaction is successfully committed
    
    http_response_code(200); // OK
    echo json_encode(['status' => 'success', 'message' => 'Product updated successfully.']);

} catch (Exception $e) {
    // If any part fails, roll back the transaction
    $db->rollBack();

    http_response_code(503); // Service Unavailable
    echo json_encode(['status' => 'error', 'message' => 'Product update failed: ' . $e->getMessage()]);
}
