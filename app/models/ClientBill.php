<?php
require_once __DIR__ . '/../core/Database.php';

class ClientBill {
    private Database $db;

    public function __construct(Database $database_instance) {
        $this->db = $database_instance;
    }

    public function ensureTable(): void {
        $sql = 'CREATE TABLE IF NOT EXISTS client_bills (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bill_id INT NOT NULL,
            client_user_id INT NOT NULL,
            sender_user_id INT NOT NULL,
            bill_amount DECIMAL(12,2) NOT NULL,
            bill_status VARCHAR(32) NOT NULL,
            pdf_path VARCHAR(255) NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_client_user_id (client_user_id),
            INDEX idx_bill_id (bill_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
        try {
            $this->db->query($sql);
            $this->db->execute();
            $this->db->closeStmt();
        } catch (Exception $e) {
        }
        try {
            $this->db->query('ALTER TABLE client_bills ADD COLUMN IF NOT EXISTS image_path VARCHAR(255) NOT NULL DEFAULT ""');
            $this->db->execute();
            $this->db->closeStmt();
        } catch (Exception $e) {
        }
    }

    public function create(int $billId, int $clientUserId, int $senderUserId, float $billAmount, string $billStatus, string $pdfPath, string $imagePath): bool {
        $this->ensureTable();
        $this->db->query('INSERT INTO client_bills (bill_id, client_user_id, sender_user_id, bill_amount, bill_status, pdf_path, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $this->db->bind([$billId, $clientUserId, $senderUserId, $billAmount, $billStatus, $pdfPath, $imagePath]);
        $ok = $this->db->execute();
        $this->db->closeStmt();
        return (bool)$ok;
    }

    public function getByClientUserId(int $userId): array {
        $this->ensureTable();
        $this->db->query('SELECT cb.*, u.username AS sender_username, u.full_name AS sender_full_name FROM client_bills cb JOIN users u ON cb.sender_user_id = u.id WHERE cb.client_user_id = ? ORDER BY cb.created_at DESC');
        $this->db->bind([$userId]);
        $rows = $this->db->resultSet();
        $this->db->closeStmt();
        return $rows ?: [];
    }

    public function updateStatusByBillId(int $billId, int $clientUserId, string $newStatus): bool {
        $this->ensureTable();
        $this->db->query('UPDATE client_bills SET bill_status = ? WHERE bill_id = ? AND client_user_id = ?');
        $this->db->bind([$newStatus, $billId, $clientUserId]);
        $ok = $this->db->execute();
        $this->db->closeStmt();
        return (bool)$ok;
    }

    public function updateImagePathByBillId(int $billId, int $clientUserId, string $imagePath): bool {
        $this->ensureTable();
        $this->db->query('UPDATE client_bills SET image_path = ? WHERE bill_id = ? AND client_user_id = ?');
        $this->db->bind([$imagePath, $billId, $clientUserId]);
        $ok = $this->db->execute();
        $this->db->closeStmt();
        return (bool)$ok;
    }
}
