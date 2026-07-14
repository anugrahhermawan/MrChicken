<?php
/** @var string $tanggal */
/** @var array $transaksi */
/** @var int|float $omzet */
/** @var int|float $volume */
/** @var float $sisa_pagi */
/** @var float $sisa_sore */
/** @var int $total_piutang */
/** @var array $chart_data_harian */
/** @var array $chart_data_mingguan */
/** @var array $chart_data_bulanan */
/** @var array $product_harian */
/** @var array $product_mingguan */
/** @var array $product_bulanan */
/** @var int $current_page */
/** @var int $total_pages */

$current_page = $current_page ?? 1;
$total_pages = $total_pages ?? 1;

$chart_data_harian = $chart_data_harian ?? [];
$chart_data_mingguan = $chart_data_mingguan ?? [];
$chart_data_bulanan = $chart_data_bulanan ?? [];
$product_harian = $product_harian ?? [];
$product_mingguan = $product_mingguan ?? [];
$product_bulanan = $product_bulanan ?? [];

require_once 'views/templates/header.php';
?>

<!-- Metrik Dashboard Row -->
<div class="row mb-4">
    <!-- Omzet Card -->
    <div class="col-12 col-sm-6 col-xl-3 mb-3">
        <div class="card premium-card p-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="me-3 p-3 rounded-3 text-primary" style="background-color: #ebf8ff;">
                    <i class="fa-solid fa-money-bill-trend-up fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted small m-0 uppercase font-weight-bold">Omzet Hari Ini</h6>
                    <h4 class="font-weight-bold m-0 mt-1">Rp <?= number_format($omzet, 0, ',', '.') ?></h4>
                    <small class="text-success"><i class="fa-solid fa-check-double me-1"></i>Status Selesai</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Volume Card -->
    <div class="col-12 col-sm-6 col-xl-3 mb-3">
        <div class="card premium-card p-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="me-3 p-3 rounded-3 text-primary" style="background-color: #ebf8ff;">
                    <i class="fa-solid fa-scale-balanced fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted small m-0 uppercase font-weight-bold">Volume Terjual</h6>
                    <h4 class="font-weight-bold m-0 mt-1"><?= number_format($volume, 1) ?> Kg</h4>
                    <small class="text-muted">Ayam Fillet Terkirim</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Sisa Slot Card -->
    <div class="col-12 col-sm-6 col-xl-3 mb-3">
        <div class="card premium-card p-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="me-3 p-3 rounded-3 text-primary" style="background-color: #ebf8ff;">
                    <i class="fa-solid fa-truck-ramp-box fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted small m-0 uppercase font-weight-bold">Sisa Slot Pengiriman</h6>
                    <h5 class="font-weight-bold m-0 mt-1" style="font-size: 1.1rem;">P: <?= number_format($sisa_pagi, 1) ?> | S: <?= number_format($sisa_sore, 1) ?> Kg</h5>
                    <small class="text-muted">Kapasitas Aman</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Piutang Card -->
    <div class="col-12 col-sm-6 col-xl-3 mb-3">
        <div class="card premium-card p-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="me-3 p-3 rounded-3 text-primary" style="background-color: #ebf8ff;">
                    <i class="fa-solid fa-hand-holding-dollar fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted small m-0 uppercase font-weight-bold">Total Piutang Aktif</h6>
                    <h4 class="font-weight-bold m-0 mt-1">Rp <?= number_format($total_piutang, 0, ',', '.') ?></h4>
                    <a href="index.php?page=hutang" class="text-primary small text-decoration-none font-weight-bold">Buku Hutang <i class="fa-solid fa-angle-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Laporan Grafik & Filter Tanggal -->
<div class="row mb-4">
    <!-- Trend Chart -->
    <div class="col-lg-8 mb-4 mb-lg-0">
        <div class="card premium-card p-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title font-weight-bold m-0"><i class="fa-solid fa-chart-line text-orange me-2"></i>Tren Omzet Penjualan</h5>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Filter Periode Grafik">
                        <button type="button" class="btn btn-outline-primary active" id="btnHarian">Harian</button>
                        <button type="button" class="btn btn-outline-primary" id="btnMingguan">Mingguan</button>
                        <button type="button" class="btn btn-outline-primary" id="btnBulanan">Bulanan</button>
                    </div>
                </div>
                <div style="position: relative; height:280px; width:100%">
                    <canvas id="omzetChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Harian -->
    <div class="col-lg-4">
        <div class="card premium-card p-4 h-100">
            <div class="card-body">
                <h5 class="card-title font-weight-bold mb-3"><i class="fa-solid fa-calendar-days text-orange me-2"></i>Filter Harian</h5>
                <form action="index.php" method="GET">
                    <input type="hidden" name="page" value="dashboard">
                    <div class="mb-3">
                        <label for="tanggal" class="form-label small text-muted">Pilih Tanggal Laporan</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                    </div>
                    <button type="submit" class="btn btn-premium w-100"><i class="fa-solid fa-magnifying-glass me-1"></i>Tampilkan Data</button>
                </form>
                <div class="mt-4 p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center gap-2 mb-2 text-primary">
                        <i class="fa-solid fa-shield-halved fa-lg"></i>
                        <strong class="small">Pembekuan Rekap Harian</strong>
                    </div>
                    <p class="small text-muted m-0">Sistem otomatis mengunci rekapitulasi data setiap akhir hari untuk mencegah manipulasi data transaksi yang telah diselesaikan.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Laporan Grafik Distribusi Produk -->
<div class="row mb-4">
    <!-- Distribusi Penjualan Produk Chart -->
    <div class="col-lg-8 mb-4 mb-lg-0">
        <div class="card premium-card p-4">
            <div class="card-body">
                <h5 class="card-title font-weight-bold mb-4"><i class="fa-solid fa-chart-pie text-orange me-2"></i>Distribusi Penjualan Produk (Total Kilogram)</h5>
                <div style="position: relative; height:280px; width:100%">
                    <canvas id="produkChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Info Analisis Kategori -->
    <div class="col-lg-4">
        <div class="card premium-card p-4 h-100 bg-light border-0" style="background-color: #f8fafc !important;">
            <div class="card-body d-flex flex-column justify-content-center">
                <h6 class="font-weight-bold text-primary mb-3" style="color: #3e506e !important;"><i class="fa-solid fa-lightbulb me-1"></i>Analisis Kategori</h6>
                <p class="small text-muted mb-0">Grafik di samping menampilkan total berat ayam riil (dalam Kg) yang dikirim dan terselesaikan per kategori produk. Fitur ini membantu Owner mengidentifikasi produk yang paling diminati untuk perencanaan restock berikutnya.</p>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Transaksi & Koreksi -->
<?php require_once 'views/templates/notifications.php'; ?>
<div class="card premium-card p-4 mb-4" id="daftar-transaksi">
    <div class="card-body">
        <h5 class="card-title font-weight-bold mb-4 d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-receipt text-orange me-2"></i>Daftar Transaksi Tanggal: <?= date('d M Y', strtotime($tanggal)) ?></span>
            <span class="badge bg-primary"><?= count($transaksi) ?> Nota</span>
        </h5>

        <div class="table-responsive table-responsive-scroll">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. Nota</th>
                        <th>Waktu</th>
                        <th>Pelanggan</th>
                        <th>Slot</th>
                        <th>Metode</th>
                        <th>Berat (Kg)</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th class="text-center">Aksi (Owner Only)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transaksi)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-receipt fa-3x mb-3 text-light"></i>
                                <p>Tidak ada transaksi pada tanggal ini.</p>
                            </td>
                        </tr>
                    <?php else: 
                        foreach ($transaksi as $t): 
                        ?>
                            <tr>
                                <td class="font-monospace font-weight-bold">#<?= $t->id_transaksi ?></td>
                                <td><?= date('H:i', strtotime($t->waktu)) ?> WIB</td>
                                <td>
                                    <strong><?= htmlspecialchars($t->nama_pelanggan) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($t->no_hp) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?= $t->slot_waktu === 'Pagi' ? '<i class="fa-solid fa-cloud-sun text-warning me-1"></i>' : '<i class="fa-solid fa-cloud-moon text-info me-1"></i>' ?>
                                        <?= $t->slot_waktu ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $t->metode_pembayaran === 'Lunas' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $t->metode_pembayaran ?>
                                    </span>
                                </td>
                                <td class="font-weight-bold"><?= number_format($t->total_berat_akumulatif, 2) ?> Kg</td>
                                <td class="font-weight-bold">Rp <?= number_format($t->total_harga, 0, ',', '.') ?></td>
                                <td>
                                    <span class="badge <?= $t->status_pengiriman === 'Selesai' ? 'bg-success' : ($t->status_pengiriman === 'Pre-Order' ? 'bg-info' : 'bg-warning') ?>">
                                        <?= $t->status_pengiriman ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <!-- Detail Modal Trigger -->
                                    <button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $t->id_transaksi ?>">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </button>
                                    
                                    <!-- Koreksi/Hapus Form -->
                                     <?php if ($tanggal >= date('Y-m-d')): ?>
                                         <form action="index.php?page=transaksi-koreksi" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi #<?= $t->id_transaksi ?>? Tindakan ini akan mengembalikan stok & membatalkan hutang terkait.');">
                                             <input type="hidden" name="id_transaksi" value="<?= $t->id_transaksi ?>">
                                             <button type="submit" class="btn btn-sm btn-outline-danger">
                                                 <i class="fa-solid fa-triangle-exclamation me-1"></i> Koreksi / Batal
                                              </button>
                                          </form>
                                     <?php else: ?>
                                         <button class="btn btn-sm btn-outline-secondary" disabled title="Data hari lampau telah dibekukan (Read-Only)">
                                             <i class="fa-solid fa-lock me-1"></i> Terkunci
                                         </button>
                                     <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Tombol Prev -->
                    <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link text-orange-link" href="index.php?page=dashboard&tanggal=<?= urlencode($tanggal) ?>&p=<?= $current_page - 1 ?>#daftar-transaksi" aria-label="Previous">
                            <i class="fa-solid fa-angle-left"></i>
                        </a>
                    </li>
                    
                    <!-- Angka Halaman -->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $current_page ? 'active active-orange' : '' ?>">
                            <a class="page-link" href="index.php?page=dashboard&tanggal=<?= urlencode($tanggal) ?>&p=<?= $i ?>#daftar-transaksi"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Tombol Next -->
                    <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link text-orange-link" href="index.php?page=dashboard&tanggal=<?= urlencode($tanggal) ?>&p=<?= $current_page + 1 ?>#daftar-transaksi" aria-label="Next">
                            <i class="fa-solid fa-angle-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Detail Transaksi (Dipindahkan ke luar tabel untuk mencegah visual glitch) -->
<?php if (!empty($transaksi)): ?>
    <?php foreach ($transaksi as $t): 
        $items = $t->details;
    ?>
        <div class="modal fade" id="modalDetail<?= $t->id_transaksi ?>" tabindex="-1" aria-labelledby="modalDetailLabel<?= $t->id_transaksi ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title font-weight-bold" id="modalDetailLabel<?= $t->id_transaksi ?>"><i class="fa-solid fa-circle-info text-primary me-2"></i>Rincian Nota #<?= $t->id_transaksi ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <p class="small text-muted mb-2">Kasir Input: <?= htmlspecialchars($t->nama_pengguna) ?></p>
                        <div class="border rounded p-3 bg-light font-monospace small">
                            <strong>Item Pembelian:</strong>
                            <hr class="my-2">
                            <?php foreach ($items as $it): ?>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>- <?= htmlspecialchars($it->nama_produk) ?></span>
                                    <span><?= number_format($it->jumlah_berat_kg, 2) ?> Kg x Rp<?= number_format($it->harga_satuan) ?> = Rp<?= number_format($it->subtotal) ?></span>
                                </div>
                            <?php endforeach; ?>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between font-weight-bold">
                                <span>Total Berat</span>
                                <span><?= number_format($t->total_berat_akumulatif, 2) ?> Kg</span>
                            </div>
                            <div class="d-flex justify-content-between font-weight-bold">
                                <span>Total Tagihan</span>
                                <span>Rp<?= number_format($t->total_harga) ?></span>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <strong>Alamat Pengiriman:</strong>
                            <p class="small text-muted mb-0 mt-1"><?= htmlspecialchars($t->alamat) ?></p>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Load ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // === 1. PRE-LOAD DATA PERIODS (Harian, Mingguan, Bulanan) ===
    
    // Harian Data
    const labelsOmzetHarian = [
        <?php foreach ($chart_data_harian as $cd) {
            $val = is_object($cd) ? $cd->tanggal : $cd['tanggal'];
            echo "'" . date('d M', strtotime($val)) . "',";
        } ?>
    ];
    const dataOmzetHarian = [
        <?php foreach ($chart_data_harian as $cd) {
            $val = is_object($cd) ? $cd->omzet : $cd['omzet'];
            echo $val . ",";
        } ?>
    ];
    const labelsProdukHarian = [
        <?php foreach ($product_harian as $pd) {
            $val = is_object($pd) ? $pd->nama_produk : $pd['nama_produk'];
            echo "'" . htmlspecialchars($val) . "',";
        } ?>
    ];
    const dataProdukHarian = [
        <?php foreach ($product_harian as $pd) {
            $val = is_object($pd) ? $pd->total_kg : $pd['total_kg'];
            echo number_format($val, 1, '.', '') . ",";
        } ?>
    ];

    // Mingguan Data
    const labelsOmzetMingguan = [
        <?php foreach ($chart_data_mingguan as $cd) {
            $val = is_object($cd) ? $cd->label : $cd['label'];
            echo "'" . htmlspecialchars($val) . "',";
        } ?>
    ];
    const dataOmzetMingguan = [
        <?php foreach ($chart_data_mingguan as $cd) {
            $val = is_object($cd) ? $cd->omzet : $cd['omzet'];
            echo $val . ",";
        } ?>
    ];
    const labelsProdukMingguan = [
        <?php foreach ($product_mingguan as $pd) {
            $val = is_object($pd) ? $pd->nama_produk : $pd['nama_produk'];
            echo "'" . htmlspecialchars($val) . "',";
        } ?>
    ];
    const dataProdukMingguan = [
        <?php foreach ($product_mingguan as $pd) {
            $val = is_object($pd) ? $pd->total_kg : $pd['total_kg'];
            echo number_format($val, 1, '.', '') . ",";
        } ?>
    ];

    // Bulanan Data
    const labelsOmzetBulanan = [
        <?php foreach ($chart_data_bulanan as $cd) {
            $val = is_object($cd) ? $cd->label : $cd['label'];
            echo "'" . htmlspecialchars($val) . "',";
        } ?>
    ];
    const dataOmzetBulanan = [
        <?php foreach ($chart_data_bulanan as $cd) {
            $val = is_object($cd) ? $cd->omzet : $cd['omzet'];
            echo $val . ",";
        } ?>
    ];
    const labelsProdukBulanan = [
        <?php foreach ($product_bulanan as $pd) {
            $val = is_object($pd) ? $pd->nama_produk : $pd['nama_produk'];
            echo "'" . htmlspecialchars($val) . "',";
        } ?>
    ];
    const dataProdukBulanan = [
        <?php foreach ($product_bulanan as $pd) {
            $val = is_object($pd) ? $pd->total_kg : $pd['total_kg'];
            echo number_format($val, 1, '.', '') . ",";
        } ?>
    ];

    // === 2. INITIALIZE CHARTS ===

    // Omzet Chart (Line)
    const ctxOmzet = document.getElementById('omzetChart').getContext('2d');
    const omzetChart = new Chart(ctxOmzet, {
        type: 'line',
        data: {
            labels: labelsOmzetHarian,
            datasets: [{
                label: 'Omzet Penjualan (Rp)',
                data: dataOmzetHarian,
                borderColor: '#3e506e',
                backgroundColor: 'rgba(62, 80, 110, 0.05)',
                borderWidth: 2.5,
                tension: 0.35,
                fill: true,
                pointBackgroundColor: '#3e506e',
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.04)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Produk Chart (Bar)
    const ctxProduk = document.getElementById('produkChart').getContext('2d');
    const produkChart = new Chart(ctxProduk, {
        type: 'bar',
        data: {
            labels: labelsProdukHarian,
            datasets: [{
                label: 'Penjualan (Kg)',
                data: dataProdukHarian,
                backgroundColor: [
                    '#3e506e',
                    '#546a90',
                    '#6c85b2',
                    '#8fa7d4',
                    '#b5c8ed',
                    '#dce6f7'
                ],
                borderColor: '#ffffff',
                borderWidth: 1.5,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.04)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + ' Kg';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // === 3. FILTER TOGGLE LOGIC ===

    const btnHarian = document.getElementById('btnHarian');
    const btnMingguan = document.getElementById('btnMingguan');
    const btnBulanan = document.getElementById('btnBulanan');

    function updateCharts(labelsO, dataO, labelsP, dataP) {
        // Update Omzet Chart
        omzetChart.data.labels = labelsO;
        omzetChart.data.datasets[0].data = dataO;
        omzetChart.update();

        // Update Produk Chart
        produkChart.data.labels = labelsP;
        produkChart.data.datasets[0].data = dataP;
        produkChart.update();
    }

    btnHarian.addEventListener('click', function() {
        btnHarian.classList.add('active');
        btnMingguan.classList.remove('active');
        btnBulanan.classList.remove('active');
        updateCharts(labelsOmzetHarian, dataOmzetHarian, labelsProdukHarian, dataProdukHarian);
    });

    btnMingguan.addEventListener('click', function() {
        btnHarian.classList.remove('active');
        btnMingguan.classList.add('active');
        btnBulanan.classList.remove('active');
        updateCharts(labelsOmzetMingguan, dataOmzetMingguan, labelsProdukMingguan, dataProdukMingguan);
    });

    btnBulanan.addEventListener('click', function() {
        btnHarian.classList.remove('active');
        btnMingguan.classList.remove('active');
        btnBulanan.classList.add('active');
        updateCharts(labelsOmzetBulanan, dataOmzetBulanan, labelsProdukBulanan, dataProdukBulanan);
    });
});
</script>

<?php
require_once 'views/templates/footer.php';
?>
