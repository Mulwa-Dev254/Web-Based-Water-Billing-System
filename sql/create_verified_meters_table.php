<?php
// SQL creator for verified_meters table
// Fields: id, client_id, client_name, meter_id, meter_serial, meter_status, initial_reading, current_reading, verification_date, admin_id, admin_name, created_at

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/config/db.php';

// Expect $mysqli from config/db.php
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    die('Database connection not initialized.');
}

$db = new Database($mysqli);

$sql = "CREATE TABLE IF NOT EXISTS verified_meters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    meter_id INT NOT NULL,
    meter_serial VARCHAR(255) NOT NULL,
    meter_status VARCHAR(64) NOT NULL,
    initial_reading DECIMAL(10,2) DEFAULT 0,
    current_reading DECIMAL(10,2) DEFAULT 0,
    verification_date DATETIME NOT NULL,
    admin_id INT NOT NULL,
    admin_name VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_client_id (client_id),
    INDEX idx_meter_id (meter_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$db->query($sql);
if ($db->execute()) {
    echo "verified_meters table ensured.\n";
} else {
    echo "Failed to create verified_meters: " . $db->getError() . "\n";
}


