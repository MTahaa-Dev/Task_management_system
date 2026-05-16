    <?php if(isset($_SESSION['user_id'])): ?>
    </div> <!-- End wrapper -->
    <?php endif; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS Animation Library JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
    
    <!-- Chart.js (for Dashboard) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- SortableJS (for Kanban) -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    
    <!-- Custom Core JS -->
    <script src="/demo/assets/js/main.js"></script>
    <script src="/demo/assets/js/animations.js"></script>
    
    <!-- Initialize AOS -->
    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 50
        });

        // Check for session flash messages and show toasts
        <?php if(isset($_SESSION['flash_success'])): ?>
            showToast("<?php echo addslashes($_SESSION['flash_success']); ?>", "success");
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['flash_error'])): ?>
            showToast("<?php echo addslashes($_SESSION['flash_error']); ?>", "error");
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
