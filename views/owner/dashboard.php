<?php
/** @var string $tanggal */
/** @var array $transaksi */
/** @var int|float $omzet */
/** @var int|float $volume */
/** @var float $sisa_pagi */
/** @var float $sisa_sore */
/** @var int $total_piutang */
/** @var int $total_writeoff */
/** @var int $total_adjustment */
/** @var int $total_cicilan */
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

$periode = $periode ?? 'harian';
$labelPeriode = $labelPeriode ?? '';
$labelPeriodeLaporan = $labelPeriodeLaporan ?? '';
$omzetBersih = $omzetBersih ?? 0;

require_once 'views/templates/header.php';
?>

<!-- Top Action Bar & Filter Tanggal -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 bg-white p-3 rounded-3 shadow-sm border-start border-3 border-left-slate">
    <div>
        <h5 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
            <i class="fa-solid fa-chart-pie icon-slate"></i> 
            Ringkasan Kinerja Bisnis
            <span class="text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="Halaman ini menyajikan analisis keuangan, piutang, logistik, dan performa produk yang diperbarui secara otomatis.">
                <i class="fa-solid fa-circle-question text-muted help-icon"></i>
            </span>
        </h5>
        <small class="text-muted">Periode Laporan: <strong><span id="labelPeriodeLaporan"><?= $periode === 'harian' ? date('d F Y', strtotime($tanggal)) : ($periode === 'mingguan' ? '7 Hari Terakhir ('. $labelPeriode .')' : '30 Hari Terakhir ('. $labelPeriode .')') ?></span></strong></small>
    </div>
    <div>
        <form action="index.php" method="GET" class="d-flex align-items-center gap-2 m-0">
            <input type="hidden" name="page" value="dashboard">
            <input type="hidden" name="periode" value="<?= htmlspecialchars($periode) ?>">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-calendar-days"></i></span>
                <input type="date" class="form-control border-start-0 date-input-max" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Row 4 Kartu Metrik Utama -->
<div class="row mb-4">
    <!-- Kartu 1: Omzet Bersih -->
    <div class="col-12 col-md-6 col-xl-3 mb-3">
        <div class="card premium-card kpi-card kpi-card-net p-3 h-100 bg-white shadow-sm">
            <div class="card-body p-2 d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="p-3 rounded me-3 kpi-icon-net d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-wallet fa-xl"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small m-0 uppercase font-weight-bold text-uppercase-bold-compact">Pendapatan Bersih (<span id="labelPeriodeCard1"><?= $labelPeriode ?></span>)</h6>
                        <h4 class="font-weight-bold m-0 mt-1 text-sea-blue" id="valOmzetBersih">Rp <?= number_format($omzetBersih, 0, ',', '.') ?></h4>
                    </div>
                </div>
                <div class="kpi-formula-bar mt-2 small-text-muted-compact">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Penjualan Kotor:</span>
                        <strong class="text-dark" id="valOmzetKotor">Rp <?= number_format($omzet, 0, ',', '.') ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Potongan Harga:</span>
                        <strong class="text-danger" id="valTotalAdjustment">-Rp <?= number_format($total_adjustment ?? 0, 0, ',', '.') ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Pembayaran Bermasalah:</span>
                        <strong class="text-danger" id="valTotalWriteOff">-Rp <?= number_format($total_writeoff ?? 0, 0, ',', '.') ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu 2: Volume Terjual -->
    <div class="col-12 col-md-6 col-xl-3 mb-3">
        <div class="card premium-card kpi-card kpi-card-volume p-3 h-100 bg-white shadow-sm">
            <div class="card-body p-2 d-flex align-items-center h-100">
                <div class="p-3 rounded me-3 kpi-icon-volume d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-scale-balanced fa-xl"></i>
                </div>
                <div>
                    <h6 class="text-muted small m-0 uppercase font-weight-bold text-uppercase-bold-compact">Volume Terjual (<span id="labelPeriodeCard2"><?= $labelPeriode ?></span>)</h6>
                    <h4 class="font-weight-bold m-0 mt-1 text-sea-blue" id="valVolumeTerjual"><?= number_format($volume, 1) ?> Kg</h4>
                    <small class="text-muted">Ayam Fillet Terkirim</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu 3: Piutang Berjalan -->
    <div class="col-12 col-md-6 col-xl-3 mb-3">
        <div class="card premium-card kpi-card kpi-card-debt p-3 h-100 bg-white shadow-sm">
            <div class="card-body p-2 d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-center">
                    <div class="p-3 rounded me-3 kpi-icon-debt d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-hand-holding-dollar fa-xl"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small m-0 uppercase font-weight-bold text-uppercase-bold-compact">Piutang Berjalan (Total)</h6>
                        <h4 class="font-weight-bold m-0 mt-1 text-sea-blue" id="valTotalPiutang">Rp <?= number_format($total_piutang, 0, ',', '.') ?></h4>
                    </div>
                </div>
                <div class="border-top pt-2 mt-2 text-center">
                    <a href="index.php?page=hutang" class="text-orange-link small text-decoration-none font-weight-bold">
                        Buka Buku Piutang <i class="fa-solid fa-angle-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu 4: Status Operasional Logistik -->
    <div class="col-12 col-md-6 col-xl-3 mb-3">
        <div class="card premium-card kpi-card kpi-card-logistic p-3 h-100 bg-white shadow-sm">
            <div class="card-body p-2 d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-center">
                    <div class="p-3 rounded me-3 kpi-icon-logistic d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-truck-ramp-box fa-xl"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small m-0 uppercase font-weight-bold text-uppercase-bold-compact">Sisa Kapasitas Kirim (<span id="labelTanggalCard4"><?= date('d M', strtotime($tanggal)) ?></span>)</h6>
                        <h5 class="font-weight-bold m-0 mt-1 text-sea-blue" id="valSisaKirim">Pagi: <?= number_format($sisa_pagi, 1) ?> | Sore: <?= number_format($sisa_sore, 1) ?> Kg</h5>
                    </div>
                </div>
                <div class="border-top pt-2 mt-2 text-center text-muted small-text-center-muted">
                    <i class="fa-solid fa-info-circle me-1 text-purple"></i>Kapasitas maks 60 Kg per slot
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row Grafik Berdampingan (Grid 2 Kolom) -->
<div class="row mb-4">
    <!-- Kolom Kiri: Tren Omzet Penjualan (col-lg-8 / 70% width on large screens) -->
    <div class="col-lg-8 col-md-12 mb-4 mb-lg-0">
        <div class="card premium-card p-4 h-100 bg-white border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title font-weight-bold m-0 card-title-custom"><i class="fa-solid fa-chart-line text-orange me-2"></i>Tren Omzet (s/d <?= date('d M', strtotime($tanggal)) ?>)</h5>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Filter Periode Grafik">
                        <button type="button" class="btn btn-outline-primary <?= $periode === 'harian' ? 'active' : '' ?>" id="btnHarian">Harian</button>
                        <button type="button" class="btn btn-outline-primary <?= $periode === 'mingguan' ? 'active' : '' ?>" id="btnMingguan">Mingguan</button>
                        <button type="button" class="btn btn-outline-primary <?= $periode === 'bulanan' ? 'active' : '' ?>" id="btnBulanan">Bulanan</button>
                    </div>
                </div>
                <div class="canvas-container-omzet">
                    <canvas id="omzetChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Distribusi Produk (col-lg-4 / 30% width on large screens) -->
    <div class="col-lg-4 col-md-12">
        <div class="card premium-card p-4 h-100 bg-white border-0 shadow-sm">
            <div class="card-body p-0 d-flex flex-column justify-content-between h-100">
                <h5 class="card-title font-weight-bold mb-3 card-title-custom"><i class="fa-solid fa-chart-pie text-orange me-2"></i>Distribusi Produk (s/d <?= date('d M', strtotime($tanggal)) ?>)</h5>
                <div class="canvas-container-produk d-flex align-items-center justify-content-center">
                    <canvas id="produkChart"></canvas>
                </div>
                <div class="text-center mt-3 text-muted small small-text-center-muted">
                    <i class="fa-solid fa-lightbulb text-warning me-1"></i>Menunjukkan persentase berat produk terkirim
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Transaksi Harian -->
<?php require_once 'views/templates/notifications.php'; ?>
<div class="card premium-card p-4 mb-4 bg-white shadow-sm border-0" id="daftar-transaksi">
    <div class="card-body p-0">
        <h5 class="card-title font-weight-bold mb-4 d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-receipt text-orange me-2"></i>Daftar Transaksi Periode: <?= $labelPeriode ?></span>
            <span class="badge bg-primary"><?= count($transaksi) ?> Nota</span>
        </h5>

        <div class="table-responsive table-responsive-scroll">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. Nota</th>
                        <th class="d-none d-md-table-cell">Waktu</th>
                        <th>Pelanggan</th>
                        <th class="d-none d-md-table-cell">Slot</th>
                        <th class="d-none d-md-table-cell">Metode</th>
                        <th class="d-none d-md-table-cell">Berat (Kg)</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="transactionTableBody">
                    <!-- Rendered dynamically via JS -->
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center" id="transactionPagination">
                <!-- Rendered dynamically via JS -->
            </ul>
        </nav>
    </div>
</div>

<!-- Modal Detail Transaksi (Dynamic Single Modal) -->
<div class="modal fade" id="modalDetailDynamic" tabindex="-1" aria-labelledby="modalDetailDynamicLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="modalDetailDynamicLabel"><i class="fa-solid fa-circle-info text-sea-blue me-2"></i>Rincian Nota <span id="modalTxId">#0</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <p class="small text-muted mb-2">Kasir Input: <span id="modalKasirName">-</span></p>
                <div class="border rounded p-3 bg-light font-monospace small">
                    <strong>Item Pembelian:</strong>
                    <hr class="my-2">
                    <div id="modalItemsContainer">
                        <!-- Dynamic items -->
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between font-weight-bold">
                        <span>Total Berat</span>
                        <span id="modalTotalWeight">0.00 Kg</span>
                    </div>
                    <div class="d-flex justify-content-between font-weight-bold">
                        <span>Total Tagihan</span>
                        <span id="modalTotalAmount">Rp0</span>
                    </div>
                </div>
                
                <div class="mt-3">
                    <strong>Alamat Pengiriman:</strong>
                    <p class="small text-muted mb-0 mt-1" id="modalDeliveryAddress">-</p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Load ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const configDashboard = {
    tanggal: "<?= $tanggal ?>",
    periode: "<?= $periode ?>",
    todayDate: "<?= date('Y-m-d') ?>",
    transaksi: <?= json_encode($transaksi) ?>,
    harian: {
        labelsOmzet: [
            <?php foreach ($chart_data_harian as $cd) {
                $val = is_object($cd) ? $cd->label : $cd['label'];
                echo "'" . htmlspecialchars($val) . "',";
            } ?>
        ],
        dataOmzet: [
            <?php foreach ($chart_data_harian as $cd) {
                $val = is_object($cd) ? $cd->omzet : $cd['omzet'];
                echo $val . ",";
            } ?>
        ],
        labelsProduk: [
            <?php foreach ($product_harian as $pd) {
                $val = is_object($pd) ? $pd->nama_produk : $pd['nama_produk'];
                echo "'" . htmlspecialchars($val) . "',";
            } ?>
        ],
        dataProduk: [
            <?php foreach ($product_harian as $pd) {
                $val = is_object($pd) ? $pd->total_kg : $pd['total_kg'];
                echo number_format($val, 1, '.', '') . ",";
            } ?>
        ]
    },
    mingguan: {
        labelsOmzet: [
            <?php foreach ($chart_data_mingguan as $cd) {
                $val = is_object($cd) ? $cd->label : $cd['label'];
                echo "'" . htmlspecialchars($val) . "',";
            } ?>
        ],
        dataOmzet: [
            <?php foreach ($chart_data_mingguan as $cd) {
                $val = is_object($cd) ? $cd->omzet : $cd['omzet'];
                echo $val . ",";
            } ?>
        ],
        labelsProduk: [
            <?php foreach ($product_mingguan as $pd) {
                $val = is_object($pd) ? $pd->nama_produk : $pd['nama_produk'];
                echo "'" . htmlspecialchars($val) . "',";
            } ?>
        ],
        dataProduk: [
            <?php foreach ($product_mingguan as $pd) {
                $val = is_object($pd) ? $pd->total_kg : $pd['total_kg'];
                echo number_format($val, 1, '.', '') . ",";
            } ?>
        ]
    },
    bulanan: {
        labelsOmzet: [
            <?php foreach ($chart_data_bulanan as $cd) {
                $val = is_object($cd) ? $cd->label : $cd['label'];
                echo "'" . htmlspecialchars($val) . "',";
            } ?>
        ],
        dataOmzet: [
            <?php foreach ($chart_data_bulanan as $cd) {
                $val = is_object($cd) ? $cd->omzet : $cd['omzet'];
                echo $val . ",";
            } ?>
        ],
        labelsProduk: [
            <?php foreach ($product_bulanan as $pd) {
                $val = is_object($pd) ? $pd->nama_produk : $pd['nama_produk'];
                echo "'" . htmlspecialchars($val) . "',";
            } ?>
        ],
        dataProduk: [
            <?php foreach ($product_bulanan as $pd) {
                $val = is_object($pd) ? $pd->total_kg : $pd['total_kg'];
                echo number_format($val, 1, '.', '') . ",";
            } ?>
        ]
    }
};
</script>
<script src="assets/js/dashboard.js"></script>

<?php
require_once 'views/templates/footer.php';
?>
