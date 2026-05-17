<?php
require_once __DIR__ . '/../core/Database.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "ID tidak valid.";
    exit;
}

try {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM product_batches WHERE id_produk = ? ORDER BY exp_date ASC, id_batch ASC");
    $stmt->execute([$id]);
    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($batches)) {
        echo "<p style='text-align:center; padding:20px; color:#999;'>Belum ada batch/produksi terdaftar untuk barang ini.</p>";
    } else {
        echo '<table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">';
        echo '<thead style="background:#f1f5f9;">
                <tr>
                    <th style="padding:12px 10px; border-bottom:2px solid #cbd5e1; color:#475569; font-weight:600;">Kode Batch</th>
                    <th style="padding:12px 10px; border-bottom:2px solid #cbd5e1; color:#475569; font-weight:600; text-align:center;">Stok Sisa</th>
                    <th style="padding:12px 10px; border-bottom:2px solid #cbd5e1; color:#475569; font-weight:600;">Exp Date</th>
                    <th style="padding:12px 10px; border-bottom:2px solid #cbd5e1; color:#475569; font-weight:600; text-align:center;">Status</th>
                </tr>
              </thead>';
        echo '<tbody>';
        $today = date('Y-m-d');
        foreach ($batches as $b) {
            $isExpired = (!empty($b['exp_date']) && $b['exp_date'] < $today);
            
            if ($b['stok'] == 0) {
                $statusColor = '#94a3b8'; // Slate
                $statusText = 'HABIS';
                $bg = '#f8fafc';
                $badgeBg = '#f1f5f9';
                $badgeColor = '#64748b';
            } elseif ($isExpired) {
                $statusColor = '#ef4444'; // Red
                $statusText = 'EXPIRED';
                $bg = '#fff5f5';
                $badgeBg = '#fee2e2';
                $badgeColor = '#ef4444';
            } else {
                $statusColor = '#10b981'; // Green
                $statusText = 'AKTIF / SEGAR';
                $bg = '#f0fdf4';
                $badgeBg = '#dcfce7';
                $badgeColor = '#10b981';
            }
            
            $expDisplay = $b['exp_date'] ? date('d M Y', strtotime($b['exp_date'])) : 'Indefinite (No Exp)';
            $boldStyle = $isExpired ? 'font-weight:bold; color:#ef4444;' : 'color:#334155;';
            
            echo "<tr style='background: $bg; border-bottom: 1px solid #e2e8f0;'>";
            echo "<td style='padding:12px 10px; font-family:monospace; font-weight:500; color:#334155;'>{$b['kode_batch']}</td>";
            echo "<td style='padding:12px 10px; font-weight:bold; text-align:center; color:#1e293b;'>{$b['stok']}</td>";
            echo "<td style='padding:12px 10px; $boldStyle'>$expDisplay</td>";
            echo "<td style='padding:12px 10px; text-align:center;'>
                    <span style='background:$badgeBg; color:$badgeColor; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:700; text-transform:uppercase;'>$statusText</span>
                  </td>";
            echo "</tr>";
        }
        echo '</tbody></table>';
    }
} catch (Exception $e) {
    echo "<p style='color:red; text-align:center; padding:20px;'>Terjadi kesalahan saat memuat data batch.</p>";
}
