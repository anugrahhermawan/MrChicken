<?php
// controllers/HutangController.php
require_once 'core/controller.php';
require_once 'models/transaksi.php';
require_once 'models/produk.php';
require_once 'models/hutang.php';
require_once 'models/pelanggan.php';

class HutangController extends Controller {
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

    // Memeriksa apakah user saat ini adalah Owner
    private function checkOwner(): void {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Owner') {
            $_SESSION['error'] = "Akses ditolak! Halaman ini hanya untuk Owner.";
            header('Location: index.php?page=kasir');
            exit();
        }
    }

    // Dashboard Owner dengan paginasi transaksi (maks 10 per halaman)
    public function dashboard(): void {
        $this->checkOwner();

        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');

        // Ambil data transaksi hari ini
        $transaksiHariIni = $this->transaksiModel->getPengirimanHarian($tanggal);

        // Hitung metrik dashboard berdasarkan semua transaksi hari ini
        $omzetHariIni = 0;
        $volumeHariIni = 0;
        foreach ($transaksiHariIni as $t) {
            if ($t->status_pengiriman === 'Selesai') {
                $omzetHariIni += $t->total_harga;
                $volumeHariIni += $t->total_berat_akumulatif;
            }
        }

        // Paginasi: Batasi 10 transaksi per halaman
        $total_records = count($transaksiHariIni);
        $limit = 10;
        $total_pages = ceil($total_records / $limit);
        if ($total_pages < 1) $total_pages = 1;

        $currentPageNum = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($currentPageNum < 1) $currentPageNum = 1;
        if ($currentPageNum > $total_pages) $currentPageNum = $total_pages;

        $offset = ($currentPageNum - 1) * $limit;
        $pagedTransaksi = array_slice($transaksiHariIni, $offset, $limit);

        // Preload details HANYA untuk transaksi yang ada di halaman aktif
        foreach ($pagedTransaksi as $t) {
            $t->details = $this->transaksiModel->getDetails($t->id_transaksi);
        }

        // Hitung sisa slot pengiriman hari ini
        $beratPagi = $this->transaksiModel->getBeratAkumulatif($tanggal, 'Pagi');
        $beratSore = $this->transaksiModel->getBeratAkumulatif($tanggal, 'Sore');

        $sisaPagi = max(0.0, 60.0 - $beratPagi);
        $sisaSore = max(0.0, 60.0 - $beratSore);

        // Hitung total piutang yang belum tertagih (dari semua pelanggan)
        $totalPiutang = 0;
        $pelanggan = $this->pelangganModel->getAll();
        foreach ($pelanggan as $p) {
            $totalPiutang += $p->saldo_hutang;
        }

        // Laporan grafik harian, mingguan, bulanan
        $harianOmzet = array_reverse($this->transaksiModel->getOmzetStats($tanggal));
        $mingguanOmzet = array_reverse($this->transaksiModel->getWeeklyOmzetStats($tanggal));
        $bulananOmzet = array_reverse($this->transaksiModel->getMonthlyOmzetStats($tanggal));

        // Laporan produk harian (7 hari), mingguan (30 hari), bulanan (180 hari)
        $harianProduk = $this->transaksiModel->getProductSalesStatsByPeriod(7, $tanggal);
        $mingguanProduk = $this->transaksiModel->getProductSalesStatsByPeriod(30, $tanggal);
        $bulananProduk = $this->transaksiModel->getProductSalesStatsByPeriod(180, $tanggal);

        $data = [
            'tanggal' => $tanggal,
            'transaksi' => $pagedTransaksi,
            'omzet' => $omzetHariIni,
            'volume' => $volumeHariIni,
            'sisa_pagi' => $sisaPagi,
            'sisa_sore' => $sisaSore,
            'total_piutang' => $totalPiutang,
            'chart_data_harian' => $harianOmzet,
            'chart_data_mingguan' => $mingguanOmzet,
            'chart_data_bulanan' => $bulananOmzet,
            'product_harian' => $harianProduk,
            'product_mingguan' => $mingguanProduk,
            'product_bulanan' => $bulananProduk,
            'current_page' => $currentPageNum,
            'total_pages' => $total_pages
        ];

        $this->view('owner/dashboard', $data);
    }

    // Menampilkan buku hutang & piutang pelanggan dengan paginasi (maks 10 per halaman)
    public function index(): void {
        $this->checkOwner();

        $hutangAktifAll = $this->hutangModel->getHutangAktif();

        // Paginasi: Batasi 10 hutang per halaman
        $total_records = count($hutangAktifAll);
        $limit = 10;
        $total_pages = ceil($total_records / $limit);
        if ($total_pages < 1) $total_pages = 1;

        $currentPageNum = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($currentPageNum < 1) $currentPageNum = 1;
        if ($currentPageNum > $total_pages) $currentPageNum = $total_pages;

        $offset = ($currentPageNum - 1) * $limit;
        $pagedHutang = array_slice($hutangAktifAll, $offset, $limit);

        $data = [
            'hutang_aktif' => $pagedHutang,
            'pelanggan_hutang' => $this->hutangModel->getPelangganBerhutang(),
            'current_page' => $currentPageNum,
            'total_pages' => $total_pages
        ];
        
        $this->view('owner/hutang', $data);
    }

    // Mencatat pembayaran cicilan hutang
    public function bayarCicilan(): void {
        $this->checkOwner();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_hutang = filter_input(INPUT_POST, 'id_hutang', FILTER_VALIDATE_INT);
            $nominal_bayar = filter_input(INPUT_POST, 'nominal_bayar', FILTER_VALIDATE_INT);

            if (!$id_hutang || $nominal_bayar <= 0) {
                $_SESSION['error'] = "Nominal pembayaran cicilan harus valid!";
                header('Location: index.php?page=hutang#daftar-piutang');
                exit();
            }

            $bayar = $this->hutangModel->bayarCicilan($id_hutang, $nominal_bayar);

            if ($bayar) {
                $_SESSION['success'] = "Pembayaran cicilan berhasil disimpan.";
            } else {
                $_SESSION['error'] = "Gagal mencatat pembayaran cicilan.";
            }

            header('Location: index.php?page=hutang#daftar-piutang');
            exit();
        }
    }

    // Koreksi transaksi (Batalkan transaksi, kembalikan stok, dan hapus hutang jika ada)
    public function koreksiTransaksi(): void {
        $this->checkOwner();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_transaksi = filter_input(INPUT_POST, 'id_transaksi', FILTER_VALIDATE_INT);

            if (!$id_transaksi) {
                $_SESSION['error'] = "ID Transaksi tidak valid!";
                header('Location: index.php?page=dashboard#daftar-transaksi');
                exit();
            }

            $transaksi = $this->transaksiModel->getById($id_transaksi);
            if (!$transaksi) {
                $_SESSION['error'] = "Transaksi tidak ditemukan!";
                header('Location: index.php?page=dashboard#daftar-transaksi');
                exit();
            }

            // Validasi Pembekuan Harian: Transaksi pada tanggal lampau bersifat read-only dan tidak boleh dikoreksi
            if ($transaksi->tanggal < date('Y-m-d')) {
                $_SESSION['error'] = "Akses ditolak! Transaksi tanggal lampau (#{$id_transaksi}) telah dibekukan (Read-Only) dan tidak dapat dimanipulasi/dihapus.";
                header('Location: index.php?page=dashboard&tanggal=' . $transaksi->tanggal . '#daftar-transaksi');
                exit();
            }

            // Jika statusnya 'Selesai', pulangkan/kembalikan stok ayam fillet yang terpotong
            if ($transaksi->status_pengiriman == 'Selesai') {
                $details = $this->transaksiModel->getDetails($id_transaksi);
                foreach ($details as $item) {
                    $this->produkModel->tambahStok($item->id_produk, $item->jumlah_berat_kg);
                }
            }

            // Batalkan catatan hutang jika menggunakan metode pembayaran 'Hutang'
            if ($transaksi->metode_pembayaran == 'Hutang') {
                $this->hutangModel->batalkanHutangByTransaksi($id_transaksi);
            }

            // Hapus transaksi (Cascade delete akan otomatis menghapus detail_transaksi)
            $hapus = $this->transaksiModel->delete($id_transaksi);

            if ($hapus) {
                $_SESSION['success'] = "Transaksi #{$id_transaksi} berhasil dikoreksi (dibatalkan), stok & piutang telah dikembalikan.";
            } else {
                $_SESSION['error'] = "Gagal membatalkan transaksi.";
            }

            header('Location: index.php?page=dashboard&tanggal=' . $transaksi->tanggal . '#daftar-transaksi');
            exit();
        }
    }
}
