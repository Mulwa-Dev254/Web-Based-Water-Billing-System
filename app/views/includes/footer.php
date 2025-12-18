<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            // Sidebar toggle for smaller screens
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('visible');
                });
            }

            // Auto-hide sidebar on mobile when clicking outside
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 992 && 
                    sidebar && !sidebar.contains(e.target) && 
                    sidebarToggle && e.target !== sidebarToggle && 
                    !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('visible');
                }
            });

            // Adjust sidebar visibility on resize for desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992 && sidebar && mainContent) {
                    sidebar.classList.remove('visible');
                    mainContent.classList.remove('full-width'); 
                }
            });

            // Highlight active navigation link
            if (sidebar) {
                const currentPath = window.location.search;
                const navLinks = sidebar.querySelectorAll('.sidebar-nav a');
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.href.includes(currentPath)) {
                        link.classList.add('active');
                    } else if (currentPath === '' && link.href.includes('admin_dashboard')) {
                        link.classList.add('active');
                    }
                });
            }
        });
    </script>
</body>
</html>