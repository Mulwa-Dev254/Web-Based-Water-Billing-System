<?php
// app/core/Database.php

class Database {
    private mysqli $conn;
    private $stmt;
    private $error; // Stores the last error message from statement or connection

    public function __construct(mysqli $mysqli_connection) {
        $this->conn = $mysqli_connection;
    }

    /**
     * Prepares a SQL query.
     * @param string $sql The SQL query string.
     * @throws Exception If query preparation fails.
     */
    public function query(string $sql) {
        $this->stmt = $this->conn->prepare($sql);
        if ($this->stmt === false) {
            // Capture error from the connection if prepare fails
            $this->error = $this->conn->error;
            error_log("Database query preparation failed: " . $this->error . " SQL: " . $sql);
            throw new Exception("Database query preparation failed: " . $this->error);
        }
        // Clear any previous statement-specific error if prepare was successful
        $this->error = null;
    }

    /**
     * Binds parameters to the prepared statement.
     * @param array $params An indexed array of values to bind.
     */
    public function bind(array $params) {
        if (empty($params)) {
            return; // No parameters to bind
        }

        if (!$this->stmt) {
            $this->error = "Attempted to bind parameters to a null statement. Call query() first.";
            error_log($this->error);
            return;
        }

        $types = '';
        foreach ($params as $value) {
            if (is_int($value)) {
                $types .= 'i'; // Integer
            } elseif (is_double($value) || is_float($value)) {
                $types .= 'd'; // Double/Float
            } elseif (is_string($value)) {
                $types .= 's'; // String
            } else {
                $types .= 'b'; // Blob (for other types, though 's' often works for most)
            }
        }

        // Create an array of references for bind_param
        $bind_args = [$types];
        foreach ($params as $key => $value) {
            $bind_args[] = &$params[$key]; // Pass by reference
        }

        // Call bind_param using call_user_func_array for dynamic arguments
        if (!call_user_func_array([$this->stmt, 'bind_param'], $bind_args)) {
            // Capture error from the statement if bind_param fails
            $this->error = $this->stmt->error;
            error_log("Database bind_param failed: " . $this->error);
            // In a production app, you might throw an exception here too.
        }
    }

    /**
     * Executes the prepared statement.
     * @return bool True on success, false on failure.
     */
    public function execute(): bool {
        try {
            if (!$this->stmt) {
                $this->error = "Attempted to execute a null statement. Call query() and bind() first.";
                error_log($this->error);
                return false;
            }
            $result = $this->stmt->execute();
            if ($result === false) {
                // Capture error from the statement if execute fails
                $this->error = $this->stmt->error;
                error_log("Database execution failed: " . $this->error);
            }
            return $result;
        } catch (Exception $e) {
            // Catch any unexpected exceptions during execution
            $this->error = "Database execution exception: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    /**
     * Gets the result set as an array of associative arrays.
     * @return array An array of associative arrays, or an empty array on failure.
     */
    public function resultSet(): array {
        // Ensure execute is called before attempting to get results
        if (!$this->execute()) {
            return []; // Return empty array if execution failed
        }
        $result = $this->stmt->get_result();
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    /**
     * Gets a single record as an associative array.
     * @return array|null An associative array representing the record, or null if not found or on failure.
     */
    public function single(): ?array {
        // Ensure execute is called before attempting to get results
        if (!$this->execute()) {
            return null; // Return null if execution failed
        }
        $result = $this->stmt->get_result();
        if ($result) {
            return $result->fetch_assoc();
        }
        return null;
    }

    /**
     * Gets the number of rows in the result set for SELECT queries.
     * @return int The number of rows, or 0 if no statement or no rows.
     */
    public function rowCount(): int {
        if ($this->stmt) {
            $this->stmt->store_result(); // Must call store_result() for num_rows to be accurate
            return $this->stmt->num_rows;
        }
        return 0;
    }

    /**
     * Gets the ID generated by a query on a table with an AUTO_INCREMENT column.
     * @return int The ID of the row inserted or 0 if no ID was generated.
     */
    public function lastInsertId(): int {
        return $this->conn->insert_id;
    }

    /**
     * Closes the prepared statement and frees resources.
     */
    public function closeStmt(): void {
        if ($this->stmt) {
            $this->stmt->close();
            $this->stmt = null; // Clear the statement property
        }
    }

    /**
     * Retrieves the last error message from the database operation.
     * Prioritizes statement error, then connection error, then internal error property.
     * @return string The error message, or an empty string if no error.
     */
    public function getError(): string {
        // Prefer error from the statement object if available
        if ($this->stmt && $this->stmt->error) {
            return $this->stmt->error;
        }
        // Fallback to error from the connection object if statement error is not available
        if ($this->conn && $this->conn->error) {
            return $this->conn->error;
        }
        // Finally, return the internal error property if set
        return $this->error ?? '';
    }
}
