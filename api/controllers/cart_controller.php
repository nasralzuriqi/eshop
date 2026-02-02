<?php
// Required headers
header("Content-Type: application/json; charset=UTF-8");


// Include database and object files
include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../models/Cart.php';
include_once __DIR__ . '/../models/CartItem.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to manage your cart.']);
    exit();
}

// Instantiate DB & connect
$database = Database::getInstance();
$db = $database->getConnection();

// Initialize objects
$cart = new Cart($db);
$cart_item = new CartItem($db);

// Get the user's cart ID
$cart_id = $cart->getForUser($_SESSION['user_id']);
if (!$cart_id) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Could not retrieve or create a cart for the user.']);
    exit();
}
$cart_item->cart_id = $cart_id;

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

switch ($method) {
    case 'GET': // Handles getting all items or just the count
        $action = $_GET['action'] ?? 'read'; // Default to reading the whole cart

        if ($action === 'get_cart_count') {
            $count = $cart_item->countByCart();
            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => ['count' => $count]]);
        } else { // 'read' action
            $stmt = $cart_item->readByCart();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $total = 0;
            foreach ($items as $item) {
                $total += $item['price'] * $item['quantity'];
            }
            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => ['items' => $items, 'total' => $total]]);
        }
        break;

    case 'POST': // Add an item to the cart
        if (empty($data->product_id) || empty($data->quantity)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Product ID and quantity are required.']);
            break;
        }
        $cart_item->product_id = $data->product_id;
        $cart_item->quantity = $data->quantity;
        if ($cart_item->create()) {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Item added to cart.']);
        } else {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => 'Unable to add item to cart.']);
        }
        break;

    case 'DELETE': // Remove an item from the cart
        $cart_item->id = $_GET['id'] ?? null;
        if (!$cart_item->id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Cart item ID is required.']);
            break;
        }
        if ($cart_item->delete()) {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Item removed from cart.']);
        } else {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => 'Unable to remove item from cart.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
        break;
}
