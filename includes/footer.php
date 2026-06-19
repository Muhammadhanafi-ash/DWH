</div> <!-- Close .page-container -->
        
            <!-- Footer Text -->
            <footer class="py-3 px-4 border-top mt-auto" style="background-color: var(--bg-card); border-color: var(--border-color) !important; font-size: 0.8rem; color: var(--text-secondary); transition: var(--transition);">
                <div class="d-flex justify-content-between align-items-center flex-column flex-sm-row">
                    <div>
                        &copy; 2026 <strong>Enterprise Data Warehouse Portal</strong>. All rights reserved.
                    </div>
                    <div class="mt-2 mt-sm-0">
                        BI Visualizations & Analytics Server
                    </div>
                </div>
            </footer>

        </main> <!-- Close #main-content -->
    </div> <!-- Close #app-container -->

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables Core & Extensions JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

    <!-- Sidebar Responsive Toggle Handler -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('mobile-sidebar-toggle');
            
            if (toggleBtn) {
                toggleBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    document.body.classList.toggle('sidebar-open');
                });
            }

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth < 992 && document.body.classList.contains('sidebar-open')) {
                    const sidebar = document.getElementById('sidebar');
                    if (sidebar && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                        document.body.classList.remove('sidebar-open');
                    }
                }
            });
        });
    </script>
</body>
</html>
