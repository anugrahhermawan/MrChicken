<?php
// index.php
date_default_timezone_set('Asia/Jakarta');
session_start();

require_once 'config/database.php';
require_once 'config/constants.php';

$database = new Database();
$db = $database->getConnection();

// Mengambil parameter halaman dari URL (default: login)
$page = isset($_GET['page']) ? $_GET['page'] : 'login';

// Sederhana: Izinkan halaman seed diakses langsung untuk setup awal
if ($page === 'seed') {
    require_once 'seed.php';
    exit();
}

// Middleware sederhana: Cek status login (kecuali halaman login sendiri)
if (!isset($_SESSION['user_id']) && $page != 'login') {
    header('Location: index.php?page=login');
    exit();
}

// Cek aktivitas berkala untuk auto-logout jika sudah masuk
if (isset($_SESSION['user_id'])) {
    if (time() - $_SESSION['last_activity'] > 3600) { // 60 menit
        session_unset();
        session_destroy();
        header('Location: index.php?page=login&error=sesi_habis');
        exit();
    }
    $_SESSION['last_activity'] = time(); // update activity
}

// Middleware RBAC (Role-Based Access Control)
$ownerOnlyPages = [
    'dashboard',
    'users',
    'users-simpan',
    'users-edit',
    'users-toggle',
    'users-hapus',
    'produk-simpan',
    'produk-edit',
    'produk-restock',
    'produk-hapus',
    'transaksi-koreksi',
    'pelanggan-limit-update'
];

if (in_array($page, $ownerOnlyPages)) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Owner') {
        http_response_code(403);
        require_once 'views/errors/403.php';
        exit();
    }
}

// Routing aplikasi
switch ($page) {
    case 'login':
        require_once 'controllers/AuthController.php';
        $auth = new AuthController($db);
        $auth->login();
        break;

    case 'logout':
        require_once 'controllers/AuthController.php';
        $auth = new AuthController($db);
        $auth->logout();
        break;

    case 'kasir':
        require_once 'controllers/TransaksiController.php';
        $controller = new TransaksiController($db);
        $controller->index();
        break;

    case 'kasir-simpan':
        require_once 'controllers/TransaksiController.php';
        $controller = new TransaksiController($db);
        $controller->simpan();
        break;

    case 'api-harga':
        require_once 'controllers/TransaksiController.php';
        $controller = new TransaksiController($db);
        $controller->getHarga();
        break;

    case 'api-pelanggan-tambah':
        require_once 'controllers/TransaksiController.php';
        $controller = new TransaksiController($db);
        $controller->tambahPelanggan();
        break;

    case 'api-dashboard-chart':
        require_once 'controllers/DashboardController.php';
        $controller = new DashboardController($db);
        $controller->apiDashboardChart();
        break;

    case 'logistik':
        require_once 'controllers/StokController.php';
        $controller = new StokController($db);
        $controller->logistik();
        break;

    case 'logistik-konfirmasi':
        require_once 'controllers/StokController.php';
        $controller = new StokController($db);
        $controller->konfirmasiSelesai();
        break;

    case 'dashboard':
        require_once 'controllers/DashboardController.php';
        $controller = new DashboardController($db);
        $controller->dashboard();
        break;

    case 'hutang':
        require_once 'controllers/HutangController.php';
        $controller = new HutangController($db);
        $controller->index();
        break;

    case 'hutang-bayar':
        require_once 'controllers/HutangController.php';
        $controller = new HutangController($db);
        $controller->bayarCicilan();
        break;

    case 'hutang-adjustment':
        require_once 'controllers/HutangController.php';
        $controller = new HutangController($db);
        $controller->adjustment();
        break;

    case 'hutang-writeoff':
        require_once 'controllers/HutangController.php';
        $controller = new HutangController($db);
        $controller->writeOff();
        break;

    case 'hutang-bayar-batal':
        require_once 'controllers/HutangController.php';
        $controller = new HutangController($db);
        $controller->batalBayar();
        break;

    case 'pelanggan-limit-update':
        require_once 'controllers/HutangController.php';
        $controller = new HutangController($db);
        $controller->updateCreditLimit();
        break;

    case 'transaksi-koreksi':
        require_once 'controllers/HutangController.php';
        $controller = new HutangController($db);
        $controller->koreksiTransaksi();
        break;

    case 'users':
        require_once 'controllers/UserController.php';
        $controller = new UserController($db);
        $controller->index();
        break;

    case 'users-simpan':
        require_once 'controllers/UserController.php';
        $controller = new UserController($db);
        $controller->simpan();
        break;

    case 'users-edit':
        require_once 'controllers/UserController.php';
        $controller = new UserController($db);
        $controller->edit();
        break;

    case 'users-toggle':
        require_once 'controllers/UserController.php';
        $controller = new UserController($db);
        $controller->toggleStatus();
        break;

    case 'users-hapus':
        require_once 'controllers/UserController.php';
        $controller = new UserController($db);
        $controller->hapus();
        break;

    case 'produk':
        require_once 'controllers/ProdukController.php';
        $controller = new ProdukController($db);
        $controller->index();
        break;

    case 'produk-simpan':
        require_once 'controllers/ProdukController.php';
        $controller = new ProdukController($db);
        $controller->simpan();
        break;

    case 'produk-edit':
        require_once 'controllers/ProdukController.php';
        $controller = new ProdukController($db);
        $controller->edit();
        break;

    case 'produk-restock':
        require_once 'controllers/ProdukController.php';
        $controller = new ProdukController($db);
        $controller->restock();
        break;

    case 'produk-hapus':
        require_once 'controllers/ProdukController.php';
        $controller = new ProdukController($db);
        $controller->hapus();
        break;

    default:
        http_response_code(404);
        require_once 'views/errors/404.php';
        break;
}
