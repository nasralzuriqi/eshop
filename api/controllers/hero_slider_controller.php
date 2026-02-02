<?php
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../models/HeroSlider.php';

$database = Database::getInstance();
$db = $database->getConnection();
$slider = new HeroSlider($db);
$method = $_SERVER['REQUEST_METHOD'];

// Public vs Admin access
if ($method === 'GET' && (!isset($_GET['context']) || $_GET['context'] !== 'admin')) {
    // Public: Get active sliders
    $stmt = $slider->readActive();
    $sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $sliders]);
    exit();
}

// --- Admin-only actions below ---
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Admin access required.']);
    exit();
}

switch ($method) {
    case 'GET':
        // Admin: Get all sliders
        $stmt = $slider->read();
        $sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $sliders]);
        break;

    case 'POST': // Handles both create and update
        $data = $_POST;
        $slider->id = $data['id'] ?? null;
        $slider->title = $data['title'];
        $slider->subtitle = $data['subtitle'];
        $slider->btn_text = $data['btn_text'];
        $slider->btn_link = $data['btn_link'];
        $slider->sort_order = $data['sort_order'] ?? 0;
        $slider->is_active = isset($data['is_active']) && $data['is_active'] !== 'false' ? 1 : 0;

        // Handle file upload
        if (isset($_FILES['image'])) {
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/eshop/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $file_name = uniqid('slider_') . '_' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $file_name)) {
                $slider->image_url = 'uploads/' . $file_name;
            }
        } else if ($slider->id) {
            // Keep old image on update if no new one is sent
            $temp_slider = new HeroSlider($db);
            $temp_slider->id = $slider->id;
            $temp_slider->readOne(); // You'll need to create readOne method
            $slider->image_url = $temp_slider->image_url;
        }

        if ($slider->id) {
            $result = $slider->update();
            $message = 'Slider updated successfully.';
        } else {
            $result = $slider->create();
            $message = 'Slider created successfully.';
        }

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => $message]);
        } else {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => 'Failed to save slider.']);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        $slider->id = $data->id;
        if ($slider->delete()) {
            echo json_encode(['status' => 'success', 'message' => 'Slider deleted.']);
        } else {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete slider.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        break;
}
