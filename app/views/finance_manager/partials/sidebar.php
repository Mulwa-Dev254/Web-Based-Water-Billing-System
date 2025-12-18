<?php
// app/views/finance_manager/partials/sidebar.php

// Ensure this page is only accessible to finance managers
// Using the auth object passed from the controller
// We don't redirect here since headers are already sent - this check should be in the controller
// This is just a safety check
if (!isset($_SESSION['user']) || !isset($auth) || !in_array($auth->getUserRole(), ['finance_manager', 'admin'])) {
    // Cannot use header redirect here as headers are already sent
    echo "<script>window.location.href = 'index.php?page=login';</script>";
    exit();
}

// Determine which page is active
$currentPage = $_GET['page'] ?? 'finance_manager_dashboard';
?>

<!-- Sidebar -->
<div class="fixed inset-y-0 left-0 w-64 bg-blue-800 text-white shadow-lg z-10" style="background-color: var(--sidebar-bg);">
    <div class="flex flex-col h-full">
        <!-- Logo and Title -->
        <div class="p-4 border-b border-opacity-20" style="border-color: var(--border-color);">
            <div class="flex items-center">
                <i class="fas fa-tint text-2xl mr-3"></i>
                <div>
                    <h1 class="text-xl font-bold">Water Billing</h1>
                    <p class="text-xs" style="color: var(--text-light);">Finance Manager Portal</p>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto py-4">
            <ul>
                <!-- Dashboard -->
                <li>
                    <a href="index.php?page=finance_manager_dashboard" 
                       class="flex items-center px-6 py-3 hover:bg-opacity-80 transition-colors duration-200 <?= $currentPage === 'finance_manager_dashboard' ? 'bg-opacity-80' : '' ?>" style="hover:background-color: var(--primary-dark);">
                        <i class="fas fa-chart-line w-5 mr-3"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Transactions -->
                <li>
                    <a href="index.php?page=finance_manager_transactions" 
                       class="flex items-center px-6 py-3 hover:bg-opacity-80 transition-colors duration-200 <?= $currentPage === 'finance_manager_transactions' ? 'bg-opacity-80' : '' ?>" style="hover:background-color: var(--primary-dark);">
                        <i class="fas fa-money-bill-wave w-5 mr-3"></i>
                        <span>Transactions</span>
                    </a>
                </li>

                <!-- Billing Management -->
                <li class="mb-2">
                    <div class="px-6 py-2 text-xs font-semibold uppercase tracking-wider" style="color: var(--text-light);">Billing Management</div>
                </li>
                
                <!-- Billing Dashboard -->
                <li>
                    <a href="index.php?page=billing_dashboard" 
                       class="flex items-center px-6 py-3 hover:bg-opacity-80 transition-colors duration-200 <?= $currentPage === 'billing_dashboard' ? 'bg-opacity-80' : '' ?>" style="hover:background-color: var(--primary-dark);">
                        <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                        <span>Billing Dashboard</span>
                    </a>
                </li>
                
                <!-- Generate Bills -->
                <li>
                    <a href="index.php?page=generate_bills" 
                       class="flex items-center px-6 py-3 hover:bg-opacity-80 transition-colors duration-200 <?= $currentPage === 'generate_bills' ? 'bg-opacity-80' : '' ?>" style="hover:background-color: var(--primary-dark);">
                        <i class="fas fa-file-invoice-dollar w-5 mr-3"></i>
                        <span>Generate Bills</span>
                    </a>
                </li>
                
                <!-- View Bills -->
                <li>
                    <a href="index.php?page=view_bills" 
                       class="flex items-center px-6 py-3 hover:bg-opacity-80 transition-colors duration-200 <?= $currentPage === 'view_bills' ? 'bg-opacity-80' : '' ?>" style="hover:background-color: var(--primary-dark);">
                        <i class="fas fa-list-alt w-5 mr-3"></i>
                        <span>View Bills</span>
                    </a>
                </li>
                
                <!-- Billing Reports -->
                <li>
                    <a href="index.php?page=billing_reports" 
                       class="flex items-center px-6 py-3 hover:bg-opacity-80 transition-colors duration-200 <?= $currentPage === 'billing_reports' ? 'bg-opacity-80' : '' ?>" style="hover:background-color: var(--primary-dark);">
                        <i class="fas fa-chart-bar w-5 mr-3"></i>
                        <span>Billing Reports</span>
                    </a>
                </li>
                
                <!-- Finance Management -->
                <li class="mb-2 mt-4">
                    <div class="px-6 py-2 text-xs font-semibold uppercase tracking-wider" style="color: var(--text-light);">Finance Management</div>
                </li>
                
                <!-- Reports -->
                <li>
                    <a href="index.php?page=finance_manager_reports" 
                       class="flex items-center px-6 py-3 hover:bg-opacity-80 transition-colors duration-200 <?= $currentPage === 'finance_manager_reports' ? 'bg-opacity-80' : '' ?>" style="hover:background-color: var(--primary-dark);">
                        <i class="fas fa-file-alt w-5 mr-3"></i>
                        <span>Financial Reports</span>
                    </a>
                </li>

                <!-- Billing Plans -->
                <li>
                    <a href="index.php?page=finance_manager_billing_plans" 
                       class="flex items-center px-6 py-3 hover:bg-opacity-80 transition-colors duration-200 <?= $currentPage === 'finance_manager_billing_plans' ? 'bg-opacity-80' : '' ?>" style="hover:background-color: var(--primary-dark);">
                        <i class="fas fa-tags w-5 mr-3"></i>
                        <span>Billing Plans</span>
                    </a>
                </li>

                <!-- Profile -->
                <li>
                    <a href="index.php?page=finance_manager_profile" 
                       class="flex items-center px-6 py-3 hover:bg-opacity-80 transition-colors duration-200 <?= $currentPage === 'finance_manager_profile' ? 'bg-opacity-80' : '' ?>" style="hover:background-color: var(--primary-dark);">
                        <i class="fas fa-user w-5 mr-3"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Logout Section -->
        <div class="p-4 border-t border-opacity-20" style="border-color: var(--border-color);">
            <a href="index.php?page=logout" class="flex items-center hover:text-white transition-colors duration-200" style="color: var(--text-light);">
                <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</div>
