<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - Client Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* General Styling - Matched with apply_service.php */
        .client-theme {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .client-dashboard-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling - Exactly matched with apply_service.php */
        .client-sidebar {
            width: 220px;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 20px 0;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
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

        /* Main Content Styling - Matched with apply_service.php */
        .client-main-content {
            flex: 1;
            transition: all 0.3s ease;
            padding: 15px;
        }

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

        /* Content Sections - Matched with apply_service.php */
        .client-content-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .client-content-section, .client-form-section {
            background: white;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .client-content-section h3, .client-form-section h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #2c3e50;
            font-size: 1.1rem;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Grid Layout - Matched with apply_service.php */
        .client-grid {
            display: grid;
            gap: 15px;
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        /* Form Styling - Matched with apply_service.php */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
            font-size: 0.85rem;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        /* Button Styling - Matched with apply_service.php */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 0.85rem;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        /* Alert Styling - Matched with apply_service.php */
        .alert {
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-info {
            background-color: #e3f2fd;
            color: #1976d2;
            border-left: 4px solid #1976d2;
        }

        /* Review Card Styling */
        .review-card {
            background: white;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-left: 3px solid #3498db;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .review-author {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        .review-rating {
            color: #f39c12;
            font-size: 0.9rem;
        }

        .review-title {
            font-weight: 600;
            margin-bottom: 8px;
            color: #3498db;
            font-size: 0.95rem;
        }

        .review-text {
            color: #555;
            margin-bottom: 8px;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        .review-date {
            font-size: 0.75rem;
            color: #95a5a6;
        }

        /* Star Rating */
        .rating-stars {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }

        .rating-stars input {
            display: none;
        }

        .rating-stars label {
            color: #ddd;
            font-size: 1.2rem;
            padding: 0 3px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .rating-stars input:checked ~ label,
        .rating-stars input:hover ~ label,
        .rating-stars label:hover,
        .rating-stars label:hover ~ label {
            color: #f39c12;
        }

        /* Responsive Design - Matched with apply_service.php */
        @media (max-width: 992px) {
            .grid-cols-2 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .client-sidebar {
                position: fixed;
                left: -220px;
                top: 0;
                bottom: 0;
                z-index: 1000;
            }
            
            .client-sidebar.visible {
                left: 0;
            }
            
            .client-main-content {
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .client-main-content.full-width {
                margin-left: 0;
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
    </style>
    <style>
        .loader-overlay{position:fixed;inset:0;z-index:2000;background:linear-gradient(120deg,rgba(255,255,255,.8),rgba(255,255,255,.6));backdrop-filter:saturate(180%) blur(8px);display:flex;align-items:center;justify-content:center;transition:opacity .4s ease}
        .loader-overlay.loader-hidden{opacity:0;pointer-events:none}
        .spinner{position:relative;width:10em;height:10em}
        .spinner:before{transform:rotateX(60deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateBefore infinite linear reverse}
        .spinner:after{transform:rotateX(240deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateAfter infinite linear}
        .spinner:before,.spinner:after{top:50%;left:50%;}
        .spinner:before,.spinner:after{box-sizing:border-box;content:'';display:block;position:absolute;margin-top:-5em;margin-left:-5em;width:10em;height:10em;transform-style:preserve-3d;transform-origin:50%;perspective-origin:50% 50%;perspective:340px;background-size:10em 10em;background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjI2NnB4IiBoZWlnaHQ9IjI5N3B4IiB2aWV3Qm94PSIwIDAgMjY2IDI5NyIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWxuczpza2V0Y2g9Imh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaC9ucyI+CiAgICA8dGl0bGU+c3Bpbm5lcjwvdGl0bGU+CiAgICA8ZGVzY3JpcHRpb24+Q3JlYXRlZCB3aXRoIFNrZXRjaCAoaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoKTwvZGVzY3JpcHRpb24+CiAgICA8ZGVmcz48L2RlZnM+CiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBza2V0Y2g6dHlwZT0iTVNQYWdlIj4KICAgICAgICA8cGF0aCBkPSJNMTcxLjUwNzgxMywzLjI1MDAwMDM4IEMyMjYuMjA4MTgzLDEyLjg1NzcxMTEgMjk3LjExMjcyMiw3MS40OTEyODIzIDI1MC44OTU1OTksMTA4LjQxMDE1NSBDMjE2LjU4MjAyNCwxMzUuODIwMzEgMTg2LjUyODQwNSw5Ny4wNjI0OTY0IDE1Ni44MDA3NzQsODUuNzczNDM0NiBDMTI3LjA3MzE0Myw3NC40ODQzNzIxIDc2Ljg4ODQ2MzIsODQuMjE2MTQ2MiA2MC4xMjg5MDY1LDEwOC40MTAxNTMgQy0xNS45ODA0Njg1LDIxOC4yODEyNDcgMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IDE0NS4yNzczNDQsMjk2LjY2Nzk2OCBDMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IC0yNS40NDkyMTg3LDI1Ny4yNDIxOTggMy4zOTg0Mzc1LDEwOC40MTAxNTUgQzE2LjMwNzA2NjEsNDEuODExNDE3NCA4NC43Mjc1ODI5LC0xMS45OTIyOTg1IDE3MS41MDc4MTMsMy4yNTAwMDAzOCBaIiBpZD0iUGF0aC0xIiBmaWxsPSIjMDAwMDAwIiBza2V0Y2g6dHlwZT0iTVNTaGFwZUdyb3VwIj48L3BhdGg+CiAgICA8L2c+Cjwvc3ZnPg==)}
        @keyframes rotateBefore{from{transform:rotateX(60deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(60deg) rotateY(45deg) rotateZ(-360deg)}}
        @keyframes rotateAfter{from{transform:rotateX(240deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(240deg) rotateY(45deg) rotateZ(360deg)}}
    </style>
    <script>window.addEventListener('load',()=>{const l=document.getElementById('loader');if(l){setTimeout(()=>{l.classList.add('loader-hidden');setTimeout(()=>{try{l.remove()}catch(e){}},600);},1500)}});</script>
</head>
<body class="client-theme">
    <div id='loader' class='loader-overlay'><div class='spinner'></div></div>
    <div class="client-dashboard-layout">
        <!-- Sidebar Navigation -->
        <?php include_once dirname(__DIR__) . '/includes/client_sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="client-main-content" id="clientMainContent">
            <!-- Header Bar -->
            <div class="client-header-bar">
                <button class="client-sidebar-toggle" id="clientSidebarToggle"><i class="fas fa-bars"></i></button>
                <h1><i class="fas fa-star"></i> Customer Reviews</h1>
                <div class="user-info">
                    <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <!-- Page Content -->
            <div class="client-content-container">
                <div class="client-grid grid-cols-2">
                    <!-- Leave a Review -->
                    <div class="client-form-section">
                        <h3><i class="fas fa-pen"></i> Share Your Feedback</h3>
                        <?php if (isset($data['message']) && $data['message'] != ''): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($data['message']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="index.php?page=client_reviews" method="POST">
                            <input type="hidden" name="action" value="submit_review">
                            
                            <div class="form-group">
                                <label>Rating</label>
                                <div class="rating-stars">
                                    <input type="radio" id="star5" name="rating" value="5">
                                    <label for="star5"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star4" name="rating" value="4">
                                    <label for="star4"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star3" name="rating" value="3">
                                    <label for="star3"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star2" name="rating" value="2">
                                    <label for="star2"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star1" name="rating" value="1">
                                    <label for="star1"><i class="fas fa-star"></i></label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="review_title">Review Title</label>
                                <input type="text" class="form-control" id="review_title" name="review_title" placeholder="Brief summary of your review" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="review_text">Your Review</label>
                                <textarea class="form-control" id="review_text" name="review_text" rows="5" placeholder="Share your experience with our services" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane"></i> Submit Review
                            </button>
                        </form>
                    </div>

                    <!-- Recent Reviews -->
                    <div class="client-content-section">
                        <h3><i class="fas fa-comments"></i> Recent Feedback</h3>
                        <?php if (!empty($data['reviews'])): ?>
                            <?php foreach ($data['reviews'] as $review): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="review-author"><?php echo htmlspecialchars($review['author']); ?></div>
                                        <div class="review-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $review['rating']): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php elseif ($i - 0.5 <= $review['rating']): ?>
                                                    <i class="fas fa-star-half-alt"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="review-title"><?php echo htmlspecialchars($review['title']); ?></div>
                                    <div class="review-text">
                                        <?php echo htmlspecialchars($review['text']); ?>
                                    </div>
                                    <div class="review-date">Posted on <?php echo date('M d, Y', strtotime($review['date'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No reviews available yet. Be the first to leave a review!
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const clientSidebarToggle = document.getElementById('clientSidebarToggle');
            const clientSidebar = document.getElementById('clientSidebar');
            const clientMainContent = document.getElementById('clientMainContent');

            // Toggle sidebar visibility
            clientSidebarToggle.addEventListener('click', function() {
                clientSidebar.classList.toggle('visible');
                clientMainContent.classList.toggle('full-width');
            });

            // Hide sidebar on larger screens if it was toggled on mobile and then resized
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    clientSidebar.classList.remove('visible');
                    clientMainContent.classList.remove('full-width');
                }
            });

            // Highlight active navigation link
            const currentPath = window.location.search;
            const navLinks = clientSidebar.querySelectorAll('a');
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.href.includes(currentPath) && currentPath !== '') {
                    link.classList.add('active');
                } else if (currentPath === '' && link.href.includes('client_dashboard')) {
                    link.classList.add('active');
                }
            });

            // Star rating interaction
            const stars = document.querySelectorAll('.rating-stars input');
            stars.forEach(star => {
                star.addEventListener('change', function() {
                    const rating = this.value;
                    // You can add any additional logic here when a star is selected
                });
            });
        });
    </script>
</body>
</html>
