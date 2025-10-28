<?php
/**
 * Add Wishlist and Compare Product Tables
 * Run this once to add new tables
 */
require 'db.php';

try {
    // 1. Create wishlists table
    $conn->query("CREATE TABLE IF NOT EXISTS `wishlists` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT(11) NOT NULL,
        `product_id` INT(11) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `user_product` (`user_id`, `product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    
    echo "✓ Wishlists table created/verified<br>";

    // 2. Create compare_products table
    $conn->query("CREATE TABLE IF NOT EXISTS `compare_products` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT(11),
        `session_id` VARCHAR(100),
        `product_id` INT(11) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `compare_unique` (`user_id`, `product_id`, `session_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    
    echo "✓ Compare Products table created/verified<br>";

    echo "<br><strong>✅ Database migration completed successfully!</strong>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
