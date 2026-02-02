<?php

include_once __DIR__ . '/../../config/Database.php';
include_once __DIR__ . '/../models/Order.php';
include_once __DIR__ . '/../models/OrderItem.php';
include_once __DIR__ . '/../models/Cart.php';
include_once __DIR__ . '/../models/CartItem.php';

class OrderController {
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    public function createOrder($data) {
        if (!isset($_SESSION['user_id'])) {
            return ['status' => 'error', 'message' => 'User not logged in.'];
        }

        $user_id = $_SESSION['user_id'];

        $cart = new Cart($this->db);
        $cart_id = $cart->getForUser($user_id);

        if (!$cart_id) {
            return ['status' => 'error', 'message' => 'No cart found for this user.'];
        }

        $cart_item = new CartItem($this->db);
        $cart_item->cart_id = $cart_id;
        $stmt = $cart_item->readByCart();
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($cart_items) === 0) {
            return ['status' => 'error', 'message' => 'Cart is empty.'];
        }

        $this->db->beginTransaction();

        try {
            // Calculate total amount
            $total_amount = 0;
            foreach ($cart_items as $item) {
                $total_amount += $item['price'] * $item['quantity'];
            }

            // Create the order
            $order = new Order($this->db);
            $order->user_id = $user_id;
            $order->total_amount = $total_amount;
            $order->status = 'pending';
            $order->shipping_name = $data->shipping_name;
            $order->shipping_address = $data->shipping_address;
            $order->shipping_phone = $data->shipping_phone;
            $order->payment_method = $data->payment_method ?? 'cash_on_delivery';

            if (!$order->create()) {
                throw new Exception('Failed to create order.');
            }

            // Move cart items to order items
            $order_item_model = new OrderItem($this->db);
            foreach ($cart_items as $item) {
                $order_item_model->order_id = $order->id;
                $order_item_model->product_id = $item['product_id'];
                $order_item_model->product_name = $item['product_name'];
                $order_item_model->price = $item['price'];
                $order_item_model->quantity = $item['quantity'];
                if (!$order_item_model->create()) {
                    throw new Exception('Failed to create order item.');
                }
            }

            // Clear the cart
            $cart_item->clearCart();

            $this->db->commit();

            return ['status' => 'success', 'message' => 'Order created successfully.', 'data' => ['order_id' => $order->id]];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function readAll() {
        if (!isset($_SESSION['admin_id'])) {
            return ['status' => 'error', 'message' => 'Admin access required.'];
        }

        $order = new Order($this->db);
        $stmt = $order->read();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $orders_arr = [];
            $orders_arr['data'] = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($orders_arr['data'], $row);
            }
            $orders_arr['status'] = 'success';
            return $orders_arr;
        } else {
            return ['status' => 'success', 'data' => [], 'message' => 'No orders found.'];
        }
    }

    public function updateStatus($data) {
        if (!isset($_SESSION['admin_id'])) {
            return ['status' => 'error', 'message' => 'Admin access required.'];
        }

        if (empty($data->id) || empty($data->status)) {
            return ['status' => 'error', 'message' => 'Order ID and status are required.'];
        }

        $order = new Order($this->db);
        $order->id = $data->id;
        $order->status = $data->status;

        if ($order->updateStatus()) {
            return ['status' => 'success', 'message' => 'Order status updated.'];
        } else {
            return ['status' => 'error', 'message' => 'Unable to update order status.'];
        }
    }

    public function readOne($id) {
        if (!isset($_SESSION['admin_id'])) {
            return ['status' => 'error', 'message' => 'Admin access required.'];
        }

        $order = new Order($this->db);
        $order->id = $id;
        $stmt = $order->readOne();

        if ($stmt->rowCount() > 0) {
            $order_data = $stmt->fetch(PDO::FETCH_ASSOC);

            $order_item = new OrderItem($this->db);
            $order_item->order_id = $id;
            $items_stmt = $order_item->readByOrderId();
            $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

            $order_data['items'] = $items;

            return ['status' => 'success', 'data' => $order_data];
        } else {
            return ['status' => 'error', 'message' => 'Order not found.'];
        }
    }
}
