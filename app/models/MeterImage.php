<?php
// app/models/MeterImage.php

require_once __DIR__ . '/../core/Database.php';

class MeterImage {
    private Database $db;

    public function __construct(Database $database_instance) {
        $this->db = $database_instance;
    }

    /**
     * Records a new meter image.
     *
     * @param int $clientId The ID of the client associated with the meter.
     * @param int $meterId The ID of the meter.
     * @param int $collectorId The ID of the collector who took the image.
     * @param string $imagePath The Base64 encoded image data.
     * @param float $latitude The latitude coordinate.
     * @param float $longitude The longitude coordinate.
     * @param string|null $notes Optional notes for the image.
     * @return int|false The ID of the newly inserted image, or false on failure.
     */
    public function recordMeterImage(int $clientId, int $meterId, int $collectorId, string $imagePath, float $latitude, float $longitude, ?string $notes = null) {
        $this->db->query('INSERT INTO meter_images (client_id, meter_id, collector_id, image_path, taken_at, notes, latitude, longitude) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)');
        $this->db->bind([$clientId, $meterId, $collectorId, $imagePath, $notes, $latitude, $longitude]);
        if ($this->db->execute()) {
            $lastId = $this->db->lastInsertId();
            $this->db->closeStmt();
            return $lastId;
        } else {
            error_log("Failed to record meter image: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Retrieves a meter image by its ID.
     *
     * @param int $imageId The ID of the meter image.
     * @return array|null An associative array of image data if found, null otherwise.
     */
    public function getMeterImageById(int $imageId): ?array {
        $this->db->query('SELECT * FROM meter_images WHERE id = ?');
        $this->db->bind([$imageId]);
        $image = $this->db->single();
        $this->db->closeStmt();
        return $image;
    }
}