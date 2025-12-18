<?php
// app/views/client/partials/sidebar.php

// Ensure this page is only accessible to clients
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php?page=login');
    exit();
}

// Determine which page is active
$currentPage = $_GET['page'] ?? 'client_dashboard';
?>

<!-- Sidebar -->
<div class="client-sidebar">
    <h3><i class="fas fa-tint"></i> AquaBill</h3>
    <ul>
        <li>
            <a href="index.php?page=client_dashboard" class="<?= $currentPage === 'client_dashboard' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        
        <!-- Billing Section -->
        <li>
            <a href="index.php?page=client_billing_dashboard" class="<?= $currentPage === 'client_billing_dashboard' ? 'active' : '' ?>">
                <i class="fas fa-file-invoice-dollar"></i> Billing Dashboard
            </a>
        </li>
        <li>
            <a href="index.php?page=client_view_bills" class="<?= $currentPage === 'client_view_bills' ? 'active' : '' ?>">
                <i class="fas fa-list-alt"></i> My Bills
            </a>
        </li>
        
        <!-- Existing Client Links -->
        <li>
            <a href="index.php?page=client_meters" class="<?= $currentPage === 'client_meters' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> My Meters
            </a>
        </li>
        <li>
            <a href="index.php?page=client_consumption" class="<?= $currentPage === 'client_consumption' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Consumption
            </a>
        </li>
        <li>
            <a href="index.php?page=client_payments" class="<?= $currentPage === 'client_payments' ? 'active' : '' ?>">
                <i class="fas fa-money-bill-wave"></i> Payments
            </a>
        </li>
        <li>
            <a href="index.php?page=client_support" class="<?= $currentPage === 'client_support' ? 'active' : '' ?>">
                <i class="fas fa-headset"></i> Support
            </a>
        </li>
        <li>
            <a href="index.php?page=client_profile" class="<?= $currentPage === 'client_profile' ? 'active' : '' ?>">
                <i class="fas fa-user"></i> Profile
            </a>
        </li>
        <li>
            <a href="index.php?page=logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>