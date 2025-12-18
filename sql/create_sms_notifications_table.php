<?php
// Connect to the database
require_once __DIR__ . '/../app/config/db.php';

// SQL to create the sms_notifications table
$sql = "CREATE TABLE IF NOT EXISTS sms_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    notification_type ENUM('bill', 'payment', 'reminder', 'disconnection', 'service', 'other') NOT NULL,
    reference_id INT,
    status ENUM('pending', 'sent', 'failed', 'delivered') NOT NULL DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (phone_number),
    INDEX (notification_type),
    INDEX (reference_id),
    INDEX (status)
)";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "<p>SMS notifications table created successfully.</p>";
} else {
    echo "<p>Error creating SMS notifications table: " . $conn->error . "</p>";
}

// Close the connection
$conn->close();

echo "<p><a href='http://localhost:8000/index.php'>Return to homepage</a></p>";
?>