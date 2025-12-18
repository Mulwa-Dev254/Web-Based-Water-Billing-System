<?php
// sql/create_mpesa_tables.php

// Include database connection
require_once __DIR__ . '/../app/config/db.php';

// Create mpesa_requests table
$mpesa_requests_table = "CREATE TABLE IF NOT EXISTS mpesa_requests (
    id INT(11) NOT NULL AUTO_INCREMENT,
    checkout_request_id VARCHAR(50) NOT NULL,
    merchant_request_id VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    reference_number VARCHAR(30) NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    result_code VARCHAR(10) DEFAULT NULL,
    result_desc TEXT,
    payment_id INT(11) DEFAULT NULL,
    bill_id INT(11) DEFAULT NULL,
    client_id INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY checkout_request_id (checkout_request_id),
    KEY merchant_request_id (merchant_request_id),
    KEY phone_number (phone_number),
    KEY reference_number (reference_number),
    KEY status (status),
    KEY payment_id (payment_id),
    KEY bill_id (bill_id),
    KEY client_id (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Execute the query
if ($conn->query($mpesa_requests_table) === TRUE) {
    echo "mpesa_requests table created successfully<br>";
} else {
    echo "Error creating mpesa_requests table: " . $conn->error . "<br>";
}

// Create sms_notifications table if it doesn't exist
$sms_notifications_table = "CREATE TABLE IF NOT EXISTS sms_notifications (
    id INT(11) NOT NULL AUTO_INCREMENT,
    phone_number VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    status ENUM('pending', 'sent', 'failed', 'delivered') NOT NULL DEFAULT 'pending',
    reference_id INT(11) DEFAULT NULL,
    response_data TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY phone_number (phone_number),
    KEY status (status),
    KEY reference_id (reference_id),
    KEY type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Execute the query
if ($conn->query($sms_notifications_table) === TRUE) {
    echo "sms_notifications table created successfully<br>";
} else {
    echo "Error creating sms_notifications table: " . $conn->error . "<br>";
}

echo "<br><a href='../index.php'>Go back to homepage</a>";

// Close connection
$conn->close();