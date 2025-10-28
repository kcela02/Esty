<?php
/**
 * Database Migrations
 * Run this once to add new tables for categories, brands, ratings, and reviews
 */
require 'db.php';

try {
    // 1. Create categories table
    $conn->query("CREATE TABLE IF NOT EXISTS `categories` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL UNIQUE,
        `description` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    
    echo "✓ Categories table created/verified<br>";

    // 2. Create brands table
    $conn->query("CREATE TABLE IF NOT EXISTS `brands` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL UNIQUE,
        `description` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    
    echo "✓ Brands table created/verified<br>";

    // 3. Create product_ratings table (for average rating, review count)
    $conn->query("CREATE TABLE IF NOT EXISTS `product_ratings` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `product_id` INT(11) NOT NULL,
        `average_rating` DECIMAL(3,2) DEFAULT 0,
        `review_count` INT(11) DEFAULT 0,
        `total_rating_value` INT(11) DEFAULT 0,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `product_unique` (`product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    
    echo "✓ Product ratings table created/verified<br>";

    // 4. Create product_reviews table
    $conn->query("CREATE TABLE IF NOT EXISTS `product_reviews` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `product_id` INT(11) NOT NULL,
        `user_id` INT(11) NOT NULL,
        `rating` INT(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
        `title` VARCHAR(100),
        `comment` TEXT,
        `helpful_count` INT(11) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    
    echo "✓ Product reviews table created/verified<br>";

    // 5. Add new columns to products table if they don't exist
    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM `products`");
    while ($col = $result->fetch_assoc()) {
        $columns[] = $col['Field'];
    }

    if (!in_array('category_id', $columns)) {
        $conn->query("ALTER TABLE `products` ADD COLUMN `category_id` INT(11) DEFAULT NULL AFTER `id`, ADD FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL");
        echo "✓ Added category_id column to products<br>";
    }

    if (!in_array('brand_id', $columns)) {
        $conn->query("ALTER TABLE `products` ADD COLUMN `brand_id` INT(11) DEFAULT NULL AFTER `category_id`, ADD FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE SET NULL");
        echo "✓ Added brand_id column to products<br>";
    }

    if (!in_array('popularity', $columns)) {
        $conn->query("ALTER TABLE `products` ADD COLUMN `popularity` INT(11) DEFAULT 0 AFTER `stock`");
        echo "✓ Added popularity column to products<br>";
    }

    echo "<br><strong>✅ Database migration completed successfully!</strong>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
