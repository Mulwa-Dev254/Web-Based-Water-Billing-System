<?php
// Connect to the database
require_once __DIR__ . '/../app/config/db.php';

// SQL to create the mpesa_requests table
$sql = "CREATE TABLE IF NOT EXISTS mpesa_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checkout_request_id VARCHAR(50) NOT NULL,
    bill_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    transaction_id VARCHAR(50),
    result_desc TEXT,
    status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (checkout_request_id),
    INDEX (bill_id),
    INDEX (status),
    INDEX (transaction_id)
)";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "<p>M-Pesa requests table created successfully.</p>";
} else {
    echo "<p>Error creating M-Pesa requests table: " . $conn->error . "</p>";
}

// Close the connection
$conn->close();

echo "<p><a href='http://localhost:8000/index.php'>Return to homepage</a></p>";
?>