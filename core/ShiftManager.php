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
    public function startShift(int $userId, float $startingCash = 0): void
    {
        if ($this->getActiveShift($userId)) {
            // Sudah ada shift yang open, abaikan.
            return;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO shifts (user_id, start_time, starting_cash, status) 
             VALUES (:user_id, NOW(), :starting_cash, 'open')"
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':starting_cash' => $startingCash
        ]);
    }

    /**
     * Mengakhiri shift yang sedang berjalan.
     */
    public function endShift(int $shiftId, float $actualCash = 0, string $notes = ''): void
    {
        $stmt = $this->db->prepare(
            "UPDATE shifts 
             SET end_time = NOW(), actual_cash = :actual_cash, notes = :notes, status = 'closed'
             WHERE id = :id AND status = 'open'"
        );
        $stmt->execute([
            ':actual_cash' => $actualCash,
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
            "SELECT * FROM shifts WHERE user_id = :user_id AND status = 'open' LIMIT 1"
        );
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
}
