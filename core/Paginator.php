<?php
/**
 * Paginator - Komponen pagination modular dan reusable.
 * 
 * Cara pakai:
 *   $paginator = new Paginator($dataArray, $limit, $paramName);
 *   $items = $paginator->getItems();   // Data untuk halaman aktif
 *   // ... render tabel ...
 *   echo $paginator->render($baseUrl); // Render UI pagination
 * 
 * @author TEFA Bakery & Coffee Dev Team
 */
class Paginator
{
    private int $totalItems;
    private int $totalPages;
    private int $currentPage;
    private int $limit;
    private int $offset;
    private array $items;
    private string $paramName;

    /**
     * @param array  $data      Seluruh data (array) yang akan dipaginasi
     * @param int    $limit     Jumlah item per halaman (default: 5)
     * @param string $paramName Nama query parameter di URL (default: 'p')
     */
    public function __construct(array $data, int $limit = 5, string $paramName = 'p')
    {
        $this->paramName  = $paramName;
        $this->limit      = max(1, $limit); // Minimal 1 per halaman
        $this->totalItems = count($data);
        $this->totalPages = (int) ceil($this->totalItems / $this->limit);

        // Ambil current page dari query string, sanitasi
        $this->currentPage = isset($_GET[$paramName]) ? (int) $_GET[$paramName] : 1;
        if ($this->currentPage < 1) $this->currentPage = 1;
        if ($this->currentPage > $this->totalPages && $this->totalPages > 0) {
            $this->currentPage = $this->totalPages;
        }

        $this->offset = ($this->currentPage - 1) * $this->limit;
        $this->items  = array_slice($data, $this->offset, $this->limit);
    }

    /** Data yang sudah di-slice untuk halaman aktif */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getCurrentPage(): int  { return $this->currentPage; }
    public function getTotalPages(): int   { return $this->totalPages; }
    public function getTotalItems(): int   { return $this->totalItems; }
    public function getLimit(): int        { return $this->limit; }
    public function getOffset(): int       { return $this->offset; }

    /**
     * Render HTML pagination UI.
     * 
     * @param string $baseUrl   URL dasar halaman (misal: 'barang.php')
     * @param string $label     Label satuan item (misal: 'barang', 'transaksi')
     * @return string           HTML string yang siap di-echo
     */
    public function render(string $baseUrl, string $label = 'barang'): string
    {
        $startItem = $this->totalItems > 0 ? $this->offset + 1 : 0;
        $endItem   = min($this->offset + $this->limit, $this->totalItems);

        // Bangun URL dengan query string yang sudah ada (jaga parameter lain)
        $buildUrl = function (int $page) use ($baseUrl): string {
            $params = $_GET; // Pertahankan query parameter lain (filter, search, dll)
            $params[$this->paramName] = $page;
            $query = http_build_query($params);
            return htmlspecialchars($baseUrl . '?' . $query);
        };

        // --- Prev Button ---
        $prevDisabled = ($this->currentPage <= 1);
        $prevAttr = $prevDisabled
            ? 'style="opacity: 0.5; cursor: default;"'
            : 'onclick="window.location.href=\'' . $buildUrl($this->currentPage - 1) . '\'"';

        // --- Next Button ---
        $nextDisabled = ($this->currentPage >= $this->totalPages);
        $nextAttr = $nextDisabled
            ? 'style="opacity: 0.5; cursor: default;"'
            : 'onclick="window.location.href=\'' . $buildUrl($this->currentPage + 1) . '\'"';

        // --- Page Numbers ---
        $pageNumbers = $this->buildPageNumbers();

        $html  = '<div class="pagination">';
        $html .= '<span class="pagination-info">Menampilkan ' . $startItem . '-' . $endItem . ' dari ' . $this->totalItems . ' ' . htmlspecialchars($label) . '</span>';
        $html .= '<button class="page-prev" ' . $prevAttr . '><i class="fa-solid fa-chevron-left"></i></button>';

        foreach ($pageNumbers as $p) {
            if ($p === '...') {
                $html .= '<span class="page-ellipsis">...</span>';
            } else {
                $activeClass = ($p === $this->currentPage) ? ' active' : '';
                $html .= '<button class="page-num' . $activeClass . '" onclick="window.location.href=\'' . $buildUrl($p) . '\'">' . $p . '</button>';
            }
        }

        $html .= '<button class="page-next" ' . $nextAttr . '><i class="fa-solid fa-chevron-right"></i></button>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Bangun array nomor halaman dengan ellipsis (...) untuk jumlah halaman banyak.
     * Contoh: [1, 2, '...', 5, 6, 7, '...', 10]
     */
    private function buildPageNumbers(): array
    {
        if ($this->totalPages <= 7) {
            return range(1, max(1, $this->totalPages));
        }

        $pages = [1]; // Selalu tampilkan halaman pertama

        if ($this->currentPage > 3) {
            $pages[] = '...';
        }

        // Halaman sekitar current page
        $start = max(2, $this->currentPage - 1);
        $end   = min($this->totalPages - 1, $this->currentPage + 1);

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        if ($this->currentPage < $this->totalPages - 2) {
            $pages[] = '...';
        }

        $pages[] = $this->totalPages; // Selalu tampilkan halaman terakhir

        return $pages;
    }
}
?>