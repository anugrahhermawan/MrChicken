<?php
// controllers/StokController.php
require_once 'core/controller.php';
require_once 'models/transaksi.php';
require_once 'models/produk.php';

class StokController extends Controller {
    private Transaksi $transaksiModel;
    private Produk $produkModel;

    public function __construct(PDO $db) {
        $this->transaksiModel = new Transaksi($db);
        $this->produkModel = new Produk($db);
    }

    // Menampilkan halaman logistik (Daftar Pengiriman Harian) dengan paginasi per slot (maks 5 per halaman)
    public function logistik(): void {
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $transaksiAll = $this->transaksiModel->getPengirimanHarian($tanggal);
        
        // Pisahkan transaksi Pagi dan Sore
        $transaksiPagi = [];
        $transaksiSore = [];
        foreach ($transaksiAll as $t) {
            // Flag untuk transaksi menggantung: tanggal transaksi kurang dari hari ini dan status belum selesai
            $t->is_menggantung = ($t->tanggal < date('Y-m-d') && $t->status_pengiriman !== 'Selesai');
            
            if ($t->slot_waktu === 'Pagi') {
                $transaksiPagi[] = $t;
            } elseif ($t->slot_waktu === 'Sore') {
                $transaksiSore[] = $t;
            }
        }

        // Hitung akumulasi berat pending untuk Slot Pagi dan Sore (dari data penuh)
        $pagiBerat = 0;
        foreach ($transaksiPagi as $t) {
            if ($t->status_pengiriman === 'Pending') {
                $pagiBerat += $t->total_berat_akumulatif;
            }
        }

        $soreBerat = 0;
        foreach ($transaksiSore as $t) {
            if ($t->status_pengiriman === 'Pending') {
                $soreBerat += $t->total_berat_akumulatif;
            }
        }

        // Paginasi Slot Pagi (Maksimal 3 per halaman)
        $limit = 3;
        $totalPagi = count($transaksiPagi);
        $pagesPagi = ceil($totalPagi / $limit);
        if ($pagesPagi < 1) $pagesPagi = 1;
        $pagePagi = isset($_GET['p_pagi']) ? (int)$_GET['p_pagi'] : 1;
        if ($pagePagi < 1) $pagePagi = 1;
        if ($pagePagi > $pagesPagi) $pagePagi = $pagesPagi;
        $offsetPagi = ($pagePagi - 1) * $limit;
        $pagedPagi = array_slice($transaksiPagi, $offsetPagi, $limit);

        // Paginasi Slot Sore (Maksimal 5 per halaman)
        $totalSore = count($transaksiSore);
        $pagesSore = ceil($totalSore / $limit);
        if ($pagesSore < 1) $pagesSore = 1;
        $pageSore = isset($_GET['p_sore']) ? (int)$_GET['p_sore'] : 1;
        if ($pageSore < 1) $pageSore = 1;
        if ($pageSore > $pagesSore) $pageSore = $pagesSore;
        $offsetSore = ($pageSore - 1) * $limit;
        $pagedSore = array_slice($transaksiSore, $offsetSore, $limit);

        // Preload details untuk item yang dirender saja
        foreach ($pagedPagi as $t) {
            $t->details = $this->transaksiModel->getDetails($t->id_transaksi);
        }
        foreach ($pagedSore as $t) {
            $t->details = $this->transaksiModel->getDetails($t->id_transaksi);
        }

        $data = [
            'tanggal' => $tanggal,
            'paged_pagi' => $pagedPagi,
            'paged_sore' => $pagedSore,
            'page_pagi' => $pagePagi,
            'pages_pagi' => $pagesPagi,
            'page_sore' => $pageSore,
            'pages_sore' => $pagesSore,
            'total_pagi_count' => $totalPagi,
            'total_sore_count' => $totalSore,
            'pagi_berat' => $pagiBerat,
            'sore_berat' => $soreBerat
        ];

        $this->view('logistik/pengiriman', $data);
    }

    // Mengonfirmasi pengiriman selesai & memotong stok ayam fillet secara riil
    public function konfirmasiSelesai(): void {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_transaksi = filter_input(INPUT_POST, 'id_transaksi', FILTER_VALIDATE_INT);

            if (!$id_transaksi) {
                $_SESSION['error'] = "ID Transaksi tidak valid!";
                header('Location: index.php?page=logistik#logistik-board');
                exit();
            }

            $transaksi = $this->transaksiModel->getById($id_transaksi);
            if (!$transaksi) {
                $_SESSION['error'] = "Transaksi tidak ditemukan!";
                header('Location: index.php?page=logistik#logistik-board');
                exit();
            }

            // Memastikan status transaksi masih Pending atau Pre-Order sebelum dikurangi stoknya
            if ($transaksi->status_pengiriman == 'Selesai') {
                $_SESSION['error'] = "Transaksi sudah berstatus Selesai sebelumnya!";
                header('Location: index.php?page=logistik#logistik-board');
                exit();
            }

            $details = $this->transaksiModel->getDetails($id_transaksi);
            
            // Validasi: periksa apakah stok cukup untuk seluruh item transaksi
            $stokCukup = true;
            $itemsKurang = [];

            foreach ($details as $item) {
                $produk = $this->produkModel->getById($item->id_produk);
                if (!$produk || $produk->jumlah_kg < $item->jumlah_berat_kg) {
                    $stokCukup = false;
                    $itemsKurang[] = $produk ? $produk->nama_produk : 'Produk Tidak Dikenal';
                }
            }

            if (!$stokCukup) {
                $_SESSION['error'] = "Gagal konfirmasi! Stok tidak mencukupi untuk: " . implode(', ', $itemsKurang);
                header('Location: index.php?page=logistik#logistik-board');
                exit();
            }

            // Mulai memotong stok untuk setiap item
            $potongBerhasil = true;
            foreach ($details as $item) {
                $potong = $this->produkModel->kurangiStok($item->id_produk, $item->jumlah_berat_kg);
                if (!$potong) {
                    $potongBerhasil = false;
                }
            }

            if ($potongBerhasil) {
                // Update status transaksi menjadi 'Selesai'
                $this->transaksiModel->updateStatusPengiriman($id_transaksi, 'Selesai');
                $_SESSION['success'] = "Pengiriman berhasil dikonfirmasi dan stok telah dipotong.";
            } else {
                $_SESSION['error'] = "Terjadi kesalahan saat memotong stok!";
            }

            header('Location: index.php?page=logistik&tanggal=' . $transaksi->tanggal . '#logistik-board');
            exit();
        }
    }
}
