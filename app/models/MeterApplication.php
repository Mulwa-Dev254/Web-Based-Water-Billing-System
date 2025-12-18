<?php

class MeterApplication {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Append structured installation details into the latest application notes
     * for a given client/meter pair. Stores as a JSON line prefixed with a tag.
     * Returns true on update, false if no matching application exists.
     */
    public function appendInstallationDetails(int $clientId, int $meterId, array $details): bool {
        // Find latest application for this client/meter
        $this->db->query("SELECT id, notes FROM meter_applications WHERE client_id = ? AND meter_id = ? ORDER BY application_date DESC LIMIT 1");
        $this->db->bind([$clientId, $meterId]);
        $row = $this->db->single();

        if (!$row || empty($row['id'])) {
            return false;
        }

        $applicationId = (int)$row['id'];
        $encoded = json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $appendLine = '[INSTALLATION_SUBMISSION] ' . $encoded;

        // Append with a newline if notes already exist
        $this->db->query("UPDATE meter_applications SET notes = CONCAT(COALESCE(notes, ''), CASE WHEN COALESCE(notes, '') = '' THEN '' ELSE '\n' END, ?) WHERE id = ?");
        $this->db->bind([$appendLine, $applicationId]);
        return $this->db->execute();
    }

    /**
     * Check if a client has already applied for a specific meter
     * 
     * @param int $clientId The client ID
     * @param int $meterId The meter ID
     * @return bool True if application exists, false otherwise
     */
    public function checkDuplicateApplication($clientId, $meterId) {
        $this->db->query("SELECT COUNT(*) as count FROM meter_applications WHERE client_id = ? AND meter_id = ?");
        $this->db->bind([$clientId, $meterId]);
        $result = $this->db->single();
        return ($result['count'] > 0);
    }

    public function createApplication($data) {
        // Check for duplicate application first
        if ($this->checkDuplicateApplication($data['client_id'], $data['meter_id'])) {
            return false;
        }
        
        $this->db->query("INSERT INTO meter_applications (client_id, meter_id, status) VALUES (?, ?, 'pending')");
        $this->db->bind([$data['client_id'], $data['meter_id']]);
        
        return $this->db->execute();
    }

    public function getApplicationsByClientId($clientId) {
        $this->db->query("SELECT ma.*, m.serial_number as meter_serial, m.photo_url as meter_image 
                         FROM meter_applications ma 
                         JOIN meters m ON ma.meter_id = m.id 
                         WHERE ma.client_id = ?
                         ORDER BY ma.application_date DESC");
        $this->db->bind([$clientId]);
        
        return $this->db->resultSet();
    }
    
    public function cancelApplication($applicationId, $clientId) {
        $this->db->query("UPDATE meter_applications SET status = 'rejected' WHERE id = ? AND client_id = ?");
        $this->db->bind([$applicationId, $clientId]);
        
        return $this->db->execute();
    }

    public function getPendingApplications() {
        $this->db->query("SELECT ma.*, u.username as client_name, m.serial_number as meter_serial, m.photo_url as meter_image
                         FROM meter_applications ma 
                         JOIN users u ON ma.client_id = u.id 
                         JOIN meters m ON ma.meter_id = m.id 
                         WHERE ma.status = 'pending'
                         ORDER BY ma.application_date ASC");
        
        return $this->db->resultSet();
    }

    public function getApplicationById($id) {
        $this->db->query("SELECT ma.*, u.username as client_name, m.serial_number as meter_serial 
                         FROM meter_applications ma 
                         JOIN users u ON ma.client_id = u.id 
                         JOIN meters m ON ma.meter_id = m.id 
                         WHERE ma.id = ?");
        $this->db->bind([$id]);
        
        return $this->db->single();
    }

    public function updateApplicationStatus($id, $status, $reviewerId, $notes = '') {
        $this->db->query("UPDATE meter_applications 
                         SET status = ?, 
                             reviewed_by = ?, 
                             review_date = NOW(), 
                             notes = ? 
                         WHERE id = ?");
        $this->db->bind([$status, $reviewerId, $notes, $id]);
        
        return $this->db->execute();
    }

    public function adminApproveApplication($id, $adminId) {
        $this->db->query("UPDATE meter_applications 
                         SET admin_approval = 1, 
                             admin_approval_date = NOW(), 
                             admin_id = ? 
                         WHERE id = ?");
        $this->db->bind([$adminId, $id]);
        
        return $this->db->execute();
    }

    public function assignMeterToClient($meterId, $clientId) {
        $this->db->query("UPDATE meters 
                         SET client_id = ?, 
                             status = 'assigned' 
                         WHERE id = ?");
        $this->db->bind([$clientId, $meterId]);
        
        return $this->db->execute();
    }
    
    /**
     * Get applications by status
     * 
     * @param string $status The status to filter by (pending, approved, rejected)
     * @return array Array of applications with the specified status
     */
    public function getApplicationsByStatus($status) {
        $this->db->query("SELECT ma.*, u.username as client_name, m.serial_number as meter_serial, m.photo_url as meter_image
                         FROM meter_applications ma 
                         JOIN users u ON ma.client_id = u.id 
                         JOIN meters m ON ma.meter_id = m.id 
                         WHERE ma.status = ?
                         ORDER BY ma.application_date DESC");
        $this->db->bind([$status]);
        
        return $this->db->resultSet();
    }

    public function getAdminVerifiedApplications(): array {
        $this->db->query("SELECT ma.*, u.username as client_name, m.serial_number as meter_serial, m.id as meter_id
                          FROM meter_applications ma
                          JOIN users u ON ma.client_id = u.id
                          JOIN meters m ON ma.meter_id = m.id
                          WHERE ma.status = 'approved' 
                            AND ma.admin_approval = 2
                            AND m.status NOT IN ('installed','verified')
                          ORDER BY ma.admin_approval_date DESC");
        return $this->db->resultSet();
    }
    
    /**
     * Approve a meter application
     * 
     * @param int $applicationId The ID of the application to approve
     * @return bool True if the application was approved, false otherwise
     */
    public function approveApplication($applicationId) {
        $this->db->query("UPDATE meter_applications 
                         SET status = 'approved', 
                             review_date = NOW()
                         WHERE id = ?");
        $this->db->bind([$applicationId]);
        
        return $this->db->execute();
    }
    
    /**
     * Reject a meter application
     * 
     * @param int $applicationId The ID of the application to reject
     * @param string $reason The reason for rejection
     * @return bool True if the application was rejected, false otherwise
     */
    public function rejectApplication($applicationId, $reason) {
        $this->db->query("UPDATE meter_applications 
                         SET status = 'rejected', 
                             review_date = NOW(),
                             notes = ?
                         WHERE id = ?");
        $this->db->bind([$reason, $applicationId]);
        
        return $this->db->execute();
    }
    
    /**
     * Submit an approved application to admin for final review
     * 
     * @param int $applicationId The ID of the application to submit to admin
     * @return bool True if the application was submitted successfully, false otherwise
     */
    public function submitToAdmin($applicationId) {
        // Mark as submitted to admin using admin_approval flag (1)
        $this->db->query("UPDATE meter_applications 
                         SET admin_approval = 1,
                             admin_approval_date = NOW()
                         WHERE id = ? AND status = 'approved'");
        $this->db->bind([$applicationId]);
        
        return $this->db->execute();
    }
    
    /**
     * Cancel admin submission for an application
     * 
     * @param int $applicationId The ID of the application to cancel submission
     * @return bool True if the submission was canceled successfully, false otherwise
     */
    public function cancelAdminSubmission($applicationId) {
        $this->db->query("UPDATE meter_applications 
                         SET admin_approval = 0, 
                             admin_approval_date = NULL
                         WHERE id = ? AND status = 'approved' AND admin_approval = 1");
        $this->db->bind([$applicationId]);
        
        return $this->db->execute();
    }

    /**
     * Admin verifies an application (approve or reject)
     *
     * @param int $applicationId
     * @param int $adminId
     * @param string $decision 'approve' or 'reject'
     * @param string $notes optional notes
     * @return bool
     */
    public function adminVerifyApplication(int $applicationId, int $adminId, string $decision, string $notes = ''): bool {
        if ($decision === 'approve') {
            // admin_approval = 2 means admin verified
            $this->db->query("UPDATE meter_applications 
                              SET admin_approval = 2, admin_approval_date = NOW(), admin_id = ?, notes = ?
                              WHERE id = ? AND status = 'approved' AND admin_approval = 1");
            $this->db->bind([$adminId, $notes, $applicationId]);
        } else {
            $this->db->query("UPDATE meter_applications 
                              SET status = 'rejected', admin_approval = 0, admin_approval_date = NOW(), admin_id = ?, notes = ?
                              WHERE id = ? AND status IN ('approved','pending')");
            $this->db->bind([$adminId, $notes, $applicationId]);
        }
        return $this->db->execute();
    }

    /**
     * Commercial manager confirms application to client after admin verification
     */
    public function confirmToClient(int $applicationId, int $managerUserId, string $notes = ''): bool {
        // admin_approval = 3 indicates CM confirmed to client
        $this->db->query("UPDATE meter_applications 
                          SET admin_approval = 3, reviewed_by = ?, review_date = NOW(), notes = ?
                          WHERE id = ? AND status = 'approved' AND admin_approval = 2");
        $this->db->bind([$managerUserId, $notes, $applicationId]);
        return $this->db->execute();
    }

    /**
     * Applications submitted to admin (status approved, admin_approval = 1)
     */
    public function getSubmittedToAdminApplications(): array {
        $this->db->query("SELECT ma.*, u.username as client_name, m.serial_number as meter_serial, m.photo_url as meter_image
                          FROM meter_applications ma
                          JOIN users u ON ma.client_id = u.id
                          JOIN meters m ON ma.meter_id = m.id
                          WHERE ma.status = 'approved' 
                            AND ma.admin_approval = 1
                            AND m.status NOT IN ('installed','verified')
                          ORDER BY ma.admin_approval_date DESC, ma.application_date DESC");
        return $this->db->resultSet();
    }
}
