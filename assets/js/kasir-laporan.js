// Filter tabel berdasarkan metode pembayaran
document.addEventListener('DOMContentLoaded', function() {
    const paymentSelect = document.getElementById('payment-filter');
    const tableRows = document.querySelectorAll('tbody tr');
    const printBtn = document.getElementById('print-btn');

    paymentSelect.addEventListener('change', function() {
        const selectedMethod = this.value;

        tableRows.forEach(row => {
            const methodCell = row.querySelector('td:nth-child(4)'); // Kolom metode pembayaran
            const methodText = methodCell.textContent.trim();

            if (selectedMethod === 'semua') {
                // Tampilkan semua baris
                row.style.display = '';
            } else if (selectedMethod === 'tunai' && methodText === 'TUNAI') {
                // Tampilkan hanya TUNAI
                row.style.display = '';
            } else if (selectedMethod === 'qris' && methodText === 'QRIS') {
                // Tampilkan hanya QRIS
                row.style.display = '';
            } else {
                // Sembunyikan baris yang tidak sesuai
                row.style.display = 'none';
            }
        });
    });

    // Fungsi cetak laporan
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            // Tambahkan class print-mode sebelum print
            document.body.classList.add('print-mode');
            
            // Delay sedikit untuk memastikan CSS terupdate
            setTimeout(function() {
                window.print();
                // Hapus class setelah print dialog ditutup
                window.addEventListener('afterprint', function() {
                    document.body.classList.remove('print-mode');
                }, { once: true });
            }, 100);
        });
    }
});
