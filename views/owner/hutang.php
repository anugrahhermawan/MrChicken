<?php
/** @var array $hutang_aktif */
/** @var array $pelanggan_hutang */
/** @var array $all_pelanggan */
/** @var array $riwayat_pembayaran */
/** @var array $aging */
/** @var int $total_piutang */
/** @var object|null $lastPembayaran */
/** @var int $current_page */
/** @var int $total_pages */

$current_page = $current_page ?? 1;
$total_pages = $total_pages ?? 1;
$userRole = $_SESSION['role'] ?? 'Karyawan';

require_once 'views/templates/header.php';
?>

<div class="container-fluid py-2">
    <!-- Success Alert for Cicilan Payment Receipt -->
    <?php if ($lastPembayaran): ?>
        <div class="alert alert-success premium-card border-success p-4 mb-4 receipt-banner">
            <div class="row">
                <div class="col-md-6 border-end pe-4">
                    <h5 class="font-weight-bold text-success mb-3"><i class="fa-solid fa-circle-check me-2"></i>Pembayaran Cicilan Berhasil</h5>
                    <div id="strukPembayaran" class="bg-white p-4 border rounded shadow-sm receipt-box">
                        <div class="text-center fw-bold mb-2 receipt-header">MR. CHICKEN</div>
                        <div class="text-center text-muted small mb-3">POS & Distribusi Ayam Fillet</div>
                        <div>-----------------------------------------</div>
                        <div class="d-flex justify-content-between"><span>No. Resi:</span><strong>BILL-PAY-<?= $lastPembayaran->id_pembayaran ?></strong></div>
                        <div class="d-flex justify-content-between"><span>Tanggal:</span><span><?= date('d M Y H:i', strtotime($lastPembayaran->tanggal_bayar)) ?></span></div>
                        <div class="d-flex justify-content-between"><span>Pelanggan:</span><span><?= htmlspecialchars($lastPembayaran->nama_pelanggan) ?></span></div>
                        <div>-----------------------------------------</div>
                        <div class="d-flex justify-content-between"><span>Nota Asal:</span><span>Nota #<?= $lastPembayaran->id_transaksi ?? $lastPembayaran->id_hutang ?? '' ?></span></div>
                        <div class="d-flex justify-content-between"><span>Tipe Aksi:</span><span class="badge bg-light text-dark border"><?= $lastPembayaran->tipe ?></span></div>
                        <div class="d-flex justify-content-between"><span>Bayar Cicilan:</span><strong class="text-success">Rp <?= number_format($lastPembayaran->nominal_bayar, 0, ',', '.') ?></strong></div>
                        <div class="d-flex justify-content-between"><span>Sisa Piutang:</span><strong class="text-danger">Rp <?= number_format($lastPembayaran->sisa_hutang, 0, ',', '.') ?></strong></div>
                        <div>-----------------------------------------</div>
                        <div class="text-center mt-3 text-muted">*** BUKTI PEMBAYARAN SAH ***</div>
                    </div>
                </div>
                <div class="col-md-6 d-flex flex-column justify-content-center gap-3">
                    <?php
                    $waPayText = "*MR. CHICKEN - BUKTI PEMBAYARAN CICILAN*\n";
                    $waPayText .= "-----------------------------------------\n";
                    $waPayText .= "No. Resi : BILL-PAY-" . $lastPembayaran->id_pembayaran . "\n";
                    $waPayText .= "Tanggal  : " . date('d M Y H:i', strtotime($lastPembayaran->tanggal_bayar)) . "\n";
                    $waPayText .= "Pelanggan: " . $lastPembayaran->nama_pelanggan . "\n";
                    $waPayText .= "-----------------------------------------\n";
                    $waPayText .= "Nota Asal: Nota #" . ($lastPembayaran->id_transaksi ?? $lastPembayaran->id_hutang ?? '') . "\n";
                    $waPayText .= "Bayar    : Rp " . number_format($lastPembayaran->nominal_bayar, 0, ',', '.') . "\n";
                    $waPayText .= "Sisa AR  : Rp " . number_format($lastPembayaran->sisa_hutang, 0, ',', '.') . "\n";
                    $waPayText .= "-----------------------------------------\n";
                    $waPayText .= "Diterima oleh: " . $lastPembayaran->nama_pengguna . "\n";
                    $waPayText .= "Terima kasih atas pembayaran Anda!";
                    ?>
                    <div>
                        <p class="text-muted small mb-2">Anda dapat mencetak bukti cetak kertas fisik atau mengirimkannya langsung ke pelanggan via WhatsApp.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success flex-grow-1" onclick="copyReceiptText(<?= htmlspecialchars(json_encode($waPayText)) ?>)">
                            <i class="fa-brands fa-whatsapp me-2"></i>Salin format WA
                        </button>
                        <button class="btn btn-outline-primary" onclick="printReceiptDiv()">
                            <i class="fa-solid fa-print me-2"></i>Cetak Resi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Owner Specific Metriks & Aging AR widgets -->
    <?php include 'views/owner/partials/_tab_aging_ar.php'; ?>

    <!-- Navigation Pills / Tabs for Owner -->
    <?php if ($userRole === 'Owner'): ?>
        <ul class="nav nav-tabs mb-4" id="arModuleTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active font-weight-bold text-dark" id="ar-piutang-tab" data-bs-toggle="tab" data-bs-target="#tab-piutang" type="button" role="tab" aria-controls="tab-piutang" aria-selected="true">
                    <i class="fa-solid fa-book-bookmark me-2 text-primary"></i>Buku Piutang Aktif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-weight-bold text-dark" id="ar-limit-tab" data-bs-toggle="tab" data-bs-target="#tab-limit" type="button" role="tab" aria-controls="tab-limit" aria-selected="false">
                    <i class="fa-solid fa-sliders me-2 text-warning"></i>Pengaturan Credit Limit
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-weight-bold text-dark" id="ar-history-tab" data-bs-toggle="tab" data-bs-target="#tab-history" type="button" role="tab" aria-controls="tab-history" aria-selected="false">
                    <i class="fa-solid fa-clock-rotate-left me-2 text-success"></i>Riwayat & Audit Trail Pembayaran
                </button>
            </li>
        </ul>
    <?php endif; ?>

    <div class="tab-content" id="arModuleTabsContent">
        <?php include 'views/owner/partials/_tab_piutang_aktif.php'; ?>
        <?php include 'views/owner/partials/_tab_credit_limit.php'; ?>
        <?php include 'views/owner/partials/_tab_audit_trail.php'; ?>
    </div>
</div>

<!-- MODALS SECTION -->
<!-- 1. Modal Cicilan (Owner & Karyawan) -->
<?php if (!empty($hutang_aktif)): ?>
    <?php foreach ($hutang_aktif as $h): ?>
        <div class="modal fade" id="modalCicilan<?= $h->id_hutang ?>" tabindex="-1" aria-labelledby="modalCicilanLabel<?= $h->id_hutang ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-content-custom">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title font-weight-bold text-success" id="modalCicilanLabel<?= $h->id_hutang ?>"><i class="fa-solid fa-cash-register me-2"></i>Bayar Cicilan Piutang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="index.php?page=hutang-bayar" method="POST">
                        <input type="hidden" name="id_hutang" value="<?= $h->id_hutang ?>">
                        <div class="modal-body py-3">
                            <div class="mb-3">
                                <label class="form-label small text-muted">Pelanggan</label>
                                <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($h->nama_pelanggan) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">Sisa Piutang Berjalan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control bg-light font-weight-bold text-danger" readonly value="<?= number_format($h->sisa_hutang, 0, ',', '.') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="nominal_bayar<?= $h->id_hutang ?>" class="form-label small">Nominal Pembayaran Tunai</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control font-weight-bold" id="nominal_bayar<?= $h->id_hutang ?>" name="nominal_bayar" min="1" max="<?= $h->sisa_hutang ?>" required placeholder="Masukkan jumlah pembayaran...">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success px-4">Simpan Pembayaran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- 2. Modal Adjustment (Owner Only) -->
<?php if (!empty($hutang_aktif)): ?>
    <?php foreach ($hutang_aktif as $h): ?>
        <div class="modal fade" id="modalAdjustment<?= $h->id_hutang ?>" tabindex="-1" aria-labelledby="modalAdjustmentLabel<?= $h->id_hutang ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-content-custom">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title font-weight-bold text-warning" id="modalAdjustmentLabel<?= $h->id_hutang ?>"><i class="fa-solid fa-scissors me-2"></i>Beri Potongan Harga</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="index.php?page=hutang-adjustment" method="POST">
                        <input type="hidden" name="id_hutang" value="<?= $h->id_hutang ?>">
                        <div class="modal-body py-3">
                            <div class="mb-3">
                                <label class="form-label small text-muted">Pelanggan</label>
                                <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($h->nama_pelanggan) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">Sisa Piutang Berjalan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control bg-light font-weight-bold text-danger" readonly value="<?= number_format($h->sisa_hutang, 0, ',', '.') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="nominal_adjustment<?= $h->id_hutang ?>" class="form-label small">Nominal Potongan Harga (Diskon Pelunasan)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control font-weight-bold text-warning" id="nominal_adjustment<?= $h->id_hutang ?>" name="nominal_adjustment" min="1" max="<?= $h->sisa_hutang ?>" required placeholder="Masukkan jumlah potongan harga...">
                                </div>
                                <small class="form-text text-muted">Nilai potongan ini akan mengurangi sisa piutang dan dicatat sebagai beban potongan harga.</small>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning text-dark px-4">Simpan Potongan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- 3. Modal Write-Off (Owner Only) -->
<?php if (!empty($hutang_aktif)): ?>
    <?php foreach ($hutang_aktif as $h): ?>
        <div class="modal fade" id="modalWriteOff<?= $h->id_hutang ?>" tabindex="-1" aria-labelledby="modalWriteOffLabel<?= $h->id_hutang ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-content-custom">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title font-weight-bold text-danger" id="modalWriteOffLabel<?= $h->id_hutang ?>"><i class="fa-solid fa-circle-exclamation me-2"></i>Tandai Pembayaran Bermasalah</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="index.php?page=hutang-writeoff" method="POST">
                        <input type="hidden" name="id_hutang" value="<?= $h->id_hutang ?>">
                        <div class="modal-body py-3 text-center">
                            <div class="text-danger mb-3 large-error-icon">
                                ⚠️
                            </div>
                            <h5 class="fw-bold mb-3">Konfirmasi Pembayaran Bermasalah</h5>
                            <p class="text-muted small">Apakah Anda yakin ingin menandai sisa piutang sebesar <strong class="text-danger">Rp <?= number_format($h->sisa_hutang, 0, ',', '.') ?></strong> untuk pelanggan <strong><?= htmlspecialchars($h->nama_pelanggan) ?></strong> pada transaksi <strong>#<?= $h->id_transaksi ?></strong> sebagai pembayaran bermasalah?</p>
                            <div class="alert alert-danger small text-start">
                                <strong>PENTING:</strong> Tindakan ini akan mengosongkan sisa piutang dan menandai transaksi ini sebagai <em>Pembayaran Bermasalah</em> (rugi/tidak tertagih). Tindakan ini <strong>tidak dapat dibatalkan!</strong>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 justify-content-center">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger px-4">Ya, Tandai Bermasalah</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- 4. Modal Edit Credit Limit (Owner Only) -->
<?php if ($userRole === 'Owner' && !empty($all_pelanggan)): ?>
    <?php foreach ($all_pelanggan as $plg): ?>
        <div class="modal fade" id="modalLimitPelanggan<?= $plg->id_pelanggan ?>" tabindex="-1" aria-labelledby="modalLimitPelangganLabel<?= $plg->id_pelanggan ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-content-custom">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title font-weight-bold" id="modalLimitPelangganLabel<?= $plg->id_pelanggan ?>"><i class="fa-solid fa-sliders text-warning me-2"></i>Ubah Limit Kredit Pelanggan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="index.php?page=pelanggan-limit-update" method="POST">
                        <input type="hidden" name="id_pelanggan" value="<?= $plg->id_pelanggan ?>">
                        <div class="modal-body py-3">
                            <div class="mb-3">
                                <label class="form-label small text-muted">Pelanggan</label>
                                <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($plg->nama_pelanggan) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">Saldo Piutang Berjalan</label>
                                <input type="text" class="form-control bg-light font-weight-bold text-danger" readonly value="Rp <?= number_format($plg->saldo_hutang, 0, ',', '.') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="credit_limit<?= $plg->id_pelanggan ?>" class="form-label small">Batas Limit Kredit Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control font-weight-bold" id="credit_limit<?= $plg->id_pelanggan ?>" name="credit_limit" min="0" value="<?= $plg->credit_limit ?>" required>
                                </div>
                                <small class="form-text text-muted">Set limit ke 0 untuk menonaktifkan batasan kredit (unlimited).</small>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- JS utilities -->
<script src="assets/js/hutang.js"></script>

<?php
require_once 'views/templates/footer.php';
?>
