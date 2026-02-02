<?php
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../models/ShopSetting.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Admin access required.']);
    exit();
}

$database = Database::getInstance();
$db = $database->getConnection();
$setting = new ShopSetting($db);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            $stmt = $setting->read();
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$settings) {
                // If no settings exist, return default values
                $settings = [
                    'id' => 1,
                    'site_name' => 'My Perfume Shop',
                    'phone_number' => '',
                    'email' => '',
                    'address' => '',
                    'facebook_url' => '',
                    'instagram_url' => '',
                    'tiktok_url' => '',
                    'whatsapp_number' => '',
                    'currency_symbol' => '$',
                    'shipping_cost' => '0.00',
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            
            echo json_encode(['status' => 'success', 'data' => $settings]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to fetch settings.']);
        }
        break;

    case 'POST':
        header('Content-Type: application/json');
        
        try {
            // Get raw POST data
            $rawData = file_get_contents("php://input");
            $data = json_decode($rawData, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data');
            }
            
            // Ensure we have the required fields
            if (empty($data['site_name']) || empty($data['email'])) {
                throw new Exception('Site name and email are required');
            }
            
            // Update settings
            if ($setting->update($data)) {
                                
                // Get the updated settings to return
                $stmt = $setting->read();
                $updatedSettings = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Ensure all expected fields exist in the response
                $defaults = [
                    'site_name' => '',
                    'email' => '',
                    'phone_number' => '',
                    'address' => '',
                    'facebook_url' => '',
                    'instagram_url' => '',
                    'tiktok_url' => '',
                    'whatsapp_number' => '',
                    'currency_symbol' => '$',
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $responseData = array_merge($defaults, array_intersect_key($updatedSettings ?: [], $defaults));
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Settings updated successfully.',
                    'data' => $responseData
                ]);
            } else {
                throw new Exception('Failed to update settings');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error', 
                'message' => $e->getMessage(),
                'data' => null
            ]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        break;
}
