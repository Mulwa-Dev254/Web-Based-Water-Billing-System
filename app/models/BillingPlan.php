<?php
// app/models/BillingPlan.php

// Ensure the Database wrapper is included as this model will use it
require_once '../app/core/Database.php';

class BillingPlan {
    private $db; // Database wrapper instance

    /**
     * Constructor for the BillingPlan model.
     * It requires an instance of the Database wrapper to perform database operations.
     */
    public function __construct($database_wrapper) {
        $this->db = $database_wrapper;
    }

    /**
     * Adds a new billing plan to the 'billing_plans' table.
     *
     * @param string $plan_name The name of the billing plan (e.g., 'Residential Standard').
     * @param string $description A brief description of the plan.
     * @param float $base_rate A fixed charge for the plan.
     * @param float $unit_rate The cost per unit (liter/cubic meter).
     * @param float $min_consumption Minimum consumption for the plan (default 0).
     * @param float|null $max_consumption Maximum consumption for tiered plans (null if no max).
     * @param string $billing_cycle The billing frequency ('monthly' or 'annually').
     * @param bool $is_active Whether the plan is currently active.
     * @return bool True on success, false on failure.
     */
    public function addPlan($plan_name, $description, $base_rate, $unit_rate, $min_consumption, $max_consumption, $billing_cycle, $is_active, $fixed_service_fee = 0.0, $sewer_charge = 0.0, $tax_percent = 0.0, $tax_inclusive = 0, $tiers_json = null) {
        $this->db->query('INSERT INTO billing_plans (plan_name, description, base_rate, unit_rate, min_consumption, max_consumption, billing_cycle, is_active, fixed_service_fee, sewer_charge, tax_percent, tax_inclusive, tiers_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $this->db->bind([$plan_name, $description, $base_rate, $unit_rate, $min_consumption, $max_consumption, $billing_cycle, $is_active, $fixed_service_fee, $sewer_charge, $tax_percent, $tax_inclusive, $tiers_json]);

        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            // Log error: error_log("Failed to add billing plan: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Retrieves all billing plans from the 'billing_plans' table.
     *
     * @return array An array of associative arrays, each representing a billing plan.
     */
    public function getAllPlans() {
        $this->db->query('SELECT * FROM billing_plans ORDER BY plan_name ASC');
        $plans = $this->db->resultSet(); // Fetch all results
        $this->db->closeStmt();
        return $plans;
    }

    /**
     * Retrieves a single billing plan by its ID.
     *
     * @param int $id The ID of the billing plan.
     * @return array|null An associative array representing the plan, or null if not found.
     */
    public function getPlanById($id) {
        $this->db->query('SELECT * FROM billing_plans WHERE id = ?');
        $this->db->bind([$id]);
        $plan = $this->db->single();
        $this->db->closeStmt();
        return $plan;
    }

    /**
     * Updates an existing billing plan.
     *
     * @param int $id The ID of the plan to update.
     * @param string $plan_name The name of the billing plan.
     * @param string $description A brief description of the plan.
     * @param float $base_rate A fixed charge for the plan.
     * @param float $unit_rate The cost per unit (liter/cubic meter).
     * @param float $min_consumption Minimum consumption for the plan.
     * @param float|null $max_consumption Maximum consumption for tiered plans (null if no max).
     * @param string $billing_cycle The billing frequency.
     * @param bool $is_active Whether the plan is currently active.
     * @return bool True on success, false on failure.
     */
    public function updatePlan($id, $plan_name, $description, $base_rate, $unit_rate, $min_consumption, $max_consumption, $billing_cycle, $is_active, $fixed_service_fee = 0.0, $sewer_charge = 0.0, $tax_percent = 0.0, $tax_inclusive = 0, $tiers_json = null) {
        $this->db->query('UPDATE billing_plans SET plan_name = ?, description = ?, base_rate = ?, unit_rate = ?, min_consumption = ?, max_consumption = ?, billing_cycle = ?, is_active = ?, fixed_service_fee = ?, sewer_charge = ?, tax_percent = ?, tax_inclusive = ?, tiers_json = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $this->db->bind([$plan_name, $description, $base_rate, $unit_rate, $min_consumption, $max_consumption, $billing_cycle, $is_active, $fixed_service_fee, $sewer_charge, $tax_percent, $tax_inclusive, $tiers_json, $id]);

        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            // Log error: error_log("Failed to update billing plan: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Deletes a billing plan by its ID.
     *
     * @param int $id The ID of the plan to delete.
     * @return bool True on success, false on failure.
     */
    public function deletePlan($id) {
        $this->db->query('DELETE FROM billing_plans WHERE id = ?');
        $this->db->bind([$id]);

        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            // Log error: error_log("Failed to delete billing plan: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }
}
