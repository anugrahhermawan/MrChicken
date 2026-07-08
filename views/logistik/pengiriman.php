<?php
/** @var string $tanggal */
/** @var array $paged_pagi */
/** @var array $paged_sore */
/** @var int $page_pagi */
/** @var int $pages_pagi */
/** @var int $page_sore */
/** @var int $pages_sore */
/** @var int $total_pagi_count */
/** @var int $total_sore_count */
/** @var float $pagi_berat */
/** @var float $sore_berat */

require_once 'views/templates/header.php';

$persenPagi = ($pagi_berat / 60) * 100;
$persenSore = ($sore_berat / 60) * 100;
?>

<!-- Filter Tanggal Pengiriman -->
<div class="card premium-card p-4 mb-4">
    <div class="card-body">
        <form action="index.php" method="GET" class="row align-items-end">
            <input type="hidden" name="page" value="logistik">
            <div class="col-md-4 mb-3 mb-md-0">
                <label for="tanggal" class="form-label font-weight-bold">Tanggal Pengiriman</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-premium w-100"><i class="fa-solid fa-filter me-2"></i>Filter</button>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <span class="badge bg-secondary p-2 small"><i class="fa-solid fa-calendar me-1"></i> Hari ini: <?= date('d M Y') ?></span>
            </div>
        </form>
    </div>
<?php require_once 'views/templates/notifications.php'; ?>
<div class="row" id="logistik-board">
    <!-- Slot Pagi (Batas 60 Kg) -->
    <div class="col-lg-6 mb-4">
        <div class="card premium-card p-4 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title font-weight-bold m-0 text-warning">
                        <i class="fa-solid fa-cloud-sun fa-lg me-2"></i>Slot Pagi
                    </h5>
                    <span class="badge bg-light text-dark font-weight-bold"><?= number_format($pagi_berat, 2) ?> / 60 Kg</span>
                </div>

                <!-- Progress Bar Muatan -->
                <div class="progress mb-4" style="height: 10px; border-radius: 5px;">
                    <div class="progress-bar <?= $persenPagi > 90 ? 'bg-danger' : ($persenPagi > 70 ? 'bg-warning' : 'bg-success') ?>" 
                         role="progressbar" style="width: <?= min(100, $persenPagi) ?>%" 
                         aria-valuenow="<?= $pagi_berat ?>" aria-valuemin="0" aria-valuemax="60"></div>
                </div>

                <!-- Daftar Transaksi Pagi -->
                <div class="shipping-list">
                    <?php 
                    $hasPagi = !empty($paged_pagi);
                    if ($hasPagi):
                        foreach ($paged_pagi as $t): 
                            $items = $t->details;
                        ?>
                            <div class="border rounded-3 p-3 mb-3 shadow-sm position-relative" style="<?= $t->is_menggantung ? 'border-left: 4px solid #EF4444 !important; background-color: #fffafb;' : 'background-color: #ffffff;' ?>">
                                <?php if ($t->is_menggantung): ?>
                                    <div class="alert alert-danger p-2 small mb-2 d-flex align-items-center gap-2" style="border-radius: 8px;">
                                        <i class="fa-solid fa-triangle-exclamation text-danger fa-lg"></i>
                                        <div>
                                            <strong>⚠️ Transaksi Menggantung!</strong> (Tanggal: <?= date('d/m/Y', strtotime($t->tanggal)) ?>) - Segera Konfirmasi
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="font-weight-bold m-0"><?= htmlspecialchars($t->nama_pelanggan) ?></h6>
                                        <small class="text-muted"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($t->no_hp) ?></small>
                                    </div>
                                    <span class="badge <?= $t->status_pengiriman === 'Selesai' ? 'bg-success' : ($t->status_pengiriman === 'Pre-Order' ? 'bg-info' : 'bg-warning') ?>">
                                        <?= htmlspecialchars($t->status_pengiriman) ?>
                                    </span>
                                </div>
                                
                                <p class="small text-muted mb-2"><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($t->alamat) ?></p>
                                
                                <!-- Items list -->
                                <div class="p-2 bg-light rounded-2 mb-3">
                                    <?php foreach ($items as $it): ?>
                                        <div class="d-flex justify-content-between small">
                                            <span>- <?= htmlspecialchars($it->nama_produk) ?></span>
                                            <strong><?= number_format($it->jumlah_berat_kg, 2) ?> Kg</strong>
                                        </div>
                                    <?php endforeach; ?>
                                    <hr class="my-1">
                                    <div class="d-flex justify-content-between small font-weight-bold">
                                        <span>Total (Rp<?= number_format($t->total_harga, 0, ',', '.') ?>)</span>
                                        <span><?= number_format($t->total_berat_akumulatif, 2) ?> Kg</span>
                                    </div>
                                </div>

                                <!-- Konfirmasi button -->
                                <?php if ($t->status_pengiriman !== 'Selesai'): ?>
                                    <form action="index.php?page=logistik-konfirmasi" method="POST">
                                        <input type="hidden" name="id_transaksi" value="<?= $t->id_transaksi ?>">
                                        <button type="submit" class="btn btn-sm <?= $t->is_menggantung ? 'btn-outline-danger' : 'btn-outline-success' ?> w-100 py-2">
                                            <i class="fa-solid fa-circle-check me-1"></i>Konfirmasi Sampai (Selesai)
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="text-center text-success small font-weight-bold"><i class="fa-solid fa-truck-ramp-box me-1"></i>Pengiriman Selesai</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <!-- Pagination Controls Slot Pagi -->
                        <?php if ($pages_pagi > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination pagination-sm justify-content-center">
                                    <!-- Tombol Prev -->
                                    <li class="page-item <?= $page_pagi <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link text-orange-link" href="index.php?page=logistik&tanggal=<?= urlencode($tanggal) ?>&p_pagi=<?= $page_pagi - 1 ?>&p_sore=<?= $page_sore ?>#logistik-board" aria-label="Previous">
                                            <i class="fa-solid fa-angle-left"></i>
                                        </a>
                                    </li>
                                    
                                    <!-- Angka Halaman -->
                                    <?php for ($i = 1; $i <= $pages_pagi; $i++): ?>
                                        <li class="page-item <?= $i == $page_pagi ? 'active active-orange' : '' ?>">
                                            <a class="page-link" href="index.php?page=logistik&tanggal=<?= urlencode($tanggal) ?>&p_pagi=<?= $i ?>&p_sore=<?= $page_sore ?>#logistik-board"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <!-- Tombol Next -->
                                    <li class="page-item <?= $page_pagi >= $pages_pagi ? 'disabled' : '' ?>">
                                        <a class="page-link text-orange-link" href="index.php?page=logistik&tanggal=<?= urlencode($tanggal) ?>&p_pagi=<?= $page_pagi + 1 ?>&p_sore=<?= $page_sore ?>#logistik-board" aria-label="Next">
                                            <i class="fa-solid fa-angle-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-truck-pickup fa-3x mb-3 text-light"></i>
                            <p>Tidak ada pengiriman untuk Slot Pagi.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Slot Sore (Batas 60 Kg) -->
    <div class="col-lg-6 mb-4">
        <div class="card premium-card p-4 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title font-weight-bold m-0 text-info">
                        <i class="fa-solid fa-cloud-moon fa-lg me-2"></i>Slot Sore
                    </h5>
                    <span class="badge bg-light text-dark font-weight-bold"><?= number_format($sore_berat, 2) ?> / 60 Kg</span>
                </div>

                <!-- Progress Bar Muatan -->
                <div class="progress mb-4" style="height: 10px; border-radius: 5px;">
                    <div class="progress-bar <?= $persenSore > 90 ? 'bg-danger' : ($persenSore > 70 ? 'bg-warning' : 'bg-success') ?>" 
                         role="progressbar" style="width: <?= min(100, $persenSore) ?>%" 
                         aria-valuenow="<?= $sore_berat ?>" aria-valuemin="0" aria-valuemax="60"></div>
                </div>

                <!-- Daftar Transaksi Sore -->
                <div class="shipping-list">
                    <?php 
                    $hasSore = !empty($paged_sore);
                    if ($hasSore):
                        foreach ($paged_sore as $t): 
                            $items = $t->details;
                        ?>
                            <div class="border rounded-3 p-3 mb-3 shadow-sm position-relative" style="<?= $t->is_menggantung ? 'border-left: 4px solid #EF4444 !important; background-color: #fffafb;' : 'background-color: #ffffff;' ?>">
                                <?php if ($t->is_menggantung): ?>
                                    <div class="alert alert-danger p-2 small mb-2 d-flex align-items-center gap-2" style="border-radius: 8px;">
                                        <i class="fa-solid fa-triangle-exclamation text-danger fa-lg"></i>
                                        <div>
                                            <strong>⚠️ Transaksi Menggantung!</strong> (Tanggal: <?= date('d/m/Y', strtotime($t->tanggal)) ?>) - Segera Konfirmasi
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="font-weight-bold m-0"><?= htmlspecialchars($t->nama_pelanggan) ?></h6>
                                        <small class="text-muted"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($t->no_hp) ?></small>
                                    </div>
                                    <span class="badge <?= $t->status_pengiriman === 'Selesai' ? 'bg-success' : ($t->status_pengiriman === 'Pre-Order' ? 'bg-info' : 'bg-warning') ?>">
                                        <?= htmlspecialchars($t->status_pengiriman) ?>
                                    </span>
                                </div>
                                
                                <p class="small text-muted mb-2"><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($t->alamat) ?></p>
                                
                                <!-- Items list -->
                                <div class="p-2 bg-light rounded-2 mb-3">
                                    <?php foreach ($items as $it): ?>
                                        <div class="d-flex justify-content-between small">
                                            <span>- <?= htmlspecialchars($it->nama_produk) ?></span>
                                            <strong><?= number_format($it->jumlah_berat_kg, 2) ?> Kg</strong>
                                        </div>
                                    <?php endforeach; ?>
                                    <hr class="my-1">
                                    <div class="d-flex justify-content-between small font-weight-bold">
                                        <span>Total (Rp<?= number_format($t->total_harga, 0, ',', '.') ?>)</span>
                                        <span><?= number_format($t->total_berat_akumulatif, 2) ?> Kg</span>
                                    </div>
                                </div>

                                <!-- Konfirmasi button -->
                                <?php if ($t->status_pengiriman !== 'Selesai'): ?>
                                    <form action="index.php?page=logistik-konfirmasi" method="POST">
                                        <input type="hidden" name="id_transaksi" value="<?= $t->id_transaksi ?>">
                                        <button type="submit" class="btn btn-sm <?= $t->is_menggantung ? 'btn-outline-danger' : 'btn-outline-success' ?> w-100 py-2">
                                            <i class="fa-solid fa-circle-check me-1"></i>Konfirmasi Sampai (Selesai)
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="text-center text-success small font-weight-bold"><i class="fa-solid fa-truck-ramp-box me-1"></i>Pengiriman Selesai</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <!-- Pagination Controls Slot Sore -->
                        <?php if ($pages_sore > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination pagination-sm justify-content-center">
                                    <!-- Tombol Prev -->
                                    <li class="page-item <?= $page_sore <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link text-orange-link" href="index.php?page=logistik&tanggal=<?= urlencode($tanggal) ?>&p_pagi=<?= $page_pagi ?>&p_sore=<?= $page_sore - 1 ?>#logistik-board" aria-label="Previous">
                                            <i class="fa-solid fa-angle-left"></i>
                                        </a>
                                    </li>
                                    
                                    <!-- Angka Halaman -->
                                    <?php for ($i = 1; $i <= $pages_sore; $i++): ?>
                                        <li class="page-item <?= $i == $page_sore ? 'active active-orange' : '' ?>">
                                            <a class="page-link" href="index.php?page=logistik&tanggal=<?= urlencode($tanggal) ?>&p_pagi=<?= $page_pagi ?>&p_sore=<?= $i ?>#logistik-board"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <!-- Tombol Next -->
                                    <li class="page-item <?= $page_sore >= $pages_sore ? 'disabled' : '' ?>">
                                        <a class="page-link text-orange-link" href="index.php?page=logistik&tanggal=<?= urlencode($tanggal) ?>&p_pagi=<?= $page_pagi ?>&p_sore=<?= $page_sore + 1 ?>#logistik-board" aria-label="Next">
                                            <i class="fa-solid fa-angle-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-truck-pickup fa-3x mb-3 text-light"></i>
                            <p>Tidak ada pengiriman untuk Slot Sore.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'views/templates/footer.php';
?>
