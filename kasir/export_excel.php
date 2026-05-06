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

$transactions = $laporanModel->getTransactions($selectedDate, $selectedDate, $methodFilter);

// Set Headers for Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Kasir_TEFA_" . date('Ymd') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border="1">
    <thead>
        <tr>
            <th colspan="5" style="font-size: 16px; font-weight: bold; text-align: center;">LAPORAN TRANSAKSI KASIR - TEFA BAKERY</th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: center;">Tanggal: <?= $selectedDate ?: 'Semua Waktu' ?> | Metode: <?= strtoupper($method) ?></th>
        </tr>
        <tr>
            <th>No</th>
            <th>Waktu</th>
            <th>Reference ID</th>
            <th>Metode</th>
            <th>Total Tagihan (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        $grandTotal = 0;
        foreach ($transactions as $t): 
            $grandTotal += $t['total_tagihan'];
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= date('H:i', strtotime($t['created_at'])) ?></td>
            <td><?= $t['transaction_id'] ?></td>
            <td><?= strtoupper($t['metode_bayar']) ?></td>
            <td align="right"><?= number_format($t['total_tagihan'], 0, '', '') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4" align="right">GRAND TOTAL</th>
            <th align="right"><?= number_format($grandTotal, 0, '', '') ?></th>
        </tr>
    </tfoot>
</table>
