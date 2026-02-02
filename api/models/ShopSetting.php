<?php
class ShopSetting {
    private $conn;
    private $table_name = 'shop_settings';

    // Properties
    public $id;
    public $site_name;
    public $phone_number;
    public $email;
    public $address;
    public $facebook_url;
    public $instagram_url;
    public $tiktok_url;
    public $whatsapp_number;
    public $currency_symbol;
    public $shipping_cost;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = 'SELECT * FROM ' . $this->table_name . ' WHERE id = 1';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update($data) {
        // Define allowed fields that exist in the database
        $allowedFields = [
            'site_name', 'phone_number', 'email', 'address',
            'facebook_url', 'instagram_url', 'tiktok_url',
            'whatsapp_number', 'currency_symbol'
        ];
        
        // Filter input data to only include allowed fields
        $filteredData = array_intersect_key($data, array_flip($allowedFields));
        
        // Build the SET part of the query dynamically
        $setParts = [];
        foreach (array_keys($filteredData) as $field) {
            $setParts[] = "$field = :$field";
        }
        
        if (empty($setParts)) {
            throw new Exception('No valid fields to update');
        }
        
        $query = 'UPDATE ' . $this->table_name . ' SET ' . implode(', ', $setParts) . ' WHERE id = :id';

        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        $this->id = 1; // We'll only have one settings row
        
        // Bind the ID parameter
        $stmt->bindParam(':id', $this->id);
        
        // Bind only the fields that exist in the filtered data
        foreach ($filteredData as $key => $value) {
            switch ($key) {
                case 'email':
                case 'site_name':
                case 'phone_number':
                case 'address':
                case 'whatsapp_number':
                case 'currency_symbol':
                    $value = htmlspecialchars(strip_tags($value ?? ''));
                    break;
                case 'facebook_url':
                case 'instagram_url':
                case 'tiktok_url':
                    $value = filter_var($value ?? '', FILTER_SANITIZE_URL);
                    break;
                default:
                    continue 2; // Skip invalid fields
            }
            
            $stmt->bindValue(":$key", $value);
        }

        return $stmt->execute();
    }
}
