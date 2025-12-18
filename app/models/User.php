<?php
// app/models/User.php

require_once __DIR__ . '/../core/Database.php';

class User {
    private Database $db;

    public function __construct(Database $database_instance) {
        $this->db = $database_instance;
    }

    /**
     * Retrieves all users from the database.
     *
     * @return array An array of associative arrays, each representing a user.
     */
    public function getAllUsers(): array {
        $this->db->query('SELECT id, username, email, role, full_name, address, contact_phone, status, created_at, updated_at FROM users ORDER BY created_at DESC');
        $users = $this->db->resultSet();
        $this->db->closeStmt();
        return $users;
    }

    /**
     * Retrieves users by their role.
     *
     * @param string $role The role to filter by (e.g., 'admin', 'client', 'collector').
     * @return array An array of associative arrays, each representing a user with the specified role.
     */
    public function getUsersByRole(string $role): array {
        $this->db->query('SELECT id, username, email, role, full_name, address, contact_phone, status FROM users WHERE role = ? ORDER BY username ASC');
        $this->db->bind([$role]);
        $users = $this->db->resultSet();
        $this->db->closeStmt();
        return $users;
    }

    /**
     * Retrieves a single user by their ID.
     *
     * @param int $userId The ID of the user.
     * @return array|null An associative array of user data if found, null otherwise.
     */
    public function getUserById(int $userId): ?array {
        $this->db->query('SELECT id, username, email, role, full_name, address, contact_phone, status FROM users WHERE id = ?');
        $this->db->bind([$userId]);
        $user = $this->db->single();
        $this->db->closeStmt();
        return $user;
    }

    /**
     * Counts the total number of users in the system.
     *
     * @return int The total number of users.
     */
    public function getUserCount(): int {
        $this->db->query('SELECT COUNT(id) AS total_users FROM users');
        $result = $this->db->single();
        $this->db->closeStmt();
        return $result['total_users'] ?? 0;
    }

    /**
     * Counts the number of users for a specific role.
     *
     * @param string $role The role to count (e.g., 'admin', 'client', 'collector').
     * @return int The number of users with the specified role.
     */
    public function getUserCountByRole(string $role): int {
        $this->db->query('SELECT COUNT(id) AS role_count FROM users WHERE role = ?');
        $this->db->bind([$role]);
        $result = $this->db->single();
        $this->db->closeStmt();
        return $result['role_count'] ?? 0;
    }


    public function updateUserProfile(int $userId, string $fullName, string $email, string $contactPhone, string $address): bool {
        $this->db->query('UPDATE users SET full_name = ?, email = ?, contact_phone = ?, address = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $this->db->bind([$fullName, $email, $contactPhone, $address, $userId]);
        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            error_log("Failed to update user profile: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Updates an existing user's details.
     * Note: This method does NOT update the password for security reasons.
     *
     * @param int $userId The ID of the user to update.
     * @param string $username The new username.
     * @param string $email The new email.
     * @param string $role The new role.
     * @param string $fullName The new full name.
     * @param string $address The new address.
     * @param string $contactPhone The new contact phone.
     * @param string $status The new status ('active', 'inactive').
     * @return bool True on success, false on failure.
     */
    public function updateUser(int $userId, string $username, string $email, string $role, string $fullName, string $address, string $contactPhone, string $status): bool {
        $this->db->query('UPDATE users SET username = ?, email = ?, role = ?, full_name = ?, address = ?, contact_phone = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $this->db->bind([$username, $email, $role, $fullName, $address, $contactPhone, $status, $userId]);
        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            error_log("Failed to update user: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }

    /**
     * Deletes a user by their ID.
     *
     * @param int $userId The ID of the user to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteUser(int $userId): bool {
        $this->db->query('DELETE FROM users WHERE id = ?');
        $this->db->bind([$userId]);
        if ($this->db->execute()) {
            $this->db->closeStmt();
            return true;
        } else {
            error_log("Failed to delete user: " . $this->db->getError());
            $this->db->closeStmt();
            return false;
        }
    }
}
