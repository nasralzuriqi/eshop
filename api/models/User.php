<?php
class User {
    private $conn;
    private $table_name = 'users';

    public $id;
    public $full_name;
    public $email;
    public $password;
    public $phone;
    public $address;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new user
    public function create() {
        $query = 'INSERT INTO ' . $this->table_name . ' SET full_name=:full_name, email=:email, password_hash=:password_hash, phone=:phone, address=:address';
        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone ?? ''));
        $this->address = htmlspecialchars(strip_tags($this->address ?? ''));

        // Hash the password
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind data
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Check if email exists
    public function emailExists() {
        $query = 'SELECT id FROM ' . $this->table_name . ' WHERE email = :email LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        }

        return false;
    }

    // Find user by email for login
    public function findByEmail() {
        $query = 'SELECT * FROM ' . $this->table_name . ' WHERE email = :email LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->full_name = $row['full_name'];
            $this->password_hash = $row['password_hash'];
            $this->role = $row['role'];
            return true;
        }

        return false;
    }
    
    // Read all users (for admin)
    public function read() {
        $query = 'SELECT id, full_name, email, phone, address, created_at FROM ' . $this->table_name . ' ORDER BY created_at DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
