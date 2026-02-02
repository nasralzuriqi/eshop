<?php
class HeroSlider {
    private $conn;
    private $table_name = 'hero_sliders';

    public $id;
    public $image_url;
    public $title;
    public $subtitle;
    public $btn_text;
    public $btn_link;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read active sliders
    // Read all sliders for admin
    public function read() {
        $query = 'SELECT * FROM ' . $this->table_name . ' ORDER BY sort_order ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = 'SELECT * FROM ' . $this->table_name . ' WHERE id = :id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->title = $row['title'];
            $this->subtitle = $row['subtitle'];
            $this->image_url = $row['image_url'];
            $this->btn_text = $row['btn_text'];
            $this->btn_link = $row['btn_link'];
            $this->sort_order = $row['sort_order'];
            $this->is_active = $row['is_active'];
            return true;
        }
        return false;
    }

    public function readActive() {
        $query = 'SELECT * FROM ' . $this->table_name . ' WHERE is_active = 1 ORDER BY sort_order ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = 'INSERT INTO ' . $this->table_name . ' (title, subtitle, image_url, btn_text, btn_link, sort_order, is_active) VALUES (:title, :subtitle, :image_url, :btn_text, :btn_link, :sort_order, :is_active)';
        $stmt = $this->conn->prepare($query);
        $this->sanitize();
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':subtitle', $this->subtitle);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':btn_text', $this->btn_text);
        $stmt->bindParam(':btn_link', $this->btn_link);
        $stmt->bindParam(':sort_order', $this->sort_order);
        $stmt->bindParam(':is_active', $this->is_active);
        return $stmt->execute();
    }

    public function update() {
        $query = 'UPDATE ' . $this->table_name . ' SET title = :title, subtitle = :subtitle, image_url = :image_url, btn_text = :btn_text, btn_link = :btn_link, sort_order = :sort_order, is_active = :is_active WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $this->sanitize();
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':subtitle', $this->subtitle);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':btn_text', $this->btn_text);
        $stmt->bindParam(':btn_link', $this->btn_link);
        $stmt->bindParam(':sort_order', $this->sort_order);
        $stmt->bindParam(':is_active', $this->is_active);
        return $stmt->execute();
    }

    public function delete() {
        $query = 'DELETE FROM ' . $this->table_name . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    private function sanitize() {
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->subtitle = htmlspecialchars(strip_tags($this->subtitle));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->btn_text = htmlspecialchars(strip_tags($this->btn_text));
        $this->btn_link = htmlspecialchars(strip_tags($this->btn_link));
        $this->sort_order = htmlspecialchars(strip_tags($this->sort_order));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));
    }
}
