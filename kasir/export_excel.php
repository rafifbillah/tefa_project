<?php
require_once '../core/Auth.php';
Auth::requireRole('kasir');

/**
 * Export Laporan Kasir to Excel
 */
require_once __DIR__ . '/../models/LaporanModel.php';

$laporanModel = new LaporanModel();

$selectedDate = isset($_GET['date']) && $_GET['date'] !== '' ? $_GET['date'] : null;
$method = isset($_GET['method']) ? strtolower($_GET['method']) : 'semua';
$methodFilter = ($method === 'semua') ? null : $method;

$userId = $_SESSION['user_id'];
$transactions = $laporanModel->getTransactions($selectedDate, $selectedDate, $methodFilter, $userId);

// Set Headers for Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Kasir_TEFA_" . date('Ymd') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border="1">
    <thead>
        <tr>
            <th colspan="7" style="font-size: 18px; font-weight: bold; text-align: center; background-color: #f3f4f6;">LAPORAN TRANSAKSI KASIR - TEFA BAKERY</th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center; background-color: #f3f4f6;">Periode: <?= $selectedDate ?: 'Semua Waktu' ?> | Kasir: <?= $_SESSION['nama_lengkap'] ?></th>
        </tr>
        <tr style="background-color: #e5e7eb; font-weight: bold;">
            <th>No</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>ID Transaksi</th>
            <th>Nama Kasir</th>
            <th>Total Bayar (Rp)</th>
            <th>Rincian Item</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        $grandTotal = 0;
        foreach ($transactions as $t): 
            $grandTotal += ($t['total_harga'] ?? 0);
            
            // Ambil rincian item untuk setiap transaksi (Sama seperti logika Google Sheets)
            $items = $laporanModel->getTransactionDetails($t['id']);
            $rincianText = "";
            foreach ($items as $item) {
                $rincianText .= $item['jumlah'] . "x " . $item['nama_produk'] . " (@" . number_format($item['harga_satuan'], 0, ',', '.') . ")\n";
            }
        ?>
        <tr>
            <td align="center"><?= $no++ ?></td>
            <td align="center"><?= date('Y-m-d', strtotime($t['created_at'])) ?></td>
            <td align="center"><?= date('H:i', strtotime($t['created_at'])) ?></td>
            <td><?= $t['transaction_id'] ?></td>
            <td><?= htmlspecialchars($t['kasir_nama'] ?? $t['kasir_username']) ?></td>
            <td align="right"><?= number_format($t['total_harga'] ?? 0, 0, ',', '.') ?></td>
            <td style="white-space: pre-line; vertical-align: top;"><?= trim($rincianText) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr style="background-color: #dcfce7; font-weight: bold;">
            <th colspan="5" align="right">TOTAL PENDAPATAN</th>
            <th align="right"><?= number_format($grandTotal, 0, ',', '.') ?></th>
            <th></th>
        </tr>
    </tfoot>
</table>
