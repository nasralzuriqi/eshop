<?php
class Cart {
    private $conn;
    private $table_name = 'carts';

    public $id;
    public $user_id;
    public $session_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get or create a cart for a user
    public function getForUser($user_id) {
        $query = 'SELECT id FROM ' . $this->table_name . ' WHERE user_id = :user_id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            return $this->id;
        } else {
            $createQuery = 'INSERT INTO ' . $this->table_name . ' SET user_id = :user_id';
            $createStmt = $this->conn->prepare($createQuery);
            $createStmt->bindParam(':user_id', $user_id);
            if ($createStmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return $this->id;
            }
            return null;
        }
    }
}
