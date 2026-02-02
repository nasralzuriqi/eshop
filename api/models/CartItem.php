<?php
class CartItem {
    private $conn;
    private $table_name = 'cart_items';

    public $id;
    public $cart_id;
    public $product_id;
    public $quantity;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all items for a specific cart
    public function readByCart() {
        $query = 'SELECT ci.id, ci.quantity, p.id as product_id, p.name as product_name, p.price, p.main_image_url FROM ' . $this->table_name . ' ci JOIN products p ON ci.product_id = p.id WHERE ci.cart_id = :cart_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cart_id', $this->cart_id);
        $stmt->execute();
        return $stmt;
    }

    // Add item to cart
    public function create() {
        // First, check if the item already exists in the cart
        $checkQuery = 'SELECT id, quantity FROM ' . $this->table_name . ' WHERE cart_id = :cart_id AND product_id = :product_id LIMIT 1';
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':cart_id', $this->cart_id);
        $checkStmt->bindParam(':product_id', $this->product_id);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            // If it exists, update the quantity
            $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
            $new_quantity = $row['quantity'] + $this->quantity;
            $updateQuery = 'UPDATE ' . $this->table_name . ' SET quantity = :quantity WHERE id = :id';
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':quantity', $new_quantity);
            $updateStmt->bindParam(':id', $row['id']);
            return $updateStmt->execute();
        } else {
            // If not, insert a new record
            $query = 'INSERT INTO ' . $this->table_name . ' SET cart_id=:cart_id, product_id=:product_id, quantity=:quantity';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cart_id', $this->cart_id);
            $stmt->bindParam(':product_id', $this->product_id);
            $stmt->bindParam(':quantity', $this->quantity);
            return $stmt->execute();
        }
    }

    // Delete item from cart
    public function delete() {
        $query = 'DELETE FROM ' . $this->table_name . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    // Clear all items from a cart
    public function clearCart() {
        $query = 'DELETE FROM ' . $this->table_name . ' WHERE cart_id = :cart_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cart_id', $this->cart_id);
        return $stmt->execute();
    }

    // Count all items in the cart
    public function countByCart() {
        $query = 'SELECT SUM(quantity) as total_items FROM ' . $this->table_name . ' WHERE cart_id = :cart_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cart_id', $this->cart_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_items'] ?? 0;
    }
}
