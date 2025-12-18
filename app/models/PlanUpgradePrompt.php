<?php
// app/models/PlanUpgradePrompt.php

require_once '../app/core/Database.php';

class PlanUpgradePrompt {
    private $db;

    public function __construct($database_wrapper) {
        $this->db = $database_wrapper;
        $this->ensureTable();
    }

    /**
     * Create table if it does not exist.
     */
    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS plan_upgrade_prompts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id INT NOT NULL,
            meter_id INT NULL,
            current_plan_id INT NOT NULL,
            recommended_plan_id INT NOT NULL,
            consumption_units DECIMAL(12,2) NOT NULL,
            message TEXT NULL,
            status ENUM('pending','accepted','declined') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_client (client_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->db->query($sql);
        $this->db->execute();
        $this->db->closeStmt();
    }

    /**
     * Create a new upgrade prompt.
     */
    public function createPrompt(array $payload): int|false {
        $this->db->query('INSERT INTO plan_upgrade_prompts (client_id, meter_id, current_plan_id, recommended_plan_id, consumption_units, message, status) VALUES (?, ?, ?, ?, ?, ?, "pending")');
        $this->db->bind([
            (int)$payload['client_id'],
            $payload['meter_id'] ?? null,
            (int)$payload['current_plan_id'],
            (int)$payload['recommended_plan_id'],
            (float)$payload['consumption_units'],
            $payload['message'] ?? null,
        ]);
        if ($this->db->execute()) {
            $id = (int)$this->db->lastInsertId();
            $this->db->closeStmt();
            return $id;
        }
        $this->db->closeStmt();
        return false;
    }

    /**
     * Get latest pending prompt for a client.
     */
    public function getPendingByClient(int $clientId): array|null {
        $this->db->query('SELECT * FROM plan_upgrade_prompts WHERE client_id = ? AND status = "pending" ORDER BY created_at DESC LIMIT 1');
        $this->db->bind([$clientId]);
        $row = $this->db->single();
        $this->db->closeStmt();
        return $row ?: null;
    }

    /**
     * Mark prompt accepted.
     */
    public function markAccepted(int $promptId): bool {
        $this->db->query('UPDATE plan_upgrade_prompts SET status = "accepted" WHERE id = ?');
        $this->db->bind([$promptId]);
        $ok = $this->db->execute();
        $this->db->closeStmt();
        return $ok;
    }

    /**
     * Mark prompt declined.
     */
    public function markDeclined(int $promptId): bool {
        $this->db->query('UPDATE plan_upgrade_prompts SET status = "declined" WHERE id = ?');
        $this->db->bind([$promptId]);
        $ok = $this->db->execute();
        $this->db->closeStmt();
        return $ok;
    }
}
?>

