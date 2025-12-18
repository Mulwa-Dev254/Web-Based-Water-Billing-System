<?php
// app/controllers/ServiceOrderController.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/ServiceOrder.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/SMS.php';

class ServiceOrderController {
    private $db;
    private $auth;
    private $serviceOrder;
    private $client;
    private $user;
    private $sms;
    
    public function __construct(Database $database_instance, Auth $auth_instance) {
        $this->db = $database_instance;
        $this->auth = $auth_instance;
        $this->serviceOrder = new ServiceOrder();
        $this->client = new Client($this->db);
        $this->user = new User($this->db);
        $this->sms = new SMS();
    }
    
    /**
     * Helper to check if the user has appropriate role
     */
    private function checkAuth($allowedRoles = ['admin', 'commercial_manager']) {
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), $allowedRoles)) {
            $_SESSION['error_message'] = "Access denied. You don't have permission to access this page.";
            $_SESSION['redirect_after_login'] = 'index.php?page=login';
            header('Location: index.php?page=login');
            exit();
        }
    }
    
    /**
     * Render a view with data
     */
    private function view($view, $data = []) {
        $viewFile = '../app/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            extract($data);
            require_once $viewFile;
        } else {
            die("View {$view} not found");
        }
    }
    
    /**
     * Display the service orders dashboard
     */
    public function dashboard() {
        $this->checkAuth(['admin', 'commercial_manager', 'meter_reader', 'collector']);
        
        $userRole = $this->auth->getUserRole();
        $userId = $this->auth->getUserId();
        
        // Get service orders based on user role
        if ($userRole === 'admin' || $userRole === 'commercial_manager') {
            $serviceOrders = $this->serviceOrder->getAllServiceOrders();
        } else {
            $serviceOrders = $this->serviceOrder->getServiceOrdersByAssignee($userId);
        }
        
        // Get staff members for assignment
        $fieldStaff = $this->user->getUsersByRoles(['meter_reader', 'collector']);
        
        $this->view('service_order/dashboard', [
            'serviceOrders' => $serviceOrders,
            'fieldStaff' => $fieldStaff,
            'userRole' => $userRole
        ]);
    }
    
    /**
     * Display the form to create a new service order
     */
    public function newServiceOrderForm() {
        $this->checkAuth(['admin', 'commercial_manager']);
        
        // Get all clients
        $clients = $this->client->getAllClients();
        
        // Get service types
        $serviceTypes = $this->serviceOrder->getServiceTypes();
        
        $this->view('service_order/new_service_order', [
            'clients' => $clients,
            'serviceTypes' => $serviceTypes
        ]);
    }
    
    /**
     * Create a new service order
     */
    public function createServiceOrder() {
        $this->checkAuth(['admin', 'commercial_manager']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clientId = $_POST['client_id'] ?? null;
            $serviceTypeId = $_POST['service_type_id'] ?? null;
            $description = $_POST['description'] ?? '';
            $scheduledDate = $_POST['scheduled_date'] ?? null;
            $priority = $_POST['priority'] ?? 'medium';
            $createdBy = $this->auth->getUserId();
            
            // Validate input
            if (!$clientId || !$serviceTypeId || !$description || !$scheduledDate) {
                $_SESSION['error_message'] = "All fields are required.";
                header('Location: index.php?page=new_service_order');
                exit();
            }
            
            // Create service order
            $orderId = $this->serviceOrder->createServiceOrder(
                $clientId, $serviceTypeId, $description, $scheduledDate, $priority, $createdBy
            );
            
            if ($orderId) {
                $_SESSION['success_message'] = "Service order created successfully.";
                header('Location: index.php?page=service_order_dashboard');
            } else {
                $_SESSION['error_message'] = "Failed to create service order.";
                header('Location: index.php?page=new_service_order');
            }
            exit();
        } else {
            header('Location: index.php?page=new_service_order');
            exit();
        }
    }
    
    /**
     * Assign a service order to a field staff
     */
    public function assignServiceOrder() {
        $this->checkAuth(['admin', 'commercial_manager']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = $_POST['order_id'] ?? null;
            $assigneeId = $_POST['assignee_id'] ?? null;
            
            // Validate input
            if (!$orderId || !$assigneeId) {
                $_SESSION['error_message'] = "All fields are required.";
                header('Location: index.php?page=service_order_dashboard');
                exit();
            }
            
            // Assign service order
            $success = $this->serviceOrder->assignServiceOrder($orderId, $assigneeId);
            
            if ($success) {
                // Send SMS notification to the assignee
                $assignee = $this->user->getUserById($assigneeId);
                if ($assignee && !empty($assignee['phone'])) {
                    $order = $this->serviceOrder->getServiceOrderById($orderId);
                    $message = "You have been assigned a new service order #{$orderId}. Type: {$order['service_type']}. Scheduled for: {$order['scheduled_date']}.";
                    $this->sms->sendSMS($assignee['phone'], $message, 'service_order', $orderId);
                }
                
                $_SESSION['success_message'] = "Service order assigned successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to assign service order.";
            }
            
            header('Location: index.php?page=service_order_dashboard');
            exit();
        }
    }
    
    /**
     * Update the status of a service order
     */
    public function updateServiceOrderStatus() {
        $this->checkAuth(['admin', 'commercial_manager', 'meter_reader', 'collector']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = $_POST['order_id'] ?? null;
            $status = $_POST['status'] ?? null;
            $notes = $_POST['notes'] ?? '';
            $updatedBy = $this->auth->getUserId();
            
            // Validate input
            if (!$orderId || !$status) {
                $_SESSION['error_message'] = "Order ID and status are required.";
                header('Location: index.php?page=service_order_dashboard');
                exit();
            }
            
            // Update service order status
            $success = $this->serviceOrder->updateServiceOrderStatus(
                $orderId, $status, $notes, $updatedBy
            );
            
            if ($success) {
                // If the order is completed, send SMS notification to the client
                if ($status === 'completed') {
                    $order = $this->serviceOrder->getServiceOrderById($orderId);
                    $client = $this->client->getClientById($order['client_id']);
                    
                    if ($client && !empty($client['phone'])) {
                        $message = "Your service order #{$orderId} for {$order['service_type']} has been completed. Thank you for using our services.";
                        $this->sms->sendSMS($client['phone'], $message, 'service_order', $orderId);
                    }
                }
                
                $_SESSION['success_message'] = "Service order status updated successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to update service order status.";
            }
            
            header('Location: index.php?page=service_order_dashboard');
            exit();
        }
    }
    
    /**
     * View details of a service order
     */
    public function viewServiceOrder() {
        $this->checkAuth(['admin', 'commercial_manager', 'meter_reader', 'collector']);
        
        $orderId = $_GET['id'] ?? null;
        
        if (!$orderId) {
            $_SESSION['error_message'] = "Order ID is required.";
            header('Location: index.php?page=service_order_dashboard');
            exit();
        }
        
        // Get service order details
        $order = $this->serviceOrder->getServiceOrderById($orderId);
        
        if (!$order) {
            $_SESSION['error_message'] = "Service order not found.";
            header('Location: index.php?page=service_order_dashboard');
            exit();
        }
        
        // Get client details
        $client = $this->client->getClientById($order['client_id']);
        
        // Get assignee details if assigned
        $assignee = null;
        if ($order['assigned_to']) {
            $assignee = $this->user->getUserById($order['assigned_to']);
        }
        
        // Get order history
        $orderHistory = $this->serviceOrder->getServiceOrderHistory($orderId);
        
        $this->view('service_order/view_service_order', [
            'order' => $order,
            'client' => $client,
            'assignee' => $assignee,
            'orderHistory' => $orderHistory
        ]);
    }
}