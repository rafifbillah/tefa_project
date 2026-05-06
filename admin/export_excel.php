<?php
require_once '../core/Auth.php';
Auth::requireRole('admin');
/**
 * Export Laporan to Excel
 */
require_once __DIR__ . '/../models/LaporanModel.php';

$laporanModel = new LaporanModel();

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate   = $_GET['end_date'] ?? date('Y-m-d');
$method    = $_GET['method'] ?? 'Semua';

$transactions = $laporanModel->getTransactions($startDate, $endDate, $method);

// Set Headers for Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Transaksi_TEFA_" . date('Ymd') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

?>
<table border="1">
    <thead>
        <tr>
            <th colspan="6" style="font-size: 16px; font-weight: bold; text-align: center;">LAPORAN TRANSAKSI TEFA BAKERY</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: center;">Periode: <?= $startDate ?> s/d <?= $endDate ?> | Metode: <?= $method ?></th>
        </tr>
        <tr>
            <th>No</th>
            <th>Kode Transaksi</th>
            <th>Tanggal</th>
            <th>Kasir</th>
            <th>Metode Bayar</th>
            <th>Total Harga (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        $grandTotal = 0;
        foreach ($transactions as $t): 
            $grandTotal += $t['total_harga'];
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $t['order_id'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
            <td><?= $t['kasir'] ?? 'System' ?></td>
            <td><?= strtoupper($t['metode_bayar']) ?></td>
            <td align="right"><?= number_format($t['total_harga'], 0, '', '') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5" align="right">GRAND TOTAL</th>
            <th align="right"><?= number_format($grandTotal, 0, '', '') ?></th>
        </tr>
    </tfoot>
</table>
