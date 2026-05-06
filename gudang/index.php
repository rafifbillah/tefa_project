<?php
require_once '../core/Auth.php';
Auth::requireRole('gudang');
/**
 * Dashboard Gudang — TEFA Bakery & Coffee
 */

$page        = 'dashboard';
$pageTitle   = 'Dashboard Inventori';

include 'includes/header.php';
?>
<section class="dashboard-page">
    
    <div class="summary-cards">
        <div class="card card-orange">
            <div class="card-title">Total Produk</div>
            <div class="card-value">5</div>
            <div class="card-subtitle">Barang terdaftar</div>
            <i class="fa-solid fa-box-open card-icon"></i>
        </div>
        <div class="card">
            <div class="card-title">Total Stok</div>
            <div class="card-value">160</div>
            <div class="card-subtitle success-text">+ 12% dari bulan lalu</div>
            <i class="fa-solid fa-arrow-up card-icon success-text"></i>
        </div>
        <div class="card card-pink">
            <div class="card-title">Expired Soon</div>
            <div class="card-value">0</div>
            <div class="card-subtitle text-pink">Dalam 7 hari</div>
            <i class="fa-solid fa-warning card-icon text-pink"></i>
        </div>
        <div class="card card-orange-light">
            <div class="card-title">Stok Menipis</div>
            <div class="card-value">2</div>
            <div class="card-subtitle text-orange">Perlu segera restok</div>
            <i class="fa-solid fa-arrow-trend-down card-icon text-orange"></i>
        </div>
    </div>

    <div class="graphs-row">
        <div class="graph-card">
            <h3 class="graph-title">Tren Stok Bulanan</h3>
            <div class="graph-container bar-chart">
                <div class="chart-y-axis">
                    <span>80</span>
                    <span>60</span>
                    <span>40</span>
                    <span>20</span>
                    <span>0</span>
                </div>
                <div class="chart-bars">
                    <div class="bar" style="height: 50%" data-value="40"><span class="label">Jan</span></div>
                    <div class="bar" style="height: 55%" data-value="44"><span class="label">Feb</span></div>
                    <div class="bar" style="height: 48%" data-value="38"><span class="label">Mar</span></div>
                    <div class="bar" style="height: 65%" data-value="52"><span class="label">Apr</span></div>
                    <div class="bar" style="height: 52%" data-value="42"><span class="label">May</span></div>
                    <div class="bar" style="height: 72%" data-value="58"><span class="label">Jun</span></div>
                </div>
            </div>
        </div>

        <div class="graph-card">
            <h3 class="graph-title">Distribusi Kategori</h3>
            <div class="graph-container donut-chart">
                <div class="donut-display" style="background: conic-gradient(var(--orange) 0% 45%, var(--orange-memek) 45% 75%, var(--grey-mid) 75% 100%);">
                    <div class="donut-center"></div>
                </div>
                <div class="donut-legend">
                    <div class="legend-item"><span class="dot" style="background-color: var(--orange);"></span><span class="label">Kopi</span><span class="value">45%</span></div>
                    <div class="legend-item"><span class="dot" style="background-color: var(--orange-memek);"></span><span class="label">Roti</span><span class="value">30%</span></div>
                    <div class="legend-item"><span class="dot" style="background-color: var(--grey-mid);"></span><span class="label">Kue</span><span class="value">25%</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="lists-row">
        <div class="list-card">
            <h3 class="list-title">Aktivitas Terbaru</h3>
            <ul class="activity-list">
                <li class="activity-item"><span class="activity-marker green"></span> Biji Kopi Espresso. Ditambahkan. <span class="time">2 jam yang lalu</span></li>
                <li class="activity-item"><span class="activity-marker orange"></span> Roti Tawar. Stok Menipis. <span class="time">5 jam yang lalu</span></li>
                <li class="activity-item"><span class="activity-marker blue"></span> Croissant. Diperbarui. <span class="time">1 hari yang lalu</span></li>
                <li class="activity-item"><span class="activity-marker red"></span> Bubuk Kopi. Hampir Habis. <span class="time">2 hari yang lalu</span></li>
            </ul>
        </div>

        <div class="list-card">
            <h3 class="list-title">Peringatan Stok Rendah</h3>
            <div class="warning-table">
                <table>
                    <tbody>
                        <tr>
                            <td>Roti Tawar</td>
                            <td class="category">Kategori: Roti</td>
                            <td class="stock-warning">5 unit</td>
                            <td class="min-stock">Min: 20</td>
                        </tr>
                        <tr>
                            <td>Roti Gembong</td>
                            <td class="category">Kategori: Roti</td>
                            <td class="stock-warning">10 unit</td>
                            <td class="min-stock">Min: 15</td>
                        </tr>
                        <tr>
                            <td>Croissant</td>
                            <td class="category">Kategori: Kue</td>
                            <td class="stock-warning">12 unit</td>
                            <td class="min-stock">Min: 20</td>
                        </tr>
                        <tr>
                            <td>Donat</td>
                            <td class="category">Kategori: Kue</td>
                            <td class="stock-warning">8 unit</td>
                            <td class="min-stock">Min: 15</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        /* Animasi Card Muncul / Refresh */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card, .graph-card, .list-card {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }
        
        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(4) { animation-delay: 0.4s; }
        .graphs-row .graph-card:nth-child(1) { animation-delay: 0.3s; }
        .graphs-row .graph-card:nth-child(2) { animation-delay: 0.4s; }
        .lists-row .list-card:nth-child(1) { animation-delay: 0.4s; }
        .lists-row .list-card:nth-child(2) { animation-delay: 0.5s; }

        /* Donut Chart Animation */
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.5); }
            to { opacity: 1; transform: scale(1); }
        }

        .donut-display {
            opacity: 0;
            animation: popIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            animation-delay: 0.6s;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const bars = document.querySelectorAll('.chart-bars .bar');
            
            const tooltip = document.createElement('div');
            tooltip.className = 'bar-tooltip';
            tooltip.style.position = 'absolute';
            tooltip.style.backgroundColor = '#333';
            tooltip.style.color = '#fff';
            tooltip.style.padding = '8px 12px';
            tooltip.style.borderRadius = '6px';
            tooltip.style.fontSize = '0.85rem';
            tooltip.style.fontWeight = 'bold';
            tooltip.style.pointerEvents = 'none';
            tooltip.style.opacity = '0';
            tooltip.style.transition = 'opacity 0.2s';
            tooltip.style.zIndex = '1000';
            tooltip.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
            document.body.appendChild(tooltip);

            bars.forEach(bar => {
                // Animasi saat load / refresh untuk Bar Chart
                const targetHeight = bar.style.height;
                bar.style.height = '0%';
                setTimeout(() => {
                    bar.style.transition = 'height 1s ease-out';
                    bar.style.height = targetHeight;
                }, 500);

                // Tooltip Informasinya (Bar Chart)
                bar.addEventListener('mouseenter', (e) => {
                    const value = bar.getAttribute('data-value') || '';
                    const label = bar.querySelector('.label').textContent || '';
                    tooltip.innerHTML = `<span style="color: #ffd166;">Bulan: ${label}</span><br/>Stok: ${value}`;
                    tooltip.style.opacity = '1';
                });

                bar.addEventListener('mousemove', (e) => {
                    tooltip.style.left = (e.pageX + 15) + 'px';
                    tooltip.style.top = (e.pageY - 40) + 'px';
                });

                bar.addEventListener('mouseleave', () => {
                    tooltip.style.opacity = '0';
                });
                
                // Hover effect scaling on bars
                bar.addEventListener('mouseover', () => {
                    bar.style.filter = 'brightness(1.1)';
                });
                bar.addEventListener('mouseout', () => {
                    bar.style.filter = 'brightness(1)';
                });
            });

            // Tooltip untuk Distribusi Kategori (Donut Chart)
            const donutDisplay = document.querySelector('.donut-display');
            if (donutDisplay) {
                donutDisplay.addEventListener('mousemove', (e) => {
                    const rect = donutDisplay.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;
                    const x = e.clientX - centerX;
                    const y = e.clientY - centerY;
                    
                    // Jarak kursor dari pusat (untuk menghindari tooltip muncul saat kursor di lubang tengah donut)
                    const distance = Math.sqrt(x*x + y*y);
                    const radius = rect.width / 2;
                    
                    // Jika kursor berada terlalu dekat dengan tengah (area lubang donut), sembunyikan tooltip
                    if (distance < radius * 0.4) {
                        tooltip.style.opacity = '0';
                        return;
                    }

                    // Menghitung derajat/sudut kursor (0 - 360), 0 derajat ada di arah jam 12
                    let angle = Math.atan2(y, x) * 180 / Math.PI;
                    angle += 90; // geser nol derajat ke atas
                    if (angle < 0) angle += 360;
                    
                    const pct = (angle / 360) * 100;

                    let label = '';
                    let value = '';
                    let color = '';

                        // Layout gradien berdasarkan style css:
                    // 0% - 45% Kopi (var(--orange))
                    // 45% - 75% Roti (var(--orange-mid))
                    // 75% - 100% Kue  (var(--grey-mid))
                    if (pct <= 45) {
                        label = 'Kopi'; value = '45%'; color = 'var(--orange)';
                    } else if (pct <= 75) {
                        label = 'Roti'; value = '30%'; color = 'var(--orange-mid)';
                    } else {
                        label = 'Kue'; value = '25%'; color = 'var(--grey-mid)';
                    }

                    tooltip.innerHTML = `<span style="font-size: 1.1em; color: ${color};">&#9679;</span> <span style="color: #fff;">${label}</span><br/><span style="color: #bbb; font-weight: normal; font-size:0.8rem;">Persentase:</span> ${value}`;
                    tooltip.style.opacity = '1';
                    tooltip.style.left = (e.pageX + 15) + 'px';
                    tooltip.style.top = (e.pageY - 40) + 'px';
                });

                donutDisplay.addEventListener('mouseleave', () => {
                    tooltip.style.opacity = '0';
                });
            }

            // Tambahkan animasi hover efek untuk semua card pendukung visual yg dinamis
            const cards = document.querySelectorAll('.card, .graph-card, .list-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-5px)';
                    card.style.boxShadow = '0 6px 12px rgba(0,0,0,0.1)';
                    card.style.transition = 'all 0.3s ease';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = '';
                    card.style.boxShadow = '';
                });
            });
        });
    </script>
</section>
<?php include 'includes/footer.php'; ?>