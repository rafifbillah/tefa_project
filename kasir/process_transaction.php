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
        $this->shiftId = $activeShift ? (int)$activeShift['id_shift'] : null;
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

            // ── Step 0: Handle Upload Bukti (Jika ada) ────────────────
            $buktiPath = $this->handleUploadBukti($_FILES['bukti_pembayaran'] ?? null);

            // ── Step 1: Simpan header transaksi ───────────────────────
            $trxId = $this->insertTransaction($noInvoice, $total, $bayar, $kembali, $metode, $catatan, $buktiPath);

            // ── Step 2: Proses tiap item ──────────────────────────────
            foreach ($payload['items'] as $item) {
                $productId = (int)   $item['id_produk'];
                $qty       = (int)   $item['quantity'];
                $harga     = (float) $item['harga'];

                if ($productId <= 0 || $qty <= 0) {
                    throw new InvalidArgumentException("Data item tidak valid (id_produk=$productId, qty=$qty).");
                }

                // a. Cek & lock stok
                $product = $this->checkStock($productId, $qty);

                $stokSebelum = (int) $product['stok'];
                $stokSesudah = $stokSebelum - $qty;

                // b. Kurangi stok produk (dengan logika FEFO)
                $this->updateStock($productId, $qty);

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
        string $catatan,
        ?string $buktiPath
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO transactions
                (transaction_id, id_user, id_shift, total_harga, bayar, kembali, metode_bayar, catatan, bukti_pembayaran, created_at)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$noInvoice, $this->userId, $this->shiftId, $total, $bayar, $kembali, $metode, $catatan, $buktiPath]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Handle upload bukti pembayaran.
     */
    private function handleUploadBukti(?array $file): ?string
    {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $targetDir = "../assets/img/bukti_bayar/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename  = 'BUKTI_' . time() . '_' . uniqid() . '.' . $extension;
        $targetFile = $targetDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $filename;
        }

        return null;
    }

    /**
     * Cek ketersediaan stok, gunakan FOR UPDATE untuk mencegah race condition.
     * @throws RuntimeException jika stok tidak cukup
     * @return array Data produk dari DB
     */
    private function checkStock(int $productId, int $qty): array
    {
        $stmt = $this->db->prepare(
            "SELECT id_produk as id, nama_produk, harga, stok FROM products WHERE id_produk = ? FOR UPDATE"
        );
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            throw new RuntimeException("Produk dengan ID {$productId} tidak ditemukan.");
        }

        // Get sellable stock (FEFO logic)
        $stmtBatch = $this->db->prepare("SELECT COALESCE(SUM(stok), 0) FROM product_batches WHERE id_produk = ? AND stok > 0 AND (exp_date >= CURDATE() OR exp_date IS NULL)");
        $stmtBatch->execute([$productId]);
        $sellable_stock = (int) $stmtBatch->fetchColumn();

        if ($sellable_stock < $qty) {
            throw new RuntimeException(
                "Stok layak jual untuk '{$product['nama_produk']}' tidak mencukupi. " .
                "Tersedia: {$sellable_stock}, diminta: {$qty}."
            );
        }

        return $product;
    }

    /**
     * Update kolom stok di tabel products dan deduct product_batches menggunakan FEFO.
     */
    private function updateStock(int $productId, int $qty): void
    {
        // 1. Dapatkan semua batch yang layak jual
        $stmt = $this->db->prepare("SELECT * FROM product_batches 
                                    WHERE id_produk = ? AND stok > 0 AND (exp_date >= CURDATE() OR exp_date IS NULL)
                                    ORDER BY CASE WHEN exp_date IS NULL THEN 1 ELSE 0 END, exp_date ASC, id_batch ASC FOR UPDATE");
        $stmt->execute([$productId]);
        $batches = $stmt->fetchAll();

        $sisa_diminta = $qty;

        foreach ($batches as $batch) {
            if ($sisa_diminta <= 0) break;

            if ($batch['stok'] <= $sisa_diminta) {
                // Habiskan batch ini
                $sisa_diminta -= $batch['stok'];
                $this->db->prepare("UPDATE product_batches SET stok = 0 WHERE id_batch = ?")->execute([$batch['id_batch']]);
            } else {
                // Kurangi sebagian
                $stok_baru = $batch['stok'] - $sisa_diminta;
                $this->db->prepare("UPDATE product_batches SET stok = ? WHERE id_batch = ?")->execute([$stok_baru, $batch['id_batch']]);
                $sisa_diminta = 0;
            }
        }

        // 2. Update total stok di tabel products
        $stmt = $this->db->prepare("UPDATE products SET stok = stok - ? WHERE id_produk = ?");
        $stmt->execute([$qty, $productId]);
    }

    /**
     * Insert satu baris detail ke tabel `transaction_details`.
     */
    private function insertDetail(int $trxId, int $productId, int $qty, float $harga): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO transaction_details (transaction_id, id_produk, jumlah, harga_satuan)
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
                (id_produk, id_user, tipe_mutasi, jumlah, stok_sebelum, stok_sesudah, keterangan, created_at)
             VALUES
                (?, ?, 'keluar', ?, ?, ?, ?, NOW())"
        );
        error_log("Attempting insertInventoryLog with id_produk=$productId, id_user={$this->userId}");
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

// Cek apakah request berupa JSON atau FormData
$input = file_get_contents('php://input');
$payload = json_decode($input, true);

// Jika bukan JSON (kemungkinan FormData/POST)
if (!$payload) {
    $payload = $_POST;
    // Decode items jika dikirim sebagai string JSON di FormData
    if (isset($payload['items']) && is_string($payload['items'])) {
        $payload['items'] = json_decode($payload['items'], true);
    }
}

if (!$payload) {
    echo json_encode(['success' => false, 'message' => 'Request body tidak valid.']);
    exit;
}

try {
    $db     = Database::getConnection();
    $userId = !empty($_SESSION['id_user']) ? (int) $_SESSION['id_user'] : 1;

    $transaction = new Transaction($db, $userId);
    $result      = $transaction->process($payload);

    echo json_encode($result);

} catch (Throwable $e) {
    error_log('[Fatal Transaction] ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem. Coba lagi.']);
}
