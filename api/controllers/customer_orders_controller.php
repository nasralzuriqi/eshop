<?php
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to view your orders.']);
    exit();
}

include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../models/Order.php';
include_once __DIR__ . '/../models/OrderItem.php';

$database = Database::getInstance();
$db = $database->getConnection();

$order = new Order($db);
$order_item = new OrderItem($db);

$order->user_id = $_SESSION['user_id'];
$stmt = $order->readByUserId();
$num = $stmt->rowCount();

if ($num > 0) {
    $orders_arr = [];
    $orders_arr['data'] = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $order_item->order_id = $id;
        $items_stmt = $order_item->readByOrderId();
        $items_arr = [];
        while ($item_row = $items_stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($items_arr, $item_row);
        }

        $order_data = [
            'id' => $id,
            'total_amount' => $total_amount,
            'status' => $status,
            'created_at' => $created_at,
            'items' => $items_arr
        ];

        array_push($orders_arr['data'], $order_data);
    }
    $orders_arr['status'] = 'success';
    http_response_code(200);
    echo json_encode($orders_arr);
} else {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'data' => [], 'message' => 'No orders found.']);
}
