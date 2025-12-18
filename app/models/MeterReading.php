<?php
// app/models/MeterReading.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/MeterImage.php'; // Include MeterImage model

class MeterReading {
    private Database $db;
    private MeterImage $meterImageModel; // New property for MeterImage model

    public function __construct(Database $database_instance) {
        $this->db = $database_instance;
        $this->meterImageModel = new MeterImage($database_instance); // Initialize MeterImage model
    }
    
    /**
     * Ensure the meter_readings table has the required columns for enhanced functionality
     */
    private function ensureMeterReadingsTableHasRequiredColumns() {
        // Check if meter_condition column exists
        $this->db->query("SHOW COLUMNS FROM meter_readings LIKE 'meter_condition'");
        $result = $this->db->single();
        
        if (!$result) {
            // Add meter_condition column if it doesn't exist
            $this->db->query("ALTER TABLE meter_readings ADD COLUMN meter_condition VARCHAR(50) DEFAULT 'normal' AFTER meter_image_id");
            $this->db->execute();
        }
        
        // Check if notes column exists
        $this->db->query("SHOW COLUMNS FROM meter_readings LIKE 'notes'");
        $result = $this->db->single();
        
        if (!$result) {
            // Add notes column if it doesn't exist
            $this->db->query("ALTER TABLE meter_readings ADD COLUMN notes TEXT AFTER meter_condition");
            $this->db->execute();
        }
    }

    /**
     * Records a new meter reading.
     * This now includes recording the image and linking to it.
     *
     * @param int $meterId The ID of the meter.
     * @param float $readingValue The value of the reading.
     * @param int $collectorId The ID of the collector who took the reading.
     * @param int $clientId The ID of the client associated with the meter.
     * @param string $photoData The Base64 encoded image data.
     * @param float $latitude The latitude coordinate.
     * @param float $longitude The longitude coordinate.
     * @return bool True on success, false on failure.
     */
    public function recordReading(int $meterId, float $readingValue, int $collectorId, int $clientId, string $photoData, float $latitude, float $longitude): bool {
        // First, record the meter image and get its ID
        $meterImageId = $this->meterImageModel->recordMeterImage($clientId, $meterId, $collectorId, $photoData, $latitude, $longitude);

        if ($meterImageId === false) {
            error_log("Failed to record meter image for reading.");
            return false; // Failed to record image
        }

        // Then, record the meter reading, linking to the new image
        $this->db->query('INSERT INTO meter_readings (meter_id, reading_value, reading_date, collector_id, meter_image_id) VALUES (?, ?, NOW(), ?, ?)');
        $this->db->bind([$meterId, $readingValue, $collectorId, $meterImageId]);
        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            error_log("Failed to record meter reading: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }
    
    /**
     * Enhanced version of recordReading that includes meter condition and notes.
     *
     * @param int $meterId The ID of the meter.
     * @param float $readingValue The value of the reading.
     * @param int $collectorId The ID of the collector who took the reading.
     * @param int $clientId The ID of the client associated with the meter.
     * @param string $photoData The Base64 encoded image data.
     * @param float $latitude The latitude coordinate.
     * @param float $longitude The longitude coordinate.
     * @param string $meterCondition The condition of the meter (normal, damaged, etc.).
     * @param string $notes Additional notes about the reading.
     * @return bool True on success, false on failure.
     */
    public function recordReadingEnhanced(int $meterId, float $readingValue, int $collectorId, int $clientId, string $photoData, float $latitude, float $longitude, string $meterCondition = 'normal', string $notes = ''): int|bool {
        // First, record the meter image and get its ID
        $meterImageId = $this->meterImageModel->recordMeterImage($clientId, $meterId, $collectorId, $photoData, $latitude, $longitude);

        if ($meterImageId === false) {
            error_log("Failed to record meter image for reading.");
            return false; // Failed to record image
        }

        // Then, record the meter reading with additional fields
        // Ensure the table has the required columns before inserting
        $this->ensureMeterReadingsTableHasRequiredColumns();
        $this->db->query('INSERT INTO meter_readings (meter_id, reading_value, reading_date, collector_id, meter_image_id, meter_condition, notes) VALUES (?, ?, NOW(), ?, ?, ?, ?)');
        $this->db->bind([$meterId, $readingValue, $collectorId, $meterImageId, $meterCondition, $notes]);
        if ($this->db->execute()) {
            $insertId = $this->db->lastInsertId();
            $this->db->closeStmt();
            return $insertId > 0 ? $insertId : true; // return ID when available, else true
        } else {
            error_log("Failed to record enhanced meter reading: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Retrieves all meter readings for a specific collector.
     * Joins with meters, users, and meter_images to get full details.
     *
     * @param int $collectorId The ID of the collector.
     * @return array An array of associative arrays, each representing a meter reading.
     */
    public function getReadingsByCollectorId(int $collectorId): array {
        $this->db->query('SELECT mr.*, m.serial_number, u.username AS client_username, mi.image_path AS photo_url, mi.latitude, mi.longitude
                          FROM meter_readings mr
                          JOIN meters m ON mr.meter_id = m.id
                          JOIN users u ON m.client_id = u.id
                          LEFT JOIN meter_images mi ON mr.meter_image_id = mi.id
                          WHERE mr.collector_id = ?
                          ORDER BY mr.reading_date DESC');
        $this->db->bind([$collectorId]);
        $readings = $this->db->resultSet();
        $this->db->closeStmt();
        return $readings;
    }

    /**
     * Retrieves all meter readings (for admin view).
     *
     * @return array An array of associative arrays, each representing a meter reading.
     */
    public function getAllReadings(): array {
        $this->db->query('SELECT mr.*, m.serial_number, u.username AS client_username, c.username AS collector_username, mi.image_path AS photo_url, mi.latitude, mi.longitude
                          FROM meter_readings mr
                          JOIN meters m ON mr.meter_id = m.id
                          JOIN users u ON m.client_id = u.id
                          JOIN users c ON mr.collector_id = c.id
                          LEFT JOIN meter_images mi ON mr.meter_image_id = mi.id
                          ORDER BY mr.reading_date DESC');
        $readings = $this->db->resultSet();
        $this->db->closeStmt();
        return $readings;
    }
    
    /**
     * Gets the count of readings recorded by a collector on a specific date.
     *
     * @param int $collectorId The ID of the collector.
     * @param string $date The date in Y-m-d format.
     * @return int The number of readings recorded on that date.
     */
    public function getReadingsCountByDate(int $collectorId, string $date): int {
        $this->db->query('SELECT COUNT(*) as count FROM meter_readings 
                          WHERE collector_id = ? AND DATE(reading_date) = ?');
        $this->db->bind([$collectorId, $date]);
        $result = $this->db->single();
        $this->db->closeStmt();
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Gets the count of readings recorded by a collector in a specific month.
     *
     * @param int $collectorId The ID of the collector.
     * @param string $yearMonth The year and month in Y-m format.
     * @return int The number of readings recorded in that month.
     */
    public function getReadingsCountByMonth(int $collectorId, string $yearMonth): int {
        $this->db->query('SELECT COUNT(*) as count FROM meter_readings 
                          WHERE collector_id = ? AND DATE_FORMAT(reading_date, "%Y-%m") = ?');
        $this->db->bind([$collectorId, $yearMonth]);
        $result = $this->db->single();
        $this->db->closeStmt();
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Retrieves meter readings for a specific collector within a date range.
     *
     * @param int $collectorId The ID of the collector.
     * @param string $dateFrom The start date in Y-m-d format.
     * @param string $dateTo The end date in Y-m-d format.
     * @return array An array of associative arrays, each representing a meter reading.
     */
    public function getReadingsByDateRange(int $collectorId, string $dateFrom, string $dateTo): array {
        $this->db->query('SELECT mr.*, m.serial_number, u.username AS client_username, mi.image_path AS photo_url, mi.latitude, mi.longitude
                          FROM meter_readings mr
                          JOIN meters m ON mr.meter_id = m.id
                          JOIN users u ON m.client_id = u.id
                          LEFT JOIN meter_images mi ON mr.meter_image_id = mi.id
                          WHERE mr.collector_id = ?
                          AND DATE(mr.reading_date) BETWEEN ? AND ?
                          ORDER BY mr.reading_date DESC');
        $this->db->bind([$collectorId, $dateFrom, $dateTo]);
        $readings = $this->db->resultSet();
        $this->db->closeStmt();
        return $readings;
    }

    /**
     * Returns counts aggregated by day between start and end dates (inclusive).
     * Keys are 'Y-m-d'.
     */
    public function getDailyCounts(int $collectorId, string $startDate, string $endDate): array {
        $this->db->query('SELECT DATE(reading_date) AS d, COUNT(*) AS c
                          FROM meter_readings
                          WHERE collector_id = ? AND DATE(reading_date) BETWEEN ? AND ?
                          GROUP BY DATE(reading_date)
                          ORDER BY d ASC');
        $this->db->bind([$collectorId, $startDate, $endDate]);
        $rows = $this->db->resultSet();
        $this->db->closeStmt();
        $map = [];
        foreach ($rows as $r) { $map[$r['d']] = (int)$r['c']; }
        return $map;
    }

    /**
     * Returns counts aggregated by month for the last 12 months (including current).
     * Keys are 'Y-m'.
     */
    public function getMonthlyCountsLast12(int $collectorId): array {
        $this->db->query('SELECT DATE_FORMAT(reading_date, "%Y-%m") AS ym, COUNT(*) AS c
                          FROM meter_readings
                          WHERE collector_id = ? AND reading_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                          GROUP BY DATE_FORMAT(reading_date, "%Y-%m")
                          ORDER BY ym ASC');
        $this->db->bind([$collectorId]);
        $rows = $this->db->resultSet();
        $this->db->closeStmt();
        $map = [];
        foreach ($rows as $r) { $map[$r['ym']] = (int)$r['c']; }
        return $map;
    }

    /**
     * Returns counts aggregated by year for the last 5 years (including current).
     * Keys are 'Y'.
     */
    public function getYearlyCountsLast5(int $collectorId): array {
        $this->db->query('SELECT YEAR(reading_date) AS y, COUNT(*) AS c
                          FROM meter_readings
                          WHERE collector_id = ? AND reading_date >= DATE_SUB(CURDATE(), INTERVAL 5 YEAR)
                          GROUP BY YEAR(reading_date)
                          ORDER BY y ASC');
        $this->db->bind([$collectorId]);
        $rows = $this->db->resultSet();
        $this->db->closeStmt();
        $map = [];
        foreach ($rows as $r) { $map[(string)$r['y']] = (int)$r['c']; }
        return $map;
    }

    /**
     * Get last readings for a list of meters.
     * Returns map: meter_id => ['reading_value' => float, 'reading_date' => string]
     */
    public function getLastReadingsForMeters(array $meterIds): array {
        if (empty($meterIds)) {
            return [];
        }
        // Build placeholders for IN clause
        $placeholders = implode(',', array_fill(0, count($meterIds), '?'));
        // Query to get latest reading per meter
        $sql = "SELECT r.meter_id, r.reading_value, r.reading_date
                FROM meter_readings r
                INNER JOIN (
                    SELECT meter_id, MAX(reading_date) AS last_date
                    FROM meter_readings
                    WHERE meter_id IN ($placeholders)
                    GROUP BY meter_id
                ) latest ON latest.meter_id = r.meter_id AND latest.last_date = r.reading_date";
        $this->db->query($sql);
        $this->db->bind($meterIds);
        $rows = $this->db->resultSet();
        $this->db->closeStmt();
        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['meter_id']] = [
                'reading_value' => $row['reading_value'],
                'reading_date' => $row['reading_date'],
            ];
        }
        return $map;
    }

    /**
     * Get the two most recent readings for a meter.
     * Returns array ordered newest first: [ ['id'=>int,'reading_value'=>float,'reading_date'=>string], ... ]
     */
    public function getLastTwoReadingsByMeterId(int $meterId): array {
        $this->db->query('SELECT id, reading_value, reading_date FROM meter_readings WHERE meter_id = ? ORDER BY reading_date DESC, id DESC LIMIT 2');
        $this->db->bind([$meterId]);
        $rows = $this->db->resultSet();
        $this->db->closeStmt();
        return $rows ?: [];
    }

    public function getLatestReadingWithImage(int $meterId): array {
        $this->db->query('SELECT mr.id, mr.reading_value, mr.reading_date, mi.image_path 
                          FROM meter_readings mr 
                          LEFT JOIN meter_images mi ON mr.meter_image_id = mi.id 
                          WHERE mr.meter_id = ? 
                          ORDER BY mr.reading_date DESC, mr.id DESC LIMIT 1');
        $this->db->bind([$meterId]);
        $row = $this->db->single();
        $this->db->closeStmt();
        return $row ?: [];
    }

    /**
     * Retrieves all readings for a given meter, ordered by date ascending.
     * Returns minimal fields suitable for billing selection.
     */
    public function getReadingsByMeterId(int $meterId): array {
        $this->db->query('SELECT id, reading_value, reading_date FROM meter_readings WHERE meter_id = ? ORDER BY reading_date ASC, id ASC');
        $this->db->bind([$meterId]);
        $rows = $this->db->resultSet();
        $this->db->closeStmt();
        return $rows ?: [];
    }
    public function getReadingById(int $id): array|false {
        $this->db->query('SELECT * FROM meter_readings WHERE id = ?');
        $this->db->bind([$id]);
        $row = $this->db->single();
        $this->db->closeStmt();
        return $row ?: false;
    }

    /**
     * Retrieves the reading history for a specific meter.
     *
     * @param int $meterId The ID of the meter.
     * @return array An array of associative arrays, each representing a meter reading.
     */
    public function getMeterReadingHistory(int $meterId): array {
        $this->db->query('SELECT mr.*, u.username AS collector_username, mi.image_path AS photo_url
                          FROM meter_readings mr
                          LEFT JOIN users u ON mr.collector_id = u.id
                          LEFT JOIN meter_images mi ON mr.meter_image_id = mi.id
                          WHERE mr.meter_id = ?
                          ORDER BY mr.reading_date DESC');
        $this->db->bind([$meterId]);
        $readings = $this->db->resultSet();
        $this->db->closeStmt();
        return $readings;
    }
}
