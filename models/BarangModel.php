<?php
require_once __DIR__ . '/../core/Database.php';

class BarangModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll() {
        $sql = "SELECT p.*, c.nama_kategori,
                (SELECT COALESCE(SUM(stok), 0) FROM product_batches pb WHERE pb.id_produk = p.id_produk AND pb.stok > 0 AND (pb.exp_date >= CURDATE() OR pb.exp_date IS NULL)) as stok_layak
                FROM products p 
                LEFT JOIN categories c ON p.id_kategori = c.id_kategori 
                ORDER BY p.id_produk DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllActive() {
        $sql = "SELECT p.*, c.nama_kategori,
                (SELECT COALESCE(SUM(stok), 0) FROM product_batches pb WHERE pb.id_produk = p.id_produk AND pb.stok > 0 AND (pb.exp_date >= CURDATE() OR pb.exp_date IS NULL)) as stok_layak
                FROM products p 
                LEFT JOIN categories c ON p.id_kategori = c.id_kategori 
                WHERE p.status = 'aktif'
                ORDER BY p.id_produk DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id_produk = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO products (sku, nama_produk, id_kategori, harga, stok, image, exp_date, status) 
                VALUES (:sku, :nama_produk, :id_kategori, :harga, :stok, :image, :exp_date, :status)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':sku'         => $data['sku'],
            ':nama_produk' => $data['nama_produk'],
            ':id_kategori' => $data['id_kategori'] ?: null,
            ':harga'       => $data['harga'] ?: 0,
            ':stok'        => $data['stok'] ?: 0,
            ':image'       => $data['image'] ?: 'default.jpg',
            ':exp_date'    => $data['exp_date'] ?: null,
            ':status'      => $data['status'] ?: 'aktif'
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE products 
                SET sku = :sku, 
                    nama_produk = :nama_produk, 
                    id_kategori = :id_kategori, 
                    harga = :harga, 
                    stok = :stok,
                    exp_date = :exp_date,
                    status = :status";
        $params = [
            ':id'          => $id,
            ':sku'         => $data['sku'],
            ':nama_produk' => $data['nama_produk'],
            ':id_kategori' => $data['id_kategori'] ?: null,
            ':harga'       => $data['harga'] ?: 0,
            ':stok'        => $data['stok'] ?: 0,
            ':exp_date'    => $data['exp_date'] ?: null,
            ':status'      => $data['status'] ?: 'aktif'
        ];

        if (!empty($data['image'])) {
            $sql .= ", image = :image";
            $params[':image'] = $data['image'];
        }

        $sql .= " WHERE id_produk = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id_produk = ?");
        return $stmt->execute([$id]);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE products SET status = ? WHERE id_produk = ?");
        return $stmt->execute([$status, $id]);
    }

    public function isNameExists($name, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE nama_produk = ? AND id_produk != ?");
            $stmt->execute([$name, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE nama_produk = ?");
            $stmt->execute([$name]);
        }
        return $stmt->fetchColumn() > 0;
    }

    public function isSkuExists($sku, $excludeId = null) {
        if (empty($sku)) return false;
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE sku = ? AND id_produk != ?");
            $stmt->execute([$sku, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE sku = ?");
            $stmt->execute([$sku]);
        }
        return $stmt->fetchColumn() > 0;
    }

    public function getCategories() {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY nama_kategori ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- BATCH & FEFO METHODS ---

    /**
     * Mendapatkan total stok yang masih layak jual (belum expired)
     */
    public function getTotalSellableStock($id_produk) {
        $stmt = $this->db->prepare("SELECT SUM(stok) FROM product_batches 
                                    WHERE id_produk = ? AND stok > 0 
                                    AND (exp_date >= CURDATE() OR exp_date IS NULL)");
        $stmt->execute([$id_produk]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Mendapatkan daftar batch yang tersedia (belum expired), diurutkan dari yang paling cepat expired (FEFO)
     */
    public function getAvailableBatches($id_produk) {
        // Mengurutkan: yang exp_date paling dekat (tapi >= hari ini) di atas.
        // Jika exp_date NULL (tidak ada masa kadaluarsa), ditaruh paling bawah.
        $sql = "SELECT * FROM product_batches 
                WHERE id_produk = ? AND stok > 0 
                AND (exp_date >= CURDATE() OR exp_date IS NULL)
                ORDER BY CASE WHEN exp_date IS NULL THEN 1 ELSE 0 END, exp_date ASC, id_batch ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_produk]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Menambahkan batch baru (Stok Masuk)
     */
    public function addBatch($id_produk, $stok, $exp_date) {
        $kode_batch = 'B-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
        
        $sql = "INSERT INTO product_batches (id_produk, kode_batch, stok, exp_date) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([$id_produk, $kode_batch, $stok, $exp_date ?: null]);
        
        if ($success) {
            // Update total physical stock in products table
            $update = $this->db->prepare("UPDATE products SET stok = stok + ? WHERE id_produk = ?");
            $update->execute([$stok, $id_produk]);
        }
        
        return $success;
    }

    /**
     * Memotong stok dengan metode FEFO (First Expired First Out).
     * Melompati batch yang sudah expired.
     * Mengembalikan true jika berhasil, false jika stok layak kurang.
     */
    public function deductStockFefo($id_produk, $qty) {
        $available_stock = $this->getTotalSellableStock($id_produk);
        if ($available_stock < $qty) return false;

        $batches = $this->getAvailableBatches($id_produk);
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

        // Update total physical stock
        $this->db->prepare("UPDATE products SET stok = stok - ? WHERE id_produk = ?")->execute([$qty, $id_produk]);

        return true;
    }

    /**
     * Memotong stok dari sisi gudang (Mutasi Keluar / Opname).
     * Mengutamakan memotong batch yang sudah EXPIRED terlebih dahulu,
     * kemudian memotong batch aktif (FEFO) jika masih kurang.
     */
    public function deductStockGudang($id_produk, $qty) {
        $stmt = $this->db->prepare("SELECT SUM(stok) FROM product_batches WHERE id_produk = ? AND stok > 0");
        $stmt->execute([$id_produk]);
        $total_physical = (int)$stmt->fetchColumn();
        
        if ($total_physical < $qty) return false;
        
        $sisa_diminta = $qty;
        $today = date('Y-m-d');
        
        // 1. Ambil batch yang EXPIRED terlebih dahulu
        $stmtExpired = $this->db->prepare("SELECT * FROM product_batches 
                                           WHERE id_produk = ? AND stok > 0 AND exp_date < ? 
                                           ORDER BY exp_date ASC, id_batch ASC");
        $stmtExpired->execute([$id_produk, $today]);
        $expiredBatches = $stmtExpired->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($expiredBatches as $batch) {
            if ($sisa_diminta <= 0) break;
            
            if ($batch['stok'] <= $sisa_diminta) {
                $sisa_diminta -= $batch['stok'];
                $this->db->prepare("UPDATE product_batches SET stok = 0 WHERE id_batch = ?")->execute([$batch['id_batch']]);
            } else {
                $stok_baru = $batch['stok'] - $sisa_diminta;
                $this->db->prepare("UPDATE product_batches SET stok = ? WHERE id_batch = ?")->execute([$stok_baru, $batch['id_batch']]);
                $sisa_diminta = 0;
            }
        }
        
        // 2. Jika masih kurang, ambil dari batch yang AKTIF (FEFO)
        if ($sisa_diminta > 0) {
            $activeBatches = $this->getAvailableBatches($id_produk);
            foreach ($activeBatches as $batch) {
                if ($sisa_diminta <= 0) break;
                
                if ($batch['stok'] <= $sisa_diminta) {
                    $sisa_diminta -= $batch['stok'];
                    $this->db->prepare("UPDATE product_batches SET stok = 0 WHERE id_batch = ?")->execute([$batch['id_batch']]);
                } else {
                    $stok_baru = $batch['stok'] - $sisa_diminta;
                    $this->db->prepare("UPDATE product_batches SET stok = ? WHERE id_batch = ?")->execute([$stok_baru, $batch['id_batch']]);
                    $sisa_diminta = 0;
                }
            }
        }
        
        // 3. Update total physical stock di tabel products
        $this->db->prepare("UPDATE products SET stok = stok - ? WHERE id_produk = ?")->execute([$qty, $id_produk]);
        
        return true;
    }
}

