<?php
/**
 * Core Inventory Class
 * Handles inventory/stock management for the gudang module.
 */
require_once __DIR__ . '/Database.php';

class Inventory {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function tambahStok($data) {
        $kode_barang = $data['kode_barang'] ?? '';
        $jumlah_stok_tambahan = $data['jumlah_stok'] ?? 0;
        $exp_baru = $data['exp'] ?? '';

        /* Contoh eksekusi ke database untuk UPDATE stok dan tanggal exp:
        $stmt = $this->db->prepare("UPDATE inventory SET stok = stok + ?, exp_date = ? WHERE sku = ?");
        $stmt->bind_param("iss", $jumlah_stok_tambahan, $exp_baru, $kode_barang);
        return $stmt->execute();
        */

        // Simulasikan berhasil menambah stok
        return true;
    }

    public function getAllItems() {
        /*
        $query = "SELECT * FROM inventory ORDER BY id DESC";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
        */

        return [
            ['foto' => 'kopi.jpg', 'nama' => 'Biji Kopi Espresso', 'kode' => 'KODE: 1', 'kategori' => 'KOPI', 'stok' => 100, 'status' => 'OPTIMAL', 'mhs' => '06-02-2027'],
            ['foto' => 'roti.jpg', 'nama' => 'Roti Sobek', 'kode' => 'KODE: 2', 'kategori' => 'ROTI', 'stok' => 20, 'status' => 'PERBARUAN', 'mhs' => '05-02-2027'],
            ['foto' => 'gembong.jpg', 'nama' => 'Roti Gembong', 'kode' => 'KODE: 3', 'kategori' => 'ROTI', 'stok' => 10, 'status' => 'STOK MENIPIS', 'mhs' => '07-02-2027'],
            ['foto' => 'tawar.jpg', 'nama' => 'Roti Tawar', 'kode' => 'KODE: 4', 'kategori' => 'ROTI', 'stok' => 0, 'status' => 'HABIS', 'mhs' => '03-02-2027'],
            ['foto' => 'bubuk.jpg', 'nama' => 'Bubuk Kopi Arabika', 'kode' => 'KODE: 5', 'kategori' => 'KOPI', 'stok' => 30, 'status' => 'PERBARUAN', 'mhs' => '10-02-2027'],
        ];
    }
}
?>
