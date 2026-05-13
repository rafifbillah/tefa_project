<?php
require_once __DIR__ . '/../core/Database.php';

class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Mengambil semua user kecuali yang sedang login
    public function getAll($current_user_id = 0) {
        $stmt = $this->db->prepare("SELECT id, username, nama_lengkap, role, status FROM users WHERE id != ? ORDER BY id DESC");
        $stmt->execute([$current_user_id]);
        return $stmt->fetchAll();
    }

    // Mendapatkan user berdasarkan ID untuk proses Edit
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Create User Baru dengan Hash Password
    public function create($data) {
        $sql = "INSERT INTO users (username, nama_lengkap, role, password, status) VALUES (:username, :nama, :role, :pass, 'aktif')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':username' => $data['username'],
            ':nama'     => $data['nama_lengkap'],
            ':role'     => $data['role'],
            ':pass'     => password_hash($data['password'], PASSWORD_BCRYPT)
        ]);
    }

    // Update Data User (dengan opsi ganti password)
    public function update($id, $data) {
        if (!empty($data['password'])) {
            $sql = "UPDATE users SET nama_lengkap = :nama, role = :role, password = :pass, status = :status WHERE id = :id";
            $params = [
                ':pass' => password_hash($data['password'], PASSWORD_BCRYPT),
                ':id'   => $id,
                ':nama' => $data['nama_lengkap'],
                ':role' => $data['role'],
                ':status' => $data['status']
            ];
        } else {
            $sql = "UPDATE users SET nama_lengkap = :nama, role = :role, status = :status WHERE id = :id";
            $params = [
                ':id'   => $id,
                ':nama' => $data['nama_lengkap'],
                ':role' => $data['role'],
                ':status' => $data['status']
            ];
        }
        return $this->db->prepare($sql)->execute($params);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}