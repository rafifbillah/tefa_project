<?php
$currentUser = Auth::user();
$namaLengkap = htmlspecialchars($currentUser['nama_lengkap'] ?? 'Kasir');
$roleLabel   = ucfirst($currentUser['role'] ?? 'kasir');
$initial     = strtoupper(substr($namaLengkap, 0, 1));
?>
<header class="main-header mb-8">
    <div class="header-left">
        <h1 class="page-title"><?= isset($page_title) ? $page_title : 'Dashboard' ?></h1>
        <p class="page-subtitle"><?= isset($page_subtitle) ? $page_subtitle : 'Ringkasan Aktivitas Toko' ?></p>
    </div>
    <div class="header-right" style="display: flex; align-items: center; gap: 30px;">
        <div class="date-picker">
            <span class="date-value"><?= date('d/m/Y') ?></span>
            <i class="fa-regular fa-calendar text-gray-400"></i>
        </div>
        <div class="user-profile">
            <div class="user-info">
                <div class="user-role"><?= $roleLabel ?></div>
                <div class="user-name"><?= $namaLengkap ?></div>
            </div>
            <div class="user-avatar-container">
                <div class="user-avatar" style="background-color: var(--orange);"><?= $initial ?></div>
                <span class="online-indicator"></span>
            </div>
        </div>
    </div>
</header>