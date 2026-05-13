<?php
require_once '../core/Auth.php';
Auth::requireRole('admin');
require_once '../models/LaporanModel.php';

$userId = $_GET['user_id'] ?? 0;
$tanggal = $_GET['tanggal'] ?? '';

if (!$userId || !$tanggal) {
    echo "<p style='color:red;'>Data tidak lengkap.</p>";
    exit;
}

$model = new LaporanModel();
$transactions = $model->getDetailedBatchData($userId, $tanggal);

if (empty($transactions)) {
    echo "<p>Tidak ada transaksi ditemukan.</p>";
    exit;
}

echo "<table style='width:100%; border-collapse: collapse; font-family: sans-serif; font-size: 14px;'>";
echo "<thead>";
echo "<tr style='background:#f1f5f9; border-bottom: 2px solid #cbd5e1;'>";
echo "<th style='padding:10px; text-align:left;'>ID Trx</th>";
echo "<th style='padding:10px; text-align:left;'>Waktu</th>";
echo "<th style='padding:10px; text-align:right;'>Total</th>";
echo "<th style='padding:10px; text-align:left;'>Rincian Item</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

foreach ($transactions as $t) {
    $waktu = date('H:i', strtotime($t['created_at']));
    echo "<tr style='border-bottom: 1px solid #e2e8f0;'>";
    echo "<td style='padding:10px; font-family:monospace;'>" . htmlspecialchars($t['transaction_id']) . "</td>";
    echo "<td style='padding:10px;'>" . $waktu . "</td>";
    echo "<td style='padding:10px; text-align:right; font-weight:bold;'>Rp " . number_format($t['total_harga'], 0, ',', '.') . "</td>";
    
    echo "<td style='padding:10px;'>";
    echo "<ul style='margin:0; padding-left:15px; font-size:13px;'>";
    foreach ($t['items'] as $item) {
        echo "<li>{$item['jumlah']}x " . htmlspecialchars($item['nama_produk']) . "</li>";
    }
    echo "</ul>";
    echo "</td>";
    echo "</tr>";
}
echo "</tbody>";
echo "</table>";
