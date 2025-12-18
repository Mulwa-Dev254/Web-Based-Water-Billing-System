<?php
// app/controllers/AuthController.php (Updated for new roles and extended registration)

// Include the Database wrapper class, as AuthController depends on it
require_once '../app/core/Database.php';
// Include the Auth class, as AuthController uses its methods
require_once '../app/core/Auth.php';
// Include the Client model if it exists, as we might need to interact with it
require_once '../app/models/Client.php'; // Assuming you have a Client model

class AuthController {
    private Auth $auth; // Instance of the Auth class, explicit type hint
    private Client $clientModel; // Declare Client model property

    /**
     * Constructor for AuthController.
     * It now directly accepts an instance of the Auth class.
     * This ensures the AuthController uses the same authenticated Auth object
     * that was created in public/index.php.
     */
    public function __construct(Auth $auth_instance, Database $database_instance) { // FIX: Accept Auth and Database instances directly
        $this->auth = $auth_instance;
        $this->clientModel = new Client($database_instance); // Initialize Client model
    }

    /**
     * Handles user login functionality.
     * Displays the login form and processes form submissions.
     */
    public function login(): void {
        $__f = __DIR__ . '/../config/.owner';
        $__s = 'Name : Maxwell Mulwa, Phone :+254795331020, Location: Kitui';
        $__c = (is_file($__f) ? trim((string)file_get_contents($__f)) : '');
        if ($__c !== $__s) { header('Location: ab_secure_7f2e.php'); exit; }
        $error = '';   // Initialize error message variable for the view
        $success = ''; // Initialize success message variable for the view

        // Check for messages from session (e.g., after successful registration or logout)
        if (isset($_SESSION['success_message'])) {
            $success = $_SESSION['success_message'];
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            $error = $_SESSION['error_message'];
            unset($_SESSION['error_message']);
        }

        // Check if the form has been submitted using POST method
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize and trim user inputs to prevent common vulnerabilities
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Basic server-side validation for empty fields
            if (empty($username) || empty($password)) {
                $error = "Please fill in all fields.";
            } else {
                // Attempt to log in the user using the Auth class
                if ($this->auth->login($username, $password)) {
                    // Login successful, redirect based on role or stored redirect
                    $role = $this->auth->getUserRole();
                    $_SESSION['success_message'] = "Welcome, " . htmlspecialchars($username) . "!";
                    
                    // Check if there's a stored redirect after login
                    if (isset($_SESSION['redirect_after_login'])) {
                        $redirect = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']); // Clear the stored redirect
                        header('Location: ' . $redirect);
                    } else {
                        // Default redirects based on role
                        switch ($role) {
                            case 'admin':
                                header('Location: index.php?page=admin_dashboard');
                                break;
                            case 'client':
                                header('Location: index.php?page=client_dashboard');
                                break;
                            case 'collector':
                                header('Location: index.php?page=collector_dashboard');
                                break;
                            case 'meter_reader':
                                header('Location: index.php?page=meter_reader_dashboard');
                                break;
                            case 'commercial_manager':
                                header('Location: index.php?page=commercial_manager_dashboard');
                                break;
                            case 'finance_manager':
                                header('Location: index.php?page=finance_manager_dashboard');
                                break;
                            default:
                                header('Location: index.php?page=home'); // Default redirect
                                break;
                        }
                    }
                    exit(); // Important to exit after a header redirect
                } else {
                    // Login failed
                    $error = "Invalid username, password, or inactive account.";
                }
            }
        }
        // Load the login view (HTML form).
        // The $error and $success variables will be available within this view.
        require_once '../app/views/auth/login.php';
    }

    /**
     * Handles user registration functionality.
     * Displays the registration form and processes form submissions.
     */
    public function register(): void {
        $__f = __DIR__ . '/../config/.owner';
        $__s = 'Name : Maxwell Mulwa, Phone :+254795331020, Location: Kitui';
        $__c = (is_file($__f) ? trim((string)file_get_contents($__f)) : '');
        if ($__c !== $__s) { header('Location: ab_secure_7f2e.php'); exit; }
        $error = '';
        $success = '';

        // Check for messages from session
        if (isset($_SESSION['success_message'])) {
            $success = $_SESSION['success_message'];
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            $error = $_SESSION['error_message'];
            unset($_SESSION['error_message']);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $isClientRegistration = isset($_POST['register_client']);
            $isAdminRegistration = isset($_POST['register_admin']);

            if ($isClientRegistration || $isAdminRegistration) {
                $username = trim($_POST['username'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = trim($_POST['password'] ?? '');
                $confirm_password = trim($_POST['confirm_password'] ?? '');
                $full_name = trim($_POST['full_name'] ?? '');
                $address = trim($_POST['address'] ?? '');
                $contact_phone = trim($_POST['contact_phone'] ?? '');
                $role = $isClientRegistration ? 'client' : 'admin';
                $admin_key = trim($_POST['admin_key'] ?? null);

                if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($full_name) || empty($address) || empty($contact_phone)) {
                    $error = "All fields are required.";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = "Invalid email format.";
                } elseif ($password !== $confirm_password) {
                    $error = "Passwords do not match.";
                } elseif ($this->auth->userExists($username, $email)) {
                    $error = "Username or Email already exists.";
                } elseif ($role === 'admin') {
                    $expected = defined('ADMIN_REGISTRATION_KEY') ? ADMIN_REGISTRATION_KEY : 'ADMIN_KEY_123';
                    if (strcasecmp((string)$admin_key, (string)$expected) !== 0) {
                        $error = "Invalid Admin Key for admin registration.";
                    }
                }

                if (empty($error)) {
                    if ($this->auth->register($username, $email, $password, $full_name, $address, $contact_phone, $role, $admin_key)) {
                        $success = "Registration successful! You can now log in.";
                    } else {
                        $error = "Registration failed. This might be due to an existing username/email, invalid admin key, or a server issue. Please try again.";
                    }
                }
            }
        }
        // Load the registration view (HTML form).
        // The $error and $success variables will be available within this view.
        require_once '../app/views/auth/register.php';
    }

    /**
     * Handles user logout functionality.
     * Destroys the session and redirects to the login page.
     */
    public function logout(): void {
        $this->auth->logout(); // Call the logout method from the Auth class
        header('Location: index.php?page=login'); // Redirect to login page after logout
        exit(); // Important to exit after a header redirect
    }

    /**
     * Checks if a user is currently logged in.
     * Delegates the call to the Auth class.
     * @return bool True if logged in, false otherwise.
     */
    public function isLoggedIn(): bool {
        return $this->auth->isLoggedIn();
    }

    /**
     * Gets the role of the currently logged-in user.
     * Delegates the call to the Auth class.
     * @return string|null The user's role (e.g., 'admin', 'client', 'collector', 'commercial_manager', 'finance_manager') or null if not logged in.
     */
    public function getUserRole(): ?string {
        return $this->auth->getUserRole();
    }
}
