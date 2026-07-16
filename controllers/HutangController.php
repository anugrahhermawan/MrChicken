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



    // Menampilkan buku hutang & piutang pelanggan / pencatatan cicilan dengan paginasi
    public function index(): void {
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

        // Fetch data for Owner specific reports
        $allPelanggan = [];
        $riwayatPembayaran = [];
        $aging = [
            'current' => 0,
            'under_90' => 0,
            'over_90' => 0
        ];
        $totalPiutang = 0;

        if (isset($_SESSION['role']) && $_SESSION['role'] === 'Owner') {
            $allPelanggan = $this->pelangganModel->getAll();
            $riwayatPembayaran = $this->hutangModel->getRiwayatPembayaran();
            
            // Calculate Aging AR
            $today = new DateTime();
            foreach ($this->hutangModel->getHutangAktif() as $h) {
                $totalPiutang += $h->sisa_hutang;
                if (empty($h->due_date)) {
                    $aging['current'] += $h->sisa_hutang;
                    continue;
                }
                
                $dueDate = new DateTime($h->due_date);
                if ($today <= $dueDate) {
                    $aging['current'] += $h->sisa_hutang;
                } else {
                    $interval = $today->diff($dueDate);
                    $days = $interval->days;
                    if ($days <= 90) {
                        $aging['under_90'] += $h->sisa_hutang;
                    } else {
                        $aging['over_90'] += $h->sisa_hutang;
                    }
                }
            }
        }

        // Preview bukti pembayaran
        $lastPembayaran = null;
        if (isset($_SESSION['last_pembayaran_id'])) {
            $lastPembayaran = $this->hutangModel->getPembayaranById($_SESSION['last_pembayaran_id']);
            unset($_SESSION['last_pembayaran_id']);
        }

        $data = [
            'hutang_aktif' => $pagedHutang,
            'pelanggan_hutang' => $this->hutangModel->getPelangganBerhutang(),
            'all_pelanggan' => $allPelanggan,
            'riwayat_pembayaran' => $riwayatPembayaran,
            'aging' => $aging,
            'total_piutang' => $totalPiutang,
            'lastPembayaran' => $lastPembayaran,
            'current_page' => $currentPageNum,
            'total_pages' => $total_pages
        ];
        
        $this->view('owner/hutang', $data);
    }

    // Mencatat pembayaran cicilan hutang (Staf/Kasir/Owner)
    public function bayarCicilan(): void {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_hutang = filter_input(INPUT_POST, 'id_hutang', FILTER_VALIDATE_INT);
            $nominal_bayar = filter_input(INPUT_POST, 'nominal_bayar', FILTER_VALIDATE_INT);
            $created_by = $_SESSION['user_id'];

            if (!$id_hutang || $nominal_bayar <= 0) {
                $_SESSION['error'] = "Nominal pembayaran cicilan harus valid!";
                header('Location: index.php?page=hutang#daftar-piutang');
                exit();
            }

            $id_pembayaran = $this->hutangModel->bayarCicilan($id_hutang, $nominal_bayar, $created_by);

            if ($id_pembayaran) {
                $_SESSION['success'] = "Pembayaran cicilan berhasil disimpan.";
                $_SESSION['last_pembayaran_id'] = $id_pembayaran;
            } else {
                $_SESSION['error'] = "Gagal mencatat pembayaran cicilan.";
            }

            header('Location: index.php?page=hutang');
            exit();
        }
    }

    // Melakukan penyesuaian/pemotongan sisa hutang (Adjustment - Owner & Karyawan)
    public function adjustment(): void {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_hutang = filter_input(INPUT_POST, 'id_hutang', FILTER_VALIDATE_INT);
            $nominal_adjustment = filter_input(INPUT_POST, 'nominal_adjustment', FILTER_VALIDATE_INT);
            $created_by = $_SESSION['user_id'];

            if (!$id_hutang || $nominal_adjustment <= 0) {
                $_SESSION['error'] = "Nominal adjustment harus valid!";
                header('Location: index.php?page=hutang');
                exit();
            }

            $success = $this->hutangModel->adjustmentHutang($id_hutang, $nominal_adjustment, $created_by);
            if ($success) {
                $_SESSION['success'] = "Penyesuaian (Adjustment) sisa hutang berhasil disimpan.";
            } else {
                $_SESSION['error'] = "Gagal menyimpan penyesuaian sisa hutang.";
            }

            header('Location: index.php?page=hutang');
            exit();
        }
    }

    // Melakukan Write-Off / Penghapusan Piutang Macet (Owner & Karyawan)
    public function writeOff(): void {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_hutang = filter_input(INPUT_POST, 'id_hutang', FILTER_VALIDATE_INT);
            $created_by = $_SESSION['user_id'];

            if (!$id_hutang) {
                $_SESSION['error'] = "ID Hutang tidak valid!";
                header('Location: index.php?page=hutang');
                exit();
            }

            $success = $this->hutangModel->writeOffHutang($id_hutang, $created_by);
            if ($success) {
                $_SESSION['success'] = "Piutang macet berhasil dihapuskan (Write-Off) dari pembukuan.";
            } else {
                $_SESSION['error'] = "Gagal melakukan write-off piutang macet.";
            }

            header('Location: index.php?page=hutang');
            exit();
        }
    }

    // Mengupdate Credit Limit Pelanggan (Owner Only)
    public function updateCreditLimit(): void {
        $this->checkOwner();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_pelanggan = filter_input(INPUT_POST, 'id_pelanggan', FILTER_VALIDATE_INT);
            $credit_limit = filter_input(INPUT_POST, 'credit_limit', FILTER_VALIDATE_INT);

            if (!$id_pelanggan || $credit_limit < 0) {
                $_SESSION['error'] = "Credit limit tidak boleh kurang dari 0 dan ID Pelanggan harus valid!";
                header('Location: index.php?page=hutang');
                exit();
            }

            $success = $this->pelangganModel->updateCreditLimit($id_pelanggan, $credit_limit);
            if ($success) {
                $_SESSION['success'] = "Batas limit kredit (Credit Limit) pelanggan berhasil diperbarui.";
            } else {
                $_SESSION['error'] = "Gagal memperbarui credit limit pelanggan.";
            }

            header('Location: index.php?page=hutang');
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
            if ($transaksi->status_pengiriman == STATUS_PENGIRIMAN_SELESAI) {
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

    // Membatalkan pembayaran cicilan/adjustment/write-off dan memulihkan saldo piutang
    public function batalBayar(): void {
        $this->checkOwner();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_pembayaran = filter_input(INPUT_POST, 'id_pembayaran', FILTER_VALIDATE_INT);

            if (!$id_pembayaran) {
                $_SESSION['error'] = "ID Pembayaran tidak valid!";
                header('Location: index.php?page=hutang');
                exit();
            }

            $success = $this->hutangModel->batalPembayaran($id_pembayaran);

            if ($success) {
                $_SESSION['success'] = "Pembayaran (BILL-PAY-{$id_pembayaran}) berhasil dibatalkan dan saldo piutang pelanggan dipulihkan.";
            } else {
                $_SESSION['error'] = "Gagal membatalkan pembayaran.";
            }

            header('Location: index.php?page=hutang');
            exit();
        }
    }
}
