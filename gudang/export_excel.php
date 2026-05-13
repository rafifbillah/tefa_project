<?php
require_once '../core/Auth.php';
Auth::requireRole('gudang');

/**
 * Export Laporan Mutasi Stok to Excel
 */
require_once __DIR__ . '/../models/InventoryLogModel.php';

$logModel = new InventoryLogModel();

$filters = [
    'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
    'end_date'   => $_GET['end_date'] ?? date('Y-m-d'),
    'tipe'       => $_GET['tipe'] ?? 'Semua',
    'search'     => $_GET['search'] ?? ''
];

$logs = $logModel->getAll($filters);

// Set Headers for Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Mutasi_Gudang_" . date('Ymd') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border="1">
    <thead>
        <tr>
            <th colspan="7" style="font-size: 16px; font-weight: bold; text-align: center;">LAPORAN MUTASI STOK GUDANG - TEFA BAKERY</th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center;">Periode: <?= $filters['start_date'] ?> s/d <?= $filters['end_date'] ?> | Tipe: <?= $filters['tipe'] ?></th>
        </tr>
        <tr>
            <th>No</th>
            <th>Waktu</th>
            <th>Barang (SKU)</th>
            <th>Tipe</th>
            <th>Jumlah</th>
            <th>Stok Akhir</th>
            <th>Petugas</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        foreach ($logs as $l): 
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= date('d/m/Y H:i', strtotime($l['created_at'])) ?></td>
            <td><?= htmlspecialchars($l['nama_produk']) ?> (<?= $l['sku'] ?>)</td>
            <td><?= strtoupper($l['tipe_mutasi']) ?></td>
            <td><?= ($l['tipe_mutasi'] == 'masuk' ? '+' : '-') . $l['jumlah'] ?></td>
            <td><?= $l['stok_sesudah'] ?></td>
            <td><?= $l['petugas'] ?: 'System' ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
