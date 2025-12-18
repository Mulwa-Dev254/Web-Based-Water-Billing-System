<?php
// app/core/Auth.php (Updated for mysqli and new roles)

// Ensure Database.php is included as it's a dependency
require_once '../app/core/Database.php';

class Auth {
    private Database $db; // Explicitly type-hint Database object

    // Constructor now expects the Database wrapper object
    public function __construct(Database $database_wrapper) { // Explicitly type-hint Database
        $this->db = $database_wrapper;
    }

    /**
     * Register a new user with extended details, including role and an optional admin key.
     *
     * @param string $username The user's chosen username.
     * @param string $email The user's email address.
     * @param string $password The user's chosen password (will be hashed).
     * @param string $full_name The user's full name.
     * @param string $address The user's address.
     * @param string $contact_phone The user's contact phone number.
     * @param string $role The role of the user (e.g., 'client', 'admin', 'collector', 'commercial_manager', 'finance_manager').
     * @param string|null $admin_key Optional: A key required for privileged roles (e.g., 'admin_secret').
     * @return bool True on successful registration, false otherwise.
     */
    public function register(string $username, string $email, string $password, string $full_name, string $address, string $contact_phone, string $role, ?string $admin_key = null): bool {
        // Hash the password securely
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Check if username or email already exists
            if ($this->userExists($username, $email)) {
                error_log("Registration failed: Username or email already exists.");
                return false;
            }

            // Validate admin key for privileged roles
            if (in_array($role, ['admin', 'commercial_manager', 'finance_manager', 'collector', 'meter_reader'])) {
                $expected_admin_key = defined('ADMIN_REGISTRATION_KEY') ? ADMIN_REGISTRATION_KEY : 'ADMIN_KEY_123';
                if (strcasecmp((string)$admin_key, (string)$expected_admin_key) !== 0) {
                    error_log("Registration failed: Invalid privileged role key for role '{$role}'.");
                    return false;
                }
            }

            // Insert into users table
            $this->db->query('INSERT INTO users (username, email, password_hash, role, full_name, address, contact_phone) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $this->db->bind([$username, $email, $password_hash, $role, $full_name, $address, $contact_phone]);

            if ($this->db->execute()) {
                $user_id = $this->db->lastInsertId(); // Get the ID of the newly registered user
                $this->db->closeStmt();

                // If the user is a client, also register them in the clients table
                if ($role === 'client') {
                    $this->db->query('INSERT INTO clients (user_id, full_name, address, contact_phone, contact_email) VALUES (?, ?, ?, ?, ?)');
                    $this->db->bind([$user_id, $full_name, $address, $contact_phone, $email]);
                    if (!$this->db->execute()) {
                        error_log("Failed to register client details for user_id: " . $user_id);
                        $this->db->closeStmt();
                        return false;
                    }
                    $this->db->closeStmt();
                }
                return true;
            } else {
                error_log("Database error during user registration: " . $this->db->getError());
                $this->db->closeStmt();
                return false;
            }
        } catch (Exception $e) {
            error_log("Exception during user registration: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Authenticates a user.
     *
     * @param string $username_or_email The username or email provided by the user.
     * @param string $password The password provided by the user.
     * @return bool True if authentication is successful, false otherwise.
     */
    public function login(string $username_or_email, string $password): bool {
        $this->db->query('SELECT id, username, password_hash, role, status FROM users WHERE username = ? OR email = ?');
        $this->db->bind([$username_or_email, $username_or_email]);
        $user = $this->db->single();
        $this->db->closeStmt();

        if ($user && is_array($user) && isset($user['password_hash'])) {
            // Verify password against the stored hash
            if (password_verify($password, $user['password_hash'])) {
                // Check if user account is active
                if ($user['status'] !== 'active') {
                    $_SESSION['error_message'] = "Your account is " . htmlspecialchars($user['status']) . ". Please contact support.";
                    error_log("Login failed: User account is not active.");
                    return false;
                }

                // Start session and set user data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_login_at'] = date('Y-m-d H:i:s'); // Record last login time
                
                // Also set the user variable for views that check it
                $_SESSION['user'] = $user['username'];

                // Update last_login_at in the database
                $this->db->query('UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?');
                $this->db->bind([$user['id']]);
                $this->db->execute(); // No need to check return, just fire and forget for this update
                $this->db->closeStmt();

                return true;
            }
        }
        error_log("Login failed: Invalid username or password for '{$username_or_email}'.");
        return false;
    }

    /**
     * Check if user is logged in.
     *
     * @return bool True if logged in, false otherwise.
     */
    public function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get current user's role.
     *
     * @return string|null The user's role (e.g., 'admin', 'client', 'collector', 'commercial_manager', 'finance_manager') or null if not logged in.
     */
    public function getUserRole(): ?string {
        return $_SESSION['role'] ?? null;
    }

    /**
     * Logout user.
     */
    public function logout(): void {
        session_unset(); // Unset all session variables
        session_destroy(); // Destroy the session
    }

    /**
     * Check if a username or email already exists.
     *
     * @param string $username The username to check.
     * @param string $email The email to check.
     * @return bool True if user exists, false otherwise.
     */
    public function userExists(string $username, string $email): bool {
        $this->db->query('SELECT id FROM users WHERE username = ? OR email = ?');
        $this->db->bind([$username, $email]);
        $this->db->execute();
        $count = $this->db->rowCount();
        $this->db->closeStmt(); // Close statement after execution
        return $count > 0;
    }

    /**
     * Get a user by their ID.
     *
     * @param int $userId The ID of the user to retrieve.
     * @return array|null An associative array of user data if found, null otherwise.
     */
    public function getUserById(int $userId): ?array {
        $this->db->query('SELECT id, username, email, role, full_name, address, contact_phone, status, created_at, updated_at, last_login_at FROM users WHERE id = ?');
        $this->db->bind([$userId]);
        $user = $this->db->single();
        $this->db->closeStmt();
        return $user;
    }

    /**
     * Get the ID of the last inserted row.
     * @return int The ID of the last inserted row.
     */
    public function lastInsertId(): int {
        return $this->db->lastInsertId();
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool {
        $this->db->query('SELECT password_hash FROM users WHERE id = ?');
        $this->db->bind([$userId]);
        $row = $this->db->single();
        $this->db->closeStmt();
        if (!$row || !isset($row['password_hash']) || !password_verify($currentPassword, (string)$row['password_hash'])) {
            return false;
        }
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->query('UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $this->db->bind([$newHash, $userId]);
        $ok = $this->db->execute();
        $this->db->closeStmt();
        return (bool)$ok;
    }
}
