<?php

class VerifiedMeter {
    private Database $db;

    public function __construct(Database $database_instance) {
        $this->db = $database_instance;
    }

    private function ensureTable(): void {
        $this->db->query('CREATE TABLE IF NOT EXISTS verified_meters (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id INT NOT NULL,
            client_name VARCHAR(255) NOT NULL,
            meter_id INT NOT NULL,
            meter_serial VARCHAR(255) NOT NULL,
            meter_status VARCHAR(64) NOT NULL,
            initial_reading DECIMAL(10,2) DEFAULT 0,
            current_reading DECIMAL(10,2) DEFAULT 0,
            verification_date DATETIME NOT NULL,
            admin_id INT NOT NULL,
            admin_name VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_client_id (client_id),
            INDEX idx_meter_id (meter_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        // ignore result; table will exist or already existed
        $this->db->execute();
    }

    public function create(array $data): bool {
        try {
            $this->db->query('INSERT INTO verified_meters (client_id, client_name, meter_id, meter_serial, meter_status, initial_reading, current_reading, verification_date, admin_id, admin_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        } catch (Exception $e) {
            // Likely table missing - ensure and retry
            $this->ensureTable();
            $this->db->query('INSERT INTO verified_meters (client_id, client_name, meter_id, meter_serial, meter_status, initial_reading, current_reading, verification_date, admin_id, admin_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        }
        $this->db->bind([
            (int)$data['client_id'],
            (string)$data['client_name'],
            (int)$data['meter_id'],
            (string)$data['meter_serial'],
            (string)$data['meter_status'],
            (float)($data['initial_reading'] ?? 0),
            (float)($data['current_reading'] ?? ($data['initial_reading'] ?? 0)),
            (string)$data['verification_date'],
            (int)$data['admin_id'],
            (string)$data['admin_name'],
        ]);
        return $this->db->execute();
    }

    public function getByClientId(int $clientId): array {
        $sql = 'SELECT vm.*, m.photo_url, m.meter_type, m.gps_location, m.assigned_collector_id, m.status AS meter_table_status
                FROM verified_meters vm
                LEFT JOIN meters m ON vm.meter_id = m.id
                WHERE vm.client_id = ?
                ORDER BY vm.verification_date DESC';
        try {
            $this->db->query($sql);
        } catch (Exception $e) {
            $this->ensureTable();
            $this->db->query($sql);
        }
        $this->db->bind([$clientId]);
        return $this->db->resultSet();
    }

    public function updateStatusByMeterId(int $meterId, string $status): bool {
        $this->ensureTable();
        $this->db->query('UPDATE verified_meters SET meter_status = ? WHERE meter_id = ?');
        $this->db->bind([$status, $meterId]);
        return $this->db->execute();
    }

    public function getWaitingInstallations(): array {
        $sql = 'SELECT vm.*, u.username as client_username, m.serial_number as meter_serial, m.id as meter_id
                FROM verified_meters vm
                LEFT JOIN users u ON vm.client_id = u.id
                LEFT JOIN meters m ON vm.meter_id = m.id
                WHERE vm.meter_status = "waiting_installation"
                ORDER BY vm.verification_date DESC';
        $this->db->query($sql);
        return $this->db->resultSet();
    }
}


