<?php
// app/models/BillingEngine.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/MeterReading.php';
require_once __DIR__ . '/BillingPlan.php';
require_once __DIR__ . '/Bill.php';
require_once __DIR__ . '/ClientPlan.php';

class BillingEngine {
    private Database $db;
    private MeterReading $meterReadingModel;
    private BillingPlan $billingPlanModel;
    private Bill $billModel;
    private ClientPlan $clientPlanModel;

    public function __construct(Database $database_instance) {
        $this->db = $database_instance;
        $this->meterReadingModel = new MeterReading($this->db);
        $this->billingPlanModel = new BillingPlan($this->db);
        $this->billModel = new Bill($this->db);
        $this->clientPlanModel = new ClientPlan($this->db);
    }

    /**
     * Calculate consumption between two meter readings
     * 
     * @param float $currentReading The current meter reading value
     * @param float $previousReading The previous meter reading value
     * @return float The consumption in units
     */
    public function calculateConsumption(float $currentReading, float $previousReading): float {
        // Basic calculation: current - previous
        // Handle potential meter reset or replacement (when current < previous)
        if ($currentReading < $previousReading) {
            // Assuming meter was reset or replaced
            return $currentReading;
        }
        
        return $currentReading - $previousReading;
    }

    /**
     * Calculate bill amount based on consumption and billing plan
     * 
     * @param float $consumption The consumption in units
     * @param array $billingPlan The billing plan details
     * @return float The calculated bill amount
     */
    public function calculateBillAmount(float $consumption, array $billingPlan): float {
        // Obtain the full plan row when possible for richer fields (tiers/charges)
        $fullPlan = $billingPlan;
        if (isset($billingPlan['plan_id'])) {
            $full = $this->billingPlanModel->getPlanById((int)$billingPlan['plan_id']);
            if (is_array($full)) {
                $fullPlan = array_merge($fullPlan, $full);
            }
        }

        // Base and simple linear rate
        $baseRate = (float)($fullPlan['base_rate'] ?? 0);
        $unitRate = (float)($fullPlan['unit_rate'] ?? 0);
        $minConsumption = (float)($fullPlan['min_consumption'] ?? 0);
        $maxConsumption = (float)($fullPlan['max_consumption'] ?? PHP_FLOAT_MAX);

        // Optional fixed charges
        $fixedServiceFee = (float)($fullPlan['fixed_service_fee'] ?? 0);
        $sewerCharge = (float)($fullPlan['sewer_charge'] ?? 0);
        $taxPercent = (float)($fullPlan['tax_percent'] ?? 0); // e.g., 16 for 16%
        $taxInclusive = isset($fullPlan['tax_inclusive']) ? (bool)$fullPlan['tax_inclusive'] : false;

        // Apply minimum/maximum consumption guards
        $billableConsumption = max($consumption, $minConsumption);
        if ($maxConsumption > 0 && $billableConsumption > $maxConsumption) {
            $billableConsumption = $maxConsumption;
        }

        // Tiered blocks support (if present). Supported sources:
        // - Columns: tierN_limit, tierN_rate (N=1..5)
        // - Column: tiers_json (JSON: {"tiers":[{"limit":x,"rate":y}, {"rate":z}]})
        $tierCharges = 0.0;
        $tiers = [];

        // Parse tiers from columns
        for ($i = 1; $i <= 5; $i++) {
            $limitKey = 'tier' . $i . '_limit';
            $rateKey = 'tier' . $i . '_rate';
            if (isset($fullPlan[$rateKey])) {
                $tiers[] = [
                    'limit' => isset($fullPlan[$limitKey]) && $fullPlan[$limitKey] !== null ? (float)$fullPlan[$limitKey] : null,
                    'rate' => (float)$fullPlan[$rateKey]
                ];
            }
        }

        // If no column-based tiers, try JSON config
        if (empty($tiers) && isset($fullPlan['tiers_json']) && is_string($fullPlan['tiers_json'])) {
            $decoded = json_decode($fullPlan['tiers_json'], true);
            if (is_array($decoded) && isset($decoded['tiers']) && is_array($decoded['tiers'])) {
                foreach ($decoded['tiers'] as $t) {
                    $tiers[] = [
                        'limit' => isset($t['limit']) ? (float)$t['limit'] : null,
                        'rate' => isset($t['rate']) ? (float)$t['rate'] : 0.0,
                    ];
                }
            }
        }

        if (!empty($tiers)) {
            // Compute block rates. Each tier consumes up to its limit; last tier may be unlimited.
            $remaining = $billableConsumption;
            foreach ($tiers as $idx => $tier) {
                $rate = (float)$tier['rate'];
                $limit = $tier['limit'];
                if ($limit === null) {
                    // No cap for last tier
                    $take = $remaining;
                } else {
                    $take = max(0, min($remaining, $limit));
                }
                if ($take <= 0) {
                    continue;
                }
                $tierCharges += $take * $rate;
                $remaining -= $take;
                if ($remaining <= 0) {
                    break;
                }
            }
            // If consumption exceeds defined tiers and no unlimited tier, charge remaining at last tier rate or unitRate fallback
            if ($remaining > 0) {
                $lastRate = !empty($tiers) ? (float)($tiers[count($tiers) - 1]['rate'] ?? $unitRate) : $unitRate;
                $tierCharges += $remaining * $lastRate;
            }
            $variableCharge = $tierCharges;
        } else {
            // Linear rate fallback
            $variableCharge = $billableConsumption * $unitRate;
        }

        // Sum base and fixed charges
        $subtotal = $baseRate + $fixedServiceFee + $sewerCharge + $variableCharge;

        // Apply tax if configured
        $total = $subtotal;
        if ($taxPercent > 0) {
            if ($taxInclusive) {
                // Already included; leave as is
            } else {
                $total += ($subtotal * ($taxPercent / 100.0));
            }
        }

        return round($total, 2);
    }

    /**
     * Generate a bill for a client based on meter readings
     * 
     * @param int $clientId The client ID
     * @param int $meterId The meter ID
     * @param int $readingIdStart The starting meter reading ID
     * @param int $readingIdEnd The ending meter reading ID
     * @return int|bool The bill ID if successful, false otherwise
     */
    public function generateBill(int $clientId, int $meterId, int $readingIdStart, int $readingIdEnd): int|bool {
        // Resolve whether $clientId is clients.id or users.id
        $clientsTableId = null;
        $clientUserId = null;
        // Try treat $clientId as clients.id
        $this->db->query('SELECT c.id as clients_id, c.user_id as users_id FROM clients c WHERE c.id = ? LIMIT 1');
        $this->db->bind([$clientId]);
        $c1 = $this->db->single();
        $this->db->closeStmt();
        if ($c1 && isset($c1['clients_id'])) {
            $clientsTableId = (int)$c1['clients_id'];
            $clientUserId = (int)$c1['users_id'];
        } else {
            // Fallback: treat $clientId as users.id and find clients row
            $this->db->query('SELECT c.id as clients_id, c.user_id as users_id FROM clients c WHERE c.user_id = ? LIMIT 1');
            $this->db->bind([$clientId]);
            $c2 = $this->db->single();
            $this->db->closeStmt();
            if ($c2 && isset($c2['clients_id'])) {
                $clientsTableId = (int)$c2['clients_id'];
                $clientUserId = (int)$c2['users_id'];
            }
        }
        if (!$clientsTableId || !$clientUserId) {
            error_log("Could not resolve client mapping for ID: {$clientId}");
            return false;
        }

        // Get the client's active or pending billing plan using users.id
        $clientPlans = $this->clientPlanModel->getClientPlansByUserId($clientUserId);
        if (empty($clientPlans)) {
            error_log("No active billing plan found for client user ID: {$clientUserId}");
            return false;
        }
        
        // Pick plan: prefer active; fallback to latest pending
        $activePlan = null;
        foreach ($clientPlans as $plan) {
            if (($plan['status'] ?? '') === 'active') { $activePlan = $plan; break; }
        }
        if (!$activePlan) {
            foreach ($clientPlans as $plan) {
                if (($plan['status'] ?? '') === 'pending') { $activePlan = $plan; break; }
            }
        }
        if (!$activePlan) {
            error_log("No active or pending billing plan found for client user ID: {$clientUserId}");
            return false;
        }
        
        // Get the readings
        $this->db->query('SELECT * FROM meter_readings WHERE id = ?');
        $this->db->bind([$readingIdStart]);
        $startReading = $this->db->single();
        $this->db->closeStmt();
        
        $this->db->query('SELECT * FROM meter_readings WHERE id = ?');
        $this->db->bind([$readingIdEnd]);
        $endReading = $this->db->single();
        $this->db->closeStmt();
        
        if (!$startReading || !$endReading) {
            error_log("Could not find meter readings with IDs: {$readingIdStart} and {$readingIdEnd}");
            return false;
        }
        
        // Calculate consumption
        $consumption = $this->calculateConsumption(
            (float)$endReading['reading_value'], 
            (float)$startReading['reading_value']
        );
        
        // Calculate bill amount
        $billAmount = $this->calculateBillAmount($consumption, $activePlan);
        
        // Determine billing period dates
        $billingPeriodStart = $startReading['reading_date'];
        $billingPeriodEnd = $endReading['reading_date'];
        
        // Calculate due date (typically 14 days from bill date)
        $dueDate = date('Y-m-d', strtotime('+14 days'));
        
        // Create the bill record
        $this->db->query('INSERT INTO bills (
            client_id, meter_id, reading_id_start, reading_id_end, 
            bill_date, due_date, consumption_units, amount_due, 
            balance, payment_status, billing_period_start, billing_period_end
        ) VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, "pending", ?, ?)');
        
        $this->db->bind([
            $clientsTableId, $meterId, $readingIdStart, $readingIdEnd,
            $dueDate, $consumption, $billAmount, $billAmount,
            $billingPeriodStart, $billingPeriodEnd
        ]);
        
        if ($this->db->execute()) {
            $billId = $this->db->lastInsertId();
            $this->db->closeStmt();
            return $billId;
        } else {
            error_log("Failed to create bill: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Generate bills for all clients with new meter readings
     * 
     * @return array Array of results with success/failure information
     */
    public function generateAllPendingBills(): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        // Get all meters with readings that haven't been billed yet
        $this->db->query('SELECT 
            m.id as meter_id, 
            m.client_id, 
            MAX(mr.id) as latest_reading_id,
            (SELECT MAX(b.reading_id_end) FROM bills b WHERE b.meter_id = m.id) as last_billed_reading_id
        FROM 
            meters m
        JOIN 
            meter_readings mr ON m.id = mr.meter_id
        GROUP BY 
            m.id
        HAVING 
            latest_reading_id > IFNULL(last_billed_reading_id, 0) OR last_billed_reading_id IS NULL');
        
        $pendingMeters = $this->db->resultSet();
        $this->db->closeStmt();
        
        foreach ($pendingMeters as $meter) {
            // Get the last billed reading ID or 0 if none exists
            $lastBilledReadingId = $meter['last_billed_reading_id'] ?? 0;
            
            // If there's no previous billing, get the earliest reading for this meter
            if ($lastBilledReadingId == 0) {
                $this->db->query('SELECT MIN(id) as first_reading_id FROM meter_readings WHERE meter_id = ?');
                $this->db->bind([$meter['meter_id']]);
                $firstReading = $this->db->single();
                $this->db->closeStmt();
                
                if ($firstReading && isset($firstReading['first_reading_id'])) {
                    $lastBilledReadingId = $firstReading['first_reading_id'];
                }
            }
            
            // Generate bill using the last billed reading as start and latest reading as end
            $billId = $this->generateBill(
                $meter['client_id'],
                $meter['meter_id'],
                $lastBilledReadingId,
                $meter['latest_reading_id']
            );
            
            if ($billId) {
                $results['success']++;
                $results['details'][] = [
                    'client_id' => $meter['client_id'],
                    'meter_id' => $meter['meter_id'],
                    'bill_id' => $billId,
                    'status' => 'success'
                ];
            } else {
                $results['failed']++;
                $results['details'][] = [
                    'client_id' => $meter['client_id'],
                    'meter_id' => $meter['meter_id'],
                    'status' => 'failed',
                    'reason' => 'Failed to generate bill'
                ];
            }
        }
        
        return $results;
    }

    public function getPendingMetersForBilling(): array {
        $this->db->query('SELECT 
            m.id as meter_id,
            m.client_id,
            MAX(mr.id) as latest_reading_id,
            (SELECT MAX(b.reading_id_end) FROM bills b WHERE b.meter_id = m.id) as last_billed_reading_id
        FROM meters m
        JOIN meter_readings mr ON m.id = mr.meter_id
        GROUP BY m.id
        HAVING latest_reading_id > IFNULL(last_billed_reading_id, 0) OR last_billed_reading_id IS NULL');
        $rows = $this->db->resultSet();
        $this->db->closeStmt();
        return $rows ?: [];
    }

    /**
     * Get billing summary for a specific client
     * 
     * @param int $clientId The client ID
     * @return array The billing summary
     */
    public function getClientBillingSummary(int $clientId): array {
        $this->db->query('SELECT 
            COUNT(*) as total_bills,
            SUM(amount_due) as total_amount_due,
            SUM(amount_paid) as total_amount_paid,
            SUM(balance) as total_balance,
            COUNT(CASE WHEN payment_status = "paid" THEN 1 END) as paid_bills,
            COUNT(CASE WHEN payment_status = "pending" THEN 1 END) as pending_bills,
            COUNT(CASE WHEN payment_status = "partially_paid" THEN 1 END) as partially_paid_bills,
            COUNT(CASE WHEN payment_status = "overdue" THEN 1 END) as overdue_bills,
            AVG(consumption_units) as average_consumption
        FROM 
            bills
        WHERE 
            client_id = ?');
        
        $this->db->bind([$clientId]);
        $summary = $this->db->single();
        $this->db->closeStmt();
        
        return $summary ?: [
            'total_bills' => 0,
            'total_amount_due' => 0,
            'total_amount_paid' => 0,
            'total_balance' => 0,
            'paid_bills' => 0,
            'pending_bills' => 0,
            'partially_paid_bills' => 0,
            'overdue_bills' => 0,
            'average_consumption' => 0
        ];
    }

    /**
     * Update overdue bills status
     * 
     * @return int Number of bills updated
     */
    public function updateOverdueBills(): int {
        $this->db->query('UPDATE bills 
                         SET payment_status = "overdue" 
                         WHERE payment_status = "pending" 
                         AND due_date < CURDATE()');
        
        $updated = $this->db->rowCount();
        $this->db->closeStmt();
        
        return $updated;
    }

    /**
     * Recommend the best higher plan for a given consumption.
     * - Consider only active plans.
     * - Filter to plans whose max_consumption is null (unlimited) or >= consumption.
     * - Use calculateBillAmount to estimate cost and pick the lowest cost option.
     * - If current plan already qualifies and is lowest cost, return null.
     *
     * @param int $clientId
     * @param int $currentPlanId
     * @param float $consumption
     * @return array|null Recommended plan row or null if no better option
     */
    public function recommendBestPlanForConsumption(int $clientId, int $currentPlanId, float $consumption): array|null {
        // Fetch all plans and the client's current plan
        $this->db->query('SELECT * FROM billing_plans WHERE is_active = 1');
        $plans = $this->db->resultSet();
        $this->db->closeStmt();

        if (empty($plans)) { return null; }

        $currentPlan = null;
        foreach ($plans as $p) {
            if ((int)$p['id'] === (int)$currentPlanId) { $currentPlan = $p; break; }
        }
        if (!$currentPlan) {
            // Attempt to retrieve current from table even if not active
            $currentPlan = $this->billingPlanModel->getPlanById($currentPlanId) ?? null;
        }

        // Candidate plans that can accommodate the consumption
        $candidates = [];
        foreach ($plans as $p) {
            $max = $p['max_consumption'];
            $maxOk = ($max === null || $max === '' || (float)$max >= $consumption);
            if ($maxOk) {
                // Estimate cost under this plan using tier logic
                $estimate = $this->calculateBillAmount($consumption, $p);
                $candidates[] = [ 'plan' => $p, 'estimate' => $estimate ];
            }
        }
        if (empty($candidates)) { return null; }

        // Also compute cost for current plan (if available)
        $currentEstimate = null;
        if ($currentPlan) {
            $currentEstimate = $this->calculateBillAmount($consumption, $currentPlan);
        }

        // Find lowest estimate among candidates
        usort($candidates, function($a, $b) {
            return $a['estimate'] <=> $b['estimate'];
        });

        $best = $candidates[0];

        // If best is the same as current plan or not cheaper, skip recommendation
        if ($currentPlan && (int)$best['plan']['id'] === (int)$currentPlanId) {
            return null;
        }
        if ($currentEstimate !== null && $best['estimate'] >= $currentEstimate) {
            return null;
        }

        return $best['plan'];
    }
}
