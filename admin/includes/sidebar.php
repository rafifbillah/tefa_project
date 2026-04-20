<?php
/**
 * Sidebar Include
 * Navigation sidebar for dashboard pages
 */
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar" aria-label="Main Navigation">
        <div class="sidebar-header">
            <div class="brand-info">
                <div class="logo-container">
                    <i class="fas fa-bread-slice logo-icon"></i>
                </div>
                <div class="brand-text">
                    <h1>TEFA</h1>
                    <p>Bakery and Coffee</p>
                </div>
            </div>
        </div>

        <nav class="sidebar-menu" aria-label="Primary Menu">
            <ul class="menu-list">
                <li>
                    <a href="index.php" class="menu-item <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">
                        <i class="fas fa-th-large" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="user.php" class="menu-item <?php echo ($currentPage == 'user.php') ? 'active' : ''; ?>">
                        <i class="fas fa-users" aria-hidden="true"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li>
                    <a href="barang.php" class="menu-item <?php echo ($currentPage == 'barang.php') ? 'active' : ''; ?>">
                        <i class="fas fa-boxes" aria-hidden="true"></i>
                        <span>Barang</span>
                    </a>
                </li>
                <li>
                    <a href="laporan.php" class="menu-item <?php echo ($currentPage == 'laporan.php') ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar" aria-hidden="true"></i>
                        <span>Laporan</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="login.php" class="logout-btn" id="logoutBtn">
                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
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
                    <strong>Ketua</strong>
                    <p>Admin Tefa</p>
                </div>
                <div class="avatar-wrapper">
                    <img
                        src="https://ui-avatars.com/api/?name=Admin+Tefa&background=d4832c&color=fff&size=90"
                        alt="Admin Avatar"
                        class="avatar"
                        loading="lazy"
                    />
                    <span class="status-dot"></span>
                </div>
            </div>
        </header>
