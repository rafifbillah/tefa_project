<?php
/**
 * Sidebar Include — Admin Module
 * Navigation sidebar for dashboard pages.
 * Menampilkan nama dan role user dari session.
 */
require_once __DIR__ . '/../../core/Auth.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$currentUser = Auth::user();
$namaLengkap = htmlspecialchars($currentUser['nama_lengkap'] ?? 'Admin');
$roleLabel   = ucfirst($currentUser['role'] ?? 'admin');
?>
<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="logo">
                <span class="logo-icon"><img src="../assets/img/logo.jpg" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;"></span>
                <div class="logo-text">
                    <span class="brand">TEFA</span>
                    <span class="role">BAKERY AND COFFEE</span>
                </div>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li class="<?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">
                    <a href="index.php">
                        <i class="fa-solid fa-table-cells-large"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="<?php echo ($currentPage == 'user.php') ? 'active' : ''; ?>">
                    <a href="user.php">
                        <i class="fa-solid fa-users"></i>
                        <span>User</span>
                    </a>
                </li>
                <li class="<?php echo ($currentPage == 'barang.php') ? 'active' : ''; ?>">
                    <a href="barang.php">
                        <i class="fa-solid fa-box"></i>
                        <span>Barang</span>
                    </a>
                </li>
                <li class="<?php echo ($currentPage == 'laporan.php') ? 'active' : ''; ?>">
                    <a href="laporan.php">
                        <i class="fa-solid fa-file-lines"></i>
                        <span>Laporan</span>
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

    <!-- Main Content -->
    <main class="main-content" id="main-content">
        <header class="top-bar">
            <div class="page-title">
                <h2 id="pageTitle"><?php echo isset($pageHeading) ? htmlspecialchars($pageHeading) : 'Dashboard'; ?></h2>
                <p class="page-subtitle">
                    Welcome back, here's what's happening today
                </p>
            </div>
            <div class="user-meta">
                <div class="user-info">
                    <!-- Tampilkan nama dan role dari SESSION (bukan hardcode) -->
                    <strong><?= $roleLabel ?></strong>
                    <p><?= $namaLengkap ?></p>
                </div>
                <div class="avatar-wrapper">
                    <img
                        src="https://ui-avatars.com/api/?name=<?= urlencode($namaLengkap) ?>&background=d4832c&color=fff&size=90"
                        alt="Avatar <?= $namaLengkap ?>"
                        class="avatar"
                        loading="lazy"
                    />
                    <span class="status-dot"></span>
                </div>
            </div>
        </header>
