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

            // Check and add bukti_pembayaran column
            $stmtBukti = $this->db->query("SHOW COLUMNS FROM transactions LIKE 'bukti_pembayaran'");
            if ($stmtBukti->rowCount() === 0) {
                $this->db->exec("ALTER TABLE transactions ADD COLUMN bukti_pembayaran VARCHAR(255) DEFAULT NULL AFTER catatan");
            }
        } catch (PDOException $e) {
            // Silently fail if there's permission issue, though it should log in production
        }
    }

    public function getTransactions($startDate = null, $endDate = null, $method = null, $userId = null) {
        $sql = "SELECT t.*, u.nama_lengkap as kasir_nama, u.username as kasir_username 
                FROM transactions t 
                LEFT JOIN users u ON t.id_user = u.id_user 
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
            $sql .= " AND t.id_user = :id_user";
            $params[':id_user'] = $userId;
        }

        $sql .= " ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTransactionDetails($transactionId) {
        $sql = "SELECT td.*, p.nama_produk 
                FROM transaction_details td 
                JOIN products p ON td.id_produk = p.id_produk 
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
                LEFT JOIN users u ON t.id_user = u.id_user 
                WHERE t.status_verifikasi = :status AND (t.status != 'void' OR t.status IS NULL)
                ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function requestBatchVerification($userId, $tanggal) {
        $sql = "UPDATE transactions 
                SET status_verifikasi = 'requested' 
                WHERE id_user = :id_user 
                  AND DATE(created_at) = :tanggal 
                  AND status_verifikasi = 'pending' 
                  AND (status != 'void' OR status IS NULL)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_user' => $userId,
            ':tanggal' => $tanggal
        ]);
    }

    public function getRequestedBatches() {
        $sql = "SELECT t.id_user, DATE(t.created_at) as tanggal, u.nama_lengkap as kasir_nama,
                       COUNT(t.id) as total_transaksi,
                       SUM(t.total_harga) as total_pendapatan,
                       MAX(t.status_verifikasi) as status_verifikasi
                FROM transactions t
                LEFT JOIN users u ON t.id_user = u.id_user
                WHERE t.status_verifikasi IN ('requested', 'verified') AND (t.status != 'void' OR t.status IS NULL)
                GROUP BY t.id_user, DATE(t.created_at), u.nama_lengkap
                ORDER BY tanggal ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetailedBatchData($userId, $tanggal) {
        $sql = "SELECT id, transaction_id, created_at, metode_bayar, total_harga
                FROM transactions 
                WHERE id_user = :id_user 
                  AND DATE(created_at) = :tanggal 
                  AND status_verifikasi IN ('requested', 'verified') 
                  AND (status != 'void' OR status IS NULL)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_user' => $userId, ':tanggal' => $tanggal]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($transactions as &$trx) {
            $trx['items'] = $this->getTransactionDetails($trx['id']);
        }

        return $transactions;
    }

    public function verifyBatch($userId, $tanggal) {
        $sql = "UPDATE transactions 
                SET status_verifikasi = 'verified' 
                WHERE id_user = :id_user 
                  AND DATE(created_at) = :tanggal 
                  AND status_verifikasi = 'requested'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_user' => $userId,
            ':tanggal' => $tanggal
        ]);
    }

    public function syncBatch($userId, $tanggal) {
        $sql = "UPDATE transactions 
                SET status_verifikasi = 'synced' 
                WHERE id_user = :id_user 
                  AND DATE(created_at) = :tanggal 
                  AND status_verifikasi = 'verified'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_user' => $userId,
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
            $sql .= " AND id_user = :id_user";
            $params[':id_user'] = $userId;
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
        $sql = "SELECT p.nama_produk, p.image, SUM(td.jumlah) as total_qty, SUM(td.jumlah * td.harga_satuan) as total_sales
                FROM transaction_details td
                JOIN products p ON td.id_produk = p.id_produk
                JOIN transactions t ON td.transaction_id = t.id
                WHERE t.status != 'void' OR t.status IS NULL
                GROUP BY td.id_produk, p.nama_produk, p.image
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
            $stmt = $this->db->prepare("SELECT id_produk, jumlah FROM transaction_details WHERE transaction_id = ?");
            $stmt->execute([$transactionId]);
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($details as $item) {
                // Ambil stok produk saat ini dengan lock
                $stmtStock = $this->db->prepare("SELECT stok FROM products WHERE id_produk = ? FOR UPDATE");
                $stmtStock->execute([$item['id_produk']]);
                $product = $stmtStock->fetch(PDO::FETCH_ASSOC);
                
                if ($product) {
                    $stokSebelum = $product['stok'];
                    $stokSesudah = $stokSebelum + $item['jumlah'];

                    // Update stok produk
                    $stmtUpdate = $this->db->prepare("UPDATE products SET stok = ? WHERE id_produk = ?");
                    $stmtUpdate->execute([$stokSesudah, $item['id_produk']]);

                    // Insert inventory log
                    $stmtLog = $this->db->prepare("INSERT INTO inventory_logs (id_produk, id_user, tipe_mutasi, jumlah, stok_sebelum, stok_sesudah, keterangan, created_at) VALUES (?, ?, 'masuk', ?, ?, ?, ?, NOW())");
                    $keterangan = "VOID Transaksi ID: " . $transactionId;
                    $stmtLog->execute([
                        $item['id_produk'],
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

    public function getChartStats($filter = 'week') {
        $labels = [];
        $data = [];

        switch ($filter) {
            case 'day':
                // Ambil data per jam untuk hari ini
                for ($i = 0; $i < 24; $i++) {
                    $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $labels[] = $hour . ':00';
                    $data[$hour] = 0;
                }
                $sql = "SELECT HOUR(created_at) as jam, SUM(total_harga) as total 
                        FROM transactions 
                        WHERE DATE(created_at) = CURDATE() AND (status != 'void' OR status IS NULL)
                        GROUP BY jam";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $h = str_pad($row['jam'], 2, '0', STR_PAD_LEFT);
                    if (isset($data[$h])) $data[$h] = (float)$row['total'];
                }
                break;

            case 'month':
                // Ambil data per hari untuk 30 hari terakhir
                for ($i = 29; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $labels[] = date('d M', strtotime($date));
                    $data[$date] = 0;
                }
                $sql = "SELECT DATE(created_at) as tgl, SUM(total_harga) as total 
                        FROM transactions 
                        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) AND (status != 'void' OR status IS NULL)
                        GROUP BY tgl";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (isset($data[$row['tgl']])) $data[$row['tgl']] = (float)$row['total'];
                }
                break;

            case 'week':
            default:
                // Ambil data per hari untuk 7 hari terakhir
                for ($i = 6; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $labels[] = date('d M', strtotime($date));
                    $data[$date] = 0;
                }
                $sql = "SELECT DATE(created_at) as tgl, SUM(total_harga) as total 
                        FROM transactions 
                        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND (status != 'void' OR status IS NULL)
                        GROUP BY tgl";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (isset($data[$row['tgl']])) $data[$row['tgl']] = (float)$row['total'];
                }
                break;
        }

        $chartValues = array_values($data);
        $total = array_sum($chartValues);

        return [
            'labels' => $labels,
            'data' => $chartValues,
            'total' => $total,
            'total_formatted' => number_format($total, 0, ',', '.')
        ];
    }
}
