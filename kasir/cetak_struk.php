<?php
require_once '../core/Auth.php';
require_once '../core/Database.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) die('<p style="font-family:sans-serif;color:red;padding:20px;">ID Transaksi tidak valid.</p>');

try {
    $db = Database::getConnection();

    $stmtTrx = $db->prepare(
        "SELECT t.*, u.nama_lengkap AS nama_kasir, u.username
         FROM transactions t JOIN users u ON t.user_id = u.id
         WHERE t.id = ?"
    );
    $stmtTrx->execute([$id]);
    $trx = $stmtTrx->fetch();

    if (!$trx) die('<p style="font-family:sans-serif;color:red;padding:20px;">Transaksi tidak ditemukan.</p>');

    $stmtDetail = $db->prepare(
        "SELECT d.*, p.nama_produk FROM transaction_details d
         JOIN products p ON d.product_id = p.id
         WHERE d.transaction_id = ?"
    );
    $stmtDetail->execute([$id]);
    $details = $stmtDetail->fetchAll();

} catch (Exception $e) {
    die('<p style="color:red;padding:20px;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>');
}

$namaKasir = ($trx['nama_kasir'] ?? '') ?: ($trx['username'] ?? 'Kasir');
$tglFormat  = date('d/m/Y', strtotime($trx['created_at']));
$jamFormat  = date('H:i', strtotime($trx['created_at']));
$metode     = strtoupper($trx['metode_bayar']);
$metodeColors = [
    'TUNAI'    => ['bg' => '#16a34a', 'text' => '#fff'],
    'QRIS'     => ['bg' => '#2563eb', 'text' => '#fff'],
    'TRANSFER' => ['bg' => '#D97706', 'text' => '#fff'],
];
$mc = $metodeColors[$metode] ?? ['bg' => '#6b7280', 'text' => '#fff'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk — <?= htmlspecialchars($trx['transaction_id']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0ece8;
            min-height: 100vh;
            padding: 20px 16px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ── Tombol Aksi ── */
        .action-bar {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .action-bar button, .action-bar a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .action-bar button:hover, .action-bar a:hover { opacity: 0.85; }
        .btn-print   { background: #1e1b2e; color: #fff; }
        .btn-new     { background: #fff; color: #1e1b2e; border: 2px solid #e5e7eb; }
        .btn-history { background: #fff; color: #6b7280; border: 2px solid #e5e7eb; }

        /* ── Kartu Struk ── */
        .struk-card {
            width: 100%;
            max-width: 380px;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }

        /* ── Header Struk (Dark Navy) ── */
        .struk-header {
            background: #1e1b2e;
            padding: 24px 20px 20px;
            text-align: center;
        }
        .struk-header .logo-icon {
            font-size: 22px;
            margin-bottom: 6px;
        }
        .struk-header h1 {
            color: #fff;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .struk-header h1 span {
            color: #D97706;
        }
        .struk-header p {
            color: #9ca3af;
            font-size: 11px;
            margin-top: 4px;
            line-height: 1.5;
        }

        /* ── Body Struk ── */
        .struk-body { padding: 16px 18px; }

        /* ── Baris Meta (Invoice, Kasir, Tanggal) ── */
        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 12px;
            border-bottom: 1.5px dashed #e5e7eb;
            margin-bottom: 14px;
        }
        .meta-left { display: flex; flex-direction: column; gap: 2px; }
        .meta-left .invoice-id {
            font-size: 12px;
            font-weight: 800;
            color: #1e1b2e;
            letter-spacing: 0.3px;
        }
        .meta-left .kasir-name {
            font-size: 10px;
            color: #9ca3af;
        }
        .meta-right { text-align: right; }
        .meta-right .date {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
        }
        .meta-right .time {
            font-size: 10px;
            color: #9ca3af;
        }

        /* ── Daftar Item ── */
        .items-list { margin-bottom: 14px; }
        .item-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #f9fafb;
        }
        .item-row:last-child { border-bottom: none; }
        .item-info { flex: 1; min-width: 0; }
        .item-name {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            line-height: 1.3;
        }
        .item-sub {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 1px;
        }
        .item-total {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            white-space: nowrap;
            margin-left: 12px;
            flex-shrink: 0;
        }

        /* ── Garis Total ── */
        .total-section {
            border-top: 1.5px dashed #e5e7eb;
            padding-top: 12px;
            margin-top: 4px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .total-label {
            font-size: 16px;
            font-weight: 800;
            color: #111827;
        }
        .total-amount {
            font-size: 16px;
            font-weight: 800;
            color: #111827;
        }
        .payment-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }
        .tunai-detail {
            margin-top: 8px;
            background: #f9fafb;
            border-radius: 8px;
            padding: 8px 12px;
        }
        .tunai-detail .detail-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #6b7280;
            padding: 2px 0;
        }
        .tunai-detail .detail-row span:last-child { font-weight: 600; color: #374151; }

        /* ── Catatan ── */
        .catatan-box {
            margin-top: 10px;
            background: #fffbeb;
            border-left: 3px solid #D97706;
            padding: 8px 10px;
            border-radius: 0 6px 6px 0;
        }
        .catatan-box p { font-size: 11px; color: #92400e; font-style: italic; }

        /* ── Footer Struk ── */
        .struk-footer {
            text-align: center;
            padding: 14px 18px 20px;
            border-top: 1px solid #f3f4f6;
        }
        .struk-footer .thanks {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 4px;
        }
        .struk-footer .sub-note {
            font-size: 10px;
            color: #9ca3af;
            line-height: 1.6;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .struk-card { box-shadow: none; border-radius: 0; max-width: 100%; }
        }
    </style>
</head>
<body>

    <!-- Tombol Aksi -->
    <div class="action-bar">
        <button class="btn-print" onclick="window.print()">
            🖨️ Cetak Struk
        </button>
        <a href="transaksi.php" class="btn-new">
            + Transaksi Baru
        </a>
        <a href="riwayat.php" class="btn-history">
            📋 Riwayat
        </a>
    </div>

    <!-- Kartu Struk -->
    <div class="struk-card">

        <!-- Header -->
        <div class="struk-header">
            <div class="logo-icon">🧁</div>
            <h1>TEFA <span>Kasir</span></h1>
            <p>Bakery &amp; Coffee · Politeknik Negeri Jember</p>
        </div>

        <!-- Body -->
        <div class="struk-body">

            <!-- Meta Info -->
            <div class="meta-row">
                <div class="meta-left">
                    <span class="invoice-id"><?= htmlspecialchars($trx['transaction_id']) ?></span>
                    <span class="kasir-name">Kasir <?= htmlspecialchars($namaKasir) ?></span>
                </div>
                <div class="meta-right">
                    <div class="date"><?= $tglFormat ?></div>
                    <div class="time"><?= $jamFormat ?> WIB</div>
                </div>
            </div>

            <!-- Daftar Item -->
            <div class="items-list">
                <?php foreach ($details as $item): ?>
                <div class="item-row">
                    <div class="item-info">
                        <div class="item-name"><?= htmlspecialchars($item['nama_produk']) ?></div>
                        <div class="item-sub"><?= $item['jumlah'] ?> × Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></div>
                    </div>
                    <div class="item-total">Rp <?= number_format($item['jumlah'] * $item['harga_satuan'], 0, ',', '.') ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Total & Metode -->
            <div class="total-section">
                <div class="total-row">
                    <span class="total-label">TOTAL</span>
                    <span class="total-amount">Rp <?= number_format($trx['total_harga'], 0, ',', '.') ?></span>
                </div>
                <div>
                    <span class="payment-badge" style="background:<?= $mc['bg'] ?>;color:<?= $mc['text'] ?>">
                        <?= $metode ?>
                    </span>
                </div>

                <?php if ($trx['metode_bayar'] === 'tunai'): ?>
                <div class="tunai-detail">
                    <div class="detail-row">
                        <span>Bayar</span>
                        <span>Rp <?= number_format($trx['bayar'], 0, ',', '.') ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Kembalian</span>
                        <span>Rp <?= number_format(max(0, $trx['kembali']), 0, ',', '.') ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($trx['catatan'])): ?>
                <div class="catatan-box">
                    <p>📝 <?= htmlspecialchars($trx['catatan']) ?></p>
                </div>
                <?php endif; ?>
            </div>

        </div><!-- /.struk-body -->

        <!-- Footer -->
        <div class="struk-footer">
            <div class="thanks">Terima kasih sudah berkunjung! 🙏</div>
            <div class="sub-note">
                Pendapatan ini tercatat sebagai PNBP Politeknik<br>
                Negeri Jember<br>
                © <?= date('Y') ?> TEFA Bakery &amp; Coffee
            </div>
        </div>

    </div><!-- /.struk-card -->

</body>
</html>
