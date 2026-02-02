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
include_once __DIR__ . '/../models/Brand.php';

// Instantiate DB & connect
$database = Database::getInstance();
$db = $database->getConnection();

// Initialize brand object
$brand = new Brand($db);

// Get the HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

switch ($method) {
    case 'GET':
        $stmt = $brand->read();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $brands_arr = array();
            $brands_arr['status'] = 'success';
            $brands_arr['data'] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $brand_item = array(
                    'id' => $id,
                    'name' => $name,
                    'logo_url' => $logo_url,
                    'description' => $description,
                    'website_url' => $website_url
                );
                array_push($brands_arr['data'], $brand_item);
            }
            http_response_code(200);
            echo json_encode($brands_arr);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'No brands found.']);
        }
        break;

    case 'POST':
        if (empty($data->name)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Brand name is required.']);
            break;
        }

        $brand->name = $data->name;
        $brand->logo_url = $data->logo_url ?? '';
        $brand->description = $data->description ?? '';
        $brand->website_url = $data->website_url ?? '';

        if ($brand->create()) {
                        http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Brand created.', 'data' => ['id' => $brand->id]]);
        } else {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create brand.']);
        }
        break;

    case 'PUT':
        if (empty($data->id) || empty($data->name)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Brand ID and name are required.']);
            break;
        }

        $brand->id = $data->id;
        $brand->name = $data->name;
        $brand->logo_url = $data->logo_url ?? '';
        $brand->description = $data->description ?? '';
        $brand->website_url = $data->website_url ?? '';

        if ($brand->update()) {
                        http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Brand updated.']);
        } else {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update brand.']);
        }
        break;

    case 'DELETE':
        if (empty($data->id)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Brand ID is required.']);
            break;
        }

        $brand->id = $data->id;

        if ($brand->delete()) {
                        http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Brand deleted.']);
        } else {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete brand.']);
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
        break;
}
