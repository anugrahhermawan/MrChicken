<?php
// views/templates/header.php
$currentPage = $_GET['page'] ?? 'login';
$userRole = $_SESSION['role'] ?? '';
$userName = $_SESSION['nama'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MR. CHICKEN - POS & Distribusi</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Style for Rich Aesthetics -->
    <link href="assets/css/main.css" rel="stylesheet">
    <?php if ($currentPage === 'dashboard'): ?>
        <link href="assets/css/dashboard.css" rel="stylesheet">
    <?php elseif ($currentPage === 'kasir'): ?>
        <link href="assets/css/kasir.css" rel="stylesheet">
    <?php endif; ?>
</head>

<body>

    <!-- Mobile Top Header (Visible only on screens < 992px) -->
    <nav class="navbar navbar-dark d-lg-none sticky-top" style="background-color: var(--sidebar-bg); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); z-index: 1030; padding: 12px 15px;">
        <div class="container-fluid">
            <!-- Brand / Logo -->
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <img src="assets/logoMrChicken.jpeg" alt="Logo MR. CHICKEN" class="rounded-circle border" style="height: 35px; width: 35px; object-fit: cover; border-color: var(--accent-orange) !important; border-width: 1.5px !important;">
                <span style="font-size: 1.05rem; font-weight: 300; letter-spacing: 1px; color: #fff;">
                    MR.<span style="font-weight: 700; color: var(--accent-orange);">CHICKEN</span>
                </span>
            </a>

            <!-- Offcanvas Menu Button Trigger -->
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarDrawer" aria-controls="mobileSidebarDrawer" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <!-- Mobile Offcanvas Sidebar Drawer (Visible on screens < 992px) -->
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="mobileSidebarDrawer" aria-labelledby="mobileSidebarDrawerLabel" style="background-color: var(--sidebar-bg); width: 280px; z-index: 1045;">
        <div class="offcanvas-header border-bottom border-secondary border-opacity-25 py-3" style="background: #0b0f19;">
            <div class="d-flex align-items-center gap-2">
                <img src="assets/logoMrChicken.jpeg" alt="Logo MR. CHICKEN" class="rounded-circle border" style="height: 35px; width: 35px; object-fit: cover; border-color: var(--accent-orange) !important; border-width: 1.5px !important;">
                <h5 class="offcanvas-title text-white font-weight-bold m-0" id="mobileSidebarDrawerLabel">
                    MR.<span style="color: var(--accent-orange);">CHICKEN</span>
                </h5>
            </div>
            <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column justify-content-between">
            <ul class="list-unstyled components">
                <p>Operasional</p>
                <li class="<?= $currentPage === 'kasir' ? 'active' : '' ?>">
                    <a href="index.php?page=kasir" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('mobileSidebarDrawer')).hide();">
                        <i class="fa-solid fa-cash-register"></i> POS Kasir (Order)
                    </a>
                </li>
                <li class="<?= $currentPage === 'logistik' ? 'active' : '' ?>">
                    <a href="index.php?page=logistik" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('mobileSidebarDrawer')).hide();">
                        <i class="fa-solid fa-truck-fast"></i> Logistik & Kirim
                    </a>
                </li>
                <li class="<?= $currentPage === 'produk' ? 'active' : '' ?>">
                    <a href="index.php?page=produk" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('mobileSidebarDrawer')).hide();">
                        <i class="fa-solid fa-boxes-stacked"></i> Stok & Harga Produk
                    </a>
                </li>
                <?php if ($userRole !== 'Owner'): ?>
                    <li class="<?= $currentPage === 'hutang' ? 'active' : '' ?>">
                        <a href="index.php?page=hutang" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('mobileSidebarDrawer')).hide();">
                            <i class="fa-solid fa-book-bookmark"></i> Pencatatan Cicilan
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($userRole === 'Owner'): ?>
                    <p>Manajemen Owner</p>
                    <li class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                        <a href="index.php?page=dashboard" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('mobileSidebarDrawer')).hide();">
                            <i class="fa-solid fa-chart-line"></i> Dashboard
                        </a>
                    </li>
                    <li class="<?= $currentPage === 'hutang' ? 'active' : '' ?>">
                        <a href="index.php?page=hutang" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('mobileSidebarDrawer')).hide();">
                            <i class="fa-solid fa-book-bookmark"></i> Buku Piutang
                        </a>
                    </li>
                    <li class="<?= $currentPage === 'users' ? 'active' : '' ?>">
                        <a href="index.php?page=users" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('mobileSidebarDrawer')).hide();">
                            <i class="fa-solid fa-users-gear"></i> Kelola Karyawan
                        </a>
                    </li>
                <?php endif; ?>

                <p>Akun</p>
                <li>
                    <a href="index.php?page=logout" class="text-danger">
                        <i class="fa-solid fa-right-from-bracket text-danger"></i> Keluar
                    </a>
                </li>
            </ul>
            <div class="p-3 text-center text-muted small border-top border-secondary border-opacity-10">
                v1.0-MVP &copy; 2026
            </div>
        </div>
    </div>

    <!-- Mobile Bottom Navigation Bar (Visible only on screens < 768px) -->
    <div class="bottom-nav d-md-none">
        <a href="index.php?page=kasir" class="bottom-nav-item <?= $currentPage === 'kasir' ? 'active' : '' ?>">
            <i class="fa-solid fa-cash-register"></i>
            <span>POS</span>
        </a>
        <a href="index.php?page=logistik" class="bottom-nav-item <?= $currentPage === 'logistik' ? 'active' : '' ?>">
            <i class="fa-solid fa-truck-fast"></i>
            <span>Logistik</span>
        </a>
        <a href="index.php?page=produk" class="bottom-nav-item <?= $currentPage === 'produk' ? 'active' : '' ?>">
            <i class="fa-solid fa-boxes-stacked"></i>
            <span>Stok</span>
        </a>
        <a href="index.php?page=hutang" class="bottom-nav-item <?= $currentPage === 'hutang' ? 'active' : '' ?>">
            <i class="fa-solid fa-book-bookmark"></i>
            <span>Piutang</span>
        </a>
        <a href="#" class="bottom-nav-item" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarDrawer">
            <i class="fa-solid fa-bars"></i>
            <span>Menu</span>
        </a>
    </div>

    <!-- Main Layout Wrapper using Grid -->
    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100 layout-row">
            <!-- Sidebar for Desktop View (Visible only on screens >= 992px) -->
            <nav id="sidebar" class="col-lg-3 col-xl-2 d-none d-lg-flex flex-column">
                <div class="sidebar-header text-center d-flex flex-column align-items-center">
                    <img src="assets/logoMrChicken.jpeg" alt="Logo MR. CHICKEN" class="rounded-circle mb-3 border" style="height: 60px; width: 60px; object-fit: cover; border-color: var(--accent-orange) !important; border-width: 2px !important;">
                    <div class="logo-text justify-content-center" style="font-size: 1rem; font-weight: 300; letter-spacing: 2px;">
                        MR.<span style="font-weight: 700; color: var(--accent-orange);">CHICKEN</span>
                        <span class="d-block w-100 mt-1" style="font-size: 0.75rem; letter-spacing: 4px; opacity: 0.6; font-weight: 300;">POS</span>
                    </div>
                </div>

                <ul class="list-unstyled components">
                    <p>Operasional</p>
                    <li class="<?= $currentPage === 'kasir' ? 'active' : '' ?>">
                        <a href="index.php?page=kasir">
                            <i class="fa-solid fa-cash-register"></i> POS Kasir (Order)
                        </a>
                    </li>
                    <li class="<?= $currentPage === 'logistik' ? 'active' : '' ?>">
                        <a href="index.php?page=logistik">
                            <i class="fa-solid fa-truck-fast"></i> Logistik & Kirim
                        </a>
                    </li>
                    <li class="<?= $currentPage === 'produk' ? 'active' : '' ?>">
                        <a href="index.php?page=produk">
                            <i class="fa-solid fa-boxes-stacked"></i> Stok & Harga Produk
                        </a>
                    </li>
                    <?php if ($userRole !== 'Owner'): ?>
                        <li class="<?= $currentPage === 'hutang' ? 'active' : '' ?>">
                            <a href="index.php?page=hutang">
                                <i class="fa-solid fa-book-bookmark"></i> Pencatatan Cicilan
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($userRole === 'Owner'): ?>
                        <p>Manajemen Owner</p>
                        <li class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                            <a href="index.php?page=dashboard">
                                <i class="fa-solid fa-chart-line"></i> Dashboard
                            </a>
                        </li>
                        <li class="<?= $currentPage === 'hutang' ? 'active' : '' ?>">
                            <a href="index.php?page=hutang">
                                <i class="fa-solid fa-book-bookmark"></i> Buku Piutang
                            </a>
                        </li>
                        <li class="<?= $currentPage === 'users' ? 'active' : '' ?>">
                            <a href="index.php?page=users">
                                <i class="fa-solid fa-users-gear"></i> Kelola Karyawan
                            </a>
                        </li>
                    <?php endif; ?>

                    <p>Akun</p>
                    <li>
                        <a href="index.php?page=logout" class="text-danger-hover">
                            <i class="fa-solid fa-right-from-bracket text-danger"></i> Keluar
                        </a>
                    </li>
                </ul>
                <div class="p-3 text-center text-muted small mt-auto border-top border-secondary">
                    v1.0-MVP &copy; 2026
                </div>
            </nav>

            <!-- Main Content Column -->
            <div id="content" class="col-12 col-lg-9 col-xl-10">
                <!-- Top Navbar -->
                <div class="top-navbar">
                    <div class="d-flex align-items-center">
                        <h4 class="m-0 font-weight-bold">
                            <?php
                            if ($currentPage === 'kasir') echo '<i class="fa-solid fa-cash-register text-orange me-2"></i> POS (Point of Sale)';
                            elseif ($currentPage === 'logistik') echo '<i class="fa-solid fa-truck-fast text-orange me-2"></i> Manajemen Distribusi & Pengiriman';
                            elseif ($currentPage === 'dashboard') echo '<i class="fa-solid fa-chart-line text-orange me-2"></i> Owner Dashboard';
                            elseif ($currentPage === 'hutang') echo '<i class="fa-solid fa-book-bookmark text-orange me-2"></i> Catatan Piutang & Cicilan';
                            elseif ($currentPage === 'users') echo '<i class="fa-solid fa-users-gear text-orange me-2"></i> Manajemen Pengguna & Karyawan';
                            elseif ($currentPage === 'produk') echo '<i class="fa-solid fa-boxes-stacked text-orange me-2"></i> Stok & Harga Produk';
                            else echo 'Aplikasi POS MR. CHICKEN';
                            ?>
                        </h4>
                    </div>
                    <div class="user-badge shadow-sm">
                        <i class="fa-solid fa-circle-user fa-lg text-secondary"></i>
                        <span><?= htmlspecialchars($userName) ?></span>
                        <span class="role-tag <?= $userRole === 'Owner' ? 'role-owner' : 'role-kasir' ?>">
                            <?= htmlspecialchars($userRole) ?>
                        </span>
                    </div>
                </div>

                <!-- Container Fluid for page content -->
                <div class="container-fluid px-0">