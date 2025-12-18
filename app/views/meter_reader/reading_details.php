<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reading Details</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
            padding: 1.5rem;
        }
        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-body {
            padding: 1.5rem;
        }
        .form-control {
            display: block;
            width: 100%;
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 400;
            line-height: 1.5;
            color: #1f2937;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-control:focus {
            border-color: #93c5fd;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background-color: #2563eb;
            color: white;
            border: 1px solid #2563eb;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        .btn-success {
            background-color: #10b981;
            color: white;
            border: 1px solid #10b981;
        }
        .btn-success:hover {
            background-color: #059669;
            border-color: #059669;
        }
        .btn-danger {
            background-color: #ef4444;
            color: white;
            border: 1px solid #ef4444;
        }
        .btn-danger:hover {
            background-color: #dc2626;
            border-color: #dc2626;
        }
        .btn-outline {
            background-color: transparent;
            border: 1px solid #d1d5db;
            color: #4b5563;
        }
        .btn-outline:hover {
            background-color: #f3f4f6;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        .badge-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        .badge-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        .badge-info {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        .timeline {
            position: relative;
            padding-left: 3rem;
        }
        .timeline:before {
            content: '';
            position: absolute;
            left: 0.75rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #e5e7eb;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-marker {
            position: absolute;
            left: -2.25rem;
            top: 0;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background-color: #3b82f6;
            border: 2px solid white;
        }
        .timeline-content {
            position: relative;
        }
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 0.5rem;
        }
        .image-gallery img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .image-gallery img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.8);
        }
        .modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90%;
        }
        .modal-close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include_once __DIR__ . '/sidebar.php'; ?>

        <div class="main-content">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Reading Details</h1>
                    <p class="text-gray-600">Detailed information about meter reading #<?= htmlspecialchars($data['reading']['id'] ?? 'N/A') ?></p>
                </div>
                <div>
                    <a href="index.php?page=meter_reader_my_records" class="btn btn-outline">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Records
                    </a>
                </div>
            </div>

            <?php if (empty($data['reading'])): ?>
                <div class="card">
                    <div class="card-body text-center py-8">
                        <i class="fas fa-exclamation-circle text-yellow-500 text-5xl mb-4"></i>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Reading Not Found</h2>
                        <p class="text-gray-600">The requested meter reading could not be found.</p>
                        <a href="index.php?page=meter_reader_my_records" class="btn btn-primary mt-4">
                            View All Records
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Reading Status Banner -->
                <?php 
                $statusClass = '';
                $statusBg = '';
                $statusIcon = '';
                
                switch ($data['reading']['status']) {
                    case 'verified':
                        $statusClass = 'text-green-800';
                        $statusBg = 'bg-green-50';
                        $statusIcon = 'check-circle';
                        break;
                    case 'pending':
                        $statusClass = 'text-yellow-800';
                        $statusBg = 'bg-yellow-50';
                        $statusIcon = 'clock';
                        break;
                    case 'flagged':
                        $statusClass = 'text-red-800';
                        $statusBg = 'bg-red-50';
                        $statusIcon = 'exclamation-circle';
                        break;
                    default:
                        $statusClass = 'text-blue-800';
                        $statusBg = 'bg-blue-50';
                        $statusIcon = 'info-circle';
                }
                ?>
                
                <div class="<?= $statusBg ?> p-4 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-<?= $statusIcon ?> <?= $statusClass ?> text-xl mr-3"></i>
                    <div>
                        <h2 class="text-md font-semibold <?= $statusClass ?>">Reading Status: <?= htmlspecialchars(ucfirst($data['reading']['status'])) ?></h2>
                        <p class="text-sm <?= $statusClass ?> opacity-80">
                            <?php 
                            switch ($data['reading']['status']) {
                                case 'verified':
                                    echo 'This reading has been verified and processed successfully.';
                                    break;
                                case 'pending':
                                    echo 'This reading is pending verification by a supervisor.';
                                    break;
                                case 'flagged':
                                    echo 'This reading has been flagged for review due to potential issues.';
                                    break;
                                default:
                                    echo 'Current status of this meter reading.';
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Reading Information -->
                    <div class="md:col-span-2">
                        <div class="card">
                            <div class="card-header">
                                <h2 class="text-lg font-semibold text-gray-800">Reading Information</h2>
                                <div class="flex space-x-2">
                                    <?php if ($data['reading']['status'] === 'pending'): ?>
                                        <button class="btn btn-outline text-xs" id="edit-reading-btn">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline text-xs" id="print-reading-btn">
                                        <i class="fas fa-print mr-1"></i> Print
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500 mb-2">Meter Details</h3>
                                        <div class="space-y-3">
                                            <div>
                                                <p class="text-xs text-gray-500">Meter Serial Number</p>
                                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($data['reading']['meter_serial_number'] ?? 'N/A') ?></p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Meter Type</p>
                                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars(ucfirst($data['reading']['meter_type'] ?? 'N/A')) ?></p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Client</p>
                                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($data['reading']['client_name'] ?? $data['reading']['client_username'] ?? 'N/A') ?></p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Location</p>
                                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($data['reading']['location'] ?? 'N/A') ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500 mb-2">Reading Details</h3>
                                        <div class="space-y-3">
                                            <div>
                                                <p class="text-xs text-gray-500">Reading Value</p>
                                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($data['reading']['reading_value'] ?? 'N/A') ?></p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Reading Date</p>
                                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars(date('F d, Y', strtotime($data['reading']['reading_date'] ?? 'now'))) ?></p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Consumption</p>
                                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($data['reading']['consumption'] ?? 'N/A') ?> units</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Recorded By</p>
                                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($data['reading']['recorder_name'] ?? 'You') ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-6">
                                    <h3 class="text-sm font-medium text-gray-500 mb-2">Notes</h3>
                                    <div class="bg-gray-50 p-3 rounded-lg">
                                        <?php if (!empty($data['reading']['notes'])): ?>
                                            <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($data['reading']['notes'])) ?></p>
                                        <?php else: ?>
                                            <p class="text-sm text-gray-500 italic">No notes provided for this reading.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($data['reading']['meter_condition'])): ?>
                                    <div class="mt-6">
                                        <h3 class="text-sm font-medium text-gray-500 mb-2">Meter Condition</h3>
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <p class="text-sm text-gray-700"><?= htmlspecialchars(ucfirst($data['reading']['meter_condition'])) ?></p>
                                            <?php if (!empty($data['reading']['condition_notes'])): ?>
                                                <p class="text-sm text-gray-700 mt-2"><?= nl2br(htmlspecialchars($data['reading']['condition_notes'])) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reading Photos -->
                    <div>
                        <div class="card">
                            <div class="card-header">
                                <h2 class="text-lg font-semibold text-gray-800">Reading Photos</h2>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($data['reading']['photos'])): ?>
                                    <div class="image-gallery">
                                        <?php foreach ($data['reading']['photos'] as $index => $photo): ?>
                                            <img src="<?= htmlspecialchars($photo['url']) ?>" alt="Reading Photo <?= $index + 1 ?>" class="gallery-img" onclick="openModal(this.src)">
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-6">
                                        <i class="fas fa-camera text-gray-300 text-4xl mb-3"></i>
                                        <p class="text-gray-500">No photos available for this reading</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Previous Reading Comparison -->
                        <div class="card mt-6">
                            <div class="card-header">
                                <h2 class="text-lg font-semibold text-gray-800">Previous Reading</h2>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($data['previous_reading'])): ?>
                                    <div class="space-y-3">
                                        <div>
                                            <p class="text-xs text-gray-500">Reading Value</p>
                                            <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($data['previous_reading']['reading_value']) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Reading Date</p>
                                            <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars(date('F d, Y', strtotime($data['previous_reading']['reading_date']))) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Difference</p>
                                            <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($data['reading']['reading_value'] - $data['previous_reading']['reading_value']) ?> units</p>
                                        </div>
                                        <div class="pt-2">
                                            <a href="index.php?page=meter_reader_reading_details&reading_id=<?= $data['previous_reading']['id'] ?>" class="btn btn-outline text-xs w-full">
                                                <i class="fas fa-eye mr-1"></i> View Previous Reading
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <p class="text-gray-500">No previous reading available</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reading History Timeline -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold text-gray-800">Reading History</h2>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php if (!empty($data['reading_history'])): ?>
                                <?php foreach ($data['reading_history'] as $history): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <h3 class="text-sm font-medium text-gray-900"><?= htmlspecialchars($history['action']) ?></h3>
                                            <p class="text-xs text-gray-500"><?= htmlspecialchars(date('F d, Y H:i', strtotime($history['timestamp']))) ?></p>
                                            <p class="text-sm text-gray-700 mt-1"><?= htmlspecialchars($history['description']) ?></p>
                                            <p class="text-xs text-gray-500 mt-1">By: <?= htmlspecialchars($history['user_name']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <p class="text-gray-500">No history available for this reading</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Actions Section -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-semibold text-gray-800">Actions</h2>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php if ($data['reading']['status'] === 'pending'): ?>
                                <button class="btn btn-danger" id="flag-reading-btn">
                                    <i class="fas fa-flag mr-1"></i> Flag for Review
                                </button>
                            <?php elseif ($data['reading']['status'] === 'flagged'): ?>
                                <button class="btn btn-outline" id="unflag-reading-btn">
                                    <i class="fas fa-flag-checkered mr-1"></i> Remove Flag
                                </button>
                            <?php endif; ?>
                            
                            <button class="btn btn-outline" id="add-note-btn">
                                <i class="fas fa-sticky-note mr-1"></i> Add Note
                            </button>
                            
                            <a href="index.php?page=meter_reader_record_reading&meter_id=<?= $data['reading']['meter_id'] ?>" class="btn btn-primary">
                                <i class="fas fa-plus-circle mr-1"></i> Record New Reading
                            </a>
                            
                            <a href="index.php?page=meter_reader_meter_history&meter_id=<?= $data['reading']['meter_id'] ?>" class="btn btn-outline">
                                <i class="fas fa-history mr-1"></i> View Meter History
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Image Modal -->
                <div id="image-modal" class="modal">
                    <span class="modal-close" onclick="closeModal()">&times;</span>
                    <img class="modal-content" id="modal-img">
                </div>
                
                <!-- Flag Reading Modal -->
                <div id="flag-modal" class="modal">
                    <div class="relative bg-white rounded-lg mx-auto mt-20 max-w-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Flag Reading for Review</h3>
                            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('flag-modal')">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form id="flag-form">
                            <div class="mb-4">
                                <label for="flag-reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Flagging</label>
                                <select id="flag-reason" class="form-control">
                                    <option value="">Select a reason...</option>
                                    <option value="unusual_consumption">Unusual Consumption</option>
                                    <option value="meter_issue">Potential Meter Issue</option>
                                    <option value="reading_error">Possible Reading Error</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="flag-notes" class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                                <textarea id="flag-notes" class="form-control" rows="3" placeholder="Provide details about the issue..."></textarea>
                            </div>
                            <div class="flex justify-end space-x-2">
                                <button type="button" class="btn btn-outline" onclick="closeModal('flag-modal')">Cancel</button>
                                <button type="submit" class="btn btn-danger">Flag Reading</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Add Note Modal -->
                <div id="note-modal" class="modal">
                    <div class="relative bg-white rounded-lg mx-auto mt-20 max-w-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Add Note to Reading</h3>
                            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('note-modal')">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form id="note-form">
                            <div class="mb-4">
                                <label for="note-text" class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                                <textarea id="note-text" class="form-control" rows="4" placeholder="Enter your note here..."></textarea>
                            </div>
                            <div class="flex justify-end space-x-2">
                                <button type="button" class="btn btn-outline" onclick="closeModal('note-modal')">Cancel</button>
                                <button type="submit" class="btn btn-primary">Add Note</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Edit Reading Modal -->
                <div id="edit-modal" class="modal">
                    <div class="relative bg-white rounded-lg mx-auto mt-20 max-w-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Edit Reading</h3>
                            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('edit-modal')">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form id="edit-form">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="edit-reading-value" class="block text-sm font-medium text-gray-700 mb-1">Reading Value</label>
                                    <input type="number" id="edit-reading-value" class="form-control" value="<?= htmlspecialchars($data['reading']['reading_value'] ?? '') ?>">
                                </div>
                                <div>
                                    <label for="edit-reading-date" class="block text-sm font-medium text-gray-700 mb-1">Reading Date</label>
                                    <input type="date" id="edit-reading-date" class="form-control" value="<?= htmlspecialchars(date('Y-m-d', strtotime($data['reading']['reading_date'] ?? 'now'))) ?>">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="edit-meter-condition" class="block text-sm font-medium text-gray-700 mb-1">Meter Condition</label>
                                <select id="edit-meter-condition" class="form-control">
                                    <option value="normal" <?= ($data['reading']['meter_condition'] ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
                                    <option value="damaged" <?= ($data['reading']['meter_condition'] ?? '') === 'damaged' ? 'selected' : '' ?>>Damaged</option>
                                    <option value="leaking" <?= ($data['reading']['meter_condition'] ?? '') === 'leaking' ? 'selected' : '' ?>>Leaking</option>
                                    <option value="obstructed" <?= ($data['reading']['meter_condition'] ?? '') === 'obstructed' ? 'selected' : '' ?>>Obstructed</option>
                                    <option value="other" <?= ($data['reading']['meter_condition'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="edit-notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea id="edit-notes" class="form-control" rows="3"><?= htmlspecialchars($data['reading']['notes'] ?? '') ?></textarea>
                            </div>
                            <div class="flex justify-end space-x-2">
                                <button type="button" class="btn btn-outline" onclick="closeModal('edit-modal')">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Image modal functionality
        function openModal(imgSrc) {
            const modal = document.getElementById('image-modal');
            const modalImg = document.getElementById('modal-img');
            modal.style.display = 'block';
            modalImg.src = imgSrc;
        }
        
        function closeModal(modalId = 'image-modal') {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Flag reading button
        const flagBtn = document.getElementById('flag-reading-btn');
        if (flagBtn) {
            flagBtn.addEventListener('click', function() {
                document.getElementById('flag-modal').style.display = 'block';
            });
        }
        
        // Unflag reading button
        const unflagBtn = document.getElementById('unflag-reading-btn');
        if (unflagBtn) {
            unflagBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to remove the flag from this reading?')) {
                    // In a real implementation, this would submit to the server
                    alert('Flag removed successfully!');
                    window.location.reload();
                }
            });
        }
        
        // Add note button
        const addNoteBtn = document.getElementById('add-note-btn');
        if (addNoteBtn) {
            addNoteBtn.addEventListener('click', function() {
                document.getElementById('note-modal').style.display = 'block';
            });
        }
        
        // Edit reading button
        const editReadingBtn = document.getElementById('edit-reading-btn');
        if (editReadingBtn) {
            editReadingBtn.addEventListener('click', function() {
                document.getElementById('edit-modal').style.display = 'block';
            });
        }
        
        // Print reading button
        const printReadingBtn = document.getElementById('print-reading-btn');
        if (printReadingBtn) {
            printReadingBtn.addEventListener('click', function() {
                window.print();
            });
        }
        
        // Form submissions
        const flagForm = document.getElementById('flag-form');
        if (flagForm) {
            flagForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const reason = document.getElementById('flag-reason').value;
                const notes = document.getElementById('flag-notes').value;
                
                if (!reason) {
                    alert('Please select a reason for flagging.');
                    return;
                }
                
                // In a real implementation, this would submit to the server
                alert('Reading flagged successfully!');
                closeModal('flag-modal');
                window.location.reload();
            });
        }
        
        const noteForm = document.getElementById('note-form');
        if (noteForm) {
            noteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const note = document.getElementById('note-text').value;
                
                if (!note.trim()) {
                    alert('Please enter a note.');
                    return;
                }
                
                // In a real implementation, this would submit to the server
                alert('Note added successfully!');
                closeModal('note-modal');
                window.location.reload();
            });
        }
        
        const editForm = document.getElementById('edit-form');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const readingValue = document.getElementById('edit-reading-value').value;
                const readingDate = document.getElementById('edit-reading-date').value;
                
                if (!readingValue || !readingDate) {
                    alert('Please fill in all required fields.');
                    return;
                }
                
                // In a real implementation, this would submit to the server
                alert('Reading updated successfully!');
                closeModal('edit-modal');
                window.location.reload();
            });
        }
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const imageModal = document.getElementById('image-modal');
            if (event.target === imageModal) {
                closeModal();
            }
            
            const flagModal = document.getElementById('flag-modal');
            if (event.target === flagModal) {
                closeModal('flag-modal');
            }
            
            const noteModal = document.getElementById('note-modal');
            if (event.target === noteModal) {
                closeModal('note-modal');
            }
            
            const editModal = document.getElementById('edit-modal');
            if (event.target === editModal) {
                closeModal('edit-modal');
            }
        });
    </script>
</body>
</html>