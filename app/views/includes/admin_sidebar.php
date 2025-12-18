<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-shield-alt"></i> Admin Panel</h3>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li><a href="index.php?page=admin_dashboard" class="<?php echo $page==='admin_dashboard'?'active':''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="index.php?page=admin_manage_users" class="<?php echo $page==='admin_manage_users'?'active':''; ?>"><i class="fas fa-users-cog"></i> Manage Users</a></li>
            <li><a href="index.php?page=admin_manage_billing_plans" class="<?php echo $page==='admin_manage_billing_plans'?'active':''; ?>"><i class="fas fa-file-invoice"></i> Billing Plans</a></li>
            <li><a href="index.php?page=admin_manage_services" class="<?php echo $page==='admin_manage_services'?'active':''; ?>"><i class="fas fa-cogs"></i> Manage Services</a></li>
            <li><a href="index.php?page=admin_manage_meters" class="<?php echo $page==='admin_manage_meters'?'active':''; ?>"><i class="fas fa-tachometer-alt"></i> Manage Meters</a></li>
            <li><a href="index.php?page=admin_manage_requests" class="<?php echo $page==='admin_manage_requests'?'active':''; ?>"><i class="fas fa-clipboard-list"></i> Service Requests</a></li>
            <li><a href="index.php?page=generate_bills" class="<?php echo $page==='generate_bills'?'active':''; ?>"><i class="fas fa-file-invoice-dollar"></i> Generate Bills</a></li>
            <li><a href="index.php?page=generate_single_bill" class="<?php echo $page==='generate_single_bill'?'active':''; ?>"><i class="fas fa-file-invoice"></i> Generate Single Bill</a></li>
            <li><a href="index.php?page=view_bills" class="<?php echo $page==='view_bills'?'active':''; ?>"><i class="fas fa-list"></i> View Bills</a></li>
            <li><a href="index.php?page=finance_manager_reports" class="<?php echo $page==='finance_manager_reports'?'active':''; ?>"><i class="fas fa-chart-pie"></i> Financial Reports</a></li>
            <li><a href="index.php?page=billing_reports" class="<?php echo $page==='billing_reports'?'active':''; ?>"><i class="fas fa-chart-line"></i> Billing Reports</a></li>
            <li><a href="index.php?page=admin_transactions" class="<?php echo $page==='admin_transactions'?'active':''; ?>"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
            <li><a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</div>

<style>
    .sidebar-header{padding:0 1.5rem 1.5rem;border-bottom:1px solid var(--border-color);margin-bottom:1.5rem}
    .sidebar-header h3{color:var(--primary);font-size:1.5rem;font-weight:700;display:flex;align-items:center;gap:.75rem}
    .sidebar-nav{flex-grow:1;overflow-y:auto;padding:0 1rem}
    .sidebar-nav ul{list-style:none}
    .sidebar-nav a{display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.5rem;font-weight:500;color:var(--text-muted);transition:all .3s}
    .sidebar-nav a:hover{background-color:rgba(255,71,87,.1);color:var(--text-light)}
    .sidebar-nav a.active{background-color:var(--primary);color:#fff;box-shadow:0 4px 15px rgba(255,71,87,.3)}
    .sidebar-nav a i{width:1.5rem;text-align:center;font-size:1.1rem}
</style>
