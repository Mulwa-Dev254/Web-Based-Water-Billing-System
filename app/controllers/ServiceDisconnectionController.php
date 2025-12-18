<?php
// app/controllers/ServiceDisconnectionController.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/ServiceDisconnection.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/SMS.php';
require_once __DIR__ . '/../models/Bill.php';

class ServiceDisconnectionController {
    private $db;
    private $auth;
    private $serviceDisconnection;
    private $client;
    private $user;
    private $sms;
    private $bill;
    
    public function __construct(Database $database_instance, Auth $auth_instance) {
        $this->db = $database_instance;
        $this->auth = $auth_instance;
        $this->serviceDisconnection = new ServiceDisconnection();
        $this->client = new Client($this->db);
        $this->user = new User($this->db);
        $this->sms = new SMS();
        $this->bill = new Bill($this->db);
    }
    
    /**
     * Helper to check if the user has appropriate role
     * Redirects to login if not authorized
     */
    private function checkAuth($allowedRoles = ['admin', 'commercial_manager', 'finance_manager']) {
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), $allowedRoles)) {
            $_SESSION['error_message'] = "Access denied. You don't have permission to access this page.";
            // Store the intended redirect in session
            $_SESSION['redirect_after_login'] = 'index.php?page=login';
            header('Location: index.php?page=login');
            exit();
        }
    }
    
    /**
     * Render a view with data
     */
    private function view($view, $data = []) {
        // Check if the view file exists
        $viewFile = '../app/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            extract($data);
            require_once $viewFile;
        } else {
            die("View {$view} not found");
        }
    }
    
    /**
     * Display the disconnection orders dashboard
     */
    public function dashboard() {
        $this->checkAuth(['admin', 'commercial_manager', 'finance_manager', 'collector']);
        
        $userRole = $this->auth->getUserRole();
        $userId = $this->auth->getUserId();
        
        // Get disconnection orders based on user role
        if ($userRole === 'admin') {
            $disconnectionOrders = $this->serviceDisconnection->getAllDisconnectionOrders();
            $reconnectionOrders = $this->serviceDisconnection->getAllReconnectionOrders();
        } elseif ($userRole === 'commercial_manager' || $userRole === 'finance_manager') {
            $disconnectionOrders = $this->serviceDisconnection->getAllDisconnectionOrders();
            $reconnectionOrders = $this->serviceDisconnection->getAllReconnectionOrders();
        } elseif ($userRole === 'collector') {
            $disconnectionOrders = $this->serviceDisconnection->getDisconnectionOrdersByAssignee($userId);
            $reconnectionOrders = $this->serviceDisconnection->getReconnectionOrdersByAssignee($userId);
        }
        
        // Get staff members for assignment
        $collectors = $this->user->getUsersByRole('collector');
        
        $this->view('service_disconnection/dashboard', [
            'disconnectionOrders' => $disconnectionOrders,
            'reconnectionOrders' => $reconnectionOrders,
            'collectors' => $collectors,
            'userRole' => $userRole
        ]);
    }
    
    /**
     * Display the form to create a new disconnection order
     */
    public function newDisconnectionForm() {
        $this->checkAuth(['admin', 'commercial_manager', 'finance_manager']);
        
        // Get clients with outstanding balances
        $clientsWithOutstandingBalances = $this->bill->getClientsWithOutstandingBalances();
        
        $this->view('service_disconnection/new_disconnection', [
            'clients' => $clientsWithOutstandingBalances
        ]);
    }
    
    /**
     * Create a new disconnection order
     */
    public function createDisconnectionOrder() {
        $this->checkAuth(['admin', 'commercial_manager', 'finance_manager']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clientId = $_POST['client_id'] ?? null;
            $meterNumber = $_POST['meter_number'] ?? null;
            $outstandingBalance = $_POST['outstanding_balance'] ?? 0;
            $reason = $_POST['reason'] ?? '';
            $createdBy = $this->auth->getUserId();
            
            // Validate input
            if (!$clientId || !$meterNumber || !$reason) {
                $_SESSION['error_message'] = "All fields are required.";
                header('Location: index.php?page=new_disconnection');
                exit();
            }
            
            // Create disconnection order
            $orderId = $this->serviceDisconnection->createDisconnectionOrder(
                $clientId,
                $meterNumber,
                $outstandingBalance,
                $reason,
                $createdBy
            );
            
            if ($orderId) {
                $_SESSION['success_message'] = "Disconnection order created successfully.";
                header('Location: index.php?page=service_disconnection_dashboard');
                exit();
            } else {
                $_SESSION['error_message'] = "Failed to create disconnection order.";
                header('Location: index.php?page=new_disconnection');
                exit();
            }
        } else {
            header('Location: index.php?page=new_disconnection');
            exit();
        }
    }
    
    /**
     * Assign a disconnection order to a collector
     */
    public function assignDisconnectionOrder() {
        $this->checkAuth(['admin', 'commercial_manager']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = $_POST['order_id'] ?? null;
            $assigneeId = $_POST['assignee_id'] ?? null;
            $scheduledDate = $_POST['scheduled_date'] ?? null;
            
            // Validate input
            if (!$orderId || !$assigneeId || !$scheduledDate) {
                $_SESSION['error_message'] = "All fields are required.";
                header('Location: index.php?page=service_disconnection_dashboard');
                exit();
            }
            
            // Assign disconnection order
            $success = $this->serviceDisconnection->assignDisconnectionOrder(
                $orderId,
                $assigneeId,
                $scheduledDate
            );
            
            if ($success) {
                $_SESSION['success_message'] = "Disconnection order assigned successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to assign disconnection order.";
            }
            
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        } else {
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        }
    }
    
    /**
     * Complete a disconnection order
     */
    public function completeDisconnectionOrder() {
        $this->checkAuth(['admin', 'collector']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = $_POST['order_id'] ?? null;
            $notes = $_POST['notes'] ?? '';
            
            // Validate input
            if (!$orderId) {
                $_SESSION['error_message'] = "Order ID is required.";
                header('Location: index.php?page=service_disconnection_dashboard');
                exit();
            }
            
            // Complete disconnection order
            $success = $this->serviceDisconnection->completeDisconnectionOrder(
                $orderId,
                $notes
            );
            
            if ($success) {
                $_SESSION['success_message'] = "Disconnection order completed successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to complete disconnection order.";
            }
            
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        } else {
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        }
    }
    
    /**
     * Display the form to create a new reconnection order
     */
    public function newReconnectionForm() {
        $this->checkAuth(['admin', 'commercial_manager', 'finance_manager']);
        
        // Get completed disconnection orders
        $completedDisconnections = $this->serviceDisconnection->getCompletedDisconnectionOrders();
        
        $this->view('service_disconnection/new_reconnection', [
            'disconnections' => $completedDisconnections
        ]);
    }
    
    /**
     * Create a new reconnection order
     */
    public function createReconnectionOrder() {
        $this->checkAuth(['admin', 'commercial_manager', 'finance_manager']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $disconnectionId = $_POST['disconnection_id'] ?? null;
            $paymentId = $_POST['payment_id'] ?? null;
            $notes = $_POST['notes'] ?? '';
            $createdBy = $this->auth->getUserId();
            
            // Validate input
            if (!$disconnectionId) {
                $_SESSION['error_message'] = "Disconnection order is required.";
                header('Location: index.php?page=new_reconnection');
                exit();
            }
            
            // Create reconnection order
            $orderId = $this->serviceDisconnection->createReconnectionOrder(
                $disconnectionId,
                $paymentId,
                $notes,
                $createdBy
            );
            
            if ($orderId) {
                $_SESSION['success_message'] = "Reconnection order created successfully.";
                header('Location: index.php?page=service_disconnection_dashboard');
                exit();
            } else {
                $_SESSION['error_message'] = "Failed to create reconnection order.";
                header('Location: index.php?page=new_reconnection');
                exit();
            }
        } else {
            header('Location: index.php?page=new_reconnection');
            exit();
        }
    }
    
    /**
     * Assign a reconnection order to a collector
     */
    public function assignReconnectionOrder() {
        $this->checkAuth(['admin', 'commercial_manager']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = $_POST['order_id'] ?? null;
            $assigneeId = $_POST['assignee_id'] ?? null;
            $scheduledDate = $_POST['scheduled_date'] ?? null;
            
            // Validate input
            if (!$orderId || !$assigneeId || !$scheduledDate) {
                $_SESSION['error_message'] = "All fields are required.";
                header('Location: index.php?page=service_disconnection_dashboard');
                exit();
            }
            
            // Assign reconnection order
            $success = $this->serviceDisconnection->assignReconnectionOrder(
                $orderId,
                $assigneeId,
                $scheduledDate
            );
            
            if ($success) {
                $_SESSION['success_message'] = "Reconnection order assigned successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to assign reconnection order.";
            }
            
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        } else {
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        }
    }
    
    /**
     * Complete a reconnection order
     */
    public function completeReconnectionOrder() {
        $this->checkAuth(['admin', 'collector']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = $_POST['order_id'] ?? null;
            $notes = $_POST['notes'] ?? '';
            
            // Validate input
            if (!$orderId) {
                $_SESSION['error_message'] = "Order ID is required.";
                header('Location: index.php?page=service_disconnection_dashboard');
                exit();
            }
            
            // Complete reconnection order
            $success = $this->serviceDisconnection->completeReconnectionOrder(
                $orderId,
                $notes
            );
            
            if ($success) {
                $_SESSION['success_message'] = "Reconnection order completed successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to complete reconnection order.";
            }
            
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        } else {
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        }
    }
    
    /**
     * View details of a disconnection order
     */
    public function viewDisconnectionOrder() {
        $this->checkAuth(['admin', 'commercial_manager', 'finance_manager', 'collector']);
        
        $orderId = $_GET['id'] ?? null;
        
        if (!$orderId) {
            $_SESSION['error_message'] = "Order ID is required.";
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        }
        
        // Get disconnection order details
        $order = $this->serviceDisconnection->getDisconnectionOrderById($orderId);
        
        if (!$order) {
            $_SESSION['error_message'] = "Disconnection order not found.";
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        }
        
        // Get client details
        $client = $this->client->getClientById($order['client_id']);
        
        // Get assignee details if assigned
        $assignee = null;
        if ($order['assigned_to']) {
            $assignee = $this->user->getUserById($order['assigned_to']);
        }
        
        $this->view('service_disconnection/view_disconnection', [
            'order' => $order,
            'client' => $client,
            'assignee' => $assignee
        ]);
    }
    
    /**
     * View details of a reconnection order
     */
    public function viewReconnectionOrder() {
        $this->checkAuth(['admin', 'commercial_manager', 'finance_manager', 'collector']);
        
        $orderId = $_GET['id'] ?? null;
        
        if (!$orderId) {
            $_SESSION['error_message'] = "Order ID is required.";
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        }
        
        // Get reconnection order details
        $order = $this->serviceDisconnection->getReconnectionOrderById($orderId);
        
        if (!$order) {
            $_SESSION['error_message'] = "Reconnection order not found.";
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        }
        
        // Get client details
        $client = $this->client->getClientById($order['client_id']);
        
        // Get assignee details if assigned
        $assignee = null;
        if ($order['assigned_to']) {
            $assignee = $this->user->getUserById($order['assigned_to']);
        }
        
        // Get disconnection order details
        $disconnectionOrder = $this->serviceDisconnection->getDisconnectionOrderById($order['disconnection_id']);
        
        $this->view('service_disconnection/view_reconnection', [
            'order' => $order,
            'client' => $client,
            'assignee' => $assignee,
            'disconnectionOrder' => $disconnectionOrder
        ]);
    }
    
    /**
     * Cancel a disconnection order
     */
    public function cancelDisconnectionOrder() {
        $this->checkAuth(['admin', 'commercial_manager', 'finance_manager']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = $_POST['order_id'] ?? null;
            $reason = $_POST['reason'] ?? '';
            
            // Validate input
            if (!$orderId) {
                $_SESSION['error_message'] = "Order ID is required.";
                header('Location: index.php?page=service_disconnection_dashboard');
                exit();
            }
            
            // Cancel disconnection order
            $success = $this->serviceDisconnection->cancelDisconnectionOrder(
                $orderId,
                $reason
            );
            
            if ($success) {
                $_SESSION['success_message'] = "Disconnection order cancelled successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to cancel disconnection order.";
            }
            
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        } else {
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        }
    }
    
    /**
     * Cancel a reconnection order
     */
    public function cancelReconnectionOrder() {
        $this->checkAuth(['admin', 'commercial_manager', 'finance_manager']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = $_POST['order_id'] ?? null;
            $reason = $_POST['reason'] ?? '';
            
            // Validate input
            if (!$orderId) {
                $_SESSION['error_message'] = "Order ID is required.";
                header('Location: index.php?page=service_disconnection_dashboard');
                exit();
            }
            
            // Cancel reconnection order
            $success = $this->serviceDisconnection->cancelReconnectionOrder(
                $orderId,
                $reason
            );
            
            if ($success) {
                $_SESSION['success_message'] = "Reconnection order cancelled successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to cancel reconnection order.";
            }
            
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        } else {
            header('Location: index.php?page=service_disconnection_dashboard');
            exit();
        }
    }
}