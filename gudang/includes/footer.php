    </div> <!-- end .content-wrapper -->
</main> <!-- end .main-content -->

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <!-- Note: Pastikan file JS dipindah ke assets/js -->
    <script src="../assets/js/<?php echo (isset($page) && $page == 'dashboard') ? 'admin-script-dashboard.js' : 'admin-script.js'; ?>"></script>
    <?php if (isset($additionalJS) && !empty($additionalJS)): ?>
        <script src="<?php echo htmlspecialchars($additionalJS); ?>"></script>
    <?php endif; ?>
</body>
</html>
