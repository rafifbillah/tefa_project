<?php
require_once __DIR__ . '/../core/Database.php';

class CategoryModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY id_kategori DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id_kategori = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($nama) {
        $stmt = $this->db->prepare("INSERT INTO categories (nama_kategori) VALUES (?)");
        return $stmt->execute([$nama]);
    }

    public function update($id, $nama) {
        $stmt = $this->db->prepare("UPDATE categories SET nama_kategori = ? WHERE id_kategori = ?");
        return $stmt->execute([$nama, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id_kategori = ?");
        return $stmt->execute([$id]);
    }
}
