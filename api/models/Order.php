<?php
class Order {
    private $conn;
    private $table_name = 'orders';

    public $id;
    public $user_id;
    public $total_amount;
    public $status;
    public $shipping_name;
    public $shipping_address;
    public $shipping_phone;
    public $payment_method;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = 'INSERT INTO ' . $this->table_name . ' SET user_id=:user_id, total_amount=:total_amount, status=:status, shipping_name=:shipping_name, shipping_address=:shipping_address, shipping_phone=:shipping_phone, payment_method=:payment_method';
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->shipping_name = htmlspecialchars(strip_tags($this->shipping_name));
        $this->shipping_address = htmlspecialchars(strip_tags($this->shipping_address));
        $this->shipping_phone = htmlspecialchars(strip_tags($this->shipping_phone));

        // Bind
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':total_amount', $this->total_amount);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':shipping_name', $this->shipping_name);
        $stmt->bindParam(':shipping_address', $this->shipping_address);
        $stmt->bindParam(':shipping_phone', $this->shipping_phone);
        $stmt->bindParam(':payment_method', $this->payment_method);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Read all orders for admin
    public function read() {
        $query = 'SELECT o.id, o.total_amount, o.status, o.shipping_name, o.created_at, u.full_name as customer_name FROM ' . $this->table_name . ' o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read all orders for a specific user
    public function readOne() {
        $query = 'SELECT o.*, u.full_name as customer_name, u.email as customer_email FROM ' . $this->table_name . ' o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = :id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function readByUserId() {
        $query = 'SELECT * FROM ' . $this->table_name . ' WHERE user_id = :user_id ORDER BY created_at DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();
        return $stmt;
    }

    // Update order status
    public function updateStatus() {
        $query = 'UPDATE ' . $this->table_name . ' SET status = :status WHERE id = :id';
        $stmt = $this->conn->prepare($query);

        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
