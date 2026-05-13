<?php 
// Mengambil nama file yang sedang dibuka di browser
// strtolower digunakan untuk mengubah huruf menjadi kecil semua agar pencocokan akurat
$current_page = strtolower(basename($_SERVER['PHP_SELF'])); 
?>

<aside class="sidebar">
    <div class="sidebar-top">
        <div class="logo">
            <span class="logo-icon"><img src="../assets/img/logo.jpg" alt="Logo" onerror="this.src='https://ui-avatars.com/api/?name=Kasir&background=D97706&color=fff'"></span>
            <div class="logo-text">
                <span class="brand">TEFA</span>
                <span class="role">BAKERY AND COFFEE</span>
            </div>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo ($current_page == 'index.php' ? 'active' : ''); ?>">
                <a href="index.php">
                    <i class="fa-solid fa-table-cells-large"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'transaksi.php' ? 'active' : ''); ?>">
                <a href="transaksi.php">
                    <i class="fa-solid fa-cash-register"></i>
                    <span>Transaksi</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'laporan.php' ? 'active' : ''); ?>">
                <a href="laporan.php">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Laporan Shift</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-bottom">
        <a href="../logout.php" class="logout-btn">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>