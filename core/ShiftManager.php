<?php
/**
 * ShiftManager — Mengelola Sesi Shift Kasir (Simple Mode)
 */
require_once __DIR__ . '/Database.php';

class ShiftManager
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Memulai shift baru jika belum ada yang terbuka untuk user tersebut.
     */
    public function startShift(int $userId): void
    {
        if ($this->getActiveShift($userId)) {
            // Sudah ada shift yang open, abaikan.
            return;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO shifts (id_user, start_time, status) 
             VALUES (:id_user, NOW(), 'open')"
        );
        $stmt->execute([
            ':id_user' => $userId
        ]);
    }

    /**
     * Mengakhiri shift yang sedang berjalan.
     */
    public function endShift(int $shiftId, string $notes = ''): void
    {
        $stmt = $this->db->prepare(
            "UPDATE shifts 
             SET end_time = NOW(), notes = :notes, status = 'closed'
             WHERE id_shift = :id AND status = 'open'"
        );
        $stmt->execute([
            ':notes'       => $notes,
            ':id'          => $shiftId
        ]);
    }

    /**
     * Mengambil data shift yang sedang aktif (open) untuk user tertentu.
     */
    public function getActiveShift(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM shifts WHERE id_user = :id_user AND status = 'open' LIMIT 1"
        );
        $stmt->execute([':id_user' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
}
