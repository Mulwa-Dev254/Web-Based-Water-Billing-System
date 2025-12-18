<?php
// Creates meter_installations table if not exists
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/config/db.php';

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    die('DB connection not initialized');
}

$db = new Database($mysqli);

$db->query("CREATE TABLE IF NOT EXISTS meter_installations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meter_id INT NOT NULL,
    client_id INT NOT NULL,
    installer_user_id INT NOT NULL,
    initial_reading DECIMAL(10,2) DEFAULT 0,
    photo_url VARCHAR(255) DEFAULT NULL,
    gps_location VARCHAR(64) DEFAULT NULL,
    notes TEXT,
    status ENUM('waiting_installation','submitted','approved','installed','rejected','cancelled') DEFAULT 'submitted',
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewed_by INT DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    review_notes TEXT,
    INDEX idx_meter (meter_id),
    INDEX idx_installer (installer_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
if ($db->execute()) {
    echo "meter_installations ensured\n";
} else {
    echo "Failed: " . $db->getError() . "\n";
}


