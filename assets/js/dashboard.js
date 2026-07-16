// assets/js/dashboard.js
document.addEventListener("DOMContentLoaded", function() {
    if (typeof configDashboard === 'undefined') {
        console.error("configDashboard is not defined!");
        return;
    }

    // === 1. PRE-LOAD DATA PERIODS (Initial State) ===
    const activePeriode = configDashboard.periode;
    const currentTanggal = configDashboard.tanggal;
    
    let initialLabelsOmzet = [];
    let initialDataOmzet = [];
    let initialLabelsProduk = [];
    let initialDataProduk = [];

    if (activePeriode === 'harian') {
        initialLabelsOmzet = configDashboard.harian.labelsOmzet;
        initialDataOmzet = configDashboard.harian.dataOmzet;
        initialLabelsProduk = configDashboard.harian.labelsProduk;
        initialDataProduk = configDashboard.harian.dataProduk;
    } else if (activePeriode === 'mingguan') {
        initialLabelsOmzet = configDashboard.mingguan.labelsOmzet;
        initialDataOmzet = configDashboard.mingguan.dataOmzet;
        initialLabelsProduk = configDashboard.mingguan.labelsProduk;
        initialDataProduk = configDashboard.mingguan.dataProduk;
    } else if (activePeriode === 'bulanan') {
        initialLabelsOmzet = configDashboard.bulanan.labelsOmzet;
        initialDataOmzet = configDashboard.bulanan.dataOmzet;
        initialLabelsProduk = configDashboard.bulanan.labelsProduk;
        initialDataProduk = configDashboard.bulanan.dataProduk;
    }

    // State management for pagination
    let allTransactions = configDashboard.transaksi || [];
    let currentPage = 1;
    const pageSize = 10;
    let activeMode = activePeriode;

    // === 2. INITIALIZE CHARTS ===
    let omzetChart = null;
    let produkChart = null;

    // Omzet Chart (Line)
    const omzetEl = document.getElementById('omzetChart');
    if (omzetEl) {
        const ctxOmzet = omzetEl.getContext('2d');
        omzetChart = new Chart(ctxOmzet, {
            type: 'line',
            data: {
                labels: initialLabelsOmzet,
                datasets: [{
                    label: 'Omzet Penjualan (Rp)',
                    data: initialDataOmzet,
                    borderColor: '#111f37',
                    backgroundColor: 'rgba(17, 31, 55, 0.04)',
                    borderWidth: 2.5,
                    tension: 0.35,
                    fill: true,
                    pointBackgroundColor: '#111f37',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.04)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Produk Chart (Donut Chart with Legend on the Right)
    const produkEl = document.getElementById('produkChart');
    if (produkEl) {
        const ctxProduk = produkEl.getContext('2d');
        const colorPalette = [
            '#111f37',
            '#2e3d54',
            '#475569',
            '#64748b',
            '#94a3b8',
            '#cbd5e1'
        ];
        produkChart = new Chart(ctxProduk, {
            type: 'doughnut',
            data: {
                labels: initialLabelsProduk,
                datasets: [{
                    data: initialDataProduk,
                    backgroundColor: colorPalette,
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 10,
                            padding: 15,
                            font: {
                                family: 'Outfit, sans-serif',
                                size: 11
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }

    // Helpers for dynamic visual rendering
    function formatRupiah(value) {
        return 'Rp ' + Math.round(value).toLocaleString('id-ID');
    }

    function formatKg(value) {
        return parseFloat(value).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + ' Kg';
    }

    function formatDateString(dateStr) {
        const parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const day = parseInt(parts[2], 10);
        const monthIndex = parseInt(parts[1], 10) - 1;
        return `${day} ${months[monthIndex]}`;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;")
                  .replace(/</g, "&lt;")
                  .replace(/>/g, "&gt;")
                  .replace(/"/g, "&quot;")
                  .replace(/'/g, "&#039;");
    }

    // === 3. SINKRONISASI FILTER TERPUSAT (AJAX) ===
    function loadDashboardData(tanggalAcuan, modeGrafik) {
        const url = `index.php?page=api-dashboard-chart&periode=${modeGrafik}&tanggal=${tanggalAcuan}`;
        
        fetch(url)
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success') {
                    // Update Line Chart (Omzet)
                    if (omzetChart) {
                        omzetChart.data.labels = res.omzet.labels;
                        omzetChart.data.datasets[0].data = res.omzet.data;
                        omzetChart.update();
                    }
                    // Update Donut Chart (Produk)
                    if (produkChart) {
                        produkChart.data.labels = res.produk.labels;
                        produkChart.data.datasets[0].data = res.produk.data;
                        produkChart.update();
                    }
                    // Update Metrics Card (DOM Manipulation)
                    const m = res.metrics;
                    
                    // Card 1
                    const elOmzetBersih = document.getElementById('valOmzetBersih');
                    const elOmzetKotor = document.getElementById('valOmzetKotor');
                    const elTotalAdjustment = document.getElementById('valTotalAdjustment');
                    const elTotalWriteOff = document.getElementById('valTotalWriteOff');
                    const elLabelPeriodeCard1 = document.getElementById('labelPeriodeCard1');
                    
                    if (elOmzetBersih) elOmzetBersih.textContent = formatRupiah(m.omzet_bersih);
                    if (elOmzetKotor) elOmzetKotor.textContent = formatRupiah(m.omzet_kotor);
                    if (elTotalAdjustment) elTotalAdjustment.textContent = '-Rp ' + Math.round(m.total_adjustment).toLocaleString('id-ID');
                    if (elTotalWriteOff) elTotalWriteOff.textContent = '-Rp ' + Math.round(m.total_writeoff).toLocaleString('id-ID');
                    if (elLabelPeriodeCard1) elLabelPeriodeCard1.textContent = m.label_periode;

                    // Card 2
                    const elVolumeTerjual = document.getElementById('valVolumeTerjual');
                    const elLabelPeriodeCard2 = document.getElementById('labelPeriodeCard2');
                    if (elVolumeTerjual) elVolumeTerjual.textContent = formatKg(m.volume_penjualan);
                    if (elLabelPeriodeCard2) elLabelPeriodeCard2.textContent = m.label_periode;

                    // Card 3
                    const elTotalPiutang = document.getElementById('valTotalPiutang');
                    if (elTotalPiutang) elTotalPiutang.textContent = formatRupiah(m.total_piutang);

                    // Card 4
                    const elSisaKirim = document.getElementById('valSisaKirim');
                    const elLabelTanggalCard4 = document.getElementById('labelTanggalCard4');
                    if (elSisaKirim) {
                        elSisaKirim.textContent = `Pagi: ${m.sisa_pagi.toFixed(1)} | Sore: ${m.sisa_sore.toFixed(1)} Kg`;
                    }
                    if (elLabelTanggalCard4) elLabelTanggalCard4.textContent = m.label_tanggal_operasional;

                    // Header Periode
                    const elLabelPeriodeLaporan = document.getElementById('labelPeriodeLaporan');
                    if (elLabelPeriodeLaporan) elLabelPeriodeLaporan.textContent = m.label_periode_header;

                    // Update local transactions data & reset page
                    allTransactions = res.transaksi || [];
                    currentPage = 1;
                    renderTransactions();
                } else {
                    console.error("Gagal memuat dashboard data:", res.message);
                }
            })
            .catch(err => {
                console.error("AJAX Error:", err);
            });
    }

    // === 4. DYNAMIC TRANSACTIONS RENDERER ===
    function renderTransactions() {
        const body = document.getElementById('transactionTableBody');
        if (!body) return;
        body.innerHTML = '';

        if (!allTransactions || allTransactions.length === 0) {
            body.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-receipt fa-3x mb-3 text-light"></i>
                        <p class="m-0">Tidak ada transaksi pada periode ini.</p>
                    </td>
                </tr>
            `;
            renderPagination(0);
            return;
        }

        const totalPages = Math.ceil(allTransactions.length / pageSize);
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = startIndex + pageSize;
        const pageItems = allTransactions.slice(startIndex, endIndex);

        pageItems.forEach(t => {
            const tr = document.createElement('tr');
            
            // Format amounts
            const totalHarga = parseFloat(t.total_harga);
            const writeoff = parseFloat(t.total_writeoff_nota || 0);
            const adjustment = parseFloat(t.total_adjustment_nota || 0);
            const realizedRevenue = Math.max(0, totalHarga - writeoff - adjustment);
            const totalBerat = parseFloat(t.total_berat_akumulatif || 0);
            const realVolume = totalHarga > 0 ? totalBerat * (realizedRevenue / totalHarga) : 0;
            
            // Formatting time
            const waktuStr = t.waktu.substring(0, 5); // "HH:MM"
            const tanggalStr = formatDateString(t.tanggal);

            // Conditional date badge
            const dateBlock = activeMode !== 'harian' ? `<small class="text-muted d-block small-text-date">${tanggalStr}</small>` : '';
            const mobileDateBlock = activeMode !== 'harian' ? ` | ${tanggalStr}` : '';

            // Slot badge
            const slotPill = t.slot_waktu === 'Pagi' 
                ? '<i class="fa-solid fa-cloud-sun text-warning me-1"></i>' 
                : '<i class="fa-solid fa-cloud-moon text-info me-1"></i>';

            // Payment badge
            const paymentBadge = t.metode_pembayaran === 'Lunas' ? 'bg-success' : 'bg-danger';

            // Delivery Status badge
            let statusBadge = 'bg-warning';
            if (t.status_pengiriman === 'Selesai') statusBadge = 'bg-success';
            else if (t.status_pengiriman === 'Pre-Order') statusBadge = 'bg-info';

            // Additional markers
            let noteBadge = '';
            if (writeoff > 0) {
                noteBadge = '<span class="badge bg-light text-secondary border ms-1 d-block d-md-inline-block mt-1 mt-md-0 badge-text-normal"><i class="fa-solid fa-ban me-1 text-muted"></i> Bermasalah</span>';
            } else if (adjustment > 0) {
                noteBadge = '<span class="badge bg-light text-secondary border ms-1 d-block d-md-inline-block mt-1 mt-md-0 badge-text-normal"><i class="fa-solid fa-scissors me-1 text-muted"></i> Potongan</span>';
            }

            // Correction action condition (Only allowed if transaction date is today or future)
            const todayStr = configDashboard.todayDate;
            let correctionOption = '';
            if (t.tanggal >= todayStr) {
                correctionOption = `
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form action="index.php?page=transaksi-koreksi" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi #${t.id_transaksi}? Tindakan ini akan mengembalikan stok & membatalkan hutang terkait.');">
                            <input type="hidden" name="id_transaksi" value="${t.id_transaksi}">
                            <button type="submit" class="dropdown-item py-2 text-danger border-0 bg-transparent w-100 text-start">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i> Koreksi
                            </button>
                        </form>
                    </li>
                `;
            }

            tr.innerHTML = `
                <td class="font-monospace font-weight-bold">
                    #${t.id_transaksi}
                    <div class="d-md-none text-muted small trans-mobile-detail">
                        ${waktuStr} WIB${mobileDateBlock}
                    </div>
                </td>
                <td class="d-none d-md-table-cell">
                    ${dateBlock}
                    ${waktuStr} WIB
                </td>
                <td>
                    <strong>${escapeHtml(t.nama_pelanggan)}</strong><br>
                    <small class="text-muted">${escapeHtml(t.no_hp)}</small>
                    <div class="d-md-none mt-1">
                        <span class="badge bg-light text-dark p-1 badge-compact">
                            ${slotPill} ${t.slot_waktu}
                        </span>
                        <span class="badge ${paymentBadge} p-1 ms-1 badge-compact">
                            ${t.metode_pembayaran}
                        </span>
                    </div>
                </td>
                <td class="d-none d-md-table-cell">
                    <span class="badge bg-light text-dark">
                        ${slotPill} ${t.slot_waktu}
                    </span>
                </td>
                <td class="d-none d-md-table-cell">
                    <span class="badge ${paymentBadge}">
                        ${t.metode_pembayaran}
                    </span>
                </td>
                <td class="d-none d-md-table-cell font-weight-bold">
                    ${realVolume.toFixed(2)} Kg
                </td>
                <td class="font-weight-bold">
                    Rp ${Math.round(realizedRevenue).toLocaleString('id-ID')}
                    <div class="d-md-none text-muted small trans-mobile-detail">
                        ${realVolume.toFixed(2)} Kg
                    </div>
                </td>
                <td>
                    <span class="badge ${statusBadge}">
                        ${t.status_pengiriman}
                    </span>
                    ${noteBadge}
                </td>
                <td class="text-center">
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-light border dropdown-toggle dropdown-btn-compact" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false">
                            <i class="fa-solid fa-ellipsis-vertical text-muted"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 dropdown-menu-custom">
                            <li>
                                <a class="dropdown-item py-2 detail-btn" href="#">
                                    <i class="fa-solid fa-eye me-2 text-sea-blue"></i> Detail
                                </a>
                            </li>
                            ${correctionOption}
                        </ul>
                    </div>
                </td>
            `;

            // Attach event listener for the Detail button
            const detailBtn = tr.querySelector('.detail-btn');
            if (detailBtn) {
                detailBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    showDetailModal(t);
                });
            }

            body.appendChild(tr);
        });

        renderPagination(totalPages);
    }

    // === 5. DYNAMIC PAGINATION CONTROL RENDERER ===
    function renderPagination(totalPages) {
        const container = document.getElementById('transactionPagination');
        if (!container) return;
        container.innerHTML = '';

        if (totalPages <= 1) return;

        // Tombol Prev
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage <= 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `
            <a class="page-link text-orange-link" href="#" aria-label="Previous">
                <i class="fa-solid fa-angle-left"></i>
            </a>
        `;
        prevLi.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                renderTransactions();
                scrollToDaftarTransaksi();
            }
        });
        container.appendChild(prevLi);

        // Angka Halaman
        for (let i = 1; i <= totalPages; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${i === currentPage ? 'active active-orange' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            pageLi.addEventListener('click', function(e) {
                e.preventDefault();
                currentPage = i;
                renderTransactions();
                scrollToDaftarTransaksi();
            });
            container.appendChild(pageLi);
        }

        // Tombol Next
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage >= totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `
            <a class="page-link text-orange-link" href="#" aria-label="Next">
                <i class="fa-solid fa-angle-right"></i>
            </a>
        `;
        nextLi.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                renderTransactions();
                scrollToDaftarTransaksi();
            }
        });
        container.appendChild(nextLi);
    }

    function scrollToDaftarTransaksi() {
        const el = document.getElementById('daftar-transaksi');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // === 6. DYNAMIC REUSABLE DETAIL MODAL POPULATOR ===
    function showDetailModal(t) {
        document.getElementById('modalTxId').textContent = '#' + t.id_transaksi;
        document.getElementById('modalKasirName').textContent = t.nama_pengguna;
        document.getElementById('modalDeliveryAddress').textContent = t.alamat || '-';
        
        const itemsContainer = document.getElementById('modalItemsContainer');
        itemsContainer.innerHTML = '';

        let totalBerat = 0;

        if (t.details && t.details.length > 0) {
            t.details.forEach(it => {
                const berat = parseFloat(it.jumlah_berat_kg);
                const harga = parseFloat(it.harga_satuan || 0);
                const subtotal = parseFloat(it.subtotal || 0);
                
                totalBerat += berat;
                
                const div = document.createElement('div');
                div.className = 'd-flex justify-content-between mb-1';
                div.innerHTML = `
                    <span>- ${escapeHtml(it.nama_produk)}</span>
                    <span>${berat.toFixed(2)} Kg x Rp${Math.round(harga).toLocaleString('id-ID')} = Rp${Math.round(subtotal).toLocaleString('id-ID')}</span>
                `;
                itemsContainer.appendChild(div);
            });
        }

        const totalWriteoff = parseFloat(t.total_writeoff_nota || 0);
        const totalAdjustment = parseFloat(t.total_adjustment_nota || 0);
        const netTagihan = Math.max(0, parseFloat(t.total_harga) - totalWriteoff - totalAdjustment);

        document.getElementById('modalTotalWeight').textContent = totalBerat.toFixed(2) + ' Kg';
        document.getElementById('modalTotalAmount').textContent = 'Rp ' + Math.round(netTagihan).toLocaleString('id-ID');

        // Trigger Bootstrap modal manually
        const modalEl = document.getElementById('modalDetailDynamic');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

    // === 7. INITIAL RENDER ===
    renderTransactions();

    // === 8. EVENT LISTENERS FOR FILTERS (AJAX-based, smooth transitions) ===
    const btnHarian = document.getElementById('btnHarian');
    const btnMingguan = document.getElementById('btnMingguan');
    const btnBulanan = document.getElementById('btnBulanan');
    const dateInput = document.getElementById('tanggal');
    const dateForm = document.querySelector('form[action="index.php"]');

    function updateActiveButton(activeBtn) {
        [btnHarian, btnMingguan, btnBulanan].forEach(btn => {
            if (btn) btn.classList.remove('active');
        });
        if (activeBtn) activeBtn.classList.add('active');
    }

    if (btnHarian) {
        btnHarian.addEventListener('click', function() {
            activeMode = 'harian';
            updateActiveButton(btnHarian);
            const dateValue = dateInput ? dateInput.value : currentTanggal;
            loadDashboardData(dateValue, activeMode);
        });
    }

    if (btnMingguan) {
        btnMingguan.addEventListener('click', function() {
            activeMode = 'mingguan';
            updateActiveButton(btnMingguan);
            const dateValue = dateInput ? dateInput.value : currentTanggal;
            loadDashboardData(dateValue, activeMode);
        });
    }

    if (btnBulanan) {
        btnBulanan.addEventListener('click', function() {
            activeMode = 'bulanan';
            updateActiveButton(btnBulanan);
            const dateValue = dateInput ? dateInput.value : currentTanggal;
            loadDashboardData(dateValue, activeMode);
        });
    }

    // Date Input change triggers unified AJAX update
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            loadDashboardData(this.value, activeMode);
        });
    }

    // Intercept form submit to also use unified AJAX
    if (dateForm) {
        dateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const dateValue = dateInput ? dateInput.value : currentTanggal;
            loadDashboardData(dateValue, activeMode);
        });
    }
});
