<?php
// Connect to the database
require_once __DIR__ . '/../app/config/db.php';

// SQL to create the service_disconnections table
$sql_disconnections = "CREATE TABLE IF NOT EXISTS `service_disconnections` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NOT NULL,
    `meter_number` VARCHAR(50) NOT NULL,
    `outstanding_balance` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `reason` TEXT NOT NULL,
    `status` ENUM('pending', 'scheduled', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `scheduled_date` DATE NULL,
    `completed_date` DATETIME NULL,
    `notes` TEXT NULL,
    `created_by` INT NOT NULL,
    `assigned_to` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`client_id`),
    INDEX (`meter_number`),
    INDEX (`status`),
    INDEX (`created_by`),
    INDEX (`assigned_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// SQL to create the service_reconnections table
$sql_reconnections = "CREATE TABLE IF NOT EXISTS `service_reconnections` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `disconnection_id` INT NOT NULL,
    `client_id` INT NOT NULL,
    `meter_number` VARCHAR(50) NOT NULL,
    `payment_id` INT NULL,
    `status` ENUM('pending', 'scheduled', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `scheduled_date` DATE NULL,
    `completed_date` DATETIME NULL,
    `notes` TEXT NULL,
    `created_by` INT NOT NULL,
    `assigned_to` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`disconnection_id`),
    INDEX (`client_id`),
    INDEX (`meter_number`),
    INDEX (`payment_id`),
    INDEX (`status`),
    INDEX (`created_by`),
    INDEX (`assigned_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// SQL to add service types if they don't exist
$sql_service_types = "INSERT INTO `services` (`name`, `description`, `price`, `is_active`) 
    VALUES 
    ('Disconnection', 'Water service disconnection due to non-payment or other reasons', 500.00, 1),
    ('Reconnection', 'Water service reconnection after resolving disconnection issues', 500.00, 1)
    ON DUPLICATE KEY UPDATE 
    `description` = VALUES(`description`), 
    `price` = VALUES(`price`), 
    `is_active` = VALUES(`is_active`)";

// Execute the queries
try {
    if ($conn->query($sql_disconnections) === TRUE) {
        echo "<p>Service disconnections table created successfully.</p>";
    } else {
        echo "<p>Error creating service disconnections table: " . $conn->error . "</p>";
    }
    
    if ($conn->query($sql_reconnections) === TRUE) {
        echo "<p>Service reconnections table created successfully.</p>";
    } else {
        echo "<p>Error creating service reconnections table: " . $conn->error . "</p>";
    }
    
    if ($conn->query($sql_service_types) === TRUE) {
        echo "<p>Service types added/updated successfully.</p>";
    } else {
        echo "<p>Error adding/updating service types: " . $conn->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

// Close the connection
$conn->close();

echo "<p><a href='http://localhost:8000/index.php'>Return to homepage</a></p>";
?>