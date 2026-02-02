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
include_once __DIR__ . '/../models/Category.php';

// Instantiate DB & connect
$database = Database::getInstance();
$db = $database->getConnection();

// Initialize category object
$category = new Category($db);

// Get the HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

switch ($method) {
    case 'GET':
        $stmt = $category->read();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $categories_arr = array();
            $categories_arr['status'] = 'success';
            $categories_arr['data'] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $category_item = array(
                    'id' => $id,
                    'name' => $name,
                    'slug' => $slug,
                    'parent_id' => $parent_id,
                    'parent_name' => $parent_name,
                    'image_url' => $image_url
                );
                array_push($categories_arr['data'], $category_item);
            }
            http_response_code(200);
            echo json_encode($categories_arr);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'No categories found.']);
        }
        break;

    case 'POST':
        if (empty($data->name) || empty($data->slug)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Category name and slug are required.']);
            break;
        }

        $category->name = $data->name;
        $category->slug = $data->slug;
        $category->parent_id = $data->parent_id ?? null;
        $category->image_url = $data->image_url ?? '';

        if ($category->create()) {
                        http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Category created.', 'data' => ['id' => $category->id]]);
        } else {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create category.']);
        }
        break;

    case 'PUT':
        if (empty($data->id) || empty($data->name) || empty($data->slug)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Category ID, name, and slug are required.']);
            break;
        }

        $category->id = $data->id;
        $category->name = $data->name;
        $category->slug = $data->slug;
        $category->parent_id = $data->parent_id ?? null;
        $category->image_url = $data->image_url ?? '';

        if ($category->update()) {
                        http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Category updated.']);
        } else {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update category.']);
        }
        break;

    case 'DELETE':
        if (empty($data->id)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Category ID is required.']);
            break;
        }

        $category->id = $data->id;

        if ($category->delete()) {
                        http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Category deleted.']);
        } else {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete category.']);
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
        break;
}
