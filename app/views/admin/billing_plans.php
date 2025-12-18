<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Billing Plans</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff4757;
            --primary-dark: #e84118;
            --dark-bg: #1e1e2d;
            --darker-bg: #151521;
            --sidebar-bg: #1a1a27;
            --card-bg: #2a2a3c;
            --text-light: #f8f9fa;
            --text-muted: #a1a5b7;
            --border-color: #2d2d3a;
            --success: #1dd1a1;
            --info: #2e86de;
            --warning: #ff9f43;
            --danger: #ee5253;
            --purple: #5f27cd;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Dashboard Layout */
        .dashboard-layout {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background-color: var(--sidebar-bg);
            padding: 1.5rem 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid var(--border-color);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar.hidden {
            transform: translateX(-280px);
        }

        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
        }

        .sidebar-header h3 {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-header h3 i {
            font-size: 1.75rem;
        }

        .sidebar-nav {
            flex-grow: 1;
            overflow-y: auto;
            padding: 0 1rem;
        }

        .sidebar-nav ul {
            list-style: none;
        }

        .sidebar-nav li {
            margin-bottom: 0.5rem;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            color: var(--text-muted);
            transition: all 0.3s ease;
        }

        .sidebar-nav a:hover {
            background-color: rgba(255, 71, 87, 0.1);
            color: var(--text-light);
        }

        .sidebar-nav a.active {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 71, 87, 0.3);
        }

        .sidebar-nav a i {
            width: 1.5rem;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Main Content Area */
        .main-content {
            margin-left: 280px;
            flex-grow: 1;
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content.full-width {
            margin-left: 0;
        }

        /* Header Bar */
        .header-bar {
            background-color: var(--sidebar-bg);
            padding: 1.25rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-title h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-light);
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-greeting {
            font-weight: 500;
            color: var(--text-light);
        }

        .user-greeting span {
            color: var(--primary);
            font-weight: 600;
        }

        .logout-btn {
            background-color: var(--primary);
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 71, 87, 0.3);
        }

        /* Toggle Button */
        .sidebar-toggle {
            background-color: var(--primary);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            display: none;
        }

        .sidebar-toggle:hover {
            background-color: var(--primary-dark);
            transform: scale(1.05);
        }

        /* Dashboard Content */
        .dashboard-container {
            padding: 2rem;
        }

        /* Content Sections */
        .content-section {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .section-title {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            font-size: 1.25rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            background-color: var(--darker-bg);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            color: var(--text-light);
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 71, 87, 0.2);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.875rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-family: 'Inter', sans-serif;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 71, 87, 0.3);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #d63031;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(238, 82, 83, 0.3);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        /* Checkbox Styles */
        .form-group.checkbox {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-group.checkbox input[type="checkbox"] {
            width: auto;
            margin: 0;
            accent-color: var(--primary);
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 1.5rem 0;
            background-color: var(--card-bg);
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .data-table th {
            background-color: var(--primary);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-light);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover td {
            background-color: rgba(255, 255, 255, 0.03);
        }

        .data-table .actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-active {
            background-color: rgba(29, 209, 161, 0.1);
            color: var(--success);
        }

        .status-inactive {
            background-color: rgba(238, 82, 83, 0.1);
            color: var(--danger);
        }

        /* Cycle Badges */
        .cycle-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            background-color: rgba(46, 134, 222, 0.1);
            color: var(--info);
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-error {
            background-color: rgba(238, 82, 83, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .alert-success {
            background-color: rgba(29, 209, 161, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert i {
            font-size: 1.25rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-280px);
            }
            
            .sidebar.visible {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar-toggle {
                display: flex;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .header-bar {
                padding: 1rem;
            }
            
            .content-section {
                padding: 1.5rem;
            }

            .data-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }

        @media (max-width: 576px) {
            .user-info {
                gap: 1rem;
            }
            
            .user-greeting {
                display: none;
            }

            .data-table th,
            .data-table td {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar Navigation -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-shield-alt"></i> Admin Panel</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php?page=admin_dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="index.php?page=admin_manage_users"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="index.php?page=admin_manage_billing_plans" class="active"><i class="fas fa-file-invoice"></i> Billing Plans</a></li>
                    <li><a href="index.php?page=admin_manage_services"><i class="fas fa-cogs"></i> Manage Services</a></li>
                    <li><a href="index.php?page=admin_manage_requests"><i class="fas fa-clipboard-list"></i> Service Requests</a></li>
                    <li><a href="index.php?page=admin_manage_meters"><i class="fas fa-tachometer-alt"></i> Manage Meters</a></li>
                    <li><a href="index.php?page=admin_manage_client_plans"><i class="fas fa-layer-group"></i> Client Plans</a></li>
                    <li><a href="index.php?page=generate_bills"><i class="fas fa-file-invoice-dollar"></i> Generate Bills</a></li>
                    <li><a href="index.php?page=view_bills"><i class="fas fa-list"></i> View Bills</a></li>
                    <li><a href="index.php?page=admin_transactions"><i class="fas fa-money-bill-wave"></i> Transactions</a></li>
                    <li><a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content Area -->
        <div class="main-content" id="mainContent">
            <!-- Header Bar -->
            <div class="header-bar">
                <div class="header-title">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Manage Billing Plans</h1>
                </div>
                <div class="user-info">
                    <div class="user-greeting">Welcome back, <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span></div>
                    <a href="index.php?page=logout" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-container">
                <?php if (isset($error) && $error != ''): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($success) && $success != ''): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-plus-circle"></i> Add New Billing Plan</h2>
                    </div>
                    <form action="index.php?page=admin_manage_billing_plans" method="POST">
                        <input type="hidden" name="action" value="add_plan">
                        <div class="form-group">
                            <label for="plan_name">Plan Name</label>
                            <input type="text" id="plan_name" name="plan_name" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="base_rate">Base Rate (Ksh)</label>
                            <input type="number" id="base_rate" name="base_rate" step="0.01" min="0" value="0.00" required>
                        </div>
                        <div class="form-group">
                            <label for="unit_rate">Unit Rate (Ksh/unit)</label>
                            <input type="number" id="unit_rate" name="unit_rate" step="0.0001" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="min_consumption">Min Consumption (units)</label>
                            <input type="number" id="min_consumption" name="min_consumption" step="0.01" min="0" value="0.00" required>
                        </div>
                        <div class="form-group">
                            <label for="max_consumption">Max Consumption (units) <small>(Optional for tiered plans)</small></label>
                            <input type="number" id="max_consumption" name="max_consumption" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label for="fixed_service_fee">Fixed Service Fee (Ksh) <small>(Optional)</small></label>
                            <input type="number" id="fixed_service_fee" name="fixed_service_fee" step="0.01" min="0" value="0.00">
                        </div>
                        <div class="form-group">
                            <label for="sewer_charge">Sewer Charge (Ksh) <small>(Optional)</small></label>
                            <input type="number" id="sewer_charge" name="sewer_charge" step="0.01" min="0" value="0.00">
                        </div>
                        <div class="form-group">
                            <label for="tax_percent">Tax Percent (%) <small>(e.g., 16 for 16%)</small></label>
                            <input type="number" id="tax_percent" name="tax_percent" step="0.01" min="0" value="0.00">
                        </div>
                        <div class="form-group checkbox">
                            <input type="checkbox" id="tax_inclusive" name="tax_inclusive" value="1">
                            <label for="tax_inclusive">Tax Inclusive (amounts already include tax)</label>
                        </div>
                        <div class="form-group">
                            <label>Tiered Rates</label>
                            <div id="tier-builder" class="tier-builder">
                                <div class="helper-text">Add tiers in simple rows. The last tier can be unlimited.</div>
                                <table class="data-table" id="tier-table">
                                    <thead>
                                        <tr>
                                            <th>Limit (units)</th>
                                            <th>Rate (KSH/unit)</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="number" class="tier-limit" min="1" step="1" placeholder="e.g., 10" required title="Max units for this tier. Kiswahili: Kikomo cha vitengo kwa kiwango hiki."></td>
                                            <td><input type="number" class="tier-rate" min="0" step="0.01" placeholder="e.g., 15" required title="Price per unit for this tier. Kiswahili: Bei kwa kila unit."></td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-tier">Remove</button></td>
                                        </tr>
                                        <tr>
                                            <td><input type="number" class="tier-limit" min="1" step="1" placeholder="e.g., 30" required title="Max units for this tier. Kiswahili: Kikomo cha vitengo kwa kiwango hiki."></td>
                                            <td><input type="number" class="tier-rate" min="0" step="0.01" placeholder="e.g., 18" required title="Price per unit for this tier. Kiswahili: Bei kwa kila unit."></td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-tier">Remove</button></td>
                                        </tr>
                                        <tr>
                                            <td><input type="number" class="tier-limit" min="1" step="1" placeholder="Leave blank for final unlimited" title="Leave blank for unlimited final tier. Kiswahili: Acha tupu kwa kiwango cha mwisho kisicho na kikomo."></td>
                                            <td><input type="number" class="tier-rate" min="0" step="0.01" placeholder="e.g., 22" required title="Price per unit for this tier. Kiswahili: Bei kwa kila unit."></td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-tier">Remove</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="tier-actions">
                                    <button type="button" id="add-tier" class="btn btn-secondary btn-sm"><i class="fas fa-plus"></i> Add Tier</button>
                                    <label class="inline-flex items-center" style="margin-left: 1rem;">
                                        <input type="checkbox" id="final-unlimited" checked title="If checked, last tier has no limit. Kiswahili: Kiwango cha mwisho hakina kikomo.">
                                        <span style="margin-left: 0.5rem;">Treat final tier as unlimited</span>
                                    </label>
                                    <div class="tier-presets" style="margin-left: 1rem; display: inline-flex; gap: .5rem; align-items: center;">
                                        <span class="helper-text" style="font-size: .85rem; color: var(--text-muted);">Presets:</span>
                                        <button type="button" id="preset-2-standard" class="btn btn-secondary btn-sm"><i class="fas fa-magic"></i> 2-tier standard</button>
                                        <button type="button" id="preset-3-progressive" class="btn btn-secondary btn-sm"><i class="fas fa-magic"></i> 3-tier progressive</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Advanced JSON (hidden by default) -->
                            <textarea id="tiers_json" name="tiers_json" style="display:none;" placeholder='{"tiers": [{"limit": 10, "rate": 15.00}, {"rate": 20.00}]}'></textarea>
                            <div class="form-group" style="margin-top: .5rem;">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" id="show-advanced-json">
                                    <span style="margin-left: 0.5rem;">Show advanced JSON field</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="billing_cycle">Billing Cycle</label>
                            <select id="billing_cycle" name="billing_cycle" required>
                                <option value="monthly">Monthly</option>
                                <option value="annually">Annually</option>
                            </select>
                        </div>
                        <div class="form-group checkbox">
                            <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                            <label for="is_active">Active Plan</label>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Plan
                            </button>
                        </div>
                    </form>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-list"></i> Existing Billing Plans</h2>
                    </div>
                    <?php if (!empty($billingPlans)): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Plan Name</th>
                                    <th>Base Rate</th>
                                    <th>Unit Rate</th>
                                    <th>Min</th>
                                    <th>Max</th>
                                    <th>Cycle</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($billingPlans as $plan): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($plan['id'] ?? ''); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($plan['plan_name'] ?? ''); ?></strong>
                                            <div class="text-muted" style="font-size: 0.875rem; margin-top: 0.25rem;">
                                                <?php echo htmlspecialchars($plan['description'] ?? ''); ?>
                                            </div>
                                        </td>
                                        <td>KSH <?php echo isset($plan['base_rate']) ? htmlspecialchars(number_format($plan['base_rate'], 2)) : '0.00'; ?></td>
                <td>KSH <?php echo isset($plan['unit_rate']) ? htmlspecialchars(number_format($plan['unit_rate'], 4)) : '0.0000'; ?></td>
                                        <td><?php echo isset($plan['min_consumption']) ? htmlspecialchars(number_format($plan['min_consumption'], 2)) : '0.00'; ?></td>
                                        <td><?php echo !empty($plan['max_consumption']) ? htmlspecialchars(number_format($plan['max_consumption'], 2)) : 'N/A'; ?></td>
                                        <td><span class="cycle-badge"><?php echo isset($plan['billing_cycle']) ? htmlspecialchars(ucfirst($plan['billing_cycle'])) : ''; ?></span></td>
                                        <td>
                                            <?php if (isset($plan['is_active']) && $plan['is_active']): ?>
                                                <span class="status-badge status-active">Active</span>
                                            <?php else: ?>
                                                <span class="status-badge status-inactive">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <a href="index.php?page=admin_edit_plan&id=<?php echo $plan['id'] ?? ''; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="index.php?page=admin_delete_plan&id=<?php echo $plan['id'] ?? ''; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this plan?');">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color: var(--text-muted); text-align: center; padding: 2rem;">No billing plans found. Add one above!</p>
                    <?php endif; ?>
                </div>
                <script>
                (function(){
                    const table = document.getElementById('tier-table');
                    const addBtn = document.getElementById('add-tier');
                    const finalUnlimited = document.getElementById('final-unlimited');
                    const form = document.querySelector('form[action="index.php?page=admin_manage_billing_plans"]');
                    const jsonField = document.getElementById('tiers_json');
                    const showAdvanced = document.getElementById('show-advanced-json');
                    const preset2Btn = document.getElementById('preset-2-standard');
                    const preset3Btn = document.getElementById('preset-3-progressive');

                    function serializeTiers(){
                        const rows = Array.from(table.querySelectorAll('tbody tr'));
                        const tiers = [];
                        rows.forEach((row, idx) => {
                            const limitEl = row.querySelector('.tier-limit');
                            const rateEl = row.querySelector('.tier-rate');
                            const rate = parseFloat(rateEl.value);
                            const limitVal = limitEl.value.trim();
                            if (!isFinite(rate) || rate < 0) return; // skip invalid
                            const tier = { rate: parseFloat(rate.toFixed(2)) };
                            if (limitVal !== '') {
                                const limit = parseInt(limitVal, 10);
                                if (isFinite(limit) && limit > 0) tier.limit = limit;
                            } else if (!finalUnlimited.checked) {
                                // If not unlimited final, enforce limit; default to previous limit + 1
                                const prevLimit = tiers.length ? tiers[tiers.length-1].limit || 0 : 0;
                                tier.limit = prevLimit + 1;
                            }
                            tiers.push(tier);
                        });
                        return JSON.stringify({ tiers });
                    }

                    function addTierRow(limitPlaceholder, ratePlaceholder, limitValue, rateValue, finalRow=false){
                        const tr = document.createElement('tr');
                        const limitAttr = (limitValue !== null && limitValue !== undefined && limitValue !== '') ? `value="${limitValue}"` : '';
                        const limitTitle = finalRow ? 'Leave blank for unlimited final tier. Kiswahili: Acha tupu kwa kiwango cha mwisho kisicho na kikomo.' : 'Max units for this tier. Kiswahili: Kikomo cha vitengo kwa kiwango hiki.';
                        const rateAttr = (rateValue !== undefined && rateValue !== null) ? `value="${rateValue}"` : '';
                        tr.innerHTML = `
                            <td><input type="number" class="tier-limit" min="1" step="1" placeholder="${limitPlaceholder}" ${limitAttr} title="${limitTitle}"></td>
                            <td><input type="number" class="tier-rate" min="0" step="0.01" placeholder="${ratePlaceholder}" ${rateAttr} required title="Price per unit for this tier. Kiswahili: Bei kwa kila unit."></td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-tier">Remove</button></td>
                        `;
                        table.querySelector('tbody').appendChild(tr);
                    }

                    addBtn.addEventListener('click', function(){
                        addTierRow('e.g., 50', 'e.g., 25', '', undefined, false);
                    });

                    table.addEventListener('click', function(e){
                        if (e.target && e.target.classList.contains('remove-tier')) {
                            const row = e.target.closest('tr');
                            row && row.remove();
                        }
                    });

                    showAdvanced.addEventListener('change', function(){
                        jsonField.style.display = this.checked ? 'block' : 'none';
                    });

                    form.addEventListener('submit', function(){
                        // auto-fill JSON before submit
                        jsonField.value = serializeTiers();
                    });

                    function applyPreset(presetKey){
                        const tbody = table.querySelector('tbody');
                        tbody.innerHTML = '';
                        finalUnlimited.checked = true;
                        if (presetKey === '2-standard') {
                            addTierRow('e.g., 10', 'e.g., 15', 10, 15);
                            addTierRow('Leave blank for final unlimited', 'e.g., 20', '', 20, true);
                        } else if (presetKey === '3-progressive') {
                            addTierRow('e.g., 10', 'e.g., 15', 10, 15);
                            addTierRow('e.g., 30', 'e.g., 18', 30, 18);
                            addTierRow('Leave blank for final unlimited', 'e.g., 22', '', 22, true);
                        }
                    }

                    preset2Btn && preset2Btn.addEventListener('click', function(){ applyPreset('2-standard'); });
                    preset3Btn && preset3Btn.addEventListener('click', function(){ applyPreset('3-progressive'); });
                })();
                </script>
            </div>
        </div>
    </div>

    <!-- FontAwesome for icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            // Toggle sidebar visibility
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('visible');
                mainContent.classList.toggle('full-width');
            });

            // Auto-hide sidebar on mobile when clicking outside
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 992 && 
                    !sidebar.contains(e.target) && 
                    e.target !== sidebarToggle && 
                    !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('visible');
                    mainContent.classList.remove('full-width');
                }
            });

            // Highlight active navigation link
            const currentPath = window.location.search;
            const navLinks = sidebar.querySelectorAll('a');
            navLinks.forEach(link => {
                if (link.href.includes(currentPath)) {
                    link.classList.add('active');
                } else if (currentPath === '' && link.href.includes('admin_dashboard')) {
                    link.classList.add('active');
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (window.innerWidth > 992) {
                sidebar.classList.remove('visible');
                mainContent.classList.remove('full-width');
            }
        });
    </script>
</body>
</html>
