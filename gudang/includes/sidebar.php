<aside class="sidebar">
    <div class="sidebar-top">
        <div class="logo">
            <span class="logo-icon"><img src="../assets/img/logo.jpg" alt="Logo"></span>
            <div class="logo-text">
                <span class="brand">TEFA</span>
                <span class="role">BAKERY AND COFFEE</span>
            </div>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo (isset($page) && $page == 'dashboard' ? 'active' : ''); ?>">
                <a href="index.php">
                    <i class="fa-solid fa-table-cells-large"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo (isset($page) && $page == 'inventory' ? 'active' : ''); ?>">
                <a href="barang.php">
                    <i class="fa-solid fa-box"></i>
                    <span>Barang</span>
                </a>
            </li>
            <li class="<?php echo (isset($page) && $page == 'laporan' ? 'active' : ''); ?>">
                <a href="laporan.php">
                    <i class="fa-solid fa-file-lines"></i>
                    <span>Laporan</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-bottom">
        <a href="logout.php" class="logout-btn">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>