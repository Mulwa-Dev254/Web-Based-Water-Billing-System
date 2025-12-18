<?php

class MeterInstallation {
    private Database $db;

    public function __construct(Database $database_instance) {
        $this->db = $database_instance;
    }

    private function ensureTable(): void {
        // Create table if missing
        $this->db->query("CREATE TABLE IF NOT EXISTS meter_installations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            meter_id INT NOT NULL,
            client_id INT NOT NULL,
            installer_user_id INT NOT NULL,
            initial_reading DECIMAL(10,2) DEFAULT 0,
            photo_url VARCHAR(255) DEFAULT NULL,
            gps_location VARCHAR(64) DEFAULT NULL,
            notes TEXT,
            status ENUM('waiting_installation','submitted','approved','installed','rejected','cancelled') DEFAULT 'submitted',
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            reviewed_by INT DEFAULT NULL,
            reviewed_at DATETIME DEFAULT NULL,
            review_notes TEXT,
            INDEX idx_meter (meter_id),
            INDEX idx_installer (installer_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $this->db->execute();

        // Ensure enum includes 'installed' for existing tables
        $this->db->query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'meter_installations' AND COLUMN_NAME = 'status'");
        $row = $this->db->single();
        $columnType = is_array($row) ? ($row['COLUMN_TYPE'] ?? '') : '';
        if ($columnType) {
            $required = ["waiting_installation","submitted","approved","installed","rejected","cancelled"];
            $needsAlter = false;
            foreach ($required as $val) {
                if (strpos($columnType, "'{$val}'") === false) {
                    $needsAlter = true;
                    break;
                }
            }
            if ($needsAlter) {
                $this->db->query("ALTER TABLE meter_installations MODIFY COLUMN status ENUM('waiting_installation','submitted','approved','installed','rejected','cancelled') DEFAULT 'submitted'");
                $this->db->execute();
            }
        }
        $this->db->query("UPDATE meter_installations SET status = 'submitted' WHERE status IS NULL OR status = ''");
        $this->db->execute();
    }

    public function create(array $data): bool {
        $this->ensureTable();
        $sql = 'INSERT INTO meter_installations (meter_id, client_id, installer_user_id, initial_reading, photo_url, gps_location, notes, status, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())';
        $this->db->query($sql);
        $this->db->bind([
            (int)$data['meter_id'],
            (int)$data['client_id'],
            (int)$data['installer_user_id'],
            (float)($data['initial_reading'] ?? 0),
            $data['photo_url'] ?? null,
            $data['gps_location'] ?? null,
            $data['notes'] ?? null,
            $data['status'] ?? 'submitted'
        ]);
        return $this->db->execute();
    }

    public function listByInstaller(int $installerUserId): array {
        $this->ensureTable();
        $this->db->query('SELECT mi.*, m.serial_number, u.username AS client_username 
                          FROM meter_installations mi 
                          JOIN meters m ON mi.meter_id = m.id 
                          JOIN clients c ON mi.client_id = c.id 
                          JOIN users u ON c.user_id = u.id 
                          WHERE mi.installer_user_id = ? 
                            AND mi.status IN (\'waiting_installation\', \'submitted\')
                          ORDER BY mi.submitted_at DESC');
        $this->db->bind([$installerUserId]);
        return $this->db->resultSet();
    }

    public function listWaiting(): array {
        $this->ensureTable();
        $this->db->query('SELECT mi.*, m.serial_number, u.username AS client_username FROM meter_installations mi JOIN meters m ON mi.meter_id = m.id JOIN clients c ON mi.client_id = c.id JOIN users u ON c.user_id = u.id WHERE mi.status = "waiting_installation" ORDER BY mi.submitted_at DESC');
        return $this->db->resultSet();
    }

    public function listSubmitted(): array {
        $this->ensureTable();
        $this->db->query('SELECT mi.*, m.serial_number, u.username AS client_username FROM meter_installations mi JOIN meters m ON mi.meter_id = m.id JOIN clients c ON mi.client_id = c.id JOIN users u ON c.user_id = u.id WHERE mi.status = "submitted" ORDER BY mi.submitted_at DESC');
        return $this->db->resultSet();
    }

    public function review(int $id, int $adminId, string $decision, string $notes = ''): bool {
        $this->ensureTable();
        $status = $decision === 'approve' ? 'installed' : 'rejected';
        $this->db->query('UPDATE meter_installations SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_notes = ? WHERE id = ?');
        $this->db->bind([$status, $adminId, $notes, $id]);
        return $this->db->execute();
    }

    public function updateStatus(int $id, string $status): bool {
        $this->ensureTable();
        $this->db->query('UPDATE meter_installations SET status = ? WHERE id = ?');
        $this->db->bind([$status, $id]);
        return $this->db->execute();
    }

    public function getById(int $id): ?array {
        $this->ensureTable();
        $this->db->query('SELECT * FROM meter_installations WHERE id = ?');
        $this->db->bind([$id]);
        return $this->db->single();
    }
}


