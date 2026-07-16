<?php
// controllers/DashboardController.php
require_once 'core/controller.php';
require_once 'models/transaksi.php';
require_once 'models/produk.php';
require_once 'models/hutang.php';
require_once 'models/pelanggan.php';

class DashboardController extends Controller {
    private Transaksi $transaksiModel;
    private Produk $produkModel;
    private Hutang $hutangModel;
    private Pelanggan $pelangganModel;

    public function __construct(PDO $db) {
        $this->transaksiModel = new Transaksi($db);
        $this->produkModel = new Produk($db);
        $this->hutangModel = new Hutang($db);
        $this->pelangganModel = new Pelanggan($db);
    }

    private function checkOwner(): void {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Owner') {
            $_SESSION['error'] = "Akses ditolak! Halaman ini hanya untuk Owner.";
            header('Location: index.php?page=kasir');
            exit();
        }
    }

    public function dashboard(): void {
        $this->checkOwner();

        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $periode = $_GET['periode'] ?? 'harian';

        // Tentukan rentang tanggal berdasarkan periode untuk data transaksi
        if ($periode === 'harian') {
            $transaksiPeriode = $this->transaksiModel->getPengirimanHarian($tanggal);
            $totalWriteOff = $this->hutangModel->getTotalByTipeDanTanggal(STATUS_HUTANG_WRITEOFF, $tanggal);
            $totalAdjustment = $this->hutangModel->getTotalByTipeDanTanggal(STATUS_HUTANG_ADJUSTMENT, $tanggal);
            $totalCicilan = $this->hutangModel->getTotalByTipeDanTanggal('Bayar', $tanggal);
            $labelPeriode = date('d M Y', strtotime($tanggal));
            $labelPeriodeLaporan = date('d F Y', strtotime($tanggal));
        } elseif ($periode === 'mingguan') {
            $startDate = date('Y-m-d', strtotime($tanggal . ' - 6 days'));
            $transaksiPeriode = $this->transaksiModel->getPengirimanRentangTanggal($startDate, $tanggal);
            $totalWriteOff = $this->hutangModel->getTotalByTipeDanRentangTanggal(STATUS_HUTANG_WRITEOFF, $startDate, $tanggal);
            $totalAdjustment = $this->hutangModel->getTotalByTipeDanRentangTanggal(STATUS_HUTANG_ADJUSTMENT, $startDate, $tanggal);
            $totalCicilan = $this->hutangModel->getTotalByTipeDanRentangTanggal('Bayar', $startDate, $tanggal);
            $rangeLabel = date('d M', strtotime($startDate)) . ' - ' . date('d M', strtotime($tanggal));
            $labelPeriode = $rangeLabel;
            $labelPeriodeLaporan = '7 Hari Terakhir (' . $rangeLabel . ')';
        } else { // bulanan
            $year = date('Y', strtotime($tanggal));
            $startDate = "$year-01-01";
            $endDate = "$year-12-31";
            $transaksiPeriode = $this->transaksiModel->getPengirimanRentangTanggal($startDate, $endDate);
            $totalWriteOff = $this->hutangModel->getTotalByTipeDanRentangTanggal(STATUS_HUTANG_WRITEOFF, $startDate, $endDate);
            $totalAdjustment = $this->hutangModel->getTotalByTipeDanRentangTanggal(STATUS_HUTANG_ADJUSTMENT, $startDate, $endDate);
            $totalCicilan = $this->hutangModel->getTotalByTipeDanRentangTanggal('Bayar', $startDate, $endDate);
            $labelPeriode = "Tahun $year";
            $labelPeriodeLaporan = "Tahun $year";
        }

        // Hitung metrik kotor & volume
        $omzetKotor = 0;
        $volumeRealized = 0;
        foreach ($transaksiPeriode as $t) {
            if ($t->status_pengiriman === STATUS_PENGIRIMAN_SELESAI) {
                $omzetKotor += $t->total_harga;
                
                $writeoff = isset($t->total_writeoff_nota) ? (int)$t->total_writeoff_nota : 0;
                $adjustment = isset($t->total_adjustment_nota) ? (int)$t->total_adjustment_nota : 0;
                $realizedRevenue = max(0, $t->total_harga - $writeoff - $adjustment);
                
                if ($t->total_harga > 0) {
                    $realizedVolume = $t->total_berat_akumulatif * ($realizedRevenue / $t->total_harga);
                    $volumeRealized += $realizedVolume;
                }
            }
        }

        $omzetBersih = max(0, $omzetKotor - $totalWriteOff - $totalAdjustment);

        // Paginasi: Batasi 10 transaksi per halaman (selalu gunakan transaksi sesuai tanggal/rentang tanggal yang terpilih)
        $total_records = count($transaksiPeriode);
        $limit = 10;
        $total_pages = ceil($total_records / $limit);
        if ($total_pages < 1) $total_pages = 1;

        $currentPageNum = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($currentPageNum < 1) $currentPageNum = 1;
        if ($currentPageNum > $total_pages) $currentPageNum = $total_pages;

        $offset = ($currentPageNum - 1) * $limit;
        $pagedTransaksi = array_slice($transaksiPeriode, $offset, $limit);

        foreach ($pagedTransaksi as $t) {
            $t->details = $this->transaksiModel->getDetails($t->id_transaksi);
        }

        // Card 3: Piutang Berjalan (ALL-TIME REAL-TIME UNPAID)
        $totalPiutang = 0;
        $pelanggan = $this->pelangganModel->getAll();
        foreach ($pelanggan as $p) {
            $totalPiutang += $p->saldo_hutang;
        }

        // Card 4: Kapasitas Logistik untuk tanggalAcuan
        $beratPagi = $this->transaksiModel->getBeratAkumulatif($tanggal, 'Pagi');
        $beratSore = $this->transaksiModel->getBeratAkumulatif($tanggal, 'Sore');
        $sisaPagi = max(0.0, MAX_SLOT_KG - $beratPagi);
        $sisaSore = max(0.0, MAX_SLOT_KG - $beratSore);

        // Laporan grafik
        $harianOmzet = $this->transaksiModel->getOmzetStats($tanggal);
        $mingguanOmzet = $this->transaksiModel->getWeeklyOmzetStats($tanggal);
        $bulananOmzet = $this->transaksiModel->getMonthlyOmzetStats($tanggal);

        $harianProduk = $this->transaksiModel->getProductSalesStatsByPeriod(0, $tanggal);
        $mingguanProduk = $this->transaksiModel->getProductSalesStatsByPeriod(7, $tanggal);
        $bulananProduk = $this->transaksiModel->getProductSalesStatsByPeriod(30, $tanggal);

        $data = [
            'tanggal' => $tanggal,
            'periode' => $periode,
            'transaksi' => $pagedTransaksi,
            'omzet' => $omzetKotor,
            'omzetBersih' => $omzetBersih,
            'volume' => $volumeRealized,
            'sisa_pagi' => $sisaPagi,
            'sisa_sore' => $sisaSore,
            'total_piutang' => $totalPiutang,
            'total_writeoff' => $totalWriteOff,
            'total_adjustment' => $totalAdjustment,
            'total_cicilan' => $totalCicilan,
            'chart_data_harian' => $harianOmzet,
            'chart_data_mingguan' => $mingguanOmzet,
            'chart_data_bulanan' => $bulananOmzet,
            'product_harian' => $harianProduk,
            'product_mingguan' => $mingguanProduk,
            'product_bulanan' => $bulananProduk,
            'current_page' => $currentPageNum,
            'total_pages' => $total_pages,
            'labelPeriode' => $labelPeriode,
            'labelPeriodeLaporan' => $labelPeriodeLaporan
        ];

        $this->view('owner/dashboard', $data);
    }

    public function apiDashboardChart(): void {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Owner') {
            echo json_encode(['status' => 'error', 'message' => 'Akses ditolak!']);
            return;
        }

        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $periode = $_GET['periode'] ?? 'harian';

        // Hitung parameter periode & metrik sinkron
        if ($periode === 'harian') {
            $transaksiPeriode = $this->transaksiModel->getPengirimanHarian($tanggal);
            $totalWriteOff = $this->hutangModel->getTotalByTipeDanTanggal(STATUS_HUTANG_WRITEOFF, $tanggal);
            $totalAdjustment = $this->hutangModel->getTotalByTipeDanTanggal(STATUS_HUTANG_ADJUSTMENT, $tanggal);
            $totalCicilan = $this->hutangModel->getTotalByTipeDanTanggal('Bayar', $tanggal);
            $labelPeriode = date('d M Y', strtotime($tanggal));
            $labelPeriodeLaporan = date('d F Y', strtotime($tanggal));
        } elseif ($periode === 'mingguan') {
            $startDate = date('Y-m-d', strtotime($tanggal . ' - 6 days'));
            $transaksiPeriode = $this->transaksiModel->getPengirimanRentangTanggal($startDate, $tanggal);
            $totalWriteOff = $this->hutangModel->getTotalByTipeDanRentangTanggal(STATUS_HUTANG_WRITEOFF, $startDate, $tanggal);
            $totalAdjustment = $this->hutangModel->getTotalByTipeDanRentangTanggal(STATUS_HUTANG_ADJUSTMENT, $startDate, $tanggal);
            $totalCicilan = $this->hutangModel->getTotalByTipeDanRentangTanggal('Bayar', $startDate, $tanggal);
            $rangeLabel = date('d M', strtotime($startDate)) . ' - ' . date('d M', strtotime($tanggal));
            $labelPeriode = $rangeLabel;
            $labelPeriodeLaporan = '7 Hari Terakhir (' . $rangeLabel . ')';
        } else { // bulanan
            $year = date('Y', strtotime($tanggal));
            $startDate = "$year-01-01";
            $endDate = "$year-12-31";
            $transaksiPeriode = $this->transaksiModel->getPengirimanRentangTanggal($startDate, $endDate);
            $totalWriteOff = $this->hutangModel->getTotalByTipeDanRentangTanggal(STATUS_HUTANG_WRITEOFF, $startDate, $endDate);
            $totalAdjustment = $this->hutangModel->getTotalByTipeDanRentangTanggal(STATUS_HUTANG_ADJUSTMENT, $startDate, $endDate);
            $totalCicilan = $this->hutangModel->getTotalByTipeDanRentangTanggal('Bayar', $startDate, $endDate);
            $labelPeriode = "Tahun $year";
            $labelPeriodeLaporan = "Tahun $year";
        }

        $omzetKotor = 0;
        $volumeRealized = 0;
        foreach ($transaksiPeriode as $t) {
            if ($t->status_pengiriman === STATUS_PENGIRIMAN_SELESAI) {
                $omzetKotor += $t->total_harga;
                
                $writeoff = isset($t->total_writeoff_nota) ? (int)$t->total_writeoff_nota : 0;
                $adjustment = isset($t->total_adjustment_nota) ? (int)$t->total_adjustment_nota : 0;
                $realizedRevenue = max(0, $t->total_harga - $writeoff - $adjustment);
                
                if ($t->total_harga > 0) {
                    $realizedVolume = $t->total_berat_akumulatif * ($realizedRevenue / $t->total_harga);
                    $volumeRealized += $realizedVolume;
                }
            }
        }

        $omzetBersih = max(0, $omzetKotor - $totalWriteOff - $totalAdjustment);

        // Card 3: Piutang Berjalan (ALL-TIME REAL-TIME UNPAID)
        $totalPiutang = 0;
        $pelanggan = $this->pelangganModel->getAll();
        foreach ($pelanggan as $p) {
            $totalPiutang += $p->saldo_hutang;
        }

        // Card 4: Kapasitas Logistik untuk tanggalAcuan
        $beratPagi = $this->transaksiModel->getBeratAkumulatif($tanggal, 'Pagi');
        $beratSore = $this->transaksiModel->getBeratAkumulatif($tanggal, 'Sore');
        $sisaPagi = max(0.0, MAX_SLOT_KG - $beratPagi);
        $sisaSore = max(0.0, MAX_SLOT_KG - $beratSore);

        // Laporan grafik
        if ($periode === 'harian') {
            $omzetData = $this->transaksiModel->getOmzetStats($tanggal);
            $productData = $this->transaksiModel->getProductSalesStatsByPeriod(0, $tanggal);
        } elseif ($periode === 'mingguan') {
            $omzetData = $this->transaksiModel->getWeeklyOmzetStats($tanggal);
            $productData = $this->transaksiModel->getProductSalesStatsByPeriod(7, $tanggal);
        } else {
            $omzetData = $this->transaksiModel->getMonthlyOmzetStats($tanggal);
            $productData = $this->transaksiModel->getProductSalesStatsByPeriod(30, $tanggal);
        }

        $labelsOmzet = [];
        $dataOmzet = [];
        foreach ($omzetData as $cd) {
            $labelsOmzet[] = $cd['label'];
            $dataOmzet[] = (int)$cd['omzet'];
        }

        $labelsProduk = [];
        $dataProduk = [];
        foreach ($productData as $pd) {
            $labelsProduk[] = $pd['nama_produk'];
            $dataProduk[] = round((float)$pd['total_kg'], 1);
        }

        foreach ($transaksiPeriode as $t) {
            $t->details = $this->transaksiModel->getDetails($t->id_transaksi);
        }

        echo json_encode([
            'status' => 'success',
            'periode' => $periode,
            'tanggal' => $tanggal,
            'metrics' => [
                'omzet_bersih' => $omzetBersih,
                'omzet_kotor' => $omzetKotor,
                'total_adjustment' => $totalAdjustment,
                'total_writeoff' => $totalWriteOff,
                'volume_penjualan' => $volumeRealized,
                'sisa_pagi' => $sisaPagi,
                'sisa_sore' => $sisaSore,
                'total_piutang' => $totalPiutang,
                'total_cicilan' => $totalCicilan,
                'label_periode' => $labelPeriode,
                'label_tanggal_operasional' => date('d M', strtotime($tanggal)),
                'label_periode_header' => $labelPeriodeLaporan
            ],
            'omzet' => [
                'labels' => $labelsOmzet,
                'data' => $dataOmzet
            ],
            'produk' => [
                'labels' => $labelsProduk,
                'data' => $dataProduk
            ],
            'transaksi' => $transaksiPeriode
        ]);
    }
}
