<?php
class Brand {
    private $conn;
    private $table_name = 'brands';

    public $id;
    public $name;
    public $logo_url;
    public $description;
    public $website_url;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all brands
    public function read() {
        $query = 'SELECT * FROM ' . $this->table_name . ' ORDER BY name ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create brand
    public function create() {
        $query = 'INSERT INTO ' . $this->table_name . ' SET name=:name, logo_url=:logo_url, description=:description, website_url=:website_url';
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->logo_url = htmlspecialchars(strip_tags($this->logo_url));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->website_url = htmlspecialchars(strip_tags($this->website_url));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':logo_url', $this->logo_url);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':website_url', $this->website_url);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Update brand
    public function update() {
        $query = 'UPDATE ' . $this->table_name . ' SET name=:name, logo_url=:logo_url, description=:description, website_url=:website_url WHERE id=:id';
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->logo_url = htmlspecialchars(strip_tags($this->logo_url));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->website_url = htmlspecialchars(strip_tags($this->website_url));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':logo_url', $this->logo_url);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':website_url', $this->website_url);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete brand
    public function delete() {
        $query = 'DELETE FROM ' . $this->table_name . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
