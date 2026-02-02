<?php
class Category {
    private $conn;
    private $table_name = 'categories';

    public $id;
    public $name;
    public $slug;
    public $parent_id;
    public $image_url;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all categories
    public function read() {
        $query = 'SELECT c1.*, c2.name as parent_name FROM ' . $this->table_name . ' c1 LEFT JOIN ' . $this->table_name . ' c2 ON c1.parent_id = c2.id ORDER BY c1.name ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create category
    public function create() {
        $query = 'INSERT INTO ' . $this->table_name . ' SET name=:name, slug=:slug, parent_id=:parent_id, image_url=:image_url';
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':parent_id', $this->parent_id);
        $stmt->bindParam(':image_url', $this->image_url);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Update category
    public function update() {
        $query = 'UPDATE ' . $this->table_name . ' SET name=:name, slug=:slug, parent_id=:parent_id, image_url=:image_url WHERE id=:id';
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':parent_id', $this->parent_id);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete category
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
