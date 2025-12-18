<?php
// app/models/Bill.php

require_once __DIR__ . '/../core/Database.php';

class Bill {
    private Database $db;

    public function __construct(Database $database_instance) {
        $this->db = $database_instance;
    }

    /**
     * Creates a new bill record
     * 
     * @param int $clientId The ID of the client
     * @param int $readingId The ID of the meter reading
     * @param float $amount The bill amount
     * @param string $dueDate The due date for payment
     * @param string $status The status of the bill (default: 'unpaid')
     * @return int|bool The ID of the new bill or false on failure
     */
    public function createBill(int $clientId, int $readingId, float $amount, string $dueDate, string $status = 'pending') {
        $this->db->query('INSERT INTO bills (client_id, reading_id_end, amount_due, bill_date, due_date, payment_status) VALUES (?, ?, ?, NOW(), ?, ?)');
        $this->db->bind([$clientId, $readingId, $amount, $dueDate, $status]);
        
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
     * Gets a bill by its ID
     * 
     * @param int $billId The ID of the bill
     * @return array|bool The bill data or false if not found
     */
    public function getBillById(int $billId) {
        $this->db->query('SELECT *, payment_status AS status FROM bills WHERE id = ?');
        $this->db->bind([$billId]);
        $bill = $this->db->single();
        $this->db->closeStmt();
        return $bill;
    }

    /**
     * Gets all bills for a client
     * 
     * @param int $clientId The ID of the client
     * @return array The bills for the client
     */
    public function getBillsByClientId(int $clientId) {
        $this->db->query('SELECT * FROM bills WHERE client_id = ? ORDER BY bill_date DESC');
        $this->db->bind([$clientId]);
        $bills = $this->db->resultSet();
        $this->db->closeStmt();
        return $bills;
    }

    /**
     * Gets all unpaid bills for a client
     * 
     * @param int $clientId The ID of the client
     * @return array The unpaid bills for the client
     */
    public function getUnpaidBillsByClientId(int $clientId) {
        $this->db->query('SELECT * FROM bills WHERE client_id = ? AND payment_status = "pending" ORDER BY due_date ASC');
        $this->db->bind([$clientId]);
        $bills = $this->db->resultSet();
        $this->db->closeStmt();
        return $bills;
    }

    /**
     * Updates the status of a bill
     * 
     * @param int $billId The ID of the bill
     * @param string $status The new status
     * @return bool True on success, false on failure
     */
    public function updateBillStatus(int $billId, string $status) {
        $this->db->query('UPDATE bills SET payment_status = ? WHERE id = ?');
        $this->db->bind([$status, $billId]);
        $success = $this->db->execute();
        $this->db->closeStmt();
        return $success;
    }

    /**
     * Gets bills by payment status
     * 
     * @param string $status The payment status to filter by
     * @return array The bills with the specified payment status
     */
     
    /**
     * Gets the total count of all bills
     * 
     * @return int The total number of bills
     */
    public function getTotalBillsCount() {
        $this->db->query('SELECT COUNT(*) as count FROM bills');
        $result = $this->db->single();
        $this->db->closeStmt();
        return $result['count'] ?? 0;
    }
    
    /**
     * Gets the count of pending bills
     * 
     * @return int The number of pending bills
     */
    public function getPendingBillsCount() {
        $this->db->query('SELECT COUNT(*) as count FROM bills WHERE payment_status = "pending"');
        $result = $this->db->single();
        $this->db->closeStmt();
        return $result['count'] ?? 0;
    }
    
    /**
     * Gets the most recent bills
     * 
     * @param int $limit The maximum number of bills to return
     * @return array The recent bills
     */
    public function getRecentBills($limit = 10) {
        $this->db->query('SELECT b.*, 
                            COALESCE(NULLIF(c.full_name, ""), NULLIF(u.full_name, ""), u.username, u.email) AS client_name
                         FROM bills b 
                         JOIN clients c ON b.client_id = c.id 
                         JOIN users u ON c.user_id = u.id 
                         ORDER BY b.bill_date DESC LIMIT ?');
        $this->db->bind([$limit]);
        $bills = $this->db->resultSet();
        $this->db->closeStmt();
        return $bills;
    }
    public function getBillsByStatus(string $status) {
        $this->db->query('SELECT b.*, c.full_name as client_name 
                         FROM bills b 
                         JOIN clients c ON b.client_id = c.id 
                         WHERE b.payment_status = ? 
                         ORDER BY b.due_date ASC');
        $this->db->bind([$status]);
        $bills = $this->db->resultSet();
        $this->db->closeStmt();
        return $bills;
    }

    /**
     * Gets bills that are overdue
     * 
     * @return array The overdue bills
     */
    public function getOverdueBills() {
        $this->db->query('SELECT b.*, c.full_name as client_name 
                         FROM bills b 
                         JOIN clients c ON b.client_id = c.id 
                         WHERE b.payment_status = "pending" AND b.due_date < CURDATE() 
                         ORDER BY b.due_date ASC');
        $bills = $this->db->resultSet();
        $this->db->closeStmt();
        return $bills;
    }

    public function getOverdueBillsByClientId(int $clientId) {
        $this->db->query('SELECT b.* 
                          FROM bills b 
                          WHERE b.client_id = ? 
                            AND b.payment_status = "pending" 
                            AND b.due_date < CURDATE() 
                          ORDER BY b.due_date ASC');
        $this->db->bind([$clientId]);
        $rows = $this->db->resultSet();
        $this->db->closeStmt();
        return $rows;
    }

    /**
     * Gets billing statistics for reports
     * 
     * @param string $startDate The start date for the report period
     * @param string $endDate The end date for the report period
     * @return array The billing statistics
     */
    public function getBillingStatistics(string $startDate, string $endDate) {
        $this->db->query('SELECT 
                            COUNT(*) as total_bills,
                            SUM(amount_due) as total_amount,
                            SUM(CASE WHEN payment_status = "paid" THEN amount_due ELSE 0 END) as paid_amount,
                            SUM(CASE WHEN payment_status = "pending" THEN amount_due ELSE 0 END) as unpaid_amount,
                            COUNT(CASE WHEN payment_status = "paid" THEN 1 END) as paid_count,
                            COUNT(CASE WHEN payment_status = "pending" THEN 1 END) as unpaid_count
                         FROM bills 
                         WHERE bill_date BETWEEN ? AND ?');
        $this->db->bind([$startDate, $endDate]);
        $stats = $this->db->single();
        $this->db->closeStmt();
        return $stats;
    }
    
    /**
     * Gets bills for a client with optional filters
     * 
     * @param int $clientId The ID of the client
     * @param string $status Optional status filter
     * @param string $fromDate Optional start date filter
     * @param string $toDate Optional end date filter
     * @return array The filtered bills for the client
     */
    public function getBillsByClient(int $clientId, string $status = '', string $fromDate = '', string $toDate = '') {
        $sql = 'SELECT b.*, m.serial_number, mr.reading_value, mr.reading_date 
                FROM bills b 
                LEFT JOIN meter_readings mr ON b.reading_id_end = mr.id 
                LEFT JOIN meters m ON mr.meter_id = m.id 
                WHERE b.client_id = ?';
        
        $params = [$clientId];
        
        if (!empty($status)) {
            $sql .= ' AND b.payment_status = ?';
            $params[] = $status;
        }
        
        if (!empty($fromDate)) {
            $sql .= ' AND b.bill_date >= ?';
            $params[] = $fromDate;
        }
        
        if (!empty($toDate)) {
            $sql .= ' AND b.bill_date <= ?';
            $params[] = $toDate;
        }
        
        $sql .= ' ORDER BY b.bill_date DESC';
        
        $this->db->query($sql);
        $this->db->bind($params);
        $bills = $this->db->resultSet();
        $this->db->closeStmt();
        return $bills;
    }
    
    /**
     * Gets recent bills for a client
     * 
     * @param int $clientId The ID of the client
     * @param int $limit The maximum number of bills to return
     * @return array The recent bills for the client
     */
    public function getRecentBillsByClient(int $clientId, int $limit = 5) {
        $this->db->query('SELECT b.*, m.serial_number, mr.reading_value, mr.reading_date 
                         FROM bills b 
                         LEFT JOIN meter_readings mr ON b.reading_id_end = mr.id 
                         LEFT JOIN meters m ON mr.meter_id = m.id 
                         WHERE b.client_id = ? 
                         ORDER BY b.bill_date DESC 
                         LIMIT ?');
        $this->db->bind([$clientId, $limit]);
        $bills = $this->db->resultSet();
        $this->db->closeStmt();
        return $bills;
    }
    
    /**
     * Gets billing summary for a client
     * 
     * @param int $clientId The ID of the client
     * @return array The billing summary for the client
     */
    public function getClientBillingSummary(int $clientId) {
        $this->db->query('SELECT 
                            COUNT(*) as total_bills,
                            SUM(amount_due) as total_amount,
                            SUM(amount_paid) as total_paid,
                            SUM(amount_due - amount_paid) as total_balance,
                            COUNT(CASE WHEN payment_status = "paid" THEN 1 END) as paid_count,
                            COUNT(CASE WHEN payment_status = "partially_paid" THEN 1 END) as partial_count,
                            COUNT(CASE WHEN payment_status = "pending" THEN 1 END) as pending_count,
                            SUM(CASE WHEN payment_status = "pending" THEN amount_due - amount_paid ELSE 0 END) as pending_amount,
                            COUNT(CASE WHEN payment_status = "pending" AND due_date < CURDATE() THEN 1 END) as overdue_count,
                            SUM(CASE WHEN payment_status = "pending" AND due_date < CURDATE() THEN amount_due - amount_paid ELSE 0 END) as overdue_amount,
                            AVG(consumption_units) as average_consumption,
                            AVG(amount_due) as average_amount,
                            SUM(CASE WHEN bill_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) THEN consumption_units ELSE 0 END) as last_month_consumption,
                            SUM(CASE WHEN bill_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) THEN amount_due ELSE 0 END) as last_month_amount
                         FROM bills 
                         WHERE client_id = ?');
        $this->db->bind([$clientId]);
        $summary = $this->db->single();
        $this->db->closeStmt();
        return $summary;
    }
    
    /**
     * Updates a bill's payment information
     * 
     * @param int $billId The ID of the bill
     * @param float $amountPaid The new amount paid
     * @param string $status The new payment status
     * @return bool True on success, false on failure
     */
    public function updateBillPayment(int $billId, float $amountPaid, string $status) {
        $this->db->query('UPDATE bills SET amount_paid = ?, payment_status = ?, balance = (amount_due - ?) WHERE id = ?');
        $this->db->bind([$amountPaid, $status, $amountPaid, $billId]);
        $success = $this->db->execute();
        $this->db->closeStmt();
        return $success;
    }
    
    /**
     * Gets all bills with optional filters
     * 
     * @param string $statusFilter Optional status filter ('all' or specific status)
     * @param string $clientFilter Optional client ID filter ('all' or specific client ID)
     * @param string $dateFilter Optional date filter ('all', 'this_month', 'last_month', etc.)
     * @return array The filtered bills
     */
    public function getAllBills(string $statusFilter = 'all', string $clientFilter = 'all', string $dateFilter = 'all') {
        $sql = "SELECT b.*, 
                        COALESCE(NULLIF(c.full_name, ''), NULLIF(u.full_name, ''), u.username, u.email) AS client_name, 
                        m.serial_number, mr.reading_value, mr.reading_date 
                FROM bills b 
                JOIN clients c ON b.client_id = c.id 
                JOIN users u ON c.user_id = u.id 
                LEFT JOIN meter_readings mr ON b.reading_id_end = mr.id 
                LEFT JOIN meters m ON b.meter_id = m.id 
                WHERE 1=1";
        
        $params = [];
        
        // Apply status filter
        if ($statusFilter !== 'all') {
            $sql .= ' AND b.payment_status = ?';
            $params[] = $statusFilter;
        }
        
        // Apply client filter
        if ($clientFilter !== 'all') {
            $sql .= ' AND b.client_id = ?';
            $params[] = $clientFilter;
        }
        
        // Apply date filter
        if ($dateFilter !== 'all') {
            switch ($dateFilter) {
                case 'this_month':
                    $sql .= ' AND MONTH(b.bill_date) = MONTH(CURRENT_DATE()) AND YEAR(b.bill_date) = YEAR(CURRENT_DATE())';
                    break;
                case 'last_month':
                    $sql .= ' AND MONTH(b.bill_date) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) AND YEAR(b.bill_date) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))';
                    break;
                case 'this_year':
                    $sql .= ' AND YEAR(b.bill_date) = YEAR(CURRENT_DATE())';
                    break;
                case 'overdue':
                    $sql .= ' AND b.payment_status = "pending" AND b.due_date < CURRENT_DATE()';
                    break;
            }
        }
        
        $sql .= ' ORDER BY b.bill_date DESC';
        
        $this->db->query($sql);
        if (!empty($params)) {
            $this->db->bind($params);
        }
        $bills = $this->db->resultSet();
        $this->db->closeStmt();
        return $bills;
    }

    public function getAllBillsByDateRange(string $startDate, string $endDate) {
        $sql = "SELECT b.*, 
                        COALESCE(NULLIF(c.full_name, ''), NULLIF(u.full_name, ''), u.username, u.email) AS client_name, 
                        m.serial_number, mr.reading_value, mr.reading_date 
                FROM bills b 
                JOIN clients c ON b.client_id = c.id 
                JOIN users u ON c.user_id = u.id 
                LEFT JOIN meter_readings mr ON b.reading_id_end = mr.id 
                LEFT JOIN meters m ON b.meter_id = m.id 
                WHERE b.bill_date BETWEEN ? AND ? 
                ORDER BY b.bill_date DESC";
        $this->db->query($sql);
        $this->db->bind([$startDate, $endDate]);
        $bills = $this->db->resultSet();
        $this->db->closeStmt();
        return $bills;
    }

    public function getBillsByClientDateRange(int $clientId, string $startDate, string $endDate) {
        $sql = "SELECT b.*, 
                        COALESCE(NULLIF(c.full_name, ''), NULLIF(u.full_name, ''), u.username, u.email) AS client_name, 
                        m.serial_number, mr.reading_value, mr.reading_date 
                FROM bills b 
                JOIN clients c ON b.client_id = c.id 
                JOIN users u ON c.user_id = u.id 
                LEFT JOIN meter_readings mr ON b.reading_id_end = mr.id 
                LEFT JOIN meters m ON b.meter_id = m.id 
                WHERE b.client_id = ? AND b.bill_date BETWEEN ? AND ? 
                ORDER BY b.bill_date DESC";
        $this->db->query($sql);
        $this->db->bind([$clientId, $startDate, $endDate]);
        $bills = $this->db->resultSet();
        $this->db->closeStmt();
        return $bills;
    }

    public function getOutstandingByDateRange(string $startDate, string $endDate, ?int $clientId = null) {
        $sql = 'SELECT b.*, c.full_name as client_name, m.serial_number, 
                         DATEDIFF(CURRENT_DATE(), b.due_date) as days_overdue 
                FROM bills b 
                JOIN clients c ON b.client_id = c.id 
                LEFT JOIN meter_readings mr ON b.reading_id_end = mr.id 
                LEFT JOIN meters m ON mr.meter_id = m.id 
                WHERE b.payment_status = "pending" AND b.due_date BETWEEN ? AND ?';
        $params = [$startDate, $endDate];
        if (!empty($clientId)) {
            $sql .= ' AND b.client_id = ?';
            $params[] = $clientId;
        }
        $sql .= ' ORDER BY c.full_name ASC, b.due_date ASC';
        $this->db->query($sql);
        $this->db->bind($params);
        $rows = $this->db->resultSet();
        $this->db->closeStmt();
        return $rows;
    }

    public function getConsumptionHistoryByMeter(int $meterId, int $limit = 8): array {
        $this->db->query('SELECT bill_date, consumption_units FROM bills WHERE meter_id = ? ORDER BY bill_date DESC, id DESC LIMIT ?');
        $this->db->bind([$meterId, $limit]);
        $rows = $this->db->resultSet();
        $this->db->closeStmt();
        return $rows ?: [];
    }
    
    /**
     * Gets client consumption report
     * 
     * @param int $clientId The client ID
     * @param string $startDate Start date for the report
     * @param string $endDate End date for the report
     * @return array The consumption report data
     */
    public function getClientConsumptionReport(int $clientId, string $startDate, string $endDate) {
        $this->db->query('SELECT 
                            b.id AS bill_id,
                            b.client_id,
                            b.meter_id,
                            b.billing_period_start,
                            b.billing_period_end,
                            b.consumption_units,
                            b.amount_due,
                            b.amount_paid,
                            b.payment_status,
                            b.bill_date,
                            COALESCE(NULLIF(c.full_name, \'\'), NULLIF(u.full_name, \'\'), u.username, u.email) AS client_name,
                            m.serial_number AS meter_serial
                          FROM bills b 
                          JOIN clients c ON b.client_id = c.id 
                          JOIN users u ON c.user_id = u.id 
                          LEFT JOIN meters m ON b.meter_id = m.id 
                          WHERE b.client_id = ? AND b.bill_date BETWEEN ? AND ?
                          ORDER BY b.meter_id ASC, b.bill_date ASC');
        $this->db->bind([$clientId, $startDate, $endDate]);
        $rows = $this->db->resultSet();
        $this->db->closeStmt();
        // Derive period, daily averages, and trend by meter
        $out = [];
        $lastByMeter = [];
        foreach ($rows as $r) {
            $periodStart = $r['billing_period_start'] ?? $r['bill_date'];
            $periodEnd = $r['billing_period_end'] ?? $r['bill_date'];
            $days = 0;
            if (!empty($periodStart) && !empty($periodEnd)) {
                $days = max(1, (int)round((strtotime($periodEnd) - strtotime($periodStart)) / 86400));
            }
            $cons = (float)($r['consumption_units'] ?? 0);
            $meterId = (int)($r['meter_id'] ?? 0);
            $prevCons = isset($lastByMeter[$meterId]) ? (float)$lastByMeter[$meterId] : null;
            $trend = 0.0;
            if ($prevCons !== null && $prevCons > 0) {
                $trend = (($cons - $prevCons) / $prevCons) * 100.0;
            }
            // Build sparkline history for this meter
            $historyRows = $this->getConsumptionHistoryByMeter($meterId, 8);
            $historyRows = array_reverse($historyRows);
            $historyLabels = array_map(function($h){ return date('M d', strtotime($h['bill_date'])); }, $historyRows);
            $historyData = array_map(function($h){ return (float)($h['consumption_units'] ?? 0); }, $historyRows);
            $out[] = [
                'client_name' => $r['client_name'] ?? '',
                'meter_serial' => $r['meter_serial'] ?? '',
                'period' => (!empty($periodStart) && !empty($periodEnd)) ? (date('d M Y', strtotime($periodStart)) . ' - ' . date('d M Y', strtotime($periodEnd))) : date('d M Y', strtotime($r['bill_date'])),
                'consumption' => $cons,
                'daily_average' => $days ? ($cons / $days) : 0,
                'trend' => $trend,
                'status' => $r['payment_status'] ?? 'pending',
                'amount_due' => (float)($r['amount_due'] ?? 0),
                'amount_paid' => (float)($r['amount_paid'] ?? 0),
                'bill_date' => $r['bill_date'] ?? null,
                'bill_id' => (int)($r['bill_id'] ?? 0),
                'meter_id' => $meterId,
                'history_labels' => $historyLabels,
                'history' => $historyData,
            ];
            $lastByMeter[$meterId] = $cons;
        }
        return $out;
    }
    
    /**
     * Gets consumption report for all clients
     * 
     * @param string $startDate Start date for the report
     * @param string $endDate End date for the report
     * @return array The consumption report data
     */
    public function getAllClientsConsumptionReport(string $startDate, string $endDate) {
        $this->db->query('SELECT 
                            b.id AS bill_id,
                            b.client_id,
                            b.meter_id,
                            b.billing_period_start,
                            b.billing_period_end,
                            b.consumption_units,
                            b.amount_due,
                            b.amount_paid,
                            b.payment_status,
                            b.bill_date,
                            COALESCE(NULLIF(c.full_name, \'\'), NULLIF(u.full_name, \'\'), u.username, u.email) AS client_name,
                            m.serial_number AS meter_serial
                          FROM bills b 
                          JOIN clients c ON b.client_id = c.id 
                          JOIN users u ON c.user_id = u.id 
                          LEFT JOIN meters m ON b.meter_id = m.id 
                          WHERE b.bill_date BETWEEN ? AND ?
                          ORDER BY b.meter_id ASC, b.bill_date ASC');
        $this->db->bind([$startDate, $endDate]);
        $rows = $this->db->resultSet();
        $this->db->closeStmt();
        $out = [];
        $lastByMeter = [];
        foreach ($rows as $r) {
            $periodStart = $r['billing_period_start'] ?? $r['bill_date'];
            $periodEnd = $r['billing_period_end'] ?? $r['bill_date'];
            $days = 0;
            if (!empty($periodStart) && !empty($periodEnd)) {
                $days = max(1, (int)round((strtotime($periodEnd) - strtotime($periodStart)) / 86400));
            }
            $cons = (float)($r['consumption_units'] ?? 0);
            $meterId = (int)($r['meter_id'] ?? 0);
            $prevCons = isset($lastByMeter[$meterId]) ? (float)$lastByMeter[$meterId] : null;
            $trend = 0.0;
            if ($prevCons !== null && $prevCons > 0) {
                $trend = (($cons - $prevCons) / $prevCons) * 100.0;
            }
            // Build sparkline history for this meter
            $historyRows = $this->getConsumptionHistoryByMeter($meterId, 8);
            $historyRows = array_reverse($historyRows); // oldest to newest
            $historyLabels = array_map(function($h){ return date('M d', strtotime($h['bill_date'])); }, $historyRows);
            $historyData = array_map(function($h){ return (float)($h['consumption_units'] ?? 0); }, $historyRows);
            $out[] = [
                'client_name' => $r['client_name'] ?? '',
                'meter_serial' => $r['meter_serial'] ?? '',
                'period' => (!empty($periodStart) && !empty($periodEnd)) ? (date('d M Y', strtotime($periodStart)) . ' - ' . date('d M Y', strtotime($periodEnd))) : date('d M Y', strtotime($r['bill_date'])),
                'consumption' => $cons,
                'daily_average' => $days ? ($cons / $days) : 0,
                'trend' => $trend,
                'status' => $r['payment_status'] ?? 'pending',
                'amount_due' => (float)($r['amount_due'] ?? 0),
                'amount_paid' => (float)($r['amount_paid'] ?? 0),
                'bill_date' => $r['bill_date'] ?? null,
                'bill_id' => (int)($r['bill_id'] ?? 0),
                'meter_id' => $meterId,
                'history_labels' => $historyLabels,
                'history' => $historyData,
            ];
            $lastByMeter[$meterId] = $cons;
        }
        return $out;
    }
    
    /**
     * Gets outstanding bills report for a client
     * 
     * @param int $clientId The client ID
     * @return array The outstanding bills report data
     */
    public function getClientOutstandingReport(int $clientId) {
        $this->db->query('SELECT b.*, c.full_name as client_name, m.serial_number, 
                         DATEDIFF(CURRENT_DATE(), b.due_date) as days_overdue
                         FROM bills b 
                         JOIN clients c ON b.client_id = c.id 
                         LEFT JOIN meter_readings mr ON b.reading_id_end = mr.id 
                         LEFT JOIN meters m ON mr.meter_id = m.id 
                         WHERE b.client_id = ? AND b.payment_status = "pending"
                         ORDER BY b.due_date ASC');
        $this->db->bind([$clientId]);
        $report = $this->db->resultSet();
        $this->db->closeStmt();
        return $report;
    }
    
    /**
     * Gets outstanding bills report for all clients
     * 
     * @return array The outstanding bills report data
     */
    public function getAllClientsOutstandingReport() {
        $this->db->query('SELECT b.*, c.full_name as client_name, m.serial_number, 
                         DATEDIFF(CURRENT_DATE(), b.due_date) as days_overdue
                         FROM bills b 
                         JOIN clients c ON b.client_id = c.id 
                         LEFT JOIN meter_readings mr ON b.reading_id_end = mr.id 
                         LEFT JOIN meters m ON mr.meter_id = m.id 
                         WHERE b.payment_status = "pending"
                         ORDER BY c.full_name ASC, b.due_date ASC');
        $report = $this->db->resultSet();
        $this->db->closeStmt();
        return $report;
    }
}
