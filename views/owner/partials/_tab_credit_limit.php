<?php
// views/owner/partials/_tab_credit_limit.php
/** @var array $all_pelanggan */
/** @var string $userRole */
?>
<?php if ($userRole === 'Owner'): ?>
    <div class="tab-pane fade" id="tab-limit" role="tabpanel" aria-labelledby="ar-limit-tab">
        <div class="card premium-card p-4 bg-white shadow-sm border-0">
            <div class="card-body p-0">
                <h5 class="card-title font-weight-bold mb-4 icon-slate"><i class="fa-solid fa-sliders me-2"></i>Pengaturan Limit Kredit Pelanggan</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Pelanggan</th>
                                <th>No. HP</th>
                                <th>Piutang Berjalan</th>
                                <th>Limit Kredit</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_pelanggan)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Tidak ada data pelanggan.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($all_pelanggan as $plg): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($plg->nama_pelanggan) ?></strong></td>
                                        <td><?= htmlspecialchars($plg->no_hp) ?></td>
                                        <td class="text-danger fw-bold">Rp <?= number_format($plg->saldo_hutang, 0, ',', '.') ?></td>
                                        <td>
                                            <?php if ($plg->credit_limit > 0): ?>
                                                <span class="badge bg-light text-dark border p-2">Rp <?= number_format($plg->credit_limit, 0, ',', '.') ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-success border p-2">Tanpa Batas (Unlimited)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalLimitPelanggan<?= $plg->id_pelanggan ?>">
                                                <i class="fa-solid fa-sliders me-1"></i> Edit Limit
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
