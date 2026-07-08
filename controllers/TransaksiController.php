<?php
// controllers/TransaksiController.php
require_once 'core/controller.php';
require_once 'models/transaksi.php';
require_once 'models/produk.php';
require_once 'models/pelanggan.php';
require_once 'models/hutang.php';

class TransaksiController extends Controller {
    private Transaksi $transaksiModel;
    private Produk $produkModel;
    private Pelanggan $pelangganModel;
    private Hutang $hutangModel;

    public function __construct(PDO $db) {
        $this->transaksiModel = new Transaksi($db);
        $this->produkModel = new Produk($db);
        $this->pelangganModel = new Pelanggan($db);
        $this->hutangModel = new Hutang($db);
    }

    // Menampilkan halaman Kasir (Split-Screen POS)
    public function index(): void {
        $data['produk'] = $this->produkModel->getAll();
        $data['pelanggan'] = $this->pelangganModel->getAll();
        
        // Hitung beban slot terpakai hari ini
        $tanggalHariIni = date('Y-m-d');
        $data['beban_pagi'] = $this->transaksiModel->getBeratAkumulatif($tanggalHariIni, 'Pagi');
        $data['beban_sore'] = $this->transaksiModel->getBeratAkumulatif($tanggalHariIni, 'Sore');
        
        $data['lastTransaksi'] = null;
        if (isset($_SESSION['last_transaksi_id'])) {
            $lastTrans = $this->transaksiModel->getById($_SESSION['last_transaksi_id']);
            if ($lastTrans) {
                $lastTrans->details = $this->transaksiModel->getDetails($_SESSION['last_transaksi_id']);
                $data['lastTransaksi'] = $lastTrans;
            }
            unset($_SESSION['last_transaksi_id']);
        }

        $this->view('kasir/index', $data);
    }

    // Mendapatkan harga produk (AJAX API)
    public function getHarga(): void {
        header('Content-Type: application/json');
        $id_produk = filter_input(INPUT_GET, 'id_produk', FILTER_VALIDATE_INT);
        if ($id_produk) {
            $prod = $this->produkModel->getById($id_produk);
            if ($prod) {
                echo json_encode(['status' => 'success', 'harga' => $prod->harga_per_kg]);
                return;
            }
        }
        echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan']);
    }

    // Tambah pelanggan baru via Modal AJAX
    public function tambahPelanggan(): void {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nama = filter_input(INPUT_POST, 'nama_pelanggan', FILTER_SANITIZE_SPECIAL_CHARS);
            $no_hp = filter_input(INPUT_POST, 'no_hp', FILTER_SANITIZE_SPECIAL_CHARS);
            $alamat = filter_input(INPUT_POST, 'alamat', FILTER_SANITIZE_SPECIAL_CHARS);

            if (empty($nama) || empty($no_hp) || empty($alamat)) {
                echo json_encode(['status' => 'error', 'message' => 'Semua kolom wajib diisi!']);
                return;
            }

            $id_pelanggan = $this->pelangganModel->create($nama, $no_hp, $alamat);
            if ($id_pelanggan) {
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'id_pelanggan' => $id_pelanggan,
                        'nama_pelanggan' => $nama
                    ]
                ]);
                return;
            }
        }
        echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan pelanggan']);
    }

    // Menyimpan transaksi baru
    public function simpan(): void {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_pelanggan = filter_input(INPUT_POST, 'id_pelanggan', FILTER_VALIDATE_INT);
            $slot_waktu = $_POST['slot_waktu'] ?? 'Pagi';
            $metode_pembayaran = $_POST['metode_pembayaran'] ?? 'Lunas';
            
            $produk_ids = $_POST['produk_id'] ?? [];
            $berat_kgs = $_POST['berat_kg'] ?? [];

            if (!$id_pelanggan || empty($produk_ids)) {
                $_SESSION['error'] = "Harap pilih pelanggan dan minimal masukkan satu produk!";
                header('Location: index.php?page=kasir');
                exit();
            }

            // Hitung total berat dan total harga pesanan ini
            $total_berat_order = 0.0;
            $total_harga_order = 0;
            $detail_items = [];

            foreach ($produk_ids as $index => $id_prod) {
                $berat = (float)($berat_kgs[$index] ?? 0.0);
                if ($berat <= 0) continue;

                $prod = $this->produkModel->getById($id_prod);
                if ($prod) {
                    $subtotal = round($berat * $prod->harga_per_kg);
                    $total_berat_order += $berat;
                    $total_harga_order += $subtotal;

                    $detail_items[] = [
                        'id_produk' => $id_prod,
                        'jumlah_berat_kg' => $berat,
                        'harga_satuan' => $prod->harga_per_kg,
                        'subtotal' => $subtotal
                    ];
                }
            }

            if (empty($detail_items)) {
                $_SESSION['error'] = "Berat produk tidak boleh kosong atau bernilai 0!";
                header('Location: index.php?page=kasir');
                exit();
            }

            // Validasi Slot Muatan: Maksimal 60 Kg per slot per hari
            $tanggal = date('Y-m-d');
            $waktu = date('H:i:s');
            $berat_terpakai = $this->transaksiModel->getBeratAkumulatif($tanggal, $slot_waktu);
            $total_berat_rencana = $berat_terpakai + $total_berat_order;

            $status_pengiriman = 'Pending';
            $pre_order_flag = false;

            if ($total_berat_rencana > 60.0) {
                // Pindahkan ke Pre-Order secara otomatis
                $status_pengiriman = 'Pre-Order';
                $pre_order_flag = true;
            }

            // Simpan Transaksi Utama
            $dataTransaksi = [
                'id_pelanggan' => $id_pelanggan,
                'id_user' => $_SESSION['user_id'],
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'slot_waktu' => $slot_waktu,
                'total_berat_akumulatif' => $total_berat_order,
                'metode_pembayaran' => $metode_pembayaran,
                'status_pengiriman' => $status_pengiriman,
                'total_harga' => $total_harga_order
            ];

            $id_transaksi = $this->transaksiModel->create($dataTransaksi);

            if ($id_transaksi) {
                // Simpan Detail Transaksi
                foreach ($detail_items as $item) {
                    $item['id_transaksi'] = $id_transaksi;
                    $this->transaksiModel->createDetail($item);
                }

                // Catat Piutang jika metode pembayaran adalah 'Hutang'
                if ($metode_pembayaran == 'Hutang') {
                    $this->hutangModel->tambahHutang($id_pelanggan, $id_transaksi, $total_harga_order);
                }

                // Set flash message sukses
                if ($pre_order_flag) {
                    $_SESSION['warning'] = "Kapasitas slot {$slot_waktu} terlampaui (Terpakai: {$berat_terpakai} Kg, Tambahan: {$total_berat_order} Kg). Orderan dialihkan ke status PRE-ORDER.";
                } else {
                    $_SESSION['success'] = "Transaksi berhasil disimpan dengan status PENDING.";
                }

                $_SESSION['last_transaksi_id'] = $id_transaksi;
            } else {
                $_SESSION['error'] = "Gagal menyimpan transaksi!";
            }

            header('Location: index.php?page=kasir');
            exit();
        }
    }
}
