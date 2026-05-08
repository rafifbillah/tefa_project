<?php
require_once __DIR__ . '/../core/Database.php';

class LaporanModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->checkAndAddStatusColumn();
    }

    private function checkAndAddStatusColumn() {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM transactions LIKE 'status'");
            if ($stmt->rowCount() === 0) {
                $this->db->exec("ALTER TABLE transactions ADD COLUMN status VARCHAR(20) DEFAULT 'success' AFTER total_harga");
            }

            // Check and add status_verifikasi column for Google Sheets Integration
            $stmtVerif = $this->db->query("SHOW COLUMNS FROM transactions LIKE 'status_verifikasi'");
            if ($stmtVerif->rowCount() === 0) {
                $this->db->exec("ALTER TABLE transactions ADD COLUMN status_verifikasi ENUM('pending', 'requested', 'verified', 'synced') DEFAULT 'pending' AFTER status");
            } else {
                // Ensure 'synced' is available
                $this->db->exec("ALTER TABLE transactions MODIFY COLUMN status_verifikasi ENUM('pending', 'requested', 'verified', 'synced') DEFAULT 'pending'");
            }
        } catch (PDOException $e) {
            // Silently fail if there's permission issue, though it should log in production
        }
    }

    public function getTransactions($startDate = null, $endDate = null, $method = null, $userId = null) {
        $sql = "SELECT t.*, u.nama_lengkap as kasir_nama, u.username as kasir_username 
                FROM transactions t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE 1=1";
        $params = [];

        if ($startDate) {
            $sql .= " AND t.created_at >= :start_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
        }

        if ($endDate) {
            $sql .= " AND t.created_at <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        if ($method && strtolower($method) !== 'semua') {
            $sql .= " AND t.metode_bayar = :method";
            $params[':method'] = strtolower($method);
        }

        if ($userId) {
            $sql .= " AND t.user_id = :user_id";
            $params[':user_id'] = $userId;
        }

        $sql .= " ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTransactionDetails($transactionId) {
        $sql = "SELECT td.*, p.nama_produk 
                FROM transaction_details td 
                JOIN products p ON td.product_id = p.id 
                WHERE td.transaction_id = :trx_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':trx_id' => $transactionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateVerificationStatus($transactionId, $status) {
        $validStatuses = ['pending', 'requested', 'verified'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $sql = "UPDATE transactions SET status_verifikasi = :status WHERE id = :id AND (status != 'void' OR status IS NULL)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $transactionId
        ]);
    }

    public function getTransactionsByVerificationStatus($status) {
        $sql = "SELECT t.*, u.nama_lengkap as kasir_nama, u.username as kasir_username 
                FROM transactions t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE t.status_verifikasi = :status AND (t.status != 'void' OR t.status IS NULL)
                ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function requestBatchVerification($userId, $tanggal) {
        $sql = "UPDATE transactions 
                SET status_verifikasi = 'requested' 
                WHERE user_id = :user_id 
                  AND DATE(created_at) = :tanggal 
                  AND status_verifikasi = 'pending' 
                  AND (status != 'void' OR status IS NULL)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':tanggal' => $tanggal
        ]);
    }

    public function getRequestedBatches() {
        $sql = "SELECT t.user_id, DATE(t.created_at) as tanggal, u.nama_lengkap as kasir_nama,
                       COUNT(t.id) as total_transaksi,
                       SUM(t.total_harga) as total_pendapatan,
                       MAX(t.status_verifikasi) as status_verifikasi
                FROM transactions t
                LEFT JOIN users u ON t.user_id = u.id
                WHERE t.status_verifikasi IN ('requested', 'verified') AND (t.status != 'void' OR t.status IS NULL)
                GROUP BY t.user_id, DATE(t.created_at), u.nama_lengkap
                ORDER BY tanggal ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetailedBatchData($userId, $tanggal) {
        $sql = "SELECT id, transaction_id, created_at, metode_bayar, total_harga
                FROM transactions 
                WHERE user_id = :user_id 
                  AND DATE(created_at) = :tanggal 
                  AND status_verifikasi IN ('requested', 'verified') 
                  AND (status != 'void' OR status IS NULL)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':tanggal' => $tanggal]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($transactions as &$trx) {
            $trx['items'] = $this->getTransactionDetails($trx['id']);
        }

        return $transactions;
    }

    public function verifyBatch($userId, $tanggal) {
        $sql = "UPDATE transactions 
                SET status_verifikasi = 'verified' 
                WHERE user_id = :user_id 
                  AND DATE(created_at) = :tanggal 
                  AND status_verifikasi = 'requested'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':tanggal' => $tanggal
        ]);
    }

    public function syncBatch($userId, $tanggal) {
        $sql = "UPDATE transactions 
                SET status_verifikasi = 'synced' 
                WHERE user_id = :user_id 
                  AND DATE(created_at) = :tanggal 
                  AND status_verifikasi = 'verified'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':tanggal' => $tanggal
        ]);
    }

    public function getSummaryStats($startDate = null, $endDate = null, $method = null, $userId = null) {
        $sql = "SELECT 
                    COUNT(*) as total_transaksi, 
                    COALESCE(SUM(total_harga), 0) as total_pendapatan,
                    COALESCE(AVG(total_harga), 0) as rata_rata_transaksi
                FROM transactions 
                WHERE (status != 'void' OR status IS NULL)";
        $params = [];

        if ($startDate) {
            $sql .= " AND created_at >= :start_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
        }

        if ($endDate) {
            $sql .= " AND created_at <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        if ($method && strtolower($method) !== 'semua') {
            $sql .= " AND metode_bayar = :method";
            $params[':method'] = strtolower($method);
        }

        if ($userId) {
            $sql .= " AND user_id = :user_id";
            $params[':user_id'] = $userId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: [
            'total_transaksi' => 0,
            'total_pendapatan' => 0,
            'rata_rata_transaksi' => 0
        ];
    }

    public function getBestSellers($limit = 5) {
        $sql = "SELECT p.nama_produk, SUM(td.jumlah) as total_qty, SUM(td.jumlah * td.harga_satuan) as total_sales
                FROM transaction_details td
                JOIN products p ON td.product_id = p.id
                JOIN transactions t ON td.transaction_id = t.id
                WHERE t.status != 'void' OR t.status IS NULL
                GROUP BY td.product_id, p.nama_produk
                ORDER BY total_qty DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function voidTransaction($transactionId, $adminId) {
        try {
            $this->db->beginTransaction();

            // 1. Cek apakah transaksi sudah void
            $stmt = $this->db->prepare("SELECT status FROM transactions WHERE id = ? FOR UPDATE");
            $stmt->execute([$transactionId]);
            $trx = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$trx || $trx['status'] === 'void') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Transaksi tidak ditemukan atau sudah di-void.'];
            }

            // 2. Update status transaksi
            $stmt = $this->db->prepare("UPDATE transactions SET status = 'void' WHERE id = ?");
            $stmt->execute([$transactionId]);

            // 3. Reversal Stok
            $stmt = $this->db->prepare("SELECT product_id, jumlah FROM transaction_details WHERE transaction_id = ?");
            $stmt->execute([$transactionId]);
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($details as $item) {
                // Ambil stok produk saat ini dengan lock
                $stmtStock = $this->db->prepare("SELECT stok FROM products WHERE id = ? FOR UPDATE");
                $stmtStock->execute([$item['product_id']]);
                $product = $stmtStock->fetch(PDO::FETCH_ASSOC);
                
                if ($product) {
                    $stokSebelum = $product['stok'];
                    $stokSesudah = $stokSebelum + $item['jumlah'];

                    // Update stok produk
                    $stmtUpdate = $this->db->prepare("UPDATE products SET stok = ? WHERE id = ?");
                    $stmtUpdate->execute([$stokSesudah, $item['product_id']]);

                    // Insert inventory log
                    $stmtLog = $this->db->prepare("INSERT INTO inventory_logs (product_id, user_id, tipe_mutasi, jumlah, stok_sebelum, stok_sesudah, keterangan, created_at) VALUES (?, ?, 'masuk', ?, ?, ?, ?, NOW())");
                    $keterangan = "VOID Transaksi ID: " . $transactionId;
                    $stmtLog->execute([
                        $item['product_id'],
                        $adminId,
                        $item['jumlah'],
                        $stokSebelum,
                        $stokSesudah,
                        $keterangan
                    ]);
                }
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Transaksi berhasil dibatalkan dan stok dikembalikan.'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Gagal melakukan void: ' . $e->getMessage()];
        }
    }
}
