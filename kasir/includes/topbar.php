<?php
$currentUser = Auth::user();
$namaLengkap = htmlspecialchars($currentUser['nama_lengkap'] ?? 'Kasir');
$roleLabel   = ucfirst($currentUser['role'] ?? 'kasir');
$initial     = strtoupper(substr($namaLengkap, 0, 1));
?>
<header class="main-header mb-8 px-4 lg:px-8">
    <div class="header-left flex items-center gap-4">
        <button onclick="toggleSidebar()" class="lg:hidden w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-gray-500 hover:text-[#D97706] transition-all border border-gray-100">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>
        <div>
            <h1 class="page-title text-xl lg:text-2xl"><?= isset($page_title) ? $page_title : 'Dashboard' ?></h1>
            <p class="page-subtitle hidden sm:block"><?= isset($page_subtitle) ? $page_subtitle : 'Ringkasan Aktivitas Toko' ?></p>
        </div>
    </div>
    <div class="header-right" style="display: flex; align-items: center; gap: 30px;">
        <div class="date-picker">
            <span class="date-value"><?= date('d/m/Y') ?></span>
            <i class="fa-regular fa-calendar text-gray-400"></i>
        </div>
        <div class="user-profile">
            <div class="user-info hidden sm:block">
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