<?php
/**
 * Add user_brands table to store customer-created brands
 * Includes popularity tracking
 */
require 'db.php';

try {
    // 1. Add user_id column to brands table (for tracking creator)
    $result = $conn->query("SHOW COLUMNS FROM `brands` LIKE 'user_id'");
    if ($result->num_rows === 0) {
        $conn->query("ALTER TABLE `brands` ADD COLUMN `user_id` INT(11) DEFAULT NULL AFTER `id`");
        $conn->query("ALTER TABLE `brands` ADD COLUMN `is_user_created` TINYINT(1) DEFAULT 0");
        $conn->query("ALTER TABLE `brands` ADD COLUMN `popularity` INT(11) DEFAULT 0");
        echo "✓ Added user_id, is_user_created, and popularity to brands table<br>";
    }

    // 2. Create user_brand_contributions table (track contributions)
    $conn->query("CREATE TABLE IF NOT EXISTS `user_brand_contributions` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT(11) NOT NULL,
        `brand_id` INT(11) NOT NULL,
        `contribution_type` VARCHAR(50) DEFAULT 'created',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    
    echo "✓ User brand contributions table created/verified<br>";

    echo "<br><strong>✅ Database migration completed successfully!</strong>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
