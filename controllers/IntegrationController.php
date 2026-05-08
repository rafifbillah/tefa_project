<?php
require_once __DIR__ . '/../config/IntegrationConfig.php';
require_once __DIR__ . '/../models/LaporanModel.php';

class IntegrationController {
    private $laporanModel;

    public function __construct() {
        $this->laporanModel = new LaporanModel();
    }

    public function sendToGoogleSheets($transactionId) {
        // Ambil data transaksi
        $sql = "SELECT t.*, u.nama_lengkap as kasir_nama 
                FROM transactions t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE t.id = :id";
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $transactionId]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$transaction) {
            return ['success' => false, 'message' => 'Transaksi tidak ditemukan.'];
        }

        // Ambil detail produk
        $details = $this->laporanModel->getTransactionDetails($transactionId);
        
        $itemStrings = [];
        $totalQty = 0;
        foreach ($details as $item) {
            $itemStrings[] = $item['jumlah'] . 'x ' . $item['nama_produk'];
            $totalQty += $item['jumlah'];
        }
        $itemsJoined = implode(", ", $itemStrings);

        // Siapkan payload SESUAI DENGAN SCRIPT GOOGLE SHEETS MILIK USER
        $payload = [
            'key' => IntegrationConfig::API_KEY, // harus match dengan var secretKey
            'id_transaksi' => $transaction['transaction_id'],
            'tanggal' => $transaction['created_at'],
            'nama_kasir' => $transaction['kasir_nama'] ?? 'System',
            'total_item' => $itemsJoined, // Mengirimkan rincian item atau total qty
            'total_bayar' => $transaction['total_harga']
        ];

        // Setup cURL
        $ch = curl_init(IntegrationConfig::GOOGLE_SHEETS_WEBHOOK_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        // Nonaktifkan verifikasi SSL untuk localhost jika bermasalah, di server produksi ini harus diaktifkan (true)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        // Mengikuti redirect, karena Google script sering melempar status 302
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => 'cURL Error: ' . $error];
        }

        // Script User mengembalikan text murni "Success" atau "Unauthorized" atau "Error: ..."
        $responseText = trim($response);
        
        if ($httpCode === 200 || $httpCode === 302) {
            if ($responseText === 'Success') {
                return ['success' => true, 'message' => 'Berhasil mengirim ke Google Sheets.'];
            }
        }

        $rawPreview = substr($response, 0, 150);
        return [
            'success' => false, 
            'message' => 'Gagal dari API. HTTP: ' . $httpCode . ' | Raw: ' . htmlspecialchars($rawPreview)
        ];
    }

    public function syncBatchToSheets($userId, $tanggal) {
        $transactions = $this->laporanModel->getDetailedBatchData($userId, $tanggal);
        
        if (empty($transactions)) {
            return ['success' => false, 'message' => 'Tidak ada transaksi untuk disinkronisasi.'];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT nama_lengkap FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $kasir = $stmt->fetchColumn() ?: 'System';

        $data_transaksi = [];
        foreach ($transactions as $trx) {
            $items = [];
            foreach ($trx['items'] as $item) {
                $items[] = [
                    'nama_produk' => $item['nama_produk'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $item['jumlah'] * $item['harga_satuan']
                ];
            }
            $data_transaksi[] = [
                'id_transaksi' => $trx['transaction_id'],
                'total_bayar' => $trx['total_harga'],
                'items' => $items
            ];
        }

        $payload = [
            'secret_key' => IntegrationConfig::API_KEY,
            'tanggal_rekap' => $tanggal,
            'nama_kasir' => $kasir,
            'data_transaksi' => $data_transaksi
        ];

        $ch = curl_init(IntegrationConfig::GOOGLE_SHEETS_WEBHOOK_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => 'cURL Error: ' . $error];
        }

        $responseText = trim($response);
        
        if (($httpCode === 200 || $httpCode === 302) && $responseText === 'Success') {
            $this->laporanModel->syncBatch($userId, $tanggal);
            return ['success' => true, 'message' => 'Berhasil mengirim ke Google Sheets. Data ditandai sebagai Terkirim.'];
        }

        $rawPreview = substr($response, 0, 150);
        return [
            'success' => false, 
            'message' => 'Gagal dari API. HTTP: ' . $httpCode . ' | Raw: ' . htmlspecialchars($rawPreview)
        ];
    }
}
