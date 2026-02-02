<?php
class OrderItem {
    private $conn;
    private $table_name = 'order_items';

    public $id;
    public $order_id;
    public $product_id;
    public $product_name;
    public $price;
    public $quantity;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readByOrderId() {
        $query = 'SELECT oi.quantity, oi.price, p.name as product_name, p.main_image_url FROM ' . $this->table_name . ' oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :order_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $this->order_id);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = 'INSERT INTO ' . $this->table_name . ' SET order_id=:order_id, product_id=:product_id, product_name=:product_name, price=:price, quantity=:quantity';
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->product_name = htmlspecialchars(strip_tags($this->product_name));

        // Bind
        $stmt->bindParam(':order_id', $this->order_id);
        $stmt->bindParam(':product_id', $this->product_id);
        $stmt->bindParam(':product_name', $this->product_name);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':quantity', $this->quantity);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
