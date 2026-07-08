<?php
/** @var array $produk */
/** @var array $pelanggan */
/** @var object|null $lastTransaksi */
/** @var float $beban_pagi */
/** @var float $beban_sore */

require_once 'views/templates/header.php';
require_once 'views/templates/notifications.php';
?>

<div class="row">
    <!-- Kolom Kiri: Form POS Kasir -->
    <div class="col-md-7 mb-4">
        <div class="card premium-card p-4">
            <div class="card-body">
                <h5 class="card-title mb-4 font-weight-bold"><i class="fa-solid fa-cart-shopping text-orange me-2"></i>Buat Pesanan Baru</h5>
                
                <form action="index.php?page=kasir-simpan" method="POST" id="formPOS">
                    <!-- Pilih Pelanggan -->
                    <div class="mb-4">
                        <label for="id_pelanggan" class="form-label font-weight-bold">Pelanggan</label>
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

                    <!-- Input Item Dinamis -->
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
                                <div class="col-md-5 mb-2 mb-md-0">
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
                                <div class="col-md-3 mb-2 mb-md-0">
                                    <label class="form-label small text-muted">Berat (Kg)</label>
                                    <input type="number" step="0.1" min="0.1" class="form-control input-berat" name="berat_kg[]" placeholder="0.0" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Subtotal (Rp)</label>
                                    <input type="text" class="form-control bg-light text-end subtotal-val" readonly value="0">
                                </div>
                                <div class="col-md-1 text-center">
                                    <button type="button" class="btn btn-link text-danger btn-hapus-item" style="display:none;">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slot Logistik & Pembayaran -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label font-weight-bold">Slot Pengiriman</label>
                            <div class="p-3 border rounded-3 bg-white">
                                <?php
                                $sisa_pagi = max(0.0, 60.0 - $beban_pagi);
                                $sisa_sore = max(0.0, 60.0 - $beban_sore);
                                ?>
                                <!-- Slot Pagi -->
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="slot_waktu" id="slotPagi" value="Pagi" checked>
                                    <label class="form-check-label d-block" for="slotPagi">
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
                                        <div class="progress mt-1" style="height: 4px;">
                                            <div id="pagiProgressBar" class="progress-bar <?= $beban_pagi >= 60.0 ? 'bg-danger' : ($beban_pagi >= 45.0 ? 'bg-warning' : 'bg-primary') ?>" 
                                                 style="width: <?= min(100.0, ($beban_pagi / 60.0) * 100) ?>%"></div>
                                        </div>
                                        <small id="pagiTerisiText" class="text-muted text-xs">Terisi: <?= number_format($beban_pagi, 1) ?> / 60 Kg</small>
                                    </label>
                                </div>

                                <hr class="my-2 text-muted opacity-25">

                                <!-- Slot Sore -->
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="slot_waktu" id="slotSore" value="Sore">
                                    <label class="form-check-label d-block" for="slotSore">
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
                                        <div class="progress mt-1" style="height: 4px;">
                                            <div id="soreProgressBar" class="progress-bar <?= $beban_sore >= 60.0 ? 'bg-danger' : ($beban_sore >= 45.0 ? 'bg-warning' : 'bg-primary') ?>" 
                                                 style="width: <?= min(100.0, ($beban_sore / 60.0) * 100) ?>%"></div>
                                        </div>
                                        <small id="soreTerisiText" class="text-muted text-xs">Terisi: <?= number_format($beban_sore, 1) ?> / 60 Kg</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Metode Pembayaran</label>
                            <div class="p-3 border rounded-3 bg-white h-100 d-flex flex-column justify-content-center gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_pembayaran" id="bayarLunas" value="Lunas" checked>
                                    <label class="form-check-label" for="bayarLunas">
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success me-1">LUNAS</span> Tunai / TF
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_pembayaran" id="bayarHutang" value="Hutang">
                                    <label class="form-check-label" for="bayarHutang">
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger me-1">HUTANG</span> Masuk Buku Piutang
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <button type="submit" class="btn btn-premium w-100 py-3 shadow"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan & Proses Orderan</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Ringkasan Total & Struk Digital -->
    <div class="col-md-5">
        <!-- Live Cart Widget -->
        <div class="card premium-card gradient-widget p-4 mb-4 text-white">
            <div class="card-body">
                <p class="text-white-50 m-0 uppercase small tracking-wider">TOTAL PEMBAYARAN</p>
                <h1 class="display-5 font-weight-bold my-2" id="liveGrandTotal">Rp 0</h1>
                <hr class="border-white-50">
                <div class="d-flex justify-content-between">
                    <span>Estimasi Berat:</span>
                    <strong id="liveTotalBerat">0.00 Kg</strong>
                </div>
            </div>
        </div>

        <!-- Struk Digital Pop-up / Preview -->
        <?php if ($lastTransaksi): ?>
            <div class="card premium-card p-4 border-success">
                <div class="card-body">
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
                    
                    <button class="btn btn-outline-success w-100 btn-sm mb-2" onclick="copyReceiptText(<?= htmlspecialchars(json_encode($waText)) ?>)">
                        <i class="fa-brands fa-whatsapp me-2"></i>Salin Struk Format WhatsApp
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Tambah Pelanggan Baru -->
<div class="modal fade" id="modalTambahPelanggan" tabindex="-1" aria-labelledby="modalTambahPelangganLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="modalTambahPelangganLabel">
                    <i class="fa-solid fa-user-plus text-primary me-2"></i>Tambah Pelanggan Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahPelanggan">
                <div class="modal-body py-3">
                    <div class="alert alert-danger small p-2 text-center" id="modalError" style="display:none;"></div>
                    
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
                        <span class="spinner-border spinner-border-sm me-1" id="spinnerPelanggan" style="display:none;" role="status" aria-hidden="true"></span>
                        Simpan Pelanggan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- AJAX and DOM Interactive Logic -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const containerItem = document.getElementById("containerItem");
    const btnTambahItem = document.getElementById("btnTambahItem");
    const liveGrandTotal = document.getElementById("liveGrandTotal");
    const liveTotalBerat = document.getElementById("liveTotalBerat");

    // Beban slot terpakai awal dari database
    const initialBebanPagi = <?= (float)$beban_pagi ?>;
    const initialBebanSore = <?= (float)$beban_sore ?>;

    // Format Rupiah
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(angka);
    }

    // Update Visual Slot Kuota secara Real-Time
    function updateSlotVisuals(totalBerat) {
        // Pagi
        const prospectivePagi = initialBebanPagi + totalBerat;
        const sisaPagi = Math.max(0.0, 60.0 - prospectivePagi);
        const pagiProgress = Math.min(100.0, (prospectivePagi / 60.0) * 100);
        
        const pagiTextStatus = document.getElementById("pagiTextStatus");
        const pagiProgressBar = document.getElementById("pagiProgressBar");
        const pagiTerisiText = document.getElementById("pagiTerisiText");
        
        if (prospectivePagi >= 60.0) {
            pagiTextStatus.innerHTML = '<span class="text-danger small font-weight-bold"><i class="fa-solid fa-triangle-exclamation"></i> Penuh (Pre-Order)</span>';
            pagiProgressBar.className = "progress-bar bg-danger";
        } else {
            pagiTextStatus.innerHTML = `<span class="text-muted small">Sisa: ${sisaPagi.toFixed(2)} Kg</span>`;
            pagiProgressBar.className = `progress-bar ${prospectivePagi >= 45.0 ? 'bg-warning' : 'bg-success'}`;
        }
        pagiProgressBar.style.width = pagiProgress + "%";
        pagiTerisiText.textContent = `Terisi: ${prospectivePagi.toFixed(2)} / 60 Kg`;

        // Sore
        const prospectiveSore = initialBebanSore + totalBerat;
        const sisaSore = Math.max(0.0, 60.0 - prospectiveSore);
        const soreProgress = Math.min(100.0, (prospectiveSore / 60.0) * 100);
        
        const soreTextStatus = document.getElementById("soreTextStatus");
        const soreProgressBar = document.getElementById("soreProgressBar");
        const soreTerisiText = document.getElementById("soreTerisiText");
        
        if (prospectiveSore >= 60.0) {
            soreTextStatus.innerHTML = '<span class="text-danger small font-weight-bold"><i class="fa-solid fa-triangle-exclamation"></i> Penuh (Pre-Order)</span>';
            soreProgressBar.className = "progress-bar bg-danger";
        } else {
            soreTextStatus.innerHTML = `<span class="text-muted small">Sisa: ${sisaSore.toFixed(2)} Kg</span>`;
            soreProgressBar.className = `progress-bar ${prospectiveSore >= 45.0 ? 'bg-warning' : 'bg-success'}`;
        }
        soreProgressBar.style.width = soreProgress + "%";
        soreTerisiText.textContent = `Terisi: ${prospectiveSore.toFixed(2)} / 60 Kg`;
    }

    // Hitung Ulang Total
    function kalkulasiTotal() {
        let grandTotal = 0;
        let totalBerat = 0.0;

        document.querySelectorAll(".item-row").forEach(row => {
            const selectProduk = row.querySelector(".select-produk");
            const inputBerat = row.querySelector(".input-berat");
            const subtotalVal = row.querySelector(".subtotal-val");

            const selectedOption = selectProduk.options[selectProduk.selectedIndex];
            const harga = selectedOption ? parseFloat(selectedOption.getAttribute("data-harga")) || 0 : 0;
            const berat = parseFloat(inputBerat.value) || 0;

            const subtotal = Math.round(berat * harga);
            subtotalVal.value = subtotal.toLocaleString('id-ID');

            grandTotal += subtotal;
            totalBerat += berat;
        });

        liveGrandTotal.textContent = formatRupiah(grandTotal);
        liveTotalBerat.textContent = totalBerat.toFixed(2) + " Kg";
        updateSlotVisuals(totalBerat);
    }

    // Event listener untuk perubahan pada select-produk & input-berat
    containerItem.addEventListener("change", function(e) {
        if (e.target.classList.contains("select-produk") || e.target.classList.contains("input-berat")) {
            kalkulasiTotal();
        }
    });

    containerItem.addEventListener("input", function(e) {
        if (e.target.classList.contains("input-berat")) {
            kalkulasiTotal();
        }
    });

    // Tambah Baris Item Baru
    btnTambahItem.addEventListener("click", function() {
        const itemRows = document.querySelectorAll(".item-row");
        const template = itemRows[0].cloneNode(true);
        
        // Reset values
        template.querySelector(".select-produk").selectedIndex = 0;
        template.querySelector(".input-berat").value = "";
        template.querySelector(".subtotal-val").value = "0";

        // Tampilkan tombol hapus
        const btnHapus = template.querySelector(".btn-hapus-item");
        btnHapus.style.display = "block";

        // Tambah ke container
        containerItem.appendChild(template);
        
        // Refresh delete listeners
        refreshHapusListeners();
        kalkulasiTotal();
    });

    function refreshHapusListeners() {
        document.querySelectorAll(".btn-hapus-item").forEach(btn => {
            btn.onclick = function() {
                this.closest(".item-row").remove();
                kalkulasiTotal();
            };
        });
    }

    // Modal submit pelanggan baru via AJAX
    const formTambahPelanggan = document.getElementById("formTambahPelanggan");
    const modalError = document.getElementById("modalError");
    const spinnerPelanggan = document.getElementById("spinnerPelanggan");
    const btnSimpanPelanggan = document.getElementById("btnSimpanPelanggan");

    formTambahPelanggan.addEventListener("submit", function(e) {
        e.preventDefault();
        
        modalError.style.display = "none";
        spinnerPelanggan.style.display = "inline-block";
        btnSimpanPelanggan.disabled = true;

        const formData = new FormData(formTambahPelanggan);

        fetch("index.php?page=api-pelanggan-tambah", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            spinnerPelanggan.style.display = "none";
            btnSimpanPelanggan.disabled = false;

            if (data.status === "success") {
                // Tambahkan pelanggan baru ke dropdown list
                const selectPelanggan = document.getElementById("id_pelanggan");
                const newOption = document.createElement("option");
                newOption.value = data.data.id_pelanggan;
                newOption.textContent = data.data.nama_pelanggan + " (Baru)";
                newOption.selected = true;
                selectPelanggan.appendChild(newOption);

                // Reset form & tutup modal
                formTambahPelanggan.reset();
                const modalEl = document.getElementById('modalTambahPelanggan');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
            } else {
                modalError.textContent = data.message;
                modalError.style.display = "block";
            }
        })
        .catch(err => {
            spinnerPelanggan.style.display = "none";
            btnSimpanPelanggan.disabled = false;
            modalError.textContent = "Koneksi gagal/error server!";
            modalError.style.display = "block";
            console.error(err);
        });
    });
});

// Copy receipt text to clipboard
function copyReceiptText(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert("Struk berhasil disalin! Silakan paste ke WhatsApp kurir/pelanggan.");
    }).catch(err => {
        console.error("Gagal menyalin text: ", err);
    });
}
</script>

<?php
require_once 'views/templates/footer.php';
?>
