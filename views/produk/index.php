<?php
/** @var array $produk */

require_once 'views/templates/header.php';

$isOwner = (isset($_SESSION['role']) && $_SESSION['role'] === 'Owner');
?>

<?php require_once 'views/templates/notifications.php'; ?>

<div class="card premium-card p-4 mb-4" id="tabel-produk">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title font-weight-bold m-0"><i class="fa-solid fa-boxes-stacked text-orange me-2"></i>Manajemen Inventaris & Harga Produk</h5>
            <?php if ($isOwner): ?>
                <button class="btn btn-premium btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahProduk">
                    <i class="fa-solid fa-circle-plus me-1"></i> Tambah Produk
                </button>
            <?php endif; ?>
        </div>

        <div class="table-responsive-scroll">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="80" class="text-center">ID</th>
                        <th>Nama Produk</th>
                        <th>Harga/Kg</th>
                        <th>Stok (Kg)</th>
                        <?php if ($isOwner): ?>
                            <th class="text-center" width="220">Aksi (Owner Only)</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($produk)): ?>
                        <tr>
                            <td colspan="<?= $isOwner ? '5' : '4' ?>" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-box-open fa-3x mb-3 text-light"></i>
                                <p>Belum ada data produk di sistem.</p>
                            </td>
                        </tr>
                    <?php else: 
                        foreach ($produk as $p): 
                        ?>
                            <tr>
                                <td class="text-center font-monospace font-weight-bold">#<?= $p->id_produk ?></td>
                                <td><strong><?= htmlspecialchars($p->nama_produk) ?></strong></td>
                                <td class="font-weight-bold text-success">Rp <?= number_format($p->harga_per_kg, 0, ',', '.') ?></td>
                                <td>
                                    <span class="font-weight-bold <?= $p->stok_kg <= 10 ? 'text-danger' : 'text-dark' ?>">
                                        <?= number_format($p->stok_kg, 1, ',', '.') ?> Kg
                                    </span>
                                    <?php if ($p->stok_kg <= 10): ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger ms-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>Hampir Habis</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($isOwner): ?>
                                    <td class="text-center">
                                        <!-- Tombol Edit -->
                                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEditProduk<?= $p->id_produk ?>">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                        </button>

                                        <!-- Tombol Hapus -->
                                        <form action="index.php?page=produk-hapus" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini? Produk yang sudah memiliki riwayat penjualan tidak akan bisa dihapus.');">
                                            <input type="hidden" name="id_produk" value="<?= $p->id_produk ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($isOwner): ?>
    <!-- Modal Tambah Produk Baru -->
    <div class="modal fade" id="modalTambahProduk" tabindex="-1" aria-labelledby="modalTambahProdukLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold" id="modalTambahProdukLabel"><i class="fa-solid fa-circle-plus text-primary me-2"></i>Tambah Produk Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?page=produk-simpan" method="POST">
                    <div class="modal-body py-3">
                        <div class="mb-3">
                            <label for="nama_produk" class="form-label small">Nama Produk</label>
                            <input type="text" class="form-control" id="nama_produk" name="nama_produk" placeholder="Contoh: Sayap Fillet Super" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="harga_per_kg" class="form-label small">Harga per Kg</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="harga_per_kg" name="harga_per_kg" placeholder="38000" min="1" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="stok_awal" class="form-label small">Stok Awal (Kg)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="stok_awal" name="stok_awal" placeholder="50.0" step="0.1" min="0" required>
                                <span class="input-group-text">Kg</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals Edit (Looping diluar tabel) -->
    <?php foreach ($produk as $p): ?>
        <!-- Modal Edit Produk -->
        <div class="modal fade" id="modalEditProduk<?= $p->id_produk ?>" tabindex="-1" aria-labelledby="modalEditProdukLabel<?= $p->id_produk ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title font-weight-bold" id="modalEditProdukLabel<?= $p->id_produk ?>"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Data Produk</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="index.php?page=produk-edit" method="POST">
                        <input type="hidden" name="id_produk" value="<?= $p->id_produk ?>">
                        <div class="modal-body py-3">
                            <div class="mb-3">
                                <label for="nama_produk<?= $p->id_produk ?>" class="form-label small">Nama Produk</label>
                                <input type="text" class="form-control" id="nama_produk<?= $p->id_produk ?>" name="nama_produk" value="<?= htmlspecialchars($p->nama_produk) ?>" required autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="harga_per_kg<?= $p->id_produk ?>" class="form-label small">Harga per Kg</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="harga_per_kg<?= $p->id_produk ?>" name="harga_per_kg" value="<?= $p->harga_per_kg ?>" min="1" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="jumlah_kg<?= $p->id_produk ?>" class="form-label small">Stok Saat Ini (Kg)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="jumlah_kg<?= $p->id_produk ?>" name="jumlah_kg" value="<?= $p->stok_kg ?>" step="0.1" min="0" required>
                                    <span class="input-group-text">Kg</span>
                                </div>
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

<?php
require_once 'views/templates/footer.php';
?>
