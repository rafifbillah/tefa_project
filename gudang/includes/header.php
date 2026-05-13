<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Tefa Bakery - Sistem Gudang'; ?> | Tefa Bakery</title>
    <link rel="stylesheet" href="../assets/css/gudang-style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/gudang-header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/gudang-sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/gudang-footer.css?v=<?php echo time(); ?>">
    <?php if (isset($page) && $page == 'dashboard'): ?>
    <link rel="stylesheet" href="../assets/css/gudang-dashboard.css?v=<?php echo time(); ?>">
    <?php elseif (isset($page) && $page == 'inventory'): ?>
    <link rel="stylesheet" href="../assets/css/gudang-inventory.css?v=<?php echo time(); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="<?php echo isset($page) ? 'page-' . $page : ''; ?>">

<?php include __DIR__ . '/sidebar.php'; ?>

<main class="main-content">
<?php
$currentUser = Auth::user();
$namaLengkap = htmlspecialchars($currentUser['nama_lengkap'] ?? 'Gudang');
$roleLabel   = ucfirst($currentUser['role'] ?? 'gudang');
$initial     = strtoupper(substr($namaLengkap, 0, 1));
?>
    <header class="main-header">
        <div class="header-left">
            <h1 class="page-title"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>
            <?php if (!isset($page) || $page == 'dashboard') : ?>
            <p class="page-subtitle">Selamat Datang Kembali, <?php echo $namaLengkap; ?></p>
            <?php endif; ?>
        </div>
        <div class="header-right" style="display: flex; align-items: center; gap: 30px;">
            <div class="date-picker">
                <span class="date-value"></span>
            </div>
            <div class="user-profile">
                <div class="user-info">
                    <div class="user-role"><?php echo $roleLabel; ?></div>
                    <div class="user-name"><?php echo $namaLengkap; ?></div>
                </div>
                <div class="user-avatar-container">
                    <div class="user-avatar"><?php echo $initial; ?></div>
                    <span class="online-indicator"></span>
                </div>
            </div>
        </div>
    </header>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const hariIni = new Date();
            const tanggal = String(hariIni.getDate()).padStart(2, '0');
            const bulan = String(hariIni.getMonth() + 1).padStart(2, '0');
            const tahun = hariIni.getFullYear();
            const formatTanggal = `${tanggal}/${bulan}/${tahun}`;
            
            const elemenTanggal = document.querySelector('.date-value');
            if (elemenTanggal) {
                elemenTanggal.textContent = formatTanggal;
            }
        });
    </script>
    <div class="content-wrapper">