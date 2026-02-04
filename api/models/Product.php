<?php

class Product {
    // Database connection and table name
    private $conn;
    private $table_name = 'products';

    // Product Properties
    public $id;
    public $name;
    public $sku;
    public $product_type;
    public $linked_product_id;
    public $description;
    public $price;
    public $discount_price;
    public $stock_quantity;
    public $is_active;
    public $brand_id;
    public $category_id;
    public $main_image_url;
    public $action; // To hold the action from the request, not a DB column

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all products
    public function read($params = []) {
        $query = 'SELECT
            c.name as category_name,
            b.name as brand_name,
            p.id, p.category_id, p.brand_id, p.name, p.sku, p.product_type, p.description,
            p.price, p.discount_price, p.stock_quantity, p.is_active, p.main_image_url, p.created_at
        FROM ' . $this->table_name . ' p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN brands b ON p.brand_id = b.id';

        $where_clauses = [];
        $bindings = [];

        if (!empty($params['type']) && $params['type'] !== 'all') {
            $where_clauses[] = 'p.product_type = :type';
            $bindings[':type'] = $params['type'];
        }

        if (!empty($params['category']) && $params['category'] !== 'all') {
            $where_clauses[] = 'p.category_id = :category';
            $bindings[':category'] = $params['category'];
        }

        if (!empty($params['brand']) && $params['brand'] !== 'all') {
            $where_clauses[] = 'p.brand_id = :brand';
            $bindings[':brand'] = $params['brand'];
        }

        if (!empty($params['search'])) {
            $where_clauses[] = '(p.name LIKE :search OR b.name LIKE :search OR p.description LIKE :search)';
            $bindings[':search'] = '%' . $params['search'] . '%';
        }

        if (count($where_clauses) > 0) {
            $query .= ' WHERE ' . implode(' AND ', $where_clauses);
        }

        $sort_options = [
            'price_asc' => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'name_asc' => 'p.name ASC',
            'name_desc' => 'p.name DESC',
            'created_at_desc' => 'p.created_at DESC'
        ];

        $sort_order = $sort_options[$params['sort']] ?? 'p.created_at DESC';
        $query .= ' ORDER BY ' . $sort_order;

        $stmt = $this->conn->prepare($query);

        foreach ($bindings as $key => &$val) {
            $stmt->bindParam($key, $val);
        }

        $stmt->execute();
        return $stmt;
    }

    // Read single product with all details
    public function readOne() {
        // Query to get the main product details
        $query = 'SELECT
                c.name as category_name,
                b.name as brand_name,
                p.id, p.name, p.sku, p.product_type, p.linked_product_id, p.description, 
                p.price, p.discount_price, p.stock_quantity, p.is_active, p.brand_id, 
                p.category_id, p.main_image_url
            FROM
                ' . $this->table_name . ' p
            LEFT JOIN
                categories c ON p.category_id = c.id
            LEFT JOIN
                brands b ON p.brand_id = b.id
            WHERE
                p.id = :id
            LIMIT 1';

        // Prepare statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        // Fetch the main product row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null; // Product not found
        }

        // Set properties from the fetched row. It's crucial to set $this->id here.
        $this->id = $row['id']; 
        $this->name = $row['name'];
        $this->sku = $row['sku'];
        $this->product_type = $row['product_type'];
        $this->linked_product_id = $row['linked_product_id'];
        $this->description = $row['description'];
        $this->price = $row['price'];
        $this->discount_price = $row['discount_price'];
        $this->stock_quantity = $row['stock_quantity'];
        $this->is_active = $row['is_active'];
        $this->brand_id = $row['brand_id'];
        $this->category_id = $row['category_id'];
        $this->main_image_url = $row['main_image_url'];
        $this->brand_name = $row['brand_name'];
        $this->category_name = $row['category_name'];

        // Now, fetch associated data
        $row['images'] = $this->getProductImages();
        $row['attributes'] = $this->getProductAttributes();

        // Handle linked products based on product type
        if ($row['product_type'] === 'inspired' && !empty($row['linked_product_id'])) {
            // An inspired product links to ONE original product.
            $row['linked_product'] = $this->getLinkedProduct($row['linked_product_id'], true);
        } elseif ($row['product_type'] === 'original') {
            // An original product can have MANY inspired products linking to it.
            $row['linked_products'] = $this->getInspiredByProducts();
        }

        return $row; // Return the row with all associated data appended
    }

    // Helper function to get product gallery images
    private function getProductImages() {
        $query = 'SELECT id, image_url, alt_text, sort_order FROM product_images WHERE product_id = :product_id ORDER BY sort_order ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $this->id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Helper function to get product attributes (scent notes)
    private function getProductAttributes() {
        $query = 'SELECT id, attribute_key, attribute_value FROM product_attributes WHERE product_id = :product_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $this->id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Helper function to get the linked "Inspired By" product details
    private function getLinkedProduct($linkedId, $isSingle) {
        if (empty($linkedId)) {
            return null;
        }
        $query = 'SELECT id, name, main_image_url FROM ' . $this->table_name . ' WHERE id = :linked_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':linked_id', $linkedId);
        $stmt->execute();
        return $isSingle ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Helper function to get all products inspired by the current original product
    private function getInspiredByProducts() {
        $query = 'SELECT id, name, main_image_url FROM ' . $this->table_name . ' WHERE linked_product_id = :original_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':original_id', $this->id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create Product
    public function create() {
        // Create query
        $query = 'INSERT INTO ' . $this->table_name . ' 
            SET name=:name, sku=:sku, product_type=:product_type, linked_product_id=:linked_product_id, 
                description=:description, price=:price, discount_price=:discount_price, 
                stock_quantity=:stock_quantity, is_active=:is_active, brand_id=:brand_id, 
                category_id=:category_id, main_image_url=:main_image_url';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize input data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->sku = htmlspecialchars(strip_tags($this->sku));
        $this->product_type = htmlspecialchars(strip_tags($this->product_type));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->main_image_url = htmlspecialchars(strip_tags($this->main_image_url));
        
        // Bind data
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':sku', $this->sku);
        $stmt->bindParam(':product_type', $this->product_type);
        $stmt->bindParam(':linked_product_id', $this->linked_product_id);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':discount_price', $this->discount_price);
        $stmt->bindParam(':stock_quantity', $this->stock_quantity);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':brand_id', $this->brand_id);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':main_image_url', $this->main_image_url);

        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    // Update Product
    public function update() {
        $allowed_columns = [
            'name', 'sku', 'product_type', 'linked_product_id', 'description', 'price',
            'discount_price', 'stock_quantity', 'is_active', 'brand_id', 'category_id', 'main_image_url'
        ];

        $set_parts = [];
        
        foreach ($allowed_columns as $column) {
            if (property_exists($this, $column) && !is_null($this->$column)) {
                $set_parts[] = "`{$column}` = :{$column}";
            }
        }

        if (empty($set_parts)) {
            return true; // Nothing to update
        }

        $query = 'UPDATE ' . $this->table_name . ' SET ' . implode(', ', $set_parts) . ' WHERE id = :id';

        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        foreach ($allowed_columns as $column) {
            if (property_exists($this, $column) && !is_null($this->$column)) {
                $sanitized_value = htmlspecialchars(strip_tags($this->$column));
                $stmt->bindValue(":{$column}", $sanitized_value);
            }
        }

        $stmt->bindValue(':id', htmlspecialchars(strip_tags($this->id)));

        if ($stmt->execute()) {
            return true;
        }

        throw new Exception($stmt->errorInfo()[2]);
    }

    // Delete Product
    public function delete() {
        // First, get the main image URL to delete the file
        $this->readOne(); // This populates the object's properties

        // Create delete query
        $query = 'DELETE FROM ' . $this->table_name . ' WHERE id = :id';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind id
        $stmt->bindParam(':id', $this->id);

        // Execute query
        if ($stmt->execute()) {
            // If deletion is successful, also delete the associated files
            // Note: product_images and product_attributes are deleted automatically by the DB (ON DELETE CASCADE)
            if (!empty($this->main_image_url) && file_exists('../../' . $this->main_image_url)) {
                unlink('../../' . $this->main_image_url);
            }
            // Also delete gallery images files
            if (!empty($this->images)) {
                foreach ($this->images as $image) {
                    if (!empty($image['image_url']) && file_exists('../../' . $image['image_url'])) {
                        unlink('../../' . $image['image_url']);
                    }
                }
            }
            return true;
        }

        return false;
    }
}
