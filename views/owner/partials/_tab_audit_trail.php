<?php
// views/owner/partials/_tab_audit_trail.php
/** @var array $riwayat_pembayaran */
/** @var string $userRole */
?>
<?php if ($userRole === 'Owner'): ?>
    <div class="tab-pane fade" id="tab-history" role="tabpanel" aria-labelledby="ar-history-tab">
        <div class="card premium-card p-4 bg-white shadow-sm border-0">
            <div class="card-body p-0">
                <h5 class="card-title font-weight-bold mb-4 icon-slate"><i class="fa-solid fa-clock-rotate-left me-2"></i>Riwayat & Audit Trail Pembayaran</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID Bayar</th>
                                <th>Tanggal Aksi</th>
                                <th>Pelanggan</th>
                                <th>Nota Terkait</th>
                                <th>Nominal</th>
                                <th>Tipe Aksi</th>
                                <th>Petugas</th>
                                <?php if ($userRole === 'Owner'): ?>
                                    <th class="text-center">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($riwayat_pembayaran)): ?>
                                <tr>
                                    <td colspan="<?= $userRole === 'Owner' ? '8' : '7' ?>" class="text-center py-4 text-muted">Belum ada riwayat pembayaran cicilan/penghapusan piutang.</td>
                                </tr>
                            <?php else: 
                                foreach ($riwayat_pembayaran as $rp): 
                                ?>
                                    <tr>
                                        <td class="font-monospace fw-bold text-dark">BILL-PAY-<?= $rp->id_pembayaran ?></td>
                                        <td><?= date('d M Y H:i:s', strtotime($rp->tanggal_bayar)) ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($rp->nama_pelanggan) ?></td>
                                        <td class="font-monospace">#<?= htmlspecialchars($rp->id_transaksi ?? '') ?></td>
                                        <td class="fw-bold <?= $rp->tipe === 'Write-Off' ? 'text-danger' : ($rp->tipe === 'Adjustment' ? 'text-warning' : 'text-success') ?>">
                                            Rp <?= number_format($rp->nominal_bayar, 0, ',', '.') ?>
                                        </td>
                                        <td>
                                            <?php if ($rp->tipe === 'Bayar'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="fa-solid fa-cash-register me-1"></i> Tunai (Masuk Kas)</span>
                                            <?php elseif ($rp->tipe === 'Adjustment'): ?>
                                                <span class="badge bg-light text-secondary border"><i class="fa-solid fa-scissors me-1 text-muted"></i> Potongan Harga</span>
                                            <?php elseif ($rp->tipe === 'Write-Off'): ?>
                                                <span class="badge bg-light text-secondary border"><i class="fa-solid fa-ban me-1 text-muted"></i> Pembayaran Bermasalah</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark p-2 border">
                                                <i class="fa-solid fa-user me-1 text-muted"></i> <?= htmlspecialchars($rp->nama_pengguna) ?>
                                            </span>
                                        </td>
                                        <?php if ($userRole === 'Owner'): ?>
                                            <td class="text-center">
                                                <form action="index.php?page=hutang-bayar-batal" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pembayaran BILL-PAY-<?= $rp->id_pembayaran ?>? Tindakan ini akan memulihkan sisa piutang pelanggan.');">
                                                    <input type="hidden" name="id_pembayaran" value="<?= $rp->id_pembayaran ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fa-solid fa-arrow-rotate-left me-1"></i> Batal
                                                     </button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; 
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
