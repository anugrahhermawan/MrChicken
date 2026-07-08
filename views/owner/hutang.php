<?php
/** @var array $hutang_aktif */
/** @var array $pelanggan_hutang */
/** @var int $current_page */
/** @var int $total_pages */

$current_page = $current_page ?? 1;
$total_pages = $total_pages ?? 1;

require_once 'views/templates/header.php';
?>

<div class="row">
    <!-- Kolom Kiri: Ringkasan Saldo Hutang Pelanggan -->
    <div class="col-lg-4 mb-4">
        <div class="card premium-card p-4">
            <div class="card-body">
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
                                <span class="badge bg-danger p-2 font-weight-bold fs-6">
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
    <div class="col-lg-8" id="daftar-piutang">
        <?php require_once 'views/templates/notifications.php'; ?>
        <div class="card premium-card p-4">
            <div class="card-body">
                <h5 class="card-title font-weight-bold mb-4"><i class="fa-solid fa-book text-orange me-2"></i>Buku Hutang Aktif (Belum Lunas)</h5>
                
                <div class="table-responsive-scroll">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No. Nota</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Jumlah Hutang</th>
                                <th>Sisa Hutang</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($hutang_aktif)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-circle-check fa-3x mb-3 text-light"></i>
                                        <p>Semua transaksi penjualan telah lunas dibayar.</p>
                                    </td>
                                </tr>
                            <?php else: 
                                foreach ($hutang_aktif as $h): 
                                ?>
                                    <tr>
                                        <td class="font-monospace font-weight-bold">#<?= $h->id_transaksi ?></td>
                                        <td><?= date('d M Y', strtotime($h->tanggal_hutang)) ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($h->nama_pelanggan) ?></strong>
                                        </td>
                                        <td>Rp <?= number_format($h->jumlah_hutang, 0, ',', '.') ?></td>
                                        <td class="font-weight-bold text-danger">Rp <?= number_format($h->sisa_hutang, 0, ',', '.') ?></td>
                                        <td class="text-center">
                                            <!-- Button Trigger Modal Cicilan -->
                                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalCicilan<?= $h->id_hutang ?>">
                                                <i class="fa-solid fa-cash-register me-1"></i> Bayar Cicilan
                                            </button>
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

<!-- Modal Cicilan (Dipindahkan ke luar tabel untuk mencegah visual glitch) -->
<?php if (!empty($hutang_aktif)): ?>
    <?php foreach ($hutang_aktif as $h): ?>
        <div class="modal fade" id="modalCicilan<?= $h->id_hutang ?>" tabindex="-1" aria-labelledby="modalCicilanLabel<?= $h->id_hutang ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title font-weight-bold" id="modalCicilanLabel<?= $h->id_hutang ?>"><i class="fa-solid fa-hand-holding-dollar text-success me-2"></i>Bayar Cicilan Nota #<?= $h->id_transaksi ?></h5>
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
                                <label class="form-label small text-muted">Sisa Hutang</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control bg-light font-weight-bold text-danger" readonly value="<?= number_format($h->sisa_hutang, 0, ',', '.') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="nominal_bayar<?= $h->id_hutang ?>" class="form-label small">Nominal Cicilan / Pelunasan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="nominal_bayar<?= $h->id_hutang ?>" name="nominal_bayar" min="1" max="<?= $h->sisa_hutang ?>" required placeholder="Masukkan nominal bayar...">
                                </div>
                                <small class="form-text text-muted">Maksimal pembayaran cicilan adalah nominal sisa hutang.</small>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">Simpan Pembayaran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php
require_once 'views/templates/footer.php';
?>
