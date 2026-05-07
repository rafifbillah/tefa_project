<?php
/**
 * process_transaction.php — TEFA Bakery Kasir
 * =============================================
 * OOP Backend untuk memproses transaksi penjualan secara atomik.
 * Menggunakan PDO Transaction (Begin/Commit/Rollback) untuk
 * menjaga integritas data di semua tabel yang terlibat.
 */

header('Content-Type: application/json');
require_once '../core/Auth.php';
require_once '../core/Database.php';
require_once '../core/ShiftManager.php';
Auth::requireRole('kasir');


/**
 * Class Transaction
 * -----------------
 * Menangani seluruh proses simpan transaksi kasir secara atomik:
 * 1. Insert ke tabel `transactions`
 * 2. Insert ke tabel `transaction_details` (per-item)
 * 3. Update stok di tabel `products`
 * 4. Insert riwayat mutasi ke `inventory_logs`
 */
class Transaction
{
    private PDO $db;
    private int $userId;
    private ?int $shiftId;

    public function __construct(PDO $db, int $userId)
    {
        $this->db     = $db;
        $this->userId = $userId;
        
        $shiftManager = new ShiftManager();
        $activeShift = $shiftManager->getActiveShift($userId);
        $this->shiftId = $activeShift ? (int)$activeShift['id'] : null;
    }

    /**
     * Proses utama transaksi secara atomik.
     *
     * @param array $payload Data dari frontend (items, total, bayar, dll)
     * @return array ['success' => bool, 'transaction_id' => int|null, 'message' => string]
     */
    public function process(array $payload): array
    {
        // ── Validasi payload awal ──────────────────────────────────────
        if (empty($payload['items']) || !is_array($payload['items'])) {
            return ['success' => false, 'message' => 'Data keranjang tidak valid atau kosong.'];
        }

        $noInvoice  = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        $total      = (float)  ($payload['total']   ?? 0);
        $bayar      = (float)  ($payload['bayar']   ?? 0);
        $kembali    = (float)  ($payload['kembali'] ?? 0);
        $metode     = $this->sanitizeMetode($payload['metode'] ?? 'tunai');
        $catatan    = htmlspecialchars(trim($payload['catatan'] ?? ''), ENT_QUOTES);

        try {
            $this->db->beginTransaction();

            // ── Step 1: Simpan header transaksi ───────────────────────
            $trxId = $this->insertTransaction($noInvoice, $total, $bayar, $kembali, $metode, $catatan);

            // ── Step 2: Proses tiap item ──────────────────────────────
            foreach ($payload['items'] as $item) {
                $productId = (int)   $item['id'];
                $qty       = (int)   $item['quantity'];
                $harga     = (float) $item['harga'];

                if ($productId <= 0 || $qty <= 0) {
                    throw new InvalidArgumentException("Data item tidak valid (id=$productId, qty=$qty).");
                }

                // a. Cek & lock stok
                $product = $this->checkStock($productId, $qty);

                $stokSebelum = (int) $product['stok'];
                $stokSesudah = $stokSebelum - $qty;

                // b. Kurangi stok produk
                $this->updateStock($productId, $stokSesudah);

                // c. Simpan detail transaksi
                $this->insertDetail($trxId, $productId, $qty, $harga);

                // d. Catat mutasi ke inventory_logs
                $keteranganLog = "Penjualan Kasir — No. Invoice: {$noInvoice}";
                $this->insertInventoryLog($productId, $qty, $stokSebelum, $stokSesudah, $keteranganLog);
            }

            $this->db->commit();

            return [
                'success'        => true,
                'transaction_id' => $trxId,
                'no_invoice'     => $noInvoice,
                'message'        => 'Transaksi berhasil disimpan.'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('[Transaction Error] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────────────

    /**
     * Insert header transaksi ke tabel `transactions`.
     * @return int ID baris yang baru dibuat
     */
    private function insertTransaction(
        string $noInvoice,
        float $total,
        float $bayar,
        float $kembali,
        string $metode,
        string $catatan
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO transactions
                (transaction_id, user_id, shift_id, total_harga, bayar, kembali, metode_bayar, catatan, created_at)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$noInvoice, $this->userId, $this->shiftId, $total, $bayar, $kembali, $metode, $catatan]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Cek ketersediaan stok, gunakan FOR UPDATE untuk mencegah race condition.
     * @throws RuntimeException jika stok tidak cukup
     * @return array Data produk dari DB
     */
    private function checkStock(int $productId, int $qty): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, nama_produk, stok FROM products WHERE id = ? FOR UPDATE"
        );
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            throw new RuntimeException("Produk dengan ID {$productId} tidak ditemukan.");
        }

        if ((int) $product['stok'] < $qty) {
            throw new RuntimeException(
                "Stok '{$product['nama_produk']}' tidak mencukupi. " .
                "Tersedia: {$product['stok']}, diminta: {$qty}."
            );
        }

        return $product;
    }

    /**
     * Update kolom stok di tabel products.
     */
    private function updateStock(int $productId, int $stokBaru): void
    {
        $stmt = $this->db->prepare("UPDATE products SET stok = ? WHERE id = ?");
        $stmt->execute([$stokBaru, $productId]);
    }

    /**
     * Insert satu baris detail ke tabel `transaction_details`.
     */
    private function insertDetail(int $trxId, int $productId, int $qty, float $harga): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO transaction_details (transaction_id, product_id, jumlah, harga_satuan)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$trxId, $productId, $qty, $harga]);
    }

    /**
     * Catat mutasi stok keluar ke `inventory_logs`.
     */
    private function insertInventoryLog(
        int $productId,
        int $qty,
        int $stokSebelum,
        int $stokSesudah,
        string $keterangan
    ): void {
        $stmt = $this->db->prepare(
            "INSERT INTO inventory_logs
                (product_id, user_id, tipe_mutasi, jumlah, stok_sebelum, stok_sesudah, keterangan, created_at)
             VALUES
                (?, ?, 'keluar', ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$productId, $this->userId, $qty, $stokSebelum, $stokSesudah, $keterangan]);
    }

    /**
     * Validasi whitelist metode pembayaran yang diizinkan.
     */
    private function sanitizeMetode(string $metode): string
    {
        $allowed = ['tunai', 'qris', 'transfer'];
        return in_array(strtolower($metode), $allowed) ? strtolower($metode) : 'tunai';
    }
}


// ─── Entry Point ──────────────────────────────────────────────────────────────

$payload = json_decode(file_get_contents('php://input'), true);

if (!$payload) {
    echo json_encode(['success' => false, 'message' => 'Request body tidak valid atau bukan JSON.']);
    exit;
}

try {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!Auth::verifyCsrfToken($csrfToken)) {
        echo json_encode(['success' => false, 'message' => 'Request tidak valid (CSRF).']);
        exit;
    }

    $db     = Database::getConnection();
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Sesi login tidak valid.']);
        exit;
    }

    $transaction = new Transaction($db, $userId);
    $result      = $transaction->process($payload);

    echo json_encode($result);

} catch (Throwable $e) {
    error_log('[Fatal Transaction] ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem. Coba lagi.']);
}
