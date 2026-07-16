<?php
/** @var array $produk */
/** @var array $pelanggan */
/** @var object|null $lastTransaksi */
/** @var float $beban_pagi */
/** @var float $beban_sore */

require_once 'views/templates/header.php';
require_once 'views/templates/notifications.php';
?>

<form action="index.php?page=kasir-simpan" method="POST" id="formPOS">
    <div class="row">
        <!-- Kolom Kiri: Informasi Pelanggan & Kapasitas Pengiriman -->
        <div class="col-lg-5 mb-4">
            <!-- Card Pelanggan -->
            <div class="card premium-card p-4 mb-4">
                <div class="card-body p-0">
                    <h5 class="card-title mb-4 font-weight-bold"><i class="fa-solid fa-user text-orange me-2"></i>Informasi Pelanggan</h5>
                    
                    <div class="mb-3">
                        <label for="id_pelanggan" class="form-label font-weight-bold">Pilih Pelanggan</label>
                        <div class="input-group">
                            <select class="form-select" id="id_pelanggan" name="id_pelanggan" required>
                                <option value="" disabled selected>-- Pilih Pelanggan --</option>
                                <?php foreach ($pelanggan as $p): ?>
                                    <option value="<?= $p->id_pelanggan ?>" data-nama="<?= htmlspecialchars($p->nama_pelanggan) ?>" data-hp="<?= htmlspecialchars($p->no_hp) ?>" data-alamat="<?= htmlspecialchars($p->alamat) ?>">
                                        <?= htmlspecialchars($p->nama_pelanggan) ?> (<?= htmlspecialchars($p->no_hp) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalTambahPelanggan">
                                <i class="fa-solid fa-user-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Slot Pengiriman -->
            <div class="card premium-card p-4 mb-4">
                <div class="card-body p-0">
                    <h5 class="card-title mb-4 font-weight-bold"><i class="fa-solid fa-truck text-orange me-2"></i>Kapasitas Pengiriman</h5>
                    
                    <?php
                    $sisa_pagi = max(0.0, 60.0 - $beban_pagi);
                    $sisa_sore = max(0.0, 60.0 - $beban_sore);
                    ?>
                    
                    <div class="d-flex flex-column gap-3">
                        <!-- Slot Pagi -->
                        <label class="card-selector-label" for="slotPagi">
                            <input class="card-selector-input" type="radio" name="slot_waktu" id="slotPagi" value="Pagi" checked>
                            <div class="card-selector-content">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-cloud-sun text-warning me-1"></i> <strong>Pagi</strong></span>
                                    <span id="pagiTextStatus">
                                        <?php if ($beban_pagi >= 60.0): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="fa-solid fa-triangle-exclamation"></i> Penuh (PO)</span>
                                        <?php else: ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success">Sisa: <?= number_format($sisa_pagi, 1) ?> Kg</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="progress mt-2 progress-bar-thin">
                                    <div id="pagiProgressBar" class="progress-bar <?= $beban_pagi >= 55.0 ? 'bg-danger' : ($beban_pagi > 40.0 ? 'bg-warning' : 'bg-success') ?>" 
                                         style="width: <?= min(100.0, ($beban_pagi / 60.0) * 100) ?>%"></div>
                                </div>
                                <small id="pagiTerisiText" class="text-muted mt-1 small">Terisi: <?= number_format($beban_pagi, 1) ?> / 60 Kg</small>
                            </div>
                        </label>

                        <!-- Slot Sore -->
                        <label class="card-selector-label" for="slotSore">
                            <input class="card-selector-input" type="radio" name="slot_waktu" id="slotSore" value="Sore">
                            <div class="card-selector-content">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-cloud-moon text-info me-1"></i> <strong>Sore</strong></span>
                                    <span id="soreTextStatus">
                                        <?php if ($beban_sore >= 60.0): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="fa-solid fa-triangle-exclamation"></i> Penuh (PO)</span>
                                        <?php else: ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success">Sisa: <?= number_format($sisa_sore, 1) ?> Kg</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="progress mt-2 progress-bar-thin">
                                    <div id="soreProgressBar" class="progress-bar <?= $beban_sore >= 55.0 ? 'bg-danger' : ($beban_sore > 40.0 ? 'bg-warning' : 'bg-success') ?>" 
                                         style="width: <?= min(100.0, ($beban_sore / 60.0) * 100) ?>%"></div>
                                </div>
                                <small id="soreTerisiText" class="text-muted mt-1 small">Terisi: <?= number_format($beban_sore, 1) ?> / 60 Kg</small>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Struk Digital Pop-up / Preview -->
            <?php if ($lastTransaksi): ?>
                <div class="card premium-card p-4 border-success mt-4">
                    <div class="card-body p-0">
                        <div class="text-center mb-3">
                            <i class="fa-solid fa-circle-check text-success fa-3x mb-2"></i>
                            <h5 class="font-weight-bold m-0">Transaksi Berhasil Disimpan</h5>
                            <p class="text-muted small">ID Transaksi: #<?= $lastTransaksi->id_transaksi ?></p>
                        </div>

                        <!-- Layout Struk Kertas -->
                        <div class="p-3 bg-light border border-dashed rounded-3 mb-3 font-monospace small" id="strukTextContainer">
                            <div class="text-center font-weight-bold mb-2">=== MR. CHICKEN POS & DISTRIBUSI ===</div>
                            <div>Tgl  : <?= $lastTransaksi->tanggal ?> <?= $lastTransaksi->waktu ?></div>
                            <div>Plg  : <?= htmlspecialchars($lastTransaksi->nama_pelanggan) ?></div>
                            <div>Slot : <?= htmlspecialchars($lastTransaksi->slot_waktu) ?> (<?= htmlspecialchars($lastTransaksi->status_pengiriman) ?>)</div>
                            <div>-----------------------------------------</div>
                            <?php foreach ($lastTransaksi->details as $dt): ?>
                                <div>- <?= str_pad(htmlspecialchars($dt->nama_produk), 15) ?> <?= str_pad(number_format($dt->jumlah_berat_kg, 2) . 'Kg', 8) ?> xRp<?= number_format($dt->harga_satuan) ?></div>
                                <div class="text-end">Rp<?= number_format($dt->subtotal) ?></div>
                            <?php endforeach; ?>
                            <div>-----------------------------------------</div>
                            <div class="d-flex justify-content-between font-weight-bold">
                                <span>TOTAL (<?= number_format($lastTransaksi->total_berat_akumulatif, 2) ?> Kg)</span>
                                <span>Rp<?= number_format($lastTransaksi->total_harga) ?></span>
                            </div>
                            <div class="d-flex justify-content-between font-weight-bold mt-1">
                                <span>METODE BAYAR</span>
                                <span><?= strtoupper($lastTransaksi->metode_pembayaran) ?></span>
                            </div>
                            <div class="text-center mt-3 text-muted">*** TERIMA KASIH ***</div>
                        </div>

                        <!-- Button to copy to WhatsApp -->
                        <?php
                        // Create formatted WA text
                        $waText = "*MR. CHICKEN - NOTA PENJUALAN*\n";
                        $waText .= "-----------------------------------------\n";
                        $waText .= "Pelanggan : " . $lastTransaksi->nama_pelanggan . "\n";
                        $waText .= "Tanggal   : " . $lastTransaksi->tanggal . " " . $lastTransaksi->waktu . "\n";
                        $waText .= "Slot Kirim: " . $lastTransaksi->slot_waktu . " (" . $lastTransaksi->status_pengiriman . ")\n";
                        $waText .= "-----------------------------------------\n";
                        foreach ($lastTransaksi->details as $dt) {
                            $waText .= "- " . $dt->nama_produk . " (" . $dt->jumlah_berat_kg . " Kg) x Rp" . number_format($dt->harga_satuan, 0, ',', '.') . " = Rp" . number_format($dt->subtotal, 0, ',', '.') . "\n";
                        }
                        $waText .= "-----------------------------------------\n";
                        $waText .= "*TOTAL    : Rp" . number_format($lastTransaksi->total_harga, 0, ',', '.') . "*\n";
                        $waText .= "Metode    : " . ($lastTransaksi->metode_pembayaran == 'Hutang' ? 'HUTANG (Masuk Catatan Piutang)' : 'LUNAS (Tunai/TF)') . "\n";
                        $waText .= "-----------------------------------------\n";
                        $waText .= "Status    : " . ($lastTransaksi->status_pengiriman == 'Pre-Order' ? 'PRE-ORDER (Antrean Slot Berikutnya)' : 'Dalam Pengiriman Kurir') . "\n";
                        $waText .= "Terima kasih telah berbelanja di Mr. Chicken!";
                        ?>
                        
                        <button type="button" class="btn btn-outline-success w-100 btn-sm" onclick="copyReceiptText(<?= htmlspecialchars(json_encode($waText)) ?>)">
                            <i class="fa-brands fa-whatsapp me-2"></i>Salin Struk Format WhatsApp
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Kolom Kanan: Keranjang Pesanan, Metode Pembayaran, & Total -->
        <div class="col-lg-7 mb-4">
            <div class="card premium-card p-4 bg-white shadow-sm">
                <div class="card-body p-0">
                    <h5 class="card-title mb-4 font-weight-bold text-orange"><i class="fa-solid fa-receipt me-2"></i>Keranjang & Pembayaran</h5>
                    
                    <!-- Keranjang Pesanan / Item Input -->
                    <div class="mb-4">
                        <label class="form-label font-weight-bold d-flex justify-content-between align-items-center">
                            <span>Item Pesanan</span>
                            <button type="button" class="btn btn-sm btn-outline-success" id="btnTambahItem">
                                <i class="fa-solid fa-plus me-1"></i> Tambah Potongan
                            </button>
                        </label>
                        
                        <div id="containerItem">
                            <!-- Baris Item Pertama (Default) -->
                            <div class="row item-row mb-3 align-items-end border-bottom pb-3">
                                <div class="col-12 col-md-5 mb-2 mb-md-0">
                                    <label class="form-label small text-muted">Nama Produk Fillet</label>
                                    <select class="form-select select-produk" name="produk_id[]" required>
                                        <option value="" disabled selected>-- Pilih Ayam Fillet --</option>
                                        <?php foreach ($produk as $pr): ?>
                                            <option value="<?= $pr->id_produk ?>" data-harga="<?= $pr->harga_per_kg ?>" data-stok="<?= $pr->jumlah_kg ?>">
                                                <?= htmlspecialchars($pr->nama_produk) ?> - Rp<?= number_format($pr->harga_per_kg, 0, ',', '.') ?>/Kg (Stok: <?= $pr->jumlah_kg ?> Kg)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3 mb-2 mb-md-0">
                                    <label class="form-label small text-muted">Berat (Kg)</label>
                                    <input type="number" step="0.1" min="0.1" class="form-control input-berat" name="berat_kg[]" placeholder="0.0" required>
                                </div>
                                <div class="col-4 col-md-3 mb-2 mb-md-0">
                                    <label class="form-label small text-muted">Subtotal (Rp)</label>
                                    <input type="text" class="form-control bg-light text-end subtotal-val" readonly value="0">
                                </div>
                                <div class="col-2 col-md-1 mb-2 mb-md-0 text-end text-md-center">
                                    <button type="button" class="btn btn-link text-danger btn-hapus-item hide-by-default btn-danger-compact">
                                        <i class="fa-solid fa-trash-can fa-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metode Pembayaran Selectable Cards -->
                    <div class="mb-4">
                        <label class="form-label font-weight-bold">Metode Pembayaran</label>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="w-100" for="bayarLunas">
                                    <input class="payment-selector-input" type="radio" name="metode_pembayaran" id="bayarLunas" value="Lunas" checked>
                                    <div class="payment-selector-card">
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success mb-2 px-3 py-1">LUNAS</span>
                                        <div class="fw-bold text-dark">Tunai / Transfer</div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6">
                                <label class="w-100" for="bayarHutang">
                                    <input class="payment-selector-input" type="radio" name="metode_pembayaran" id="bayarHutang" value="Hutang">
                                    <div class="payment-selector-card">
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger mb-2 px-3 py-1">HUTANG</span>
                                        <div class="fw-bold text-dark">Piutang Dagang</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div id="dueDateContainer" class="due-date-container mt-3">
                            <label for="due_date" class="form-label small font-weight-bold text-muted"><i class="fa-solid fa-calendar-day me-1"></i>Tanggal Jatuh Tempo</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <!-- Grand Total Highlight Box -->
                    <div class="grand-total-highlight">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted fw-bold">ESTIMASI TOTAL BERAT</span>
                            <span class="fw-bold text-dark" id="liveTotalBerat">0.00 Kg</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">GRAND TOTAL</span>
                            <span class="fw-bold text-success grand-total-text" id="liveGrandTotal">Rp 0</span>
                        </div>
                    </div>

                    <!-- Simpan & Proses Transaksi Button (btn-lg, w-100) -->
                    <button type="submit" class="btn btn-premium btn-lg w-100 btn-checkout-large shadow-sm">
                        <i class="fa-solid fa-circle-check me-2"></i>Simpan & Proses Transaksi
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Sticky Bottom Summary for Mobile (Visible on screens < 768px, aligned above bottom navigation) -->
<div class="d-md-none fixed-bottom bg-white border-top p-3 shadow-lg d-flex justify-content-between align-items-center mobile-total-bar">
    <div>
        <small class="text-muted d-block mobile-total-label">Total Order</small>
        <strong class="text-primary fs-5" id="mobileLiveGrandTotal">Rp 0</strong>
        <small class="text-muted small-text-date">(<span id="mobileLiveTotalBerat">0.00</span> Kg)</small>
    </div>
    <button type="button" class="btn btn-primary btn-sm py-2 px-3" onclick="document.getElementById('liveGrandTotal').scrollIntoView({ behavior: 'smooth' });">
        <i class="fa-solid fa-receipt me-1"></i> Detail Struk
    </button>
</div>

<!-- Modal Tambah Pelanggan Baru -->
<div class="modal fade" id="modalTambahPelanggan" tabindex="-1" aria-labelledby="modalTambahPelangganLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="modalTambahPelangganLabel">
                    <i class="fa-solid fa-user-plus text-primary me-2"></i>Tambah Pelanggan Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahPelanggan">
                <div class="modal-body py-3">
                    <div class="alert alert-danger small p-2 text-center hide-by-default" id="modalError"></div>
                    
                    <div class="mb-3">
                        <label for="modal_nama" class="form-label small">Nama Lengkap</label>
                        <input type="text" class="form-control" id="modal_nama" name="nama_pelanggan" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal_no_hp" class="form-label small">Nomor WhatsApp / HP</label>
                        <input type="text" class="form-control" id="modal_no_hp" name="no_hp" required placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="mb-3">
                        <label for="modal_alamat" class="form-label small">Alamat Lengkap Pengiriman</label>
                        <textarea class="form-control" id="modal_alamat" name="alamat" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanPelanggan">
                        <span class="spinner-border spinner-border-sm me-1 hide-by-default" id="spinnerPelanggan" role="status" aria-hidden="true"></span>
                        Simpan Pelanggan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- AJAX and DOM Interactive Logic -->
<script>
const configPOS = {
    initialBebanPagi: <?= (float)$beban_pagi ?>,
    initialBebanSore: <?= (float)$beban_sore ?>,
    maxSlotKg: <?= (float)MAX_SLOT_KG ?>,
    defaultDueDays: <?= (int)DEFAULT_DUE_DAYS ?>
};
</script>
<script src="assets/js/kasir.js"></script>

<?php
require_once 'views/templates/footer.php';
?>
