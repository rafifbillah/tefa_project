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
        } catch (PDOException $e) {
            // Silently fail if there's permission issue, though it should log in production
        }
    }

    public function getTransactions($startDate = null, $endDate = null, $method = null) {
        $sql = "SELECT t.*, u.nama_lengkap as kasir_nama, u.username as kasir_username 
                FROM transactions t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE 1=1";
        $params = [];

        if ($startDate) {
            $sql .= " AND DATE(t.created_at) >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND DATE(t.created_at) <= :end_date";
            $params[':end_date'] = $endDate;
        }

        if ($method && strtolower($method) !== 'semua') {
            $sql .= " AND t.metode_bayar = :method";
            $params[':method'] = strtolower($method);
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

    public function getSummaryStats($startDate = null, $endDate = null, $method = null) {
        $sql = "SELECT 
                    COUNT(*) as total_transaksi, 
                    COALESCE(SUM(total_harga), 0) as total_pendapatan,
                    COALESCE(AVG(total_harga), 0) as rata_rata_transaksi
                FROM transactions 
                WHERE 1=1";
        $params = [];

        if ($startDate) {
            $sql .= " AND DATE(created_at) >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND DATE(created_at) <= :end_date";
            $params[':end_date'] = $endDate;
        }

        if ($method && strtolower($method) !== 'semua') {
            $sql .= " AND metode_bayar = :method";
            $params[':method'] = strtolower($method);
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
