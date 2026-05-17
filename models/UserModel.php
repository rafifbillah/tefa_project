<?php
require_once __DIR__ . '/../core/Database.php';

class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Mengambil semua user kecuali yang sedang login
    public function getAll($current_id_user = 0) {
        $stmt = $this->db->prepare("SELECT id_user, username, nama_lengkap, role, status FROM users WHERE id_user != ? ORDER BY id_user DESC");
        $stmt->execute([$current_id_user]);
        return $stmt->fetchAll();
    }

    // Mendapatkan user berdasarkan ID untuk proses Edit
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id_user = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Create User Baru dengan Hash Password
    public function create($data) {
        $sql = "INSERT INTO users (username, nama_lengkap, role, password, status) VALUES (:username, :nama, :role, :pass, :status)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':username' => trim($data['username']),
            ':nama'     => trim($data['nama_lengkap']),
            ':role'     => trim($data['role']),
            ':pass'     => password_hash($data['password'], PASSWORD_BCRYPT),
            ':status'   => isset($data['status']) ? trim($data['status']) : 'aktif'
        ]);
    }

    // Update Data User (dengan opsi ganti password)
    public function update($id, $data) {
        if (!empty($data['password']) && strlen(trim($data['password'])) > 0) {
            $sql = "UPDATE users SET nama_lengkap = :nama, role = :role, password = :pass, status = :status WHERE id_user = :id";
            $params = [
                ':pass' => password_hash($data['password'], PASSWORD_BCRYPT),
                ':id'   => $id,
                ':nama' => trim($data['nama_lengkap']),
                ':role' => trim($data['role']),
                ':status' => trim($data['status'])
            ];
        } else {
            $sql = "UPDATE users SET nama_lengkap = :nama, role = :role, status = :status WHERE id_user = :id";
            $params = [
                ':id'   => $id,
                ':nama' => trim($data['nama_lengkap']),
                ':role' => trim($data['role']),
                ':status' => trim($data['status'])
            ];
        }
        return $this->db->prepare($sql)->execute($params);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id_user = ?");
        return $stmt->execute([$id]);
    }
}