<?php
$currentPage = $_GET['page'] ?? '';
?>

<div class="client-sidebar" id="clientSidebar">
    <h3><i class="fas fa-tint"></i> AquaBill</h3>
    <ul>
        <li>
            <a href="index.php?page=client_dashboard" class="<?php echo ($currentPage === 'client_dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="index.php?page=client_billing_dashboard" class="<?php echo ($currentPage === 'client_billing_dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i> Billing Dashboard
            </a>
        </li>
        <li>
            <a href="index.php?page=client_view_bills" class="<?php echo ($currentPage === 'client_view_bills') ? 'active' : ''; ?>">
                <i class="fas fa-list-alt"></i> My Bills
            </a>
        </li>
        <li>
            <a href="index.php?page=client_meters" class="<?php echo ($currentPage === 'client_meters') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> My Meters
            </a>
        </li>
        <li>
            <a href="index.php?page=client_consumption" class="<?php echo ($currentPage === 'client_consumption') ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Consumption
            </a>
        </li>
        <li>
            <a href="index.php?page=client_payments" class="<?php echo ($currentPage === 'client_payments') ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i> Payments
            </a>
        </li>
        <li>
            <a href="index.php?page=client_support" class="<?php echo ($currentPage === 'client_support') ? 'active' : ''; ?>">
                <i class="fas fa-headset"></i> Support
            </a>
        </li>
        <li>
            <a href="index.php?page=client_profile" class="<?php echo ($currentPage === 'client_profile') ? 'active' : ''; ?>">
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
