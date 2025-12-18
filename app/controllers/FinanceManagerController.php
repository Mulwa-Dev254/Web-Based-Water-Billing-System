<?php
// app/controllers/FinanceManagerController.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Bill.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/BillingPlan.php';
require_once __DIR__ . '/../models/ClientBill.php';

class FinanceManagerController {
    private Database $db;
    private Auth $auth;
    private User $userModel;
    private Payment $paymentModel;
    private Bill $billModel;
    private Client $clientModel;
    private BillingPlan $billingPlanModel;

    public function __construct(Database $database_instance, Auth $auth_instance) {
        $this->db = $database_instance;
        $this->auth = $auth_instance;
        $this->userModel = new User($this->db);
        $this->paymentModel = new Payment($this->db);
        $this->billModel = new Bill($this->db);
        $this->clientModel = new Client($this->db);
        $this->billingPlanModel = new BillingPlan($this->db);
    }

    /**
     * Helper to check if the user is a finance manager.
     * Redirects to login if not.
     */
    private function checkFinanceManagerAuth(): void {
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), ['finance_manager','admin'])) {
            $_SESSION['error_message'] = "Access denied. You must be logged in with appropriate permissions.";
            $_SESSION['redirect_after_login'] = 'index.php?page=finance_manager_dashboard';
            header('Location: index.php?page=login');
            exit();
        }
    }

    /**
     * Displays the Finance Manager dashboard.
     */
    public function dashboard(): void {
        $this->checkFinanceManagerAuth();

        // Get summary data for dashboard
        $totalPayments = $this->paymentModel->getTotalPaymentsAmount();
        $pendingBills = $this->billModel->getBillsByStatus('pending');
        $overdueBills = $this->billModel->getBillsByStatus('overdue');
        $recentTransactions = $this->paymentModel->getRecentPayments(10);
        
        $data = [
            'totalPayments' => $totalPayments,
            'pendingBillsCount' => count($pendingBills),
            'overdueBillsCount' => count($overdueBills),
            'recentTransactions' => $recentTransactions,
            'error' => $_SESSION['error_message'] ?? '',
            'success' => $_SESSION['success_message'] ?? ''
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']);

        // Pass auth object to the view before including any HTML
        $auth = $this->auth;
        require_once dirname(__DIR__) . '/views/finance_manager/dashboard.php';
    }

    /**
     * Manages all financial transactions.
     */
    public function manageTransactions(): void {
        $this->checkFinanceManagerAuth();

        $error = '';
        $success = '';
        $transactions = $this->paymentModel->getAllPayments();

        // Pass auth object to the view
        $auth = $this->auth;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            switch ($action) {
                case 'verify_transaction':
                    $paymentId = filter_var($_POST['payment_id'] ?? 0, FILTER_VALIDATE_INT);
                    $verificationStatus = $_POST['verification_status'] ?? '';
                    $verificationNotes = trim($_POST['verification_notes'] ?? '');

                    if ($paymentId <= 0 || empty($verificationStatus)) {
                        $error = "Invalid transaction data for verification.";
                    } else {
                        if ($this->paymentModel->verifyPayment($paymentId, $verificationStatus, $verificationNotes)) {
                            $success = "Transaction verified successfully!";
                            $transactions = $this->paymentModel->getAllPayments(); // Refresh the list
                        } else {
                            $error = "Failed to verify transaction.";
                        }
                    }
                    break;

                case 'flag_transaction':
                    $paymentId = filter_var($_POST['payment_id'] ?? 0, FILTER_VALIDATE_INT);
                    $flagReason = trim($_POST['flag_reason'] ?? '');

                    if ($paymentId <= 0 || empty($flagReason)) {
                        $error = "Please provide a reason for flagging the transaction.";
                    } else {
                        if ($this->paymentModel->flagPayment($paymentId, $flagReason)) {
                            $success = "Transaction flagged for review!";
                            $transactions = $this->paymentModel->getAllPayments(); // Refresh the list
                        } else {
                            $error = "Failed to flag transaction.";
                        }
                    }
                    break;
            }
        }

        $data = [
            'transactions' => $transactions,
            'error' => $error ?: ($_SESSION['error_message'] ?? ''),
            'success' => $success ?: ($_SESSION['success_message'] ?? '')
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']);

        // Pass auth object to the view
        $auth = $this->auth;
        $viewBase = dirname(__DIR__) . '/views/' . ($this->auth->getUserRole() === 'admin' ? 'admin' : 'finance_manager');
        require_once $viewBase . '/transactions.php';
    }

    /**
     * View details of a specific transaction
     */
    public function transactionDetails(): void {
        $this->checkFinanceManagerAuth();
        
        $error = '';
        $success = '';
        $paymentId = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        
        if ($paymentId <= 0) {
            $_SESSION['error_message'] = "Invalid transaction ID.";
            header('Location: index.php?page=finance_manager_transactions');
            exit();
        }
        
        $transaction = $this->paymentModel->getPaymentById($paymentId);
        
        if (!$transaction) {
            $_SESSION['error_message'] = "Transaction not found.";
            header('Location: index.php?page=finance_manager_transactions');
            exit();
        }
        
        // Pass auth object to the view
        $auth = $this->auth;
        
        // Get related bill information if available
        $billInfo = null;
        if (isset($transaction['bill_id']) && $transaction['bill_id'] > 0) {
            $billInfo = $this->billModel->getBillById($transaction['bill_id']);
        }
        
        // Get client information
        $clientInfo = null;
        if (isset($transaction['client_id']) && $transaction['client_id'] > 0) {
            $clientInfo = $this->clientModel->getClientById($transaction['client_id']);
        }
        
        $data = [
            'transaction' => $transaction,
            'billInfo' => $billInfo,
            'clientInfo' => $clientInfo,
            'error' => $error ?: ($_SESSION['error_message'] ?? ''),
            'success' => $success ?: ($_SESSION['success_message'] ?? '')
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']);
        
        // Auth object already passed to the view above
        $viewBase = dirname(__DIR__) . '/views/' . ($this->auth->getUserRole() === 'admin' ? 'admin' : 'finance_manager');
        require_once $viewBase . '/transaction_details.php';
    }

    /**
     * Verify transaction from details page
     */
    public function verifyTransaction(): void {
        $this->checkFinanceManagerAuth();
        $paymentId = filter_var($_POST['payment_id'] ?? 0, FILTER_VALIDATE_INT);
        $redirect = $_POST['redirect'] ?? '';
        $ajax = isset($_POST['ajax']);
        if ($paymentId <= 0) {
            $_SESSION['error_message'] = 'Invalid payment ID.';
            if ($ajax) { echo json_encode(['success' => false, 'message' => $_SESSION['error_message']]); exit(); }
            header('Location: index.php?page=finance_manager_transactions');
            exit();
        }
        $ok = $this->paymentModel->verifyPayment($paymentId, 'approved', 'verified by finance manager');
        if ($ok) {
            $_SESSION['success_message'] = 'Transaction confirmed and verified.';
        } else {
            $_SESSION['error_message'] = 'Failed to verify transaction.';
        }
        if ($ajax) {
            echo json_encode(['success' => (bool)$ok, 'message' => $ok ? $_SESSION['success_message'] : $_SESSION['error_message']]);
            exit();
        }
        if ($redirect === 'details') {
            header('Location: index.php?page=finance_manager_transaction_details&id=' . $paymentId);
        } else {
            header('Location: index.php?page=finance_manager_transactions');
        }
        exit();
    }

    public function verifyBill(): void {
        if (!$this->auth->isLoggedIn() || !in_array($this->auth->getUserRole(), ['finance_manager','admin'])) {
            $_SESSION['error_message'] = "Access denied. You must be logged in with appropriate permissions.";
            $_SESSION['redirect_after_login'] = 'index.php?page=billing_view_bills';
            header('Location: index.php?page=login');
            exit();
        }
        $billId = filter_var($_POST['bill_id'] ?? 0, FILTER_VALIDATE_INT);
        $ajax = isset($_POST['ajax']);
        if ($billId <= 0) {
            $_SESSION['error_message'] = 'Invalid bill ID.';
            if ($ajax) { echo json_encode(['success' => false, 'message' => $_SESSION['error_message']]); exit(); }
            header('Location: index.php?page=billing_view_bills');
            exit();
        }
        $bill = $this->billModel->getBillById($billId);
        if (!$bill) {
            $_SESSION['error_message'] = 'Bill not found.';
            if ($ajax) { echo json_encode(['success' => false, 'message' => $_SESSION['error_message']]); exit(); }
            header('Location: index.php?page=billing_view_bills');
            exit();
        }
        $amountDue = (float)($bill['amount_due'] ?? 0);
        $fullPaid = strtolower(trim((string)($_POST['full_paid'] ?? '')));
        $inputAmount = isset($_POST['paid_amount']) ? (float)$_POST['paid_amount'] : null;
        $useFull = ($fullPaid === 'yes' || $fullPaid === 'true' || $fullPaid === '1');
        $newPaid = $useFull ? $amountDue : max(0.0, min((float)$inputAmount, $amountDue));
        $newStatus = ($newPaid >= $amountDue) ? 'confirmed_and_verified' : 'partially_paid';
        $ok = $this->billModel->updateBillPayment($billId, $newPaid, $newStatus);
        if ($ok) {
            $clientUserId = 0;
            $clientRow = $this->clientModel->getClientById((int)($bill['client_id'] ?? 0));
            if ($clientRow) { $clientUserId = (int)($clientRow['user_id'] ?? 0); }
            if ($clientUserId > 0) {
                $cb = new ClientBill($this->db);
                $cb->updateStatusByBillId($billId, $clientUserId, 'confirmed_and_verified');
            }
            $recent = $this->paymentModel->getPaymentsByBill($billId);
            $pRow = is_array($recent) && count($recent) > 0 ? $recent[0] : null;
            $clientName = (string)($clientRow['full_name'] ?? ($clientRow['username'] ?? ''));
            if ($pRow && isset($pRow['id'])) {
                $amountToRecord = isset($pRow['amount']) ? (float)$pRow['amount'] : (float)$newPaid;
                $this->paymentModel->insertVerifiedPayment((int)$pRow['id'], $clientName, $amountToRecord, (string)($pRow['payment_date'] ?? date('Y-m-d H:i:s')), 'bill_payment');
            } else {
                // Fallback: insert using bill verification details (no payment row yet)
                // Use a synthetic payment_id based on bill to avoid collisions across different bills
                $syntheticId = (int)($billId * 100000 + time() % 100000);
                $this->paymentModel->insertVerifiedPayment($syntheticId, $clientName, (float)$newPaid, date('Y-m-d H:i:s'), 'bill_verification');
            }
            $_SESSION['success_message'] = 'Bill verified successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to verify bill.';
        }
            if ($ajax) {
                $balance = max(0.0, $amountDue - $newPaid);
                $label = ($newStatus === 'confirmed_and_verified') ? 'Confirmed & Verified' : 'Partially Paid';
                echo json_encode([
                    'success' => (bool)$ok,
                    'message' => $ok ? $_SESSION['success_message'] : $_SESSION['error_message'],
                    'paid' => $newPaid,
                    'balance' => $balance,
                    'status' => $newStatus,
                    'status_label' => $label,
                ]);
                exit();
            }
        header('Location: index.php?page=view_bill_details&bill_id=' . $billId);
        exit();
    }

    public function flagTransaction(): void {
        $this->checkFinanceManagerAuth();
        $paymentId = filter_var($_POST['payment_id'] ?? 0, FILTER_VALIDATE_INT);
        if ($paymentId <= 0) {
            $_SESSION['error_message'] = 'Invalid payment ID.';
            header('Location: index.php?page=finance_manager_transactions');
            exit();
        }
        if ($this->paymentModel->flagPayment($paymentId, 'flagged by finance manager')) {
            $_SESSION['success_message'] = 'Transaction flagged.';
        } else {
            $_SESSION['error_message'] = 'Failed to flag transaction.';
        }
        header('Location: index.php?page=finance_manager_transaction_details&id=' . $paymentId);
        exit();
    }

    public function unflagTransaction(): void {
        $this->checkFinanceManagerAuth();
        $paymentId = filter_var($_POST['payment_id'] ?? 0, FILTER_VALIDATE_INT);
        if ($paymentId <= 0) {
            $_SESSION['error_message'] = 'Invalid payment ID.';
            header('Location: index.php?page=finance_manager_transactions');
            exit();
        }
        if ($this->paymentModel->unflagPayment($paymentId)) {
            $_SESSION['success_message'] = 'Transaction unflagged.';
        } else {
            $_SESSION['error_message'] = 'Failed to unflag transaction.';
        }
        header('Location: index.php?page=finance_manager_transaction_details&id=' . $paymentId);
        exit();
    }

    public function addTransactionNote(): void {
        $this->checkFinanceManagerAuth();
        $paymentId = filter_var($_POST['payment_id'] ?? 0, FILTER_VALIDATE_INT);
        $notes = trim($_POST['notes'] ?? '');
        if ($paymentId <= 0) {
            $_SESSION['error_message'] = 'Invalid payment ID.';
            header('Location: index.php?page=finance_manager_transactions');
            exit();
        }
        if ($this->paymentModel->addNote($paymentId, $notes)) {
            $_SESSION['success_message'] = 'Notes saved.';
        } else {
            $_SESSION['error_message'] = 'Failed to save notes.';
        }
        header('Location: index.php?page=finance_manager_transaction_details&id=' . $paymentId);
        exit();
    }

    public function generateReceipt(): void {
        $this->checkFinanceManagerAuth();
        $paymentId = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if ($paymentId <= 0) {
            $_SESSION['error_message'] = 'Invalid payment ID.';
            header('Location: index.php?page=finance_manager_transactions');
            exit();
        }
        $txn = $this->paymentModel->getPaymentById($paymentId);
        if (!$txn) {
            $_SESSION['error_message'] = 'Transaction not found.';
            header('Location: index.php?page=finance_manager_transactions');
            exit();
        }
        header('Content-Type: text/html; charset=UTF-8');
        $statusLabel = (strtolower($txn['status']) === 'confirmed_and_verified') ? 'Confirmed & Verified' : ucfirst($txn['status']);
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Receipt #' . (int)$paymentId . '</title><style>
        body{font-family:Inter,Arial,sans-serif;background:#f5f7fb;color:#0f172a;margin:24px}
        .receipt{max-width:860px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden}
        .header{display:flex;justify-content:space-between;align-items:center;padding:20px 24px;background:#0ea5e9;color:#fff}
        .brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:20px}
        .section{padding:20px 24px}
        h3{margin:.2rem 0 .6rem 0;font-size:16px;color:#334155}
        table{width:100%;border-collapse:collapse}
        th,td{padding:10px 8px;border-bottom:1px solid #e5e7eb;text-align:left;font-size:14px}
        .badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:9999px;background:#ecfeff;color:#0ea5e9;font-weight:600}
        .footer{padding:16px 24px;color:#64748b;font-size:12px}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#0f172a;font-weight:600;cursor:pointer}
        .btn:hover{background:#f1f5f9}
        .floating-controls{position:fixed;top:16px;right:16px;z-index:9999;display:flex;gap:8px}
        @media print{body{margin:0}.floating-controls{display:none}}
        </style></head><body>';
        echo '<div class="floating-controls">'
            . '<button class="btn" onclick="window.print()">Print</button>'
            . '<button class="btn" onclick="downloadReceiptPdf()">Download PDF</button>'
            . '</div>';
        echo '<div class="receipt">';
        echo '<div class="header"><div class="brand"><span>💧 AquaBill</span></div><div>Official Receipt</div></div>';
        echo '<div class="section"><h3>Transaction</h3><table><tr><th>ID</th><th>Date</th><th>Amount</th><th>Status</th></tr>';
        echo '<tr><td>#' . (int)$txn['payment_id'] . '</td><td>' . htmlspecialchars($txn['payment_date']) . '</td><td>KES ' . number_format((float)$txn['amount'],2) . '</td><td><span class="badge">' . htmlspecialchars($statusLabel) . ' ✅</span></td></tr></table></div>';
        echo '<div class="section"><h3>Client</h3><table><tr><th>Name</th><th>Email</th><th>Client ID</th></tr>';
        echo '<tr><td>' . htmlspecialchars($txn['client_name'] ?? '') . '</td><td>' . htmlspecialchars($txn['client_email'] ?? '') . '</td><td>' . htmlspecialchars((string)$txn['client_id']) . '</td></tr></table></div>';
        if (!empty($txn['bill_id'])) {
            $bill = $this->billModel->getBillById((int)$txn['bill_id']);
            if ($bill) {
                echo '<div class="section"><h3>Bill</h3><table><tr><th>Bill ID</th><th>Status</th><th>Due</th><th>Paid</th></tr>';
                echo '<tr><td>#' . (int)$bill['id'] . '</td><td>' . htmlspecialchars(ucfirst($bill['status'] ?? $bill['payment_status'] ?? 'pending')) . '</td><td>KES ' . number_format((float)($bill['amount_due'] ?? 0),2) . '</td><td>KES ' . number_format((float)($bill['amount_paid'] ?? 0),2) . '</td></tr></table></div>';
            }
        }
        echo '<div class="footer">This is a system-generated receipt. Thank you for your payment.</div></div>';
        echo '<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>';
        echo '<script>function downloadReceiptPdf(){var el=document.querySelector(".receipt");if(!el){return;}html2canvas(el,{scale:2}).then(function(canvas){var imgData=canvas.toDataURL("image/png");var pdf=new window.jspdf.jsPDF("p","mm","a4");var pageW=pdf.internal.pageSize.getWidth();var pageH=pdf.internal.pageSize.getHeight();var margin=10;var imgW=pageW-margin*2;var imgH=canvas.height*imgW/canvas.width;var x=margin;var y=margin;if(imgH>pageH-margin*2){var scale=(pageH-margin*2)/imgH;imgW=imgW*scale;imgH=imgH*scale;x=(pageW-imgW)/2;y=margin;}pdf.addImage(imgData,"PNG",x,y,imgW,imgH);pdf.save("receipt-' . (int)$paymentId . '.pdf");});}</script>';
        echo '</body></html>';
        exit();
    }
    
    /**
     * Generates financial reports.
     */
    public function generateReports(): void {
        $this->checkFinanceManagerAuth();

        $error = '';
        $success = '';
        $reportData = null;
        $reportType = $_GET['report_type'] ?? '';
        $startDate = $_GET['start_date'] ?? ($_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days')));
        $endDate = $_GET['end_date'] ?? ($_GET['date_to'] ?? date('Y-m-d'));
        $sd = date('Y-m-d', strtotime($startDate));
        $ed = date('Y-m-d', strtotime($endDate));
        if ($sd > $ed) { $tmp = $sd; $sd = $ed; $ed = $tmp; }
        $startDate = $sd; $endDate = $ed;
        
        // Pass auth object to the view
        $auth = $this->auth;

        if ($reportType) {
            switch ($reportType) {
                case 'clients':
                    $rows = $this->paymentModel->getAllClientsPaymentReport($startDate, $endDate) ?? [];
                    $totalAmount = 0.0; $count = 0;
                    $table = [];
                    foreach ($rows as $r) {
                        $count++;
                        $totalAmount += (float)($r['amount'] ?? 0);
                        $table[] = [
                            'date' => $r['payment_date'] ?? '',
                            'client' => $r['client_name'] ?? ($r['client_email'] ?? ''),
                            'bill_id' => (int)($r['reference_id'] ?? 0),
                            'amount' => (float)($r['amount'] ?? 0),
                            'method' => $r['payment_method'] ?? '',
                            'reference' => $r['transaction_id'] ?? '',
                            'status' => $r['status'] ?? '',
                        ];
                    }
                    $reportData = [
                        'report' => true,
                        'report_title' => 'Client Payments',
                        'report_summary' => [ 'Payments Count' => $count, 'Total Amount' => $totalAmount ],
                        'report_columns' => ['Date','Client','Bill ID','Amount','Method','Reference','Status'],
                        'report_data' => $table,
                        'chart_type' => 'bar',
                        'chart_data' => [
                            'labels' => ['Total Amount'],
                            'dataset_label' => 'Payments',
                            'dataset_data' => [$totalAmount]
                        ]
                    ];
                    break;
                case 'revenue':
                    // Placeholder: aggregate from payments
                    $rows = $this->paymentModel->getAllClientsPaymentReport($startDate, $endDate) ?? [];
                    $sum = 0.0; foreach ($rows as $r) { $sum += (float)($r['amount'] ?? 0); }
                    $reportData = [
                        'report' => true,
                        'report_title' => 'Revenue Report',
                        'report_summary' => [ 'Total Revenue' => $sum, 'Period' => $startDate . ' to ' . $endDate ],
                        'report_columns' => ['Date','Client','Bill ID','Amount','Method','Reference','Status'],
                        'report_data' => array_map(function($r){ return [ 'date'=>$r['payment_date']??'', 'client'=>$r['client_name']??'', 'bill_id'=>(int)($r['reference_id']??0), 'amount'=>(float)($r['amount']??0), 'method'=>$r['payment_method']??'', 'reference'=>$r['transaction_id']??'', 'status'=>$r['status']??'' ]; }, $rows),
                        'chart_type' => 'line',
                        'chart_data' => [ 'labels' => array_map(function($r){ return substr((string)($r['payment_date']??''),0,10); }, $rows), 'dataset_label' => 'Revenue', 'dataset_data' => array_map(function($r){ return (float)($r['amount']??0); }, $rows) ]
                    ];
                    break;
                case 'flagged':
                    $rows = $this->paymentModel->getAllClientsPaymentReport($startDate, $endDate) ?? [];
                    $rows = array_filter($rows, function($r){ return strtolower((string)($r['status']??'')) === 'flagged'; });
                    $table = []; foreach ($rows as $r) { $table[] = [ 'date'=>$r['payment_date']??'', 'client'=>$r['client_name']??'', 'bill_id'=>(int)($r['reference_id']??0), 'amount'=>(float)($r['amount']??0), 'method'=>$r['payment_method']??'', 'reference'=>$r['transaction_id']??'', 'status'=>$r['status']??'' ]; }
                    $reportData = [ 'report' => true, 'report_title' => 'Flagged Transactions', 'report_summary' => [ 'Flagged Count' => count($table), 'Period' => $startDate . ' to ' . $endDate ], 'report_columns' => ['Date','Client','Bill ID','Amount','Method','Reference','Status'], 'report_data' => $table, 'chart_type' => 'bar', 'chart_data' => [ 'labels' => ['Flagged'], 'dataset_label' => 'Count', 'dataset_data' => [count($table)] ] ];
                    break;
                case 'bills':
                    $rows = $this->billModel->getAllBillsByDateRange($startDate, $endDate);
                    $reportData = [ 'report' => true, 'report_title' => 'Bills Report', 'report_summary' => [ 'Bills Count' => count($rows), 'Period' => $startDate . ' to ' . $endDate ], 'report_columns' => ['Bill ID','Client','Amount Due','Amount Paid','Status','Bill Date'], 'report_data' => array_map(function($b){ return [ 'bill_id'=>(int)($b['id']??0), 'client'=>($b['client_name']??($b['client_id']??'')), 'amount_due'=>(float)($b['amount_due']??0), 'amount_paid'=>(float)($b['amount_paid']??0), 'status'=>($b['status']??$b['payment_status']??''), 'bill_date'=>($b['bill_date']??'') ]; }, $rows), 'chart_type' => 'bar', 'chart_data' => [ 'labels' => ['Amount Due','Amount Paid'], 'dataset_label' => 'Bills', 'dataset_data' => [ array_sum(array_map(function($b){return (float)($b['amount_due']??0);}, $rows)), array_sum(array_map(function($b){return (float)($b['amount_paid']??0);}, $rows)) ] ] ];
                    break;
                default:
                    // Unknown type
                    $error = "Invalid report type selected.";
            }
        }

        $clients = $this->clientModel->getAllClients();

        // Suppress carry-over payment validation errors when success exists
        if (!empty($_SESSION['success_message'])) { unset($_SESSION['error_message']); }
        $sessionError = $_SESSION['error_message'] ?? '';
        if (stripos($sessionError, 'Payment amount cannot exceed the remaining balance') !== false || stripos($sessionError, 'No remaining balance to record') !== false || stripos($sessionError, 'Payment amount must be greater than zero') !== false) {
            unset($_SESSION['error_message']);
            $sessionError = '';
        }

        $data = [
            'reportType' => $reportType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'report' => (bool)($reportData && isset($reportData['report']) ? $reportData['report'] : false),
            'report_title' => $reportData['report_title'] ?? '',
            'report_summary' => $reportData['report_summary'] ?? [],
            'report_columns' => $reportData['report_columns'] ?? [],
            'report_data' => $reportData['report_data'] ?? [],
            'chart_type' => $reportData['chart_type'] ?? '',
            'chart_data' => $reportData['chart_data'] ?? [],
            'clients' => $clients,
            'error' => $error ?: $sessionError,
            'success' => $success ?: ($_SESSION['success_message'] ?? '')
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']);

        if ($this->auth->getUserRole() === 'admin') {
            require_once dirname(__DIR__) . '/views/admin/financial_reports.php';
        } else {
            require_once dirname(__DIR__) . '/views/finance_manager/reports.php';
        }
    }

    /**
     * Manages billing plans.
     */
    public function manageBillingPlans(): void {
        $this->checkFinanceManagerAuth();

        $error = '';
        $success = '';
        $billingPlans = $this->billingPlanModel->getAllPlans();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            switch ($action) {
                case 'add_plan':
                    $planName = trim($_POST['plan_name'] ?? '');
                    $description = trim($_POST['description'] ?? '');
                    $baseRate = filter_var($_POST['base_rate'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $unitRate = filter_var($_POST['unit_rate'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $minConsumption = filter_var($_POST['min_consumption'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $maxConsumption = isset($_POST['max_consumption']) && $_POST['max_consumption'] !== '' ? filter_var($_POST['max_consumption'], FILTER_VALIDATE_FLOAT) : null;
                    $billingCycle = trim($_POST['billing_cycle'] ?? '');
                    $isActive = isset($_POST['is_active']) ? 1 : 0;

                    $fixedServiceFee = filter_var($_POST['fixed_service_fee'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $sewerCharge = filter_var($_POST['sewer_charge'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $taxPercent = filter_var($_POST['tax_percent'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $taxInclusive = isset($_POST['tax_inclusive']) ? 1 : 0;
                    $tiersJson = $_POST['tiers_json'] ?? null;

                    if (empty($planName) || $baseRate === false || $unitRate === false || $minConsumption === false || empty($billingCycle)) {
                        $error = "Please fill all required fields for adding a billing plan.";
                    } else {
                        if ($this->billingPlanModel->addPlan($planName, $description, $baseRate, $unitRate, $minConsumption, $maxConsumption, $billingCycle, $isActive, $fixedServiceFee ?: 0, $sewerCharge ?: 0, $taxPercent ?: 0, $taxInclusive, $tiersJson)) {
                            $success = "Billing plan added successfully!";
                            $billingPlans = $this->billingPlanModel->getAllPlans(); // Refresh the list
                        } else {
                            $error = "Failed to add billing plan. It might already exist or there was a database error.";
                        }
                    }
                    break;

                case 'update_plan':
                    $planId = filter_var($_POST['plan_id'] ?? 0, FILTER_VALIDATE_INT);
                    $planName = trim($_POST['plan_name'] ?? '');
                    $description = trim($_POST['description'] ?? '');
                    $baseRate = filter_var($_POST['base_rate'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $unitRate = filter_var($_POST['unit_rate'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $minConsumption = filter_var($_POST['min_consumption'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $maxConsumption = isset($_POST['max_consumption']) && $_POST['max_consumption'] !== '' ? filter_var($_POST['max_consumption'], FILTER_VALIDATE_FLOAT) : null;
                    $billingCycle = trim($_POST['billing_cycle'] ?? '');
                    $isActive = isset($_POST['is_active']) ? 1 : 0;

                    $fixedServiceFee = filter_var($_POST['fixed_service_fee'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $sewerCharge = filter_var($_POST['sewer_charge'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $taxPercent = filter_var($_POST['tax_percent'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $taxInclusive = isset($_POST['tax_inclusive']) ? 1 : 0;
                    $tiersJson = $_POST['tiers_json'] ?? null;

                    if ($planId <= 0 || empty($planName) || $baseRate === false || $unitRate === false || $minConsumption === false || empty($billingCycle)) {
                        $error = "Invalid billing plan data for update.";
                    } else {
                        if ($this->billingPlanModel->updatePlan($planId, $planName, $description, $baseRate, $unitRate, $minConsumption, $maxConsumption, $billingCycle, $isActive, $fixedServiceFee ?: 0, $sewerCharge ?: 0, $taxPercent ?: 0, $taxInclusive, $tiersJson)) {
                            $success = "Billing plan updated successfully!";
                            $billingPlans = $this->billingPlanModel->getAllPlans(); // Refresh the list
                        } else {
                            $error = "Failed to update billing plan.";
                        }
                    }
                    break;

                case 'delete_plan':
                    $planId = filter_var($_POST['plan_id'] ?? 0, FILTER_VALIDATE_INT);

                    if ($planId <= 0) {
                        $error = "Invalid billing plan ID for deletion.";
                    } else {
                        if ($this->billingPlanModel->deletePlan($planId)) {
                            $success = "Billing plan deleted successfully!";
                            $billingPlans = $this->billingPlanModel->getAllPlans(); // Refresh the list
                        } else {
                            $error = "Failed to delete billing plan. It might be in use by clients.";
                        }
                    }
                    break;
            }
        }

        $data = [
            'billingPlans' => $billingPlans,
            'error' => $error ?: ($_SESSION['error_message'] ?? ''),
            'success' => $success ?: ($_SESSION['success_message'] ?? '')
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']);

        // Pass auth object to the view
        $auth = $this->auth;
        require_once dirname(__DIR__) . '/views/finance_manager/billing_plans.php';
    }

    /**
     * Manages user profile.
     */
    public function profile(): void {
        $this->checkFinanceManagerAuth();

        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            switch ($action) {
                case 'update_profile':
                    $fullName = trim($_POST['name'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $address = trim($_POST['address'] ?? '');
                    $contactPhone = trim($_POST['phone'] ?? '');

                    if (empty($fullName) || empty($email)) {
                        $error = "Full name and email are required.";
                    } else {
                        if ($this->userModel->updateUser($userId, $user['username'], $email, $user['role'], $fullName, $address, $contactPhone, $user['status'])) {
                            $success = "Profile updated successfully!";
                            $user = $this->userModel->getUserById($userId); // Refresh user data
                        } else {
                            $error = "Failed to update profile.";
                        }
                    }
                    break;

                case 'change_password':
                    $currentPassword = $_POST['current_password'] ?? '';
                    $newPassword = $_POST['new_password'] ?? '';
                    $confirmPassword = $_POST['confirm_password'] ?? '';

                    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                        $error = "All password fields are required.";
                    } elseif ($newPassword !== $confirmPassword) {
                        $error = "New password and confirmation do not match.";
                    } else {
                        if ($this->auth->changePassword($userId, $currentPassword, $newPassword)) {
                            $success = "Password changed successfully!";
                        } else {
                            $error = "Failed to change password. Current password may be incorrect.";
                        }
                    }
                    break;
            }
        }

        $data = [
            'user' => $user,
            'error' => $error ?: ($_SESSION['error_message'] ?? ''),
            'success' => $success ?: ($_SESSION['success_message'] ?? '')
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']);
        
        // Pass auth object to the view
        $auth = $this->auth;

        require_once dirname(__DIR__) . '/views/finance_manager/profile.php';
    }
}
?>
