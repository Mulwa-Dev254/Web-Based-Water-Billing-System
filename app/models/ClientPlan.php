<?php
// app/models/ClientPlan.php

require_once '../app/core/Database.php';

class ClientPlan {
    private $db;

    public function __construct($database_wrapper) {
        $this->db = $database_wrapper;
    }

    /**
     * Subscribes a client to a billing plan.
     *
     * @param int $user_id The ID of the user (client).
     * @param int $plan_id The ID of the billing plan.
     * @param string $status Initial status (e.g., 'pending', 'active').
     * @param string|null $next_billing_date Optional: date for next billing.
     * @return bool True on success, false on failure.
     */
    public function subscribeToPlan($user_id, $plan_id, $status = 'pending', $next_billing_date = null) {
        $this->db->query('INSERT INTO client_plans (user_id, plan_id, status, next_billing_date) VALUES (?, ?, ?, ?)');
        $this->db->bind([$user_id, $plan_id, $status, $next_billing_date]);

        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            error_log("Failed to subscribe client to plan: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Retrieves all plans subscribed by a specific user.
     * Joins with `billing_plans` to get plan details.
     *
     * @param int $user_id The ID of the user.
     * @return array An array of associative arrays, each representing a subscribed plan.
     */
    public function getClientPlansByUserId($user_id) {
        $this->db->query('
            SELECT 
                cp.*, 
                bp.plan_name, bp.description, bp.base_rate, bp.unit_rate, bp.billing_cycle,
                (
                    SELECT p.status 
                    FROM payments p 
                    WHERE p.type = "plan_renewal" AND p.reference_id = cp.id 
                    ORDER BY p.payment_date DESC 
                    LIMIT 1
                ) AS payment_status,
                (
                    SELECT p.payment_date 
                    FROM payments p 
                    WHERE p.type = "plan_renewal" AND p.reference_id = cp.id 
                    ORDER BY p.payment_date DESC 
                    LIMIT 1
                ) AS payment_date
            FROM client_plans cp
            JOIN billing_plans bp ON cp.plan_id = bp.id
            WHERE cp.user_id = ?
            ORDER BY cp.created_at DESC
        ');
        $this->db->bind([$user_id]);
        $plans = $this->db->resultSet();
        $this->db->closeStmt();
        return $plans;
    }

    /**
     * Retrieves a single client plan record by its primary ID.
     *
     * @param int $id The ID of the client_plans record.
     * @return array|false An associative array of client plan data if found, false otherwise.
     */
    public function getClientPlanById($id) {
        $this->db->query('SELECT * FROM client_plans WHERE id = ?');
        $this->db->bind([$id]);
        $plan = $this->db->single();
        $this->db->closeStmt();
        return $plan;
    }

    /**
     * Retrieves a single client plan record by its ID and user ID.
     * This method is used for secure retrieval where the client_plan_id must belong to the given user.
     *
     * @param int $id The ID of the client_plans record.
     * @param int $user_id The ID of the user.
     * @return array|false An associative array of client plan data if found, false otherwise.
     */
    public function getUsersClientPlanById($id, $user_id) {
        $this->db->query('
            SELECT cp.*, bp.plan_name, bp.base_rate, bp.billing_cycle
            FROM client_plans cp
            JOIN billing_plans bp ON cp.plan_id = bp.id
            WHERE cp.id = ? AND cp.user_id = ?
        ');
        $this->db->bind([$id, $user_id]);
        $plan = $this->db->single();
        $this->db->closeStmt();
        return $plan;
    }

    /**
     * Retrieves a client plan by a specific plan_id and user_id.
     * Useful for checking if a client is already subscribed to a particular plan.
     *
     * @param int $plan_id The ID of the billing plan.
     * @param int $user_id The ID of the user.
     * @return array|null An associative array of client plan data if found, null otherwise.
     */
    public function getClientPlanByIdAndUserId($plan_id, $user_id) {
        $this->db->query('
            SELECT cp.*
            FROM client_plans cp
            WHERE cp.plan_id = ? AND cp.user_id = ? AND (cp.status = "active" OR cp.status = "pending")
        ');
        $this->db->bind([$plan_id, $user_id]);
        $plan = $this->db->single();
        $this->db->closeStmt();
        return $plan;
    }

    /**
     * Updates the status of a client's subscribed plan.
     *
     * @param int $client_plan_id The ID of the client's subscribed plan record.
     * @param string $new_status The new status (e.g., 'active', 'inactive', 'cancelled', 'expired').
     * @return bool True on success, false on failure.
     */
    public function updateClientPlanStatus($client_plan_id, $new_status) {
        $this->db->query('UPDATE client_plans SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $this->db->bind([$new_status, $client_plan_id]);
        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            error_log("Failed to update client plan status: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Marks a client plan as paid and updates next billing date.
     *
     * @param int $clientPlanId The ID of the client plan
     * @return bool True if successful, false otherwise
     */
    public function markPlanAsPaid(int $clientPlanId): bool {
        // First get the plan details to determine billing cycle
        $this->db->query('SELECT cp.plan_id, bp.billing_cycle 
                         FROM client_plans cp 
                         JOIN billing_plans bp ON cp.plan_id = bp.id 
                         WHERE cp.id = ?');
        $this->db->bind([$clientPlanId]);
        $planDetails = $this->db->single();
        $this->db->closeStmt();

        if (!$planDetails) {
            error_log("ClientPlan model: Plan details not found for ID " . $clientPlanId);
            return false;
        }

        // Calculate next billing date based on billing cycle
        $next_billing_date = date('Y-m-d', strtotime('+1 ' . $planDetails['billing_cycle']));

        $this->db->query('UPDATE client_plans SET status = "active", last_payment_date = NOW(), next_billing_date = ? WHERE id = ?');
        $this->db->bind([$next_billing_date, $clientPlanId]);
        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            error_log("Failed to mark plan as paid: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Cancels a client's plan by updating its status to 'cancelled'.
     *
     * @param int $client_plan_id The ID of the client's subscribed plan record.
     * @return bool True on success, false on failure.
     */
    public function cancelClientPlan($client_plan_id) {
        return $this->updateClientPlanStatus($client_plan_id, 'cancelled');
    }

    /**
     * Renews a client's plan by updating its status and next billing date.
     *
     * @param int $client_plan_id The ID of the client's subscribed plan record.
     * @param string $billing_cycle The billing cycle (e.g., 'monthly', 'annually') to calculate next billing date.
     * @return bool True on success, false on failure.
     */
    public function renewClientPlan($client_plan_id, $billing_cycle) {
        // Calculate next billing date based on the cycle
        $next_billing_date = date('Y-m-d', strtotime('+1 ' . $billing_cycle));

        $this->db->query('UPDATE client_plans SET status = "active", last_payment_date = CURRENT_TIMESTAMP, next_billing_date = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $this->db->bind([$next_billing_date, $client_plan_id]);
        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            error_log("Failed to renew client plan: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Updates client plan details: plan_id and/or next_billing_date.
     */
    public function updateClientPlanDetails(int $client_plan_id, ?int $new_plan_id, ?string $next_billing_date): bool {
        $setParts = [];
        $binds = [];
        if ($new_plan_id !== null) { $setParts[] = 'plan_id = ?'; $binds[] = $new_plan_id; }
        if ($next_billing_date !== null) { $setParts[] = 'next_billing_date = ?'; $binds[] = $next_billing_date; }
        $setParts[] = 'updated_at = CURRENT_TIMESTAMP';
        $sql = 'UPDATE client_plans SET ' . implode(', ', $setParts) . ' WHERE id = ?';
        $binds[] = $client_plan_id;
        $this->db->query($sql);
        $this->db->bind($binds);
        if ($this->db->execute()) { $this->db->closeStmt(); return true; }
        error_log('Failed to update client plan details: ' . $this->db->getError());
        $this->db->closeStmt();
        return false;
    }
}
