<?php
/**
 * Footer Include
 * Close body tag and load scripts
 */
?>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="assets/js/<?php echo isset($dashboardPage) && $dashboardPage ? 'script-dashboard.js' : 'script.js'; ?>"></script>
    <?php if (isset($additionalJS) && !empty($additionalJS)): ?>
        <script src="<?php echo htmlspecialchars($additionalJS); ?>"></script>
    <?php endif; ?>
</body>
</html>
