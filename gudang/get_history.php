<?php
require_once __DIR__ . '/../core/Database.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "ID tidak valid.";
    exit;
}

try {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT il.*, u.nama_lengkap 
                          FROM inventory_logs il 
                          LEFT JOIN users u ON il.user_id = u.id 
                          WHERE il.product_id = ? 
                          ORDER BY il.created_at DESC 
                          LIMIT 5");
    $stmt->execute([$id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($history)) {
        echo "<p style='text-align:center; padding:20px; color:#999;'>Belum ada riwayat mutasi untuk barang ini.</p>";
    } else {
        echo '<table style="width:100%; border-collapse:collapse; font-size:13px;">';
        echo '<thead style="background:#f9f9f9;"><tr><th style="padding:10px; border-bottom:2px solid #eee;">Tanggal</th><th style="padding:10px; border-bottom:2px solid #eee;">Tipe</th><th style="padding:10px; border-bottom:2px solid #eee;">Jumlah</th><th style="padding:10px; border-bottom:2px solid #eee;">Saldo</th></tr></thead>';
        echo '<tbody>';
        foreach ($history as $h) {
            $color = $h['tipe_mutasi'] == 'masuk' ? '#27ae60' : '#e74c3c';
            $sign = $h['tipe_mutasi'] == 'masuk' ? '+' : '-';
            echo "<tr>";
            echo "<td style='padding:10px; border-bottom:1px solid #eee;'>" . date('d/m/Y H:i', strtotime($h['created_at'])) . "</td>";
            echo "<td style='padding:10px; border-bottom:1px solid #eee; text-transform:uppercase; font-weight:bold; color:$color;'>" . $h['tipe_mutasi'] . "</td>";
            echo "<td style='padding:10px; border-bottom:1px solid #eee; font-weight:bold;'>$sign" . $h['jumlah'] . "</td>";
            echo "<td style='padding:10px; border-bottom:1px solid #eee;'>" . $h['stok_sesudah'] . "</td>";
            echo "</tr>";
            echo "<tr><td colspan='4' style='padding:0 10px 10px 10px; border-bottom:1px solid #eee; font-size:11px; color:#777;'>Ket: " . htmlspecialchars($h['keterangan'] ?: '-') . " | Petugas: " . htmlspecialchars($h['nama_lengkap'] ?: 'System') . "</td></tr>";
        }
        echo '</tbody></table>';
    }
} catch (Exception $e) {
    echo "Terjadi kesalahan.";
}
