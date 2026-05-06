<?php
/**
 * Flash Message Helper — TEFA
 * ----------------------------
 * Menyimpan pesan sementara di session yang tampil SEKALI
 * lalu otomatis dihapus (one-time display / flash pattern).
 *
 * Jenis flash: 'success', 'error', 'warning', 'info'
 *
 * Cara pakai:
 *   Flash::set('error', 'Username atau password salah!');
 *   Flash::get(); // kembalikan array semua flash, lalu hapus
 */
class Flash
{
    private const KEY = '_flash';

    /**
     * Simpan pesan flash ke session.
     *
     * @param string $type    Tipe: 'success'|'error'|'warning'|'info'
     * @param string $message Pesan yang ditampilkan
     */
    public static function set(string $type, string $message): void
    {
        // ─── SECURITY: Escape pesan sebelum disimpan ──────────────────
        $_SESSION[self::KEY][] = [
            'type'    => htmlspecialchars($type, ENT_QUOTES, 'UTF-8'),
            'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
        ];
    }

    /**
     * Ambil semua pesan flash, lalu hapus dari session.
     *
     * @return array<int, array{type: string, message: string}>
     */
    public static function get(): array
    {
        $messages = $_SESSION[self::KEY] ?? [];
        unset($_SESSION[self::KEY]);
        return $messages;
    }

    /**
     * Cek apakah ada flash message tersedia.
     */
    public static function has(): bool
    {
        return !empty($_SESSION[self::KEY]);
    }

    /**
     * Render semua flash message sebagai HTML.
     * Siap dipakai langsung di view.
     */
    public static function render(): string
    {
        $messages = self::get();
        if (empty($messages)) {
            return '';
        }

        $icons = [
            'success' => 'fa-circle-check',
            'error'   => 'fa-circle-xmark',
            'warning' => 'fa-triangle-exclamation',
            'info'    => 'fa-circle-info',
        ];

        $html = '<div class="flash-messages" role="alert" aria-live="polite">';
        foreach ((array)$messages as $msg) {
            if (is_array($msg) && isset($msg['type'], $msg['message'])) {
                $type = $msg['type'];
                $message = $msg['message']; // sudah di-escape saat set()
            } else {
                $type = 'info';
                $message = htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8');
            }
            $icon = $icons[$type] ?? 'fa-circle-info';
            $html .= sprintf(
                '<div class="alert alert-%s"><i class="fas %s"></i> %s</div>',
                htmlspecialchars($type, ENT_QUOTES),
                $icon,
                $message
            );
        }
        $html .= '</div>';

        return $html;
    }
}
