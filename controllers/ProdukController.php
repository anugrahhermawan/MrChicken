<?php
// controllers/ProdukController.php
require_once 'core/Controller.php';
require_once 'models/Produk.php';

class ProdukController extends Controller {
    private Produk $produkModel;

    public function __construct(PDO $db) {
        $this->produkModel = new Produk($db);
    }

    // Memastikan hanya Owner yang bisa melakukan mutasi data
    private function checkOwner(): void {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Owner') {
            $_SESSION['error'] = "Akses ditolak! Tindakan ini hanya boleh dilakukan oleh Owner.";
            header('Location: index.php?page=produk#tabel-produk');
            exit();
        }
    }

    // Halaman daftar produk
    public function index(): void {
        $data['produk'] = $this->produkModel->getAll();
        $this->view('produk/index', $data);
    }

    // Menyimpan produk baru
    public function simpan(): void {
        $this->checkOwner();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nama_produk = trim(filter_input(INPUT_POST, 'nama_produk', FILTER_SANITIZE_SPECIAL_CHARS));
            $harga_per_kg = filter_input(INPUT_POST, 'harga_per_kg', FILTER_VALIDATE_INT);
            $stok_awal = filter_input(INPUT_POST, 'stok_awal', FILTER_VALIDATE_FLOAT);

            if (empty($nama_produk) || $harga_per_kg <= 0 || $stok_awal < 0) {
                $_SESSION['error'] = "Input data produk tidak valid! Pastikan semua data terisi dengan benar.";
                header('Location: index.php?page=produk#tabel-produk');
                exit();
            }

            $save = $this->produkModel->insert($nama_produk, $harga_per_kg, $stok_awal);

            if ($save) {
                $_SESSION['success'] = "Produk '{$nama_produk}' berhasil ditambahkan ke inventaris.";
            } else {
                $_SESSION['error'] = "Gagal menambahkan produk baru.";
            }

            header('Location: index.php?page=produk#tabel-produk');
            exit();
        }
    }

    // Memperbarui data produk & stok
    public function edit(): void {
        $this->checkOwner();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_produk = filter_input(INPUT_POST, 'id_produk', FILTER_VALIDATE_INT);
            $nama_produk = trim(filter_input(INPUT_POST, 'nama_produk', FILTER_SANITIZE_SPECIAL_CHARS));
            $harga_per_kg = filter_input(INPUT_POST, 'harga_per_kg', FILTER_VALIDATE_INT);
            $stok_kg = filter_input(INPUT_POST, 'jumlah_kg', FILTER_VALIDATE_FLOAT);

            if (!$id_produk || empty($nama_produk) || $harga_per_kg <= 0 || $stok_kg < 0) {
                $_SESSION['error'] = "Input data edit produk tidak valid!";
                header('Location: index.php?page=produk#tabel-produk');
                exit();
            }

            $update = $this->produkModel->update($id_produk, $nama_produk, $harga_per_kg, $stok_kg);

            if ($update) {
                $_SESSION['success'] = "Data produk '{$nama_produk}' berhasil diperbarui.";
            } else {
                $_SESSION['error'] = "Gagal memperbarui data produk.";
            }

            header('Location: index.php?page=produk#tabel-produk');
            exit();
        }
    }

    // Restock cepat (menambah stok ayam fillet yang sudah ada)
    public function restock(): void {
        $this->checkOwner();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_produk = filter_input(INPUT_POST, 'id_produk', FILTER_VALIDATE_INT);
            $tambahan_stok = filter_input(INPUT_POST, 'tambahan_stok', FILTER_VALIDATE_FLOAT);

            if (!$id_produk || $tambahan_stok <= 0) {
                $_SESSION['error'] = "Jumlah tambahan restock harus lebih dari 0 Kg!";
                header('Location: index.php?page=produk#tabel-produk');
                exit();
            }

            $produk = $this->produkModel->getById($id_produk);
            if (!$produk) {
                $_SESSION['error'] = "Produk tidak ditemukan!";
                header('Location: index.php?page=produk#tabel-produk');
                exit();
            }

            $restock = $this->produkModel->tambahStok($id_produk, $tambahan_stok);

            if ($restock) {
                $_SESSION['success'] = "Restock berhasil! Menambahkan {$tambahan_stok} Kg ke produk '{$produk->nama_produk}'.";
            } else {
                $_SESSION['error'] = "Gagal menambahkan stok produk.";
            }

            header('Location: index.php?page=produk#tabel-produk');
            exit();
        }
    }

    // Menghapus produk
    public function hapus(): void {
        $this->checkOwner();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_produk = filter_input(INPUT_POST, 'id_produk', FILTER_VALIDATE_INT);

            if (!$id_produk) {
                $_SESSION['error'] = "ID Produk tidak valid!";
                header('Location: index.php?page=produk#tabel-produk');
                exit();
            }

            $produk = $this->produkModel->getById($id_produk);
            if (!$produk) {
                $_SESSION['error'] = "Produk tidak ditemukan!";
                header('Location: index.php?page=produk#tabel-produk');
                exit();
            }

            $delete = $this->produkModel->delete($id_produk);

            if ($delete) {
                $_SESSION['success'] = "Produk '{$produk->nama_produk}' berhasil dihapus dari inventaris.";
            } else {
                $_SESSION['error'] = "Gagal menghapus produk! Produk ini sudah terikat dengan riwayat transaksi penjualan.";
            }

            header('Location: index.php?page=produk#tabel-produk');
            exit();
        }
    }
}
