<?php
require_once __DIR__ . '/../core/Database.php';

class BarangModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll() {
        $sql = "SELECT p.*, c.nama_kategori 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO products (sku, nama_produk, category_id, harga, stok, image, exp_date, status) 
                VALUES (:sku, :nama_produk, :category_id, :harga, :stok, :image, :exp_date, :status)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':sku'         => $data['sku'],
            ':nama_produk' => $data['nama_produk'],
            ':category_id' => $data['category_id'] ?: null,
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
                    category_id = :category_id, 
                    harga = :harga, 
                    stok = :stok,
                    exp_date = :exp_date,
                    status = :status";
        $params = [
            ':id'          => $id,
            ':sku'         => $data['sku'],
            ':nama_produk' => $data['nama_produk'],
            ':category_id' => $data['category_id'] ?: null,
            ':harga'       => $data['harga'] ?: 0,
            ':stok'        => $data['stok'] ?: 0,
            ':exp_date'    => $data['exp_date'] ?: null,
            ':status'      => $data['status'] ?: 'aktif'
        ];

        if (!empty($data['image'])) {
            $sql .= ", image = :image";
            $params[':image'] = $data['image'];
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE products SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function getCategories() {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY nama_kategori ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
