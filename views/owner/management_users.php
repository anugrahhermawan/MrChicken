<?php
/** @var array $users */

// Proteksi Hak Akses (RBAC) di tingkat view
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Owner') {
    $_SESSION['error'] = "Akses Ditolak! Menu ini hanya untuk Owner.";
    header('Location: index.php?page=dashboard');
    exit();
}

require_once 'views/templates/header.php';
?>

<?php require_once 'views/templates/notifications.php'; ?>
<div class="card premium-card p-4 mb-4" id="tabel-karyawan">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title font-weight-bold m-0"><i class="fa-solid fa-users-gear text-orange me-2"></i>Manajemen Pengguna Sistem</h5>
            <button class="btn btn-premium btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                <i class="fa-solid fa-user-plus me-1"></i> Tambah Pengguna
            </button>
        </div>

        <div class="table-responsive table-responsive-scroll">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="80" class="text-center d-none d-sm-table-cell">No</th>
                        <th>Nama Lengkap</th>
                        <th class="d-none d-md-table-cell">Username</th>
                        <th class="d-none d-md-table-cell">Peran (Role)</th>
                        <th>Status</th>
                        <th class="text-center" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($users as $u): 
                    ?>
                        <tr>
                            <td class="text-center font-weight-bold d-none d-sm-table-cell"><?= $no++ ?></td>
                            <td>
                                <strong><?= htmlspecialchars($u->nama_pengguna) ?></strong>
                                <div class="d-md-none text-muted small mt-1">
                                    <span class="font-monospace">@<?= htmlspecialchars($u->username) ?></span> &middot; 
                                    <span class="badge <?= $u->role === 'Owner' ? 'bg-danger' : 'bg-primary' ?> p-1 badge-compact"><?= htmlspecialchars($u->role) ?></span>
                                </div>
                            </td>
                            <td class="font-monospace d-none d-md-table-cell"><?= htmlspecialchars($u->username) ?></td>
                            <td class="d-none d-md-table-cell">
                                <span class="badge <?= $u->role === 'Owner' ? 'bg-danger' : 'bg-primary' ?>">
                                    <?= htmlspecialchars($u->role) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($u->status_aktif == 1): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="fa-solid fa-circle-check me-1"></i>Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary"><i class="fa-solid fa-circle-xmark me-1"></i>Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-light border dropdown-toggle dropdown-btn-compact" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false">
                                        <i class="fa-solid fa-ellipsis-vertical text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 dropdown-menu-custom">
                                        <li>
                                            <a class="dropdown-item py-2 text-primary" href="#" data-bs-toggle="modal" data-bs-target="#modalEditUser<?= $u->id_user ?>">
                                                <i class="fa-solid fa-user-pen me-2"></i> Edit Data
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2 <?= $u->status_aktif == 1 ? 'text-warning' : 'text-success' ?>" href="index.php?page=users-toggle&id=<?= $u->id_user ?>" 
                                               onclick="return confirm('Apakah Anda yakin ingin mengubah status keaktifan pengguna ini?');">
                                                <i class="fa-solid <?= $u->status_aktif == 1 ? 'fa-ban' : 'fa-check' ?> me-2"></i>
                                                <?= $u->status_aktif == 1 ? 'Nonaktifkan' : 'Aktifkan' ?>
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <form action="index.php?page=users-hapus" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pengguna ini secara permanen dari sistem?');">
                                                <input type="hidden" name="id_user" value="<?= $u->id_user ?>">
                                                <button type="submit" class="dropdown-item py-2 text-danger border-0 bg-transparent w-100 text-start">
                                                    <i class="fa-solid fa-trash-can me-2"></i> Hapus
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah User Baru (Dipindahkan ke luar tabel untuk mencegah visual glitch) -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-labelledby="modalTambahUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="modalTambahUserLabel"><i class="fa-solid fa-user-plus text-primary me-2"></i>Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=users-simpan" method="POST">
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label for="nama_pengguna" class="form-label small">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_pengguna" name="nama_pengguna" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label small">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label small">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label small">Peran Akses (Role)</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="Karyawan" selected>Karyawan / Kasir</option>
                            <option value="Owner">Owner (Pemilik)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit User (Dipindahkan ke luar tabel untuk mencegah visual glitch) -->
<?php foreach ($users as $u): ?>
    <div class="modal fade" id="modalEditUser<?= $u->id_user ?>" tabindex="-1" aria-labelledby="modalEditUserLabel<?= $u->id_user ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold" id="modalEditUserLabel<?= $u->id_user ?>"><i class="fa-solid fa-user-pen text-primary me-2"></i>Edit Data Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?page=users-edit" method="POST">
                    <input type="hidden" name="id_user" value="<?= $u->id_user ?>">
                    <div class="modal-body py-3">
                        <div class="mb-3">
                            <label for="nama_pengguna<?= $u->id_user ?>" class="form-label small">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_pengguna<?= $u->id_user ?>" name="nama_pengguna" value="<?= htmlspecialchars($u->nama_pengguna) ?>" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="username<?= $u->id_user ?>" class="form-label small">Username</label>
                            <input type="text" class="form-control" id="username<?= $u->id_user ?>" name="username" value="<?= htmlspecialchars($u->username) ?>" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="password_edit<?= $u->id_user ?>" class="form-label small">Password Baru <span class="text-muted">(Kosongkan jika tidak ingin diubah)</span></label>
                            <input type="password" class="form-control" id="password_edit<?= $u->id_user ?>" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="role<?= $u->id_user ?>" class="form-label small">Peran Akses (Role)</label>
                            <select class="form-select" id="role<?= $u->id_user ?>" name="role" required>
                                <option value="Karyawan" <?= $u->role === 'Karyawan' ? 'selected' : '' ?>>Karyawan / Kasir</option>
                                <option value="Owner" <?= $u->role === 'Owner' ? 'selected' : '' ?>>Owner (Pemilik)</option>
                            </select>
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

<?php
require_once 'views/templates/footer.php';
?>
