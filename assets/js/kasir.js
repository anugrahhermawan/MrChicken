// assets/js/kasir.js
document.addEventListener("DOMContentLoaded", function() {
    const containerItem = document.getElementById("containerItem");
    const btnTambahItem = document.getElementById("btnTambahItem");
    const liveGrandTotal = document.getElementById("liveGrandTotal");
    const liveTotalBerat = document.getElementById("liveTotalBerat");

    // Toggle due date input based on payment method
    const bayarLunas = document.getElementById("bayarLunas");
    const bayarHutang = document.getElementById("bayarHutang");
    const dueDateContainer = document.getElementById("dueDateContainer");
    const dueDateInput = document.getElementById("due_date");

    function toggleDueDate() {
        if (bayarHutang && bayarHutang.checked) {
            if (dueDateContainer) dueDateContainer.style.display = "block";
            if (dueDateInput) {
                dueDateInput.required = true;
                if (!dueDateInput.value) {
                    const defaultDate = new Date();
                    // Gunakan config default_due_days jika ada
                    const dueDays = typeof configPOS !== 'undefined' && configPOS.defaultDueDays ? configPOS.defaultDueDays : 7;
                    defaultDate.setDate(defaultDate.getDate() + dueDays);
                    const yyyy = defaultDate.getFullYear();
                    const mm = String(defaultDate.getMonth() + 1).padStart(2, '0');
                    const dd = String(defaultDate.getDate()).padStart(2, '0');
                    dueDateInput.value = `${yyyy}-${mm}-${dd}`;
                }
            }
        } else {
            if (dueDateContainer) dueDateContainer.style.display = "none";
            if (dueDateInput) {
                dueDateInput.required = false;
                dueDateInput.value = "";
            }
        }
    }

    if (bayarLunas) bayarLunas.addEventListener("change", toggleDueDate);
    if (bayarHutang) bayarHutang.addEventListener("change", toggleDueDate);

    // Beban slot terpakai awal dari database (lewat configPOS)
    const initialBebanPagi = typeof configPOS !== 'undefined' ? parseFloat(configPOS.initialBebanPagi) || 0.0 : 0.0;
    const initialBebanSore = typeof configPOS !== 'undefined' ? parseFloat(configPOS.initialBebanSore) || 0.0 : 0.0;
    const maxSlotKg = typeof configPOS !== 'undefined' ? parseFloat(configPOS.maxSlotKg) || 60.0 : 60.0;

    // Format Rupiah
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(angka);
    }

    // Update Visual Slot Kuota secara Real-Time
    function updateSlotVisuals(totalBerat) {
        // Pagi
        const prospectivePagi = initialBebanPagi + totalBerat;
        const sisaPagi = Math.max(0.0, maxSlotKg - prospectivePagi);
        const pagiProgress = Math.min(100.0, (prospectivePagi / maxSlotKg) * 100);
        
        const pagiTextStatus = document.getElementById("pagiTextStatus");
        const pagiProgressBar = document.getElementById("pagiProgressBar");
        const pagiTerisiText = document.getElementById("pagiTerisiText");
        
        if (pagiTextStatus && pagiProgressBar && pagiTerisiText) {
            if (prospectivePagi >= maxSlotKg) {
                pagiTextStatus.innerHTML = '<span class="text-danger small font-weight-bold"><i class="fa-solid fa-triangle-exclamation"></i> Penuh (Pre-Order)</span>';
                pagiProgressBar.className = "progress-bar bg-danger";
            } else {
                pagiTextStatus.innerHTML = `<span class="text-muted small">Sisa: ${sisaPagi.toFixed(2)} Kg</span>`;
                pagiProgressBar.className = `progress-bar ${prospectivePagi >= 55.0 ? 'bg-danger' : (prospectivePagi > 40.0 ? 'bg-warning' : 'bg-success')}`;
            }
            pagiProgressBar.style.width = pagiProgress + "%";
            pagiTerisiText.textContent = `Terisi: ${prospectivePagi.toFixed(2)} / ${maxSlotKg} Kg`;
        }

        // Sore
        const prospectiveSore = initialBebanSore + totalBerat;
        const sisaSore = Math.max(0.0, maxSlotKg - prospectiveSore);
        const soreProgress = Math.min(100.0, (prospectiveSore / maxSlotKg) * 100);
        
        const soreTextStatus = document.getElementById("soreTextStatus");
        const soreProgressBar = document.getElementById("soreProgressBar");
        const soreTerisiText = document.getElementById("soreTerisiText");
        
        if (soreTextStatus && soreProgressBar && soreTerisiText) {
            if (prospectiveSore >= maxSlotKg) {
                soreTextStatus.innerHTML = '<span class="text-danger small font-weight-bold"><i class="fa-solid fa-triangle-exclamation"></i> Penuh (Pre-Order)</span>';
                soreProgressBar.className = "progress-bar bg-danger";
            } else {
                soreTextStatus.innerHTML = `<span class="text-muted small">Sisa: ${sisaSore.toFixed(2)} Kg</span>`;
                soreProgressBar.className = `progress-bar ${prospectiveSore >= 55.0 ? 'bg-danger' : (prospectiveSore > 40.0 ? 'bg-warning' : 'bg-success')}`;
            }
            soreProgressBar.style.width = soreProgress + "%";
            soreTerisiText.textContent = `Terisi: ${prospectiveSore.toFixed(2)} / ${maxSlotKg} Kg`;
        }
    }

    // Hitung Ulang Total
    function kalkulasiTotal() {
        let grandTotal = 0;
        let totalBerat = 0.0;

        document.querySelectorAll(".item-row").forEach(row => {
            const selectProduk = row.querySelector(".select-produk");
            const inputBerat = row.querySelector(".input-berat");
            const subtotalVal = row.querySelector(".subtotal-val");

            if (selectProduk && inputBerat && subtotalVal) {
                const selectedOption = selectProduk.options[selectProduk.selectedIndex];
                const harga = selectedOption ? parseFloat(selectedOption.getAttribute("data-harga")) || 0 : 0;
                const berat = parseFloat(inputBerat.value) || 0;

                const subtotal = Math.round(berat * harga);
                subtotalVal.value = subtotal.toLocaleString('id-ID');

                grandTotal += subtotal;
                totalBerat += berat;
            }
        });

        if (liveGrandTotal) liveGrandTotal.textContent = formatRupiah(grandTotal);
        if (liveTotalBerat) liveTotalBerat.textContent = totalBerat.toFixed(2) + " Kg";

        // Update Mobile Sticky Bottom Bar values
        const mobTotal = document.getElementById("mobileLiveGrandTotal");
        const mobBerat = document.getElementById("mobileLiveTotalBerat");
        if (mobTotal) mobTotal.textContent = formatRupiah(grandTotal);
        if (mobBerat) mobBerat.textContent = totalBerat.toFixed(2);

        updateSlotVisuals(totalBerat);
    }

    // Event listener untuk perubahan pada select-produk & input-berat
    if (containerItem) {
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
    }

    // Tambah Baris Item Baru
    if (btnTambahItem) {
        btnTambahItem.addEventListener("click", function() {
            const itemRows = document.querySelectorAll(".item-row");
            if (itemRows.length > 0) {
                const template = itemRows[0].cloneNode(true);
                
                // Reset values
                template.querySelector(".select-produk").selectedIndex = 0;
                template.querySelector(".input-berat").value = "";
                template.querySelector(".subtotal-val").value = "0";

                // Tampilkan tombol hapus
                const btnHapus = template.querySelector(".btn-hapus-item");
                if (btnHapus) btnHapus.style.display = "block";

                // Tambah ke container
                containerItem.appendChild(template);
                
                // Refresh delete listeners
                refreshHapusListeners();
                kalkulasiTotal();
            }
        });
    }

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

    if (formTambahPelanggan) {
        formTambahPelanggan.addEventListener("submit", function(e) {
            e.preventDefault();
            
            if (modalError) modalError.style.display = "none";
            if (spinnerPelanggan) spinnerPelanggan.style.display = "inline-block";
            if (btnSimpanPelanggan) btnSimpanPelanggan.disabled = true;

            const formData = new FormData(formTambahPelanggan);

            fetch("index.php?page=api-pelanggan-tambah", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (spinnerPelanggan) spinnerPelanggan.style.display = "none";
                if (btnSimpanPelanggan) btnSimpanPelanggan.disabled = false;

                if (data.status === "success") {
                    // Tambahkan pelanggan baru ke dropdown list
                    const selectPelanggan = document.getElementById("id_pelanggan");
                    if (selectPelanggan) {
                        const newOption = document.createElement("option");
                        newOption.value = data.data.id_pelanggan;
                        newOption.textContent = data.data.nama_pelanggan + " (Baru)";
                        newOption.selected = true;
                        selectPelanggan.appendChild(newOption);
                    }

                    // Reset form & tutup modal
                    formTambahPelanggan.reset();
                    const modalEl = document.getElementById('modalTambahPelanggan');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                } else {
                    if (modalError) {
                        modalError.textContent = data.message;
                        modalError.style.display = "block";
                    }
                }
            })
            .catch(err => {
                if (spinnerPelanggan) spinnerPelanggan.style.display = "none";
                if (btnSimpanPelanggan) btnSimpanPelanggan.disabled = false;
                if (modalError) {
                    modalError.textContent = "Koneksi gagal/error server!";
                    modalError.style.display = "block";
                }
                console.error(err);
            });
        });
    }
});

// Copy receipt text to clipboard
function copyReceiptText(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            alert("Struk berhasil disalin! Silakan paste ke WhatsApp kurir/pelanggan.");
        }).catch(err => {
            console.error("Gagal menyalin text: ", err);
        });
    } else {
        // Fallback
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            alert("Struk berhasil disalin! Silakan paste ke WhatsApp kurir/pelanggan.");
        } catch (err) {
            console.error("Fallback gagal menyalin: ", err);
        }
        document.body.removeChild(textArea);
    }
}
