<?php
// Include database configuration
require_once __DIR__ . '/../app/config/db.php';

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Creating Meter Applications Table\n";

// Create meter_applications table
$sql = "CREATE TABLE IF NOT EXISTS meter_applications (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    client_id INT(11) NOT NULL,
    meter_id INT(11) NOT NULL,
    application_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    reviewed_by INT(11) DEFAULT NULL,
    review_date DATETIME DEFAULT NULL,
    admin_approval TINYINT(1) DEFAULT 0,
    admin_approval_date DATETIME DEFAULT NULL,
    admin_id INT(11) DEFAULT NULL,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (meter_id) REFERENCES meters(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'meter_applications' created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
echo "Database connection closed\n";
?>