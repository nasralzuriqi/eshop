<?php
require_once __DIR__ . '/../config/Database.php';

try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/migrations/2024_02_01_000001_create_shop_settings_table.sql');
    
    // Execute the SQL
    $db->exec($sql);
    
    echo "Migration executed successfully!\n";
} catch (PDOException $e) {
    echo "Error executing migration: " . $e->getMessage() . "\n";
    exit(1);
}
