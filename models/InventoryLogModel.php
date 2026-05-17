<?php
require_once __DIR__ . '/../core/Database.php';

class InventoryLogModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll($filters = []) {
        $sql = "SELECT il.*, p.sku, p.nama_produk, u.nama_lengkap as petugas
                FROM inventory_logs il
                JOIN products p ON il.id_produk = p.id_produk
                LEFT JOIN users u ON il.id_user = u.id_user
                WHERE 1=1";
        $params = [];

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(il.created_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(il.created_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        if (!empty($filters['tipe']) && $filters['tipe'] !== 'Semua') {
            $sql .= " AND il.tipe_mutasi = :tipe";
            $params[':tipe'] = strtolower($filters['tipe']);
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (p.nama_produk LIKE :search OR p.sku LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY il.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSummaryStats($filters = []) {
        $stats = [
            'masuk' => 0,
            'keluar' => 0,
            'rusak' => 0,
            'kritis' => 0
        ];

        // Sums
        $sql = "SELECT tipe_mutasi, SUM(jumlah) as total 
                FROM inventory_logs 
                WHERE 1=1";
        $params = [];
        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(created_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(created_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        $sql .= " GROUP BY tipe_mutasi";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            $stats[$row['tipe_mutasi']] = $row['total'];
        }

        // Kritis count
        $stmt = $this->db->query("SELECT COUNT(*) FROM products WHERE stok < 10 AND status = 'aktif'");
        $stats['kritis'] = $stmt->fetchColumn();

        return $stats;
    }
}
