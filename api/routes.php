<?php
// Start session at the very beginning
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// This is a simple router to direct API requests.
// In a real-world application, a more robust routing library would be used.

// Get the requested resource and action from the URL
// e.g., /api/routes.php?resource=products&action=read
$resource = $_GET['resource'] ?? null;
$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;

// Define the base path for controllers
$controller_path = __DIR__ . '/controllers/';

// Route the request to the appropriate controller
switch ($resource) {
    case 'products':
        switch ($action) {
            case 'read':
                require $controller_path . 'read_products.php';
                break;
            case 'read_one':
                // The read_one_product.php script will get the 'id' from $_GET
                require $controller_path . 'read_one_product.php';
                break;
            case 'create':
                require $controller_path . 'create_product.php';
                break;
            case 'update':
                require $controller_path . 'update_product.php';
                break;
            case 'delete':
                require $controller_path . 'delete_product.php';
                break;
            default:
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Invalid action for products.']);
                break;
        }
        break;

    case 'brands':
        require $controller_path . 'brand_controller.php';
        break;

    case 'categories':
        require $controller_path . 'category_controller.php';
        break;

    case 'orders':
        require_once $controller_path . 'OrderController.php';
        $controller = new OrderController();
        $action = $_GET['action'] ?? 'readAll'; // Default action
        $id = $_GET['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'));
        $result = [];

        switch ($action) {
            case 'readAll':
                $result = $controller->readAll();
                break;
            case 'readOne':
                if ($id) {
                    $result = $controller->readOne($id);
                } else {
                    $result = ['status' => 'error', 'message' => 'Order ID is required.'];
                }
                break;
            case 'updateStatus':
                $result = $controller->updateStatus($data);
                break;
            case 'create':
                $result = $controller->createOrder($data);
                break;
            default:
                $result = ['status' => 'error', 'message' => 'Invalid order action.'];
                break;
        }
        
        http_response_code(isset($result['status']) && $result['status'] === 'error' ? 500 : 200);
        echo json_encode($result);
        break;

    case 'cart':
        require $controller_path . 'cart_controller.php';
        break;

    case 'customer_orders':
        require $controller_path . 'customer_orders_controller.php';
        break;

    case 'ui':
        switch ($action) {
            case 'hero_sliders':
                require $controller_path . 'hero_slider_controller.php';
                break;
            default:
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Invalid UI action.']);
                break;
        }
        break;

    case 'auth':
        switch ($action) {
            case 'customer_login':
                require $controller_path . 'customer_login.php';
                break;
            case 'admin_login':
                require $controller_path . 'admin_login.php';
                break;
            case 'check_status':
                require $controller_path . 'check_status.php';
                break;
            case 'logout':
                require $controller_path . 'logout.php';
                break;
            default:
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Invalid auth action.']);
                break;
        }
        break;

    case 'shop_settings':
        require $controller_path . 'shop_settings_controller.php';
        break;


    case 'admin_users':
        require $controller_path . 'admin_users_controller.php';
        break;

    case 'users':
        switch ($action) {
            case 'register':
                require $controller_path . 'customer_register.php';
                break;
            case 'read':
                require $controller_path . 'read_users.php';
                break;
            default:
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Invalid user action.']);
                break;
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Resource not found.']);
        break;
}
