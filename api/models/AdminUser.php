<?php
class AdminUser {
    private $conn;
    private $table_name = 'admin_users';

    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $full_name;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Find user by username
    public function read() {
        $query = 'SELECT id, username, email, full_name, role FROM ' . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function findByUsername($username) {
        $query = 'SELECT * FROM ' . $this->table_name . ' WHERE username = :username LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->full_name = $row['full_name'];
            $this->role = $row['role'];
            return true;
        }

        return false;
    }

    public function create() {
        $query = 'INSERT INTO ' . $this->table_name . ' (username, email, password_hash, full_name, role) VALUES (:username, :email, :password, :full_name, :role)';
        $stmt = $this->conn->prepare($query);
        $this->sanitize();
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password_hash);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':role', $this->role);
        return $stmt->execute();
    }

    public function update() {
        $password_part = !empty($this->password_hash) ? ', password_hash = :password' : '';
        $query = 'UPDATE ' . $this->table_name . ' SET username = :username, email = :email, full_name = :full_name, role = :role' . $password_part . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $this->sanitize();
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':role', $this->role);
        if (!empty($this->password_hash)) {
            $stmt->bindParam(':password', $this->password_hash);
        }
        return $stmt->execute();
    }

    public function delete() {
        $query = 'DELETE FROM ' . $this->table_name . ' WHERE id = :id AND id != 1'; // Prevent deleting super admin
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    private function sanitize() {
        if ($this->id) $this->id = htmlspecialchars(strip_tags($this->id));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->role = htmlspecialchars(strip_tags($this->role));
    }
}
