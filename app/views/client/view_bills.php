<?php
// app/views/client/view_bills.php

// Ensure this page is only accessible to clients
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: index.php?page=login');
    exit;
}

// Extract data from controller
$bills = $data['bills'] ?? [];
$filters = $data['filters'] ?? [];
$error = $data['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bills - AquaBill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
        /* Sidebar Styling - Matched with client dashboard */
        .client-sidebar {
            width: 220px;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px 0;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        


        .client-sidebar h3 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 0 15px;
        }

        .client-sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .client-sidebar li {
            margin-bottom: 0;
        }

        .client-sidebar li a {
            display: block;
            padding: 10px 15px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-size: 0.9rem;
        }

        .client-sidebar li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid #fff;
        }

        .client-sidebar li a.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 3px solid #fff;
            font-weight: 500;
        }

        .client-sidebar li a i {
            margin-right: 8px;
            width: 18px;
            text-align: center;
            font-size: 0.9rem;
        }
        
        /* Main Content Styles */
        .main-content {
            margin-left: 220px;
            padding: 15px;
            transition: all 0.3s ease;
        }
        
        /* Header Bar Styles */
        .client-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .client-header-bar h1 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin: 0;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
        }

        .user-info a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 500;
        }

        .user-info a:hover {
            text-decoration: underline;
        }

        .client-sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.1rem;
            cursor: pointer;
            color: #2c3e50;
            display: none;
        }
        
        /* Card Animations */
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        
        .card-header {
            border-bottom: none;
            padding: 15px 20px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Button Styles */
        .btn {
            border-radius: 5px;
            padding: 8px 15px;
            transition: all 0.3s;
            border: none;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        
        /* Badge Styles */
        .badge {
            padding: 8px 12px;
            border-radius: 30px;
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        /* Table Styles */
        .table {
            border-collapse: separate;
            border-spacing: 0 5px;
        }
        
        .table th {
            border-top: none;
            border-bottom: 2px solid #e9ecef;
            padding: 12px;
            font-weight: 600;
        }
        
        .table td {
            padding: 12px;
            vertical-align: middle;
        }
        
        .table tr {
            transition: all 0.3s;
        }
        
        .table tr:hover {
            background-color: rgba(0,123,255,0.05);
        }
        
        /* Breadcrumb Styles */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
        }
        
        .breadcrumb-item a {
            color: #3498db;
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: #6c757d;
        }
        
        /* Animation Styles */
        .card {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        
        tbody tr {
            opacity: 0;
            transform: translateX(20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .client-sidebar {
                position: fixed;
                left: -220px;
                top: 0;
                bottom: 0;
                z-index: 1000;
            }
            
            .client-sidebar.active {
                left: 0;
            }
            
            .client-sidebar-toggle {
                display: block;
            }
            
            .client-header-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .user-info {
                width: 100%;
                justify-content: space-between;
            }
        }
        .preview-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(2px); display: none; align-items: center; justify-content: center; z-index: 2000; }
        .preview-modal { width: 80vw; max-width: 1000px; height: 85vh; background: #fff; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); display: flex; flex-direction: column; position: relative; }
        .preview-header { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid #e9ecef; }
        .preview-title { font-weight: 600; }
        .preview-close { background: none; border: none; font-size: 1.25rem; line-height: 1; cursor: pointer; color: #2c3e50; }
        .preview-content { flex: 1; }
        .preview-frame { width: 100%; height: 100%; border: none; }
        .blurred { filter: blur(4px); }
    </style>
</head>
<body>

<!-- Include Sidebar -->
<?php include_once dirname(__DIR__) . '/includes/client_sidebar.php'; ?>

<div class="main-content">
    <div class="client-header-bar">
        <button class="client-sidebar-toggle" id="clientSidebarToggle"><i class="fas fa-bars"></i></button>
        <h1>My Bills</h1>
        <div class="user-info">
            <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?page=client_billing_dashboard">Billing Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Bills</li>
        </ol>
    </nav>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #3498db, #2980b9);">
            <h5 class="mb-0">Filter Bills</h5>
        </div>
        <div class="card-body">
            <form method="get" action="">
                <input type="hidden" name="page" value="client_view_bills">
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" <?= ($filters['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="paid" <?= ($filters['status'] ?? '') == 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="partial" <?= ($filters['status'] ?? '') == 'partial' ? 'selected' : '' ?>>Partially Paid</option>
                            <option value="overdue" <?= ($filters['status'] ?? '') == 'overdue' ? 'selected' : '' ?>>Overdue</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="start_date" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?= $filters['start_date'] ?? '' ?>">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="end_date" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="<?= $filters['end_date'] ?? '' ?>">
                    </div>
                    
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn me-2" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
                            <i class="fas fa-filter me-2"></i> Filter
                        </button>
                        <a href="index.php?page=client_view_bills" class="btn" style="background: linear-gradient(135deg, #6c757d, #5a6268); color: white;">
                            <i class="fas fa-redo me-2"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #3498db, #2980b9);">
            <h5 class="mb-0">My Bills</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($bills)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Bill #</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Sent By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td><?= (int)$bill['bill_id'] ?></td>
                                <td>KES <?= number_format((float)$bill['bill_amount'], 2) ?></td>
                                <td>
                                    <?php 
                                        $isVerified = false; 
                                        try { 
                                            require_once dirname(__DIR__, 2) . '/models/Payment.php'; 
                                            $pm = new Payment(); 
                                            $pps = $pm->getPaymentsByBill((int)$bill['bill_id']); 
                                            foreach ($pps as $pv) { if (strtolower($pv['status'] ?? '') === 'confirmed_and_verified') { $isVerified = true; break; } }
                                        } catch (\Throwable $e) {}
                                    ?>
                                    <span class="badge" style="background: linear-gradient(135deg, #6c757d, #5a6268);">
                                        <?= htmlspecialchars(ucfirst(str_replace('_',' ', $bill['bill_status']))) ?>
                                        <?php if ($isVerified): ?><i class="fas fa-check-circle" style="color:#10b981;margin-left:6px;"></i><?php endif; ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($bill['sender_full_name'] ?? $bill['sender_username'] ?? 'N/A') ?></td>
                                <td>
                                    <?php $pdfUrl = 'index.php?page=client_download_bill&bill_id=' . (int)$bill['bill_id']; $imgUrl = 'bill_images/' . basename($bill['image_path'] ?? ''); ?>
                                    <a href="?page=client_bill_details&bill_id=<?= (int)$bill['bill_id'] ?>" class="btn btn-sm" style="background: linear-gradient(135deg, #17a2b8, #138496); color: white;" title="View Bill">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= htmlspecialchars($pdfUrl) ?>" class="btn btn-sm" style="background: linear-gradient(135deg, #6c757d, #5a6268); color: white;" title="Download Bill" target="_blank">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <?php $clientStatus = strtolower($bill['bill_status'] ?? ''); if (!in_array($clientStatus, ['paid','confirmed_and_verified'], true)): ?>
                                    <a href="index.php?page=client_payments&bill_id=<?= (int)$bill['bill_id'] ?>" class="btn btn-sm" style="background: linear-gradient(135deg, #28a745, #218838); color: white;" title="Pay Bill">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> No bills found matching your criteria.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="billPreviewOverlay" class="preview-overlay">
    <div class="preview-modal">
        <div class="preview-header">
            <div class="preview-title">Bill Preview</div>
            <div style="display:flex; gap:8px; align-items:center;">
                <button class="btn btn-sm" id="zoomOutBtn" title="Zoom Out" style="background:#e5e7eb; color:#111827;">-</button>
                <button class="btn btn-sm" id="zoomInBtn" title="Zoom In" style="background:#e5e7eb; color:#111827;">+</button>
                <button class="preview-close" onclick="closeBillPreview()" title="Close"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <div class="preview-content" id="imageViewerContainer" style="overflow:auto; display:flex; align-items:center; justify-content:center;">
            <img id="billPreviewImage" src="" alt="Bill Preview" style="max-width:100%; height:auto;" />
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('clientSidebarToggle');
        const sidebar = document.querySelector('.client-sidebar');
        const mainContent = document.querySelector('.main-content');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }
        
        // Add animation to elements when they come into view
        // Animate cards on page load
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100 * index);
        });
        
        // Animate table rows when they come into view
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach((row, index) => {
            setTimeout(() => {
                row.style.opacity = '1';
                row.style.transform = 'translateX(0)';
            }, 50 * index);
        });
    });
</script>
<script>
function deliverAndDownload(url, billId){
    window.location.href = url;
}
function deliverAndOpen(url, billId){
    window.location.href = url;
}
let pdfScale = 1.0;
function openBillPreview(){
    var overlay = document.getElementById('billPreviewOverlay');
    overlay.style.display = 'flex';
    var mc = document.querySelector('.main-content');
    if (mc) mc.classList.add('blurred');
    var sb = document.querySelector('.client-sidebar');
    if (sb) sb.classList.add('blurred');
}
function closeBillPreview(){
    var overlay = document.getElementById('billPreviewOverlay');
    var img = document.getElementById('billPreviewImage');
    img.src = '';
    overlay.style.display = 'none';
    var mc = document.querySelector('.main-content');
    if (mc) mc.classList.remove('blurred');
    var sb = document.querySelector('.client-sidebar');
    if (sb) sb.classList.remove('blurred');
}
function openPreviewFlow(url, billId){
    openBillPreview();
}
document.getElementById('billPreviewOverlay').addEventListener('click', function(e){ if (e.target === this) { closeBillPreview(); } });

let currentPdfUrl = '';
async function renderPdfFromUrl(url){
    const pdfjsLib = window['pdfjs-dist/build/pdf'];
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';
    const container = document.getElementById('pdfPages');
    container.innerHTML = '';
    currentPdfUrl = url;
    const loadingTask = pdfjsLib.getDocument(url);
    const pdf = await loadingTask.promise;
    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
        const page = await pdf.getPage(pageNum);
        const viewport = page.getViewport({ scale: pdfScale });
        const canvas = document.createElement('canvas');
        canvas.style.background = '#fff';
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        const context = canvas.getContext('2d');
        await page.render({ canvasContext: context, viewport }).promise;
        container.appendChild(canvas);
    }
}

function openPdfViewerFlow(url, billId){
    openBillPreview();
    pdfScale = 1.0;
    renderPdfFromUrl(url);
}

function openImagePreviewFlow(url, billId){
    openBillPreview();
    var img = document.getElementById('billPreviewImage');
    img.src = url;
}

async function openBillSnapshotFlow(billId, fallbackImgUrl){
    openBillPreview();
    const overlayImg = document.getElementById('billPreviewImage');
    if (fallbackImgUrl && fallbackImgUrl.trim() !== '') {
        overlayImg.src = fallbackImgUrl;
    }
    try {
        const iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.left = '-9999px';
        iframe.style.top = '0';
        iframe.style.width = '1024px';
        iframe.style.height = '1400px';
        iframe.style.opacity = '0';
        iframe.src = 'index.php?page=client_bill_details&bill_id=' + billId;
        document.body.appendChild(iframe);
        iframe.onload = async function(){
            try {
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                const target = doc.querySelector('.invoice');
                if (!target) { document.body.removeChild(iframe); return; }
                const canvas = await html2canvas(target, { scale: 2 });
                const dataUrl = canvas.toDataURL('image/png');
                overlayImg.src = dataUrl;
                try {
                    const base64 = dataUrl.split(',')[1];
                    const resp = await fetch('index.php?page=client_store_bill_image', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ bill_id: billId, image_base64: base64 })
                    });
                    const j = await resp.json();
                    if (j && j.path) { overlayImg.src = j.path; }
                } catch(e) {}
            } catch(e) {
            } finally {
                document.body.removeChild(iframe);
            }
        };
    } catch(e){
    }
}

document.getElementById('zoomInBtn').addEventListener('click', function(){
    var img = document.getElementById('billPreviewImage');
    if (!img) return;
    const s = parseFloat(img.getAttribute('data-scale') || '1');
    const ns = Math.min(s + 0.1, 2.5);
    img.style.transform = 'scale(' + ns + ')';
    img.style.transformOrigin = 'top center';
    img.setAttribute('data-scale', ns.toString());
});
document.getElementById('zoomOutBtn').addEventListener('click', function(){
    var img = document.getElementById('billPreviewImage');
    if (!img) return;
    const s = parseFloat(img.getAttribute('data-scale') || '1');
    const ns = Math.max(s - 0.1, 0.5);
    img.style.transform = 'scale(' + ns + ')';
    img.style.transformOrigin = 'top center';
    img.setAttribute('data-scale', ns.toString());
});
</script>
</body>
</html>
