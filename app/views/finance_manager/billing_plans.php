<?php
// app/views/finance_manager/billing_plans.php

// Get billing plans data from controller
$billingPlans = $data['billingPlans'] ?? [];
$error = $data['error'] ?? '';
$success = $data['success'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Manager - Billing Plans</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --dark-bg: #1e1e2d;
            --darker-bg: #151521;
            --sidebar-bg: #1a1a27;
            --card-bg: #2a2a3c;
            --text-light: #f8f9fa;
            --text-muted: #a1a5b7;
            --border-color: #2d2d3a;
            --success: #10b981;
            --info: #3b82f6;
            --warning: #f59e0b;
            --danger: #ef4444;
            --purple: #8b5cf6;
        }

        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Dashboard Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 16rem; /* Width of sidebar */
            padding: 1.5rem;
        }

        /* Main Content Styles */

        .header-bar {
            background-color: white;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .content-section {
            background-color: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            background-color: white;
            border: 1px solid #d1d5db;
            color: #1f2937;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border-left: 4px solid var(--success);
            color: var(--success);
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--danger);
            color: var(--danger);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include_once __DIR__ . '/partials/sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Header Bar -->
            <div class="header-bar">
                <h1 class="text-2xl font-bold text-gray-800">Billing Plans Management</h1>
                <div class="flex items-center">
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Finance Manager'); ?></span>
                    <a href="index.php?page=logout" class="ml-4 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </div>
            
            <div class="mb-6">
                <p class="text-gray-600">Create and manage billing plans for customers</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Add New Billing Plan Section -->
            <div class="content-section bg-white rounded-lg shadow">
                <h2 style="color: var(--primary); margin-bottom: 1.5rem;"><i class="fas fa-plus-circle"></i> Add New Billing Plan</h2>
                <form action="index.php?page=finance_manager_billing_plans" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="add_plan">
                    <div class="form-group">
                        <label for="plan_name" class="block text-sm font-medium text-gray-700">Plan Name</label>
                        <input type="text" id="plan_name" name="plan_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                    <div class="form-group">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" required style="resize: vertical; min-height: 100px;" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="base_rate" class="block text-sm font-medium text-gray-700">Base Rate (KSH)</label>
                        <input type="number" id="base_rate" name="base_rate" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                    <div class="form-group">
                        <label for="unit_rate" class="block text-sm font-medium text-gray-700">Unit Rate (KSH/unit)</label>
                        <input type="number" id="unit_rate" name="unit_rate" step="0.0001" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                    <div class="form-group">
                        <label for="min_consumption" class="block text-sm font-medium text-gray-700">Min Consumption (units)</label>
                        <input type="number" id="min_consumption" name="min_consumption" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                    <div class="form-group">
                        <label for="max_consumption" class="block text-sm font-medium text-gray-700">Max Consumption (units)</label>
                        <input type="number" id="max_consumption" name="max_consumption" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label for="fixed_service_fee" class="block text-sm font-medium text-gray-700">Fixed Service Fee (KSH)</label>
                            <input type="number" id="fixed_service_fee" name="fixed_service_fee" step="0.01" min="0" value="0.00" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        </div>
                        <div class="form-group">
                            <label for="sewer_charge" class="block text-sm font-medium text-gray-700">Sewer Charge (KSH)</label>
                            <input type="number" id="sewer_charge" name="sewer_charge" step="0.01" min="0" value="0.00" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        </div>
                        <div class="form-group">
                            <label for="tax_percent" class="block text-sm font-medium text-gray-700">Tax Percent (%)</label>
                            <input type="number" id="tax_percent" name="tax_percent" step="0.01" min="0" value="0.00" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="tax_inclusive" name="tax_inclusive" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Tax Inclusive (amounts already include tax)</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700">Tiered Rates</label>
                        <div id="fm-tier-builder" class="space-y-2">
                            <p class="text-xs text-gray-500">Add tiers in simple rows. The last tier can be unlimited.</p>
                            <table class="transactions-table w-full" id="fm-tier-table">
                                <thead>
                                    <tr>
                                        <th class="text-left py-2 px-3 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Limit (units)</th>
                                        <th class="text-left py-2 px-3 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Rate (KSH/unit)</th>
                                        <th class="text-left py-2 px-3 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr>
                                        <td class="py-2 px-3"><input type="number" class="fm-tier-limit mt-1 block w-full rounded-md border-gray-300" min="1" step="1" placeholder="e.g., 10" required title="Max units for this tier. Kiswahili: Kikomo cha vitengo kwa kiwango hiki."></td>
                                        <td class="py-2 px-3"><input type="number" class="fm-tier-rate mt-1 block w-full rounded-md border-gray-300" min="0" step="0.01" placeholder="e.g., 15" required title="Price per unit for this tier. Kiswahili: Bei kwa kila unit."></td>
                                        <td class="py-2 px-3"><button type="button" class="inline-flex items-center px-2 py-1 bg-red-600 text-white rounded-md fm-remove-tier">Remove</button></td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-3"><input type="number" class="fm-tier-limit mt-1 block w-full rounded-md border-gray-300" min="1" step="1" placeholder="e.g., 30" required title="Max units for this tier. Kiswahili: Kikomo cha vitengo kwa kiwango hiki."></td>
                                        <td class="py-2 px-3"><input type="number" class="fm-tier-rate mt-1 block w-full rounded-md border-gray-300" min="0" step="0.01" placeholder="e.g., 18" required title="Price per unit for this tier. Kiswahili: Bei kwa kila unit."></td>
                                        <td class="py-2 px-3"><button type="button" class="inline-flex items-center px-2 py-1 bg-red-600 text-white rounded-md fm-remove-tier">Remove</button></td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-3"><input type="number" class="fm-tier-limit mt-1 block w-full rounded-md border-gray-300" min="1" step="1" placeholder="Leave blank for final unlimited" title="Leave blank for unlimited final tier. Kiswahili: Acha tupu kwa kiwango cha mwisho kisicho na kikomo."></td>
                                        <td class="py-2 px-3"><input type="number" class="fm-tier-rate mt-1 block w-full rounded-md border-gray-300" min="0" step="0.01" placeholder="e.g., 22" required title="Price per unit for this tier. Kiswahili: Bei kwa kila unit."></td>
                                        <td class="py-2 px-3"><button type="button" class="inline-flex items-center px-2 py-1 bg-red-600 text-white rounded-md fm-remove-tier">Remove</button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="flex items-center space-x-3">
                                <button type="button" id="fm-add-tier" class="inline-flex items-center px-3 py-1 bg-gray-700 text-white rounded-md"><i class="fas fa-plus mr-1"></i> Add Tier</button>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" id="fm-final-unlimited" class="rounded" checked title="If checked, last tier has no limit. Kiswahili: Kiwango cha mwisho hakina kikomo.">
                                    <span class="ml-2 text-sm text-gray-700">Treat final tier as unlimited</span>
                                </label>
                            </div>
                            <div class="flex items-center space-x-2 mt-2">
                                <span class="text-xs text-gray-500">Presets:</span>
                                <button type="button" id="fm-preset-2-standard" class="inline-flex items-center px-3 py-1 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"><i class="fas fa-magic mr-1"></i> 2-tier standard</button>
                                <button type="button" id="fm-preset-3-progressive" class="inline-flex items-center px-3 py-1 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"><i class="fas fa-magic mr-1"></i> 3-tier progressive</button>
                            </div>
                        </div>
                        <!-- Advanced JSON (hidden by default) -->
                        <textarea id="tiers_json" name="tiers_json" class="mt-1 block w-full rounded-md border-gray-300" style="display:none;" placeholder='{"tiers": [{"limit": 10, "rate": 15.00}, {"rate": 20.00}]}'></textarea>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="fm-show-advanced-json" class="rounded">
                                <span class="ml-2 text-sm text-gray-700">Show advanced JSON field</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="billing_cycle" class="block text-sm font-medium text-gray-700">Billing Cycle</label>
                        <select id="billing_cycle" name="billing_cycle" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <option value="monthly">Monthly</option>
                            <option value="annually">Annually</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Active Plan</span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 flex items-center"><i class="fas fa-save mr-2"></i> Add Plan</button>
                </form>
            </div>

            <!-- Existing Billing Plans Section -->
            <div class="content-section bg-white rounded-lg shadow">
                <h2 class="text-xl font-semibold text-blue-600 mb-4 flex items-center"><i class="fas fa-list mr-2"></i> Existing Billing Plans</h2>
                <?php if (!empty($billingPlans)): ?>
                    <div class="overflow-x-auto">
                        <table class="transactions-table w-full">
                            <thead>
                                <tr>
                                    <th class="text-left py-3 px-4 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="text-left py-3 px-4 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Plan Name</th>
                                    <th class="text-left py-3 px-4 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Base Rate</th>
                                    <th class="text-left py-3 px-4 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Rate</th>
                                    <th class="text-left py-3 px-4 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Min</th>
                                    <th class="text-left py-3 px-4 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Max</th>
                                    <th class="text-left py-3 px-4 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Cycle</th>
                                    <th class="text-left py-3 px-4 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="text-left py-3 px-4 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($billingPlans as $plan): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-4 px-4"><?php echo htmlspecialchars($plan['id'] ?? ''); ?></td>
                                        <td class="py-4 px-4">
                                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($plan['plan_name'] ?? ''); ?></div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($plan['description'] ?? ''); ?>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">KSH <?php echo isset($plan['base_rate']) ? htmlspecialchars(number_format($plan['base_rate'], 2)) : '0.00'; ?></td>
                                        <td class="py-4 px-4">KSH <?php echo isset($plan['unit_rate']) ? htmlspecialchars(number_format($plan['unit_rate'], 4)) : '0.0000'; ?></td>
                                        <td class="py-4 px-4"><?php echo isset($plan['min_consumption']) ? htmlspecialchars(number_format($plan['min_consumption'], 2)) : '0.00'; ?></td>
                                        <td class="py-4 px-4"><?php echo !empty($plan['max_consumption']) ? htmlspecialchars(number_format($plan['max_consumption'], 2)) : 'N/A'; ?></td>
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?php echo isset($plan['billing_cycle']) ? htmlspecialchars(ucfirst($plan['billing_cycle'])) : ''; ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <?php if (isset($plan['is_active']) && $plan['is_active']): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex space-x-2">
                                                <form action="index.php?page=finance_manager_edit_plan" method="POST">
                                                    <input type="hidden" name="plan_id" value="<?php echo $plan['id'] ?? ''; ?>">
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                        <i class="fas fa-edit mr-1"></i> Edit
                                                    </button>
                                                </form>
                                                <form action="index.php?page=finance_manager_billing_plans" method="POST" onsubmit="return confirm('Are you sure you want to delete this plan?');">
                                                    <input type="hidden" name="action" value="delete_plan">
                                                    <input type="hidden" name="plan_id" value="<?php echo $plan['id'] ?? ''; ?>">
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                        <i class="fas fa-trash-alt mr-1"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="py-8 text-center text-gray-500 bg-gray-50 rounded-lg">
                        <p class="text-lg">No billing plans found. Add one above!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Script to enhance UI interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation enhancement
            const planForm = document.querySelector('form[action="index.php?page=finance_manager_billing_plans"]');
            if (planForm) {
                planForm.addEventListener('submit', function(e) {
                    const planName = document.getElementById('plan_name').value.trim();
                    const baseRate = document.getElementById('base_rate').value;
                    const unitRate = document.getElementById('unit_rate').value;
                    
                    if (planName === '') {
                        e.preventDefault();
                        alert('Please enter a plan name');
                        return false;
                    }
                    
                    if (parseFloat(baseRate) < 0 || parseFloat(unitRate) < 0) {
                        e.preventDefault();
                        alert('Rates cannot be negative');
                        return false;
                    }
                });
            }
            
            // Table row hover effect
            const tableRows = document.querySelectorAll('.transactions-table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.classList.add('bg-gray-50');
                });
                row.addEventListener('mouseleave', function() {
                    this.classList.remove('bg-gray-50');
                });
            });

            // Tier builder logic
            const fmTable = document.getElementById('fm-tier-table');
            const fmAddBtn = document.getElementById('fm-add-tier');
            const fmFinalUnlimited = document.getElementById('fm-final-unlimited');
            const fmForm = document.querySelector('form[action="index.php?page=finance_manager_billing_plans"]');
            const fmJsonField = document.getElementById('tiers_json');
            const fmShowAdvanced = document.getElementById('fm-show-advanced-json');
            const fmPreset2Btn = document.getElementById('fm-preset-2-standard');
            const fmPreset3Btn = document.getElementById('fm-preset-3-progressive');

            function fmSerializeTiers(){
                const rows = Array.from(fmTable.querySelectorAll('tbody tr'));
                const tiers = [];
                rows.forEach((row) => {
                    const limitEl = row.querySelector('.fm-tier-limit');
                    const rateEl = row.querySelector('.fm-tier-rate');
                    const rate = parseFloat(rateEl.value);
                    const limitVal = limitEl.value.trim();
                    if (!isFinite(rate) || rate < 0) return;
                    const tier = { rate: parseFloat(rate.toFixed(2)) };
                    if (limitVal !== '') {
                        const limit = parseInt(limitVal, 10);
                        if (isFinite(limit) && limit > 0) tier.limit = limit;
                    } else if (!fmFinalUnlimited.checked) {
                        const prevLimit = tiers.length ? tiers[tiers.length-1].limit || 0 : 0;
                        tier.limit = prevLimit + 1;
                    }
                    tiers.push(tier);
                });
                return JSON.stringify({ tiers });
            }

            function fmAddTierRow(limitPlaceholder, ratePlaceholder, limitValue, rateValue, finalRow=false){
                const tr = document.createElement('tr');
                const limitAttr = (limitValue !== null && limitValue !== undefined && limitValue !== '') ? `value="${limitValue}"` : '';
                const limitTitle = finalRow ? 'Leave blank for unlimited final tier. Kiswahili: Acha tupu kwa kiwango cha mwisho kisicho na kikomo.' : 'Max units for this tier. Kiswahili: Kikomo cha vitengo kwa kiwango hiki.';
                const rateAttr = (rateValue !== undefined && rateValue !== null) ? `value="${rateValue}"` : '';
                tr.innerHTML = `
                    <td class="py-2 px-3"><input type="number" class="fm-tier-limit mt-1 block w-full rounded-md border-gray-300" min="1" step="1" placeholder="${limitPlaceholder}" ${limitAttr} title="${limitTitle}"></td>
                    <td class="py-2 px-3"><input type="number" class="fm-tier-rate mt-1 block w-full rounded-md border-gray-300" min="0" step="0.01" placeholder="${ratePlaceholder}" ${rateAttr} required title="Price per unit for this tier. Kiswahili: Bei kwa kila unit."></td>
                    <td class="py-2 px-3"><button type="button" class="inline-flex items-center px-2 py-1 bg-red-600 text-white rounded-md fm-remove-tier">Remove</button></td>
                `;
                fmTable.querySelector('tbody').appendChild(tr);
            }

            fmAddBtn.addEventListener('click', function(){
                fmAddTierRow('e.g., 50', 'e.g., 25', '', undefined, false);
            });

            fmTable.addEventListener('click', function(e){
                if (e.target && e.target.classList.contains('fm-remove-tier')) {
                    const row = e.target.closest('tr');
                    row && row.remove();
                }
            });

            fmShowAdvanced.addEventListener('change', function(){
                fmJsonField.style.display = this.checked ? 'block' : 'none';
            });

            fmForm.addEventListener('submit', function(){
                fmJsonField.value = fmSerializeTiers();
            });

            function fmApplyPreset(presetKey){
                const tbody = fmTable.querySelector('tbody');
                tbody.innerHTML = '';
                fmFinalUnlimited.checked = true;
                if (presetKey === '2-standard') {
                    fmAddTierRow('e.g., 10', 'e.g., 15', 10, 15);
                    fmAddTierRow('Leave blank for final unlimited', 'e.g., 20', '', 20, true);
                } else if (presetKey === '3-progressive') {
                    fmAddTierRow('e.g., 10', 'e.g., 15', 10, 15);
                    fmAddTierRow('e.g., 30', 'e.g., 18', 30, 18);
                    fmAddTierRow('Leave blank for final unlimited', 'e.g., 22', '', 22, true);
                }
            }

            fmPreset2Btn && fmPreset2Btn.addEventListener('click', function(){ fmApplyPreset('2-standard'); });
            fmPreset3Btn && fmPreset3Btn.addEventListener('click', function(){ fmApplyPreset('3-progressive'); });
        });
    </script>
</body>
</html>
