<?php
// app/config/db.php

$servername = "localhost";   // Your database server name
$username = "root";          // Your MySQL username (default for XAMPP is 'root')
$password = "";              // Your MySQL password (default for XAMPP is empty '')
$database = "water_billing_db"; // The database name you created in phpMyAdmin

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    // In a real application, you'd log this error and show a user-friendly message.
    // For development, we'll die with the error.
    die("Connection failed: " . $conn->connect_error);
}
// Connection successful - the $conn object is now available for use.

if (!defined('ADMIN_REGISTRATION_KEY')) {
    define('ADMIN_REGISTRATION_KEY', 'MULWA_6879');
}


// SQL Commands for Database and Table Creation (for setting up on a new machine)

// Uncomment the following lines and run them in a MySQL client (like phpMyAdmin SQL tab)
// if you need to set up the database and tables from scratch.

// 1. Create the Database (if it doesn't exist)
// CREATE DATABASE IF NOT EXISTS water_billing_db;

// 2. Use the Database
// USE water_billing_db;

// 3. Create Tables
/*
// Table: billing_plans
$sql="CREATE TABLE billing_plans (
     id INT AUTO_INCREMENT PRIMARY KEY,
     plan_name VARCHAR(100) NOT NULL UNIQUE,
     description TEXT,
     base_rate DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
     unit_rate DECIMAL(10, 4) NOT NULL,
     min_consumption DECIMAL(10, 2) DEFAULT 0.00,
     max_consumption DECIMAL(10, 2),
     billing_cycle ENUM('monthly', 'quarterly', 'annually') NOT NULL,
     is_active TINYINT(1) NOT NULL DEFAULT 1,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
 )";

// Table: bills
$sql="CREATE TABLE bills (
     id INT AUTO_INCREMENT PRIMARY KEY,
     client_id INT NOT NULL,
     meter_id INT NOT NULL,
     reading_id_start INT,
     reading_id_end INT NOT NULL,
     bill_date DATE NOT NULL DEFAULT (CURDATE()),
     due_date DATE NOT NULL,
     consumption_units DECIMAL(15, 3) NOT NULL,
     amount_due DECIMAL(10, 2) NOT NULL,
     amount_paid DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
     balance DECIMAL(10, 2) NOT NULL,
     payment_status ENUM('pending', 'paid', 'partially_paid', 'overdue') NOT NULL DEFAULT 'pending',
     billing_period_start DATE NOT NULL,
     billing_period_end DATE NOT NULL,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     FOREIGN KEY (client_id) REFERENCES clients(id),
     FOREIGN KEY (meter_id) REFERENCES meters(id),
     FOREIGN KEY (reading_id_start) REFERENCES meter_readings(id),
     FOREIGN KEY (reading_id_end) REFERENCES meter_readings(id)
 )";

// Table: clients
$sql="CREATE TABLE clients (
     id INT AUTO_INCREMENT PRIMARY KEY,
     user_id INT UNIQUE,
     full_name VARCHAR(255) NOT NULL,
     address VARCHAR(255) NOT NULL,
     contact_phone VARCHAR(20),
     contact_email VARCHAR(100) UNIQUE,
     account_status ENUM('pending', 'active', 'suspended', 'cancelled') NOT NULL DEFAULT 'pending',
     application_date DATE DEFAULT (CURDATE()),
     connection_date DATE,
     plan_id INT,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     FOREIGN KEY (user_id) REFERENCES users(id),
     FOREIGN KEY (plan_id) REFERENCES billing_plans(id)
 )";

// Table: client_plans
$sql="CREATE TABLE client_plans (
     id INT AUTO_INCREMENT PRIMARY KEY,
     user_id INT NOT NULL,
     plan_id INT NOT NULL,
     subscription_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     status ENUM('pending', 'active', 'cancelled') NOT NULL DEFAULT 'pending',
     last_payment_date TIMESTAMP,
     next_billing_date DATE,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     FOREIGN KEY (user_id) REFERENCES users(id),
     FOREIGN KEY (plan_id) REFERENCES billing_plans(id)
 )";

// Table: client_services
$sql="CREATE TABLE client_services (
     id INT AUTO_INCREMENT PRIMARY KEY,
     user_id INT NOT NULL,
     service_id INT NOT NULL,
     service_request_id INT,
     application_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     status ENUM('pending', 'approved', 'rejected', 'completed', 'in_progress') NOT NULL DEFAULT 'pending',
     payment_date DATETIME,
     notes TEXT,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     FOREIGN KEY (user_id) REFERENCES users(id),
     FOREIGN KEY (service_id) REFERENCES services(id),
     FOREIGN KEY (service_request_id) REFERENCES service_requests(id)
 )";

// Table: meters
$sql="CREATE TABLE meters (
     id INT AUTO_INCREMENT PRIMARY KEY,
     serial_number VARCHAR(100) NOT NULL UNIQUE,
     client_id INT,
     installation_date DATE NOT NULL,
     meter_type ENUM('residential', 'commercial', 'industrial') NOT NULL,
     initial_reading DECIMAL(15, 3) NOT NULL DEFAULT 0.000,
     status ENUM('in_stock', 'installed', 'faulty', 'retired') NOT NULL DEFAULT 'in_stock',
     photo_url VARCHAR(255),
     gps_location VARCHAR(255),
     next_update_date DATE,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     assigned_collector_id INT,
     FOREIGN KEY (client_id) REFERENCES clients(id)
 )";

// Table: meter_images
$sql="CREATE TABLE meter_images (
     id INT AUTO_INCREMENT PRIMARY KEY,
     client_id INT NOT NULL,
     meter_id INT,
     collector_id INT NOT NULL,
     image_path TEXT NOT NULL,
     taken_at DATETIME DEFAULT CURRENT_TIMESTAMP,
     notes TEXT,
     latitude DECIMAL(10, 8),
     longitude DECIMAL(11, 8),
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     FOREIGN KEY (client_id) REFERENCES clients(id),
     FOREIGN KEY (meter_id) REFERENCES meters(id),
     FOREIGN KEY (collector_id) REFERENCES users(id)
 )";

// Table: meter_readings

$sql=" CREATE TABLE meter_readings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        meter_id INT NOT NULL,
        reading_value DECIMAL(15, 3) NOT NULL,
        reading_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        collector_id INT,
        meter_image_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (meter_id) REFERENCES meters(id),
        FOREIGN KEY (collector_id) REFERENCES users(id)
 )";

// Table: payments
$sql=" CREATE TABLE payments (
     id INT AUTO_INCREMENT PRIMARY KEY,
     user_id INT NOT NULL,
     type ENUM('bill_payment', 'service_payment', 'penalty') NOT NULL,
     reference_id INT NOT NULL,
     amount DECIMAL(10, 2) NOT NULL,
     payment_method VARCHAR(50) NOT NULL DEFAULT 'M-Pesa STK Push',
     transaction_id VARCHAR(100),
     payment_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     FOREIGN KEY (user_id) REFERENCES users(id)
 )";

// Table: services
$sql="CREATE TABLE services (
     id INT AUTO_INCREMENT PRIMARY KEY,
     service_name VARCHAR(255) NOT NULL UNIQUE,
     description TEXT,
     cost DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
     is_active TINYINT(1) NOT NULL DEFAULT 1,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
 )";

// Table: service_attendances
$sql="CREATE TABLE service_attendances (
     id INT AUTO_INCREMENT PRIMARY KEY,
     service_request_id INT NOT NULL,
     collector_id INT NOT NULL,
     attendance_date DATETIME DEFAULT CURRENT_TIMESTAMP,
     notes TEXT,
     latitude DECIMAL(10, 8),
     longitude DECIMAL(11, 8),
     status_update VARCHAR(50) NOT NULL,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     FOREIGN KEY (service_request_id) REFERENCES service_requests(id),
     FOREIGN KEY (collector_id) REFERENCES users(id)
 )";

// Table: service_requests
$sql="CREATE TABLE service_requests (
     id INT AUTO_INCREMENT PRIMARY KEY,
     client_id INT NOT NULL,
     service_id INT,
     collector_id INT,
     request_type ENUM('new_connection', 'disconnection', 'reconnection', 'meter_repair', 'billing_inquiry', 'other') NOT NULL,
     description TEXT NOT NULL,
     admin_notes TEXT,
     status VARCHAR(50) NOT NULL DEFAULT 'pending',
     assigned_to_collector_id INT,
     request_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     completion_date DATETIME,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     FOREIGN KEY (client_id) REFERENCES clients(id),
     FOREIGN KEY (service_id) REFERENCES services(id),
     FOREIGN KEY (collector_id) REFERENCES users(id),
     FOREIGN KEY (assigned_to_collector_id) REFERENCES users(id)
 )";

// Table: users
$sql="CREATE TABLE users (
     id INT AUTO_INCREMENT PRIMARY KEY,
     username VARCHAR(50) NOT NULL UNIQUE,
     password_hash VARCHAR(255) NOT NULL,
     email VARCHAR(100) NOT NULL UNIQUE,
     full_name VARCHAR(255),
     address VARCHAR(255),
     contact_phone VARCHAR(50),
     status VARCHAR(50) NOT NULL DEFAULT 'active',
     role ENUM('admin', 'client', 'collector', 'support') NOT NULL,
     last_login_at TIMESTAMP,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
 )";
*/
?>
