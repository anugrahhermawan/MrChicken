<?php
// views/owner/partials/_tab_piutang_aktif.php
/** @var array $pelanggan_hutang */
/** @var array $hutang_aktif */
/** @var int $total_pages */
/** @var int $current_page */
?>
<div class="tab-pane fade show active" id="tab-piutang" role="tabpanel" aria-labelledby="ar-piutang-tab">
    <!-- Mobile Sub-Tab Switcher for Ledger (Visible only on mobile < 992px) -->
    <div class="d-lg-none mb-3">
        <ul class="nav nav-pills nav-fill bg-white p-1 rounded-3 shadow-sm border border-slate-light" id="ledgerMobileTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active font-weight-bold" id="rekap-tab" data-bs-toggle="tab" data-bs-target="#ledger-rekap-pane" type="button" role="tab" aria-controls="ledger-rekap-pane" aria-selected="true">
                    <i class="fa-solid fa-users me-1 text-primary"></i> Rekap Saldo
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-weight-bold" id="nota-tab" data-bs-toggle="tab" data-bs-target="#ledger-nota-pane" type="button" role="tab" aria-controls="ledger-nota-pane" aria-selected="false">
                    <i class="fa-solid fa-book me-1 text-success"></i> Daftar Nota
                </button>
            </li>
        </ul>
    </div>

    <div class="row tab-content responsive-tab-content" id="ledgerTabContent">
        <!-- Kolom Kiri: Ringkasan Saldo Hutang Pelanggan -->
        <div class="tab-pane fade show active col-lg-4 mb-4" id="ledger-rekap-pane" role="tabpanel" aria-labelledby="rekap-tab">
            <div class="card premium-card p-4 bg-white shadow-sm border-0">
                <div class="card-body p-0">
                    <h5 class="card-title font-weight-bold mb-4"><i class="fa-solid fa-users text-orange me-2"></i>Rekap Saldo Piutang</h5>
                    
                    <div class="list-group list-group-flush">
                        <?php if (empty($pelanggan_hutang)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fa-solid fa-face-smile fa-2x mb-2 text-light"></i>
                                <p class="small m-0">Tidak ada piutang aktif. Semua pelanggan lunas!</p>
                            </div>
                        <?php else: 
                            foreach ($pelanggan_hutang as $ph): 
                            ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                    <div>
                                        <h6 class="font-weight-bold m-0"><?= htmlspecialchars($ph->nama_pelanggan) ?></h6>
                                        <small class="text-muted"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($ph->no_hp) ?></small>
                                    </div>
                                    <span class="badge bg-danger p-2 font-weight-bold fs-6 rounded-8">
                                        Rp <?= number_format($ph->saldo_hutang, 0, ',', '.') ?>
                                    </span>
                                </div>
                            <?php endforeach; 
                        endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Rincian Nota Piutang Aktif -->
        <div class="tab-pane fade col-lg-8" id="ledger-nota-pane" role="tabpanel" aria-labelledby="nota-tab">
            <?php require_once 'views/templates/notifications.php'; ?>
            <div class="card premium-card p-4 bg-white shadow-sm border-0">
                <div class="card-body p-0">
                    <h5 class="card-title font-weight-bold mb-4"><i class="fa-solid fa-book text-orange me-2"></i>Daftar Nota Piutang Aktif</h5>
                    
                    <div class="table-responsive table-responsive-scroll">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nota</th>
                                    <th class="d-none d-md-table-cell">Tgl Hutang</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Pelanggan</th>
                                    <th class="d-none d-md-table-cell">Hutang</th>
                                    <th>Sisa Hutang</th>
                                    <th class="text-center d-none d-md-table-cell">Status</th>
                                    <th class="text-end width-80"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($hutang_aktif)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-circle-check fa-3x mb-3 text-light"></i>
                                            <p>Semua transaksi penjualan telah lunas dibayar.</p>
                                        </td>
                                    </tr>
                                <?php else: 
                                    $todayStr = date('Y-m-d');
                                    foreach ($hutang_aktif as $h): 
                                        $overdue = false;
                                        $daysOverdue = 0;
                                        if ($h->due_date && $todayStr > $h->due_date) {
                                            $overdue = true;
                                            $interval = (new DateTime($todayStr))->diff(new DateTime($h->due_date));
                                            $daysOverdue = $interval->days;
                                        }
                                    ?>
                                        <tr>
                                            <td class="font-monospace font-weight-bold">#<?= $h->id_transaksi ?></td>
                                            <td class="d-none d-md-table-cell"><?= date('d M Y', strtotime($h->tanggal_hutang)) ?></td>
                                            <td>
                                                <?php if ($h->due_date): ?>
                                                    <span class="<?= $overdue ? 'text-danger font-weight-bold' : 'text-muted' ?>">
                                                        <?= date('d M Y', strtotime($h->due_date)) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted italic">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($h->nama_pelanggan) ?></strong>
                                                <?php if ($overdue): ?>
                                                    <div class="d-md-none mt-1">
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger small-text-ar">Terlambat <?= $daysOverdue ?> hr</span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="d-md-none mt-1">
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success small-text-ar">Lancar</span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="d-none d-md-table-cell">Rp <?= number_format($h->jumlah_hutang, 0, ',', '.') ?></td>
                                            <td class="font-weight-bold text-danger">
                                                Rp <?= number_format($h->sisa_hutang, 0, ',', '.') ?>
                                                <div class="d-md-none text-muted trans-mobile-detail">dari Rp <?= number_format($h->jumlah_hutang, 0, ',', '.') ?></div>
                                            </td>
                                            <td class="text-center d-none d-md-table-cell">
                                                <?php if ($overdue): ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Terlambat <?= $daysOverdue ?> hr</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success">Lancar</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                 <div class="dropdown d-inline-block">
                                                     <button class="btn btn-sm btn-light border dropdown-toggle dropdown-btn-compact" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false">
                                                         <i class="fa-solid fa-ellipsis-vertical text-muted"></i>
                                                     </button>
                                                     <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 dropdown-menu-custom">
                                                         <!-- Bayar -->
                                                         <li>
                                                             <a class="dropdown-item py-2 text-success" href="#" data-bs-toggle="modal" data-bs-target="#modalCicilan<?= $h->id_hutang ?>">
                                                                 <i class="fa-solid fa-cash-register me-2"></i> Bayar Cicilan
                                                             </a>
                                                         </li>
                                                         <li><hr class="dropdown-divider my-1"></li>
                                                         <!-- Potongan -->
                                                         <li>
                                                             <a class="dropdown-item py-2 text-warning" href="#" data-bs-toggle="modal" data-bs-target="#modalAdjustment<?= $h->id_hutang ?>">
                                                                 <i class="fa-solid fa-scissors me-2"></i> Potongan Harga
                                                             </a>
                                                         </li>
                                                         <!-- Bermasalah -->
                                                         <li>
                                                             <a class="dropdown-item py-2 text-danger" href="#" data-bs-toggle="modal" data-bs-target="#modalWriteOff<?= $h->id_hutang ?>">
                                                                 <i class="fa-solid fa-circle-exclamation me-2"></i> Tandai Bermasalah
                                                             </a>
                                                         </li>
                                                     </ul>
                                                 </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; 
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination Controls -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <!-- Tombol Prev -->
                                <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link text-orange-link" href="index.php?page=hutang&p=<?= $current_page - 1 ?>" aria-label="Previous">
                                        <i class="fa-solid fa-angle-left"></i>
                                    </a>
                                </li>
                                
                                <!-- Angka Halaman -->
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= $i == $current_page ? 'active active-orange' : '' ?>">
                                        <a class="page-link" href="index.php?page=hutang&p=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <!-- Tombol Next -->
                                <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link text-orange-link" href="index.php?page=hutang&p=<?= $current_page + 1 ?>" aria-label="Next">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
