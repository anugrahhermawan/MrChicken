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
    <style>
        :root {
            --bg-primary: #f5f7fa;
            --sidebar-bg: #111f37;
            --sidebar-color: #d1d9e6;
            --sidebar-active-bg: #2e3d54;
            --sidebar-active-color: #9aa7c7;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --card-border-radius: 12px;
            --accent-orange: #ffffff;
            --accent-gradient: linear-gradient(135deg, #3e506e 0%, #2e3d54 100%);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-primary);
            color: #32415a;
            overflow-x: hidden;
        }

        /* Layout */
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        /* Sidebar Styling (Sticky Sidebar) */
        #sidebar {
            min-width: 260px;
            max-width: 260px;
            background-color: var(--sidebar-bg);
            color: #fff;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
        }

        /* Scrollable Table Container */
        .table-responsive-scroll {
            max-height: 65vh;
            overflow-y: auto;
            border: 1.5px solid #f1f5f9;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        #sidebar .sidebar-header {
            padding: 24px 15px;
            background: #0b0f19;
            border-bottom: 1px solid #1e293b;
        }

        #sidebar .logo-text {
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: 1px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #sidebar .logo-text span {
            color: var(--accent-orange);
        }

        #sidebar ul.components {
            padding: 20px 0;
            flex-grow: 1;
        }

        #sidebar ul p {
            color: #64748b;
            padding: 10px 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        #sidebar ul li a {
            padding: 12px 20px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 15px;
            color: var(--sidebar-color);
            text-decoration: none;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
            border-left: 4px solid transparent;
        }

        #sidebar ul li a:hover {
            color: #fff;
            background: var(--sidebar-active-bg);
        }

        #sidebar ul li.active>a {
            color: #fff;
            background: var(--sidebar-active-bg);
            border-left-color: var(--sidebar-active-color);
        }

        #sidebar ul li a i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        /* Content Page Area */
        #content {
            width: 100%;
            padding: 30px;
            min-height: 100vh;
            transition: all 0.3s;
        }

        /* Top Navigation Header */
        .top-navbar {
            background-color: #fff;
            padding: 15px 30px;
            border-radius: var(--card-border-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-badge {
            background-color: #f1f5f9;
            padding: 8px 16px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .user-badge .role-tag {
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 50px;
            font-weight: 700;
        }

        .role-owner {
            background-color: #ffffff;
            color: #34c2f6;
        }

        .role-kasir {
            background-color: #ffffff;
            color: #34c2f6;
        }

        /* Cards & Widgets */
        .premium-card {
            background: #fff;
            border: 1px solid #edf2f7;
            border-radius: var(--card-border-radius);
            box-shadow: var(--card-shadow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .premium-card:hover {
            box-shadow: 0 12px 20px -5px rgba(62, 80, 110, 0.08), 0 8px 8px -5px rgba(62, 80, 110, 0.04);
            border-color: #9aa7c7;
        }

        .gradient-widget {
            background: var(--accent-gradient);
            color: #fff;
        }

        /* Forms styling */
        .form-control,
        .form-select {
            border: 1.5px solid #e2e8f0;
            padding: 10px 15px;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 3px rgba(62, 80, 110, 0.15);
        }

        .btn-premium {
            background: var(--accent-gradient);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-premium:hover {
            opacity: 0.95;
            color: white;
            box-shadow: 0 4px 12px rgba(62, 80, 110, 0.2);
        }

        /* Custom Pagination styles to match slate-blue theme */
        .pagination .page-link {
            color: var(--accent-orange);
            border-color: #e2e8f0;
            padding: 8px 14px;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 8px;
            margin: 0 2px;
        }

        .pagination .page-link:hover {
            background-color: #ebf0f7;
            border-color: var(--accent-orange);
            color: var(--accent-orange);
        }

        .pagination .page-item.active .page-link {
            background: var(--accent-gradient);
            border-color: var(--accent-orange);
            color: #fff !important;
        }

        .pagination .page-item.disabled .page-link {
            color: #94a3b8;
            background-color: #f8fafc;
            border-color: #e2e8f0;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Slate Theme Button Overrides */
        .btn-primary {
            background-color: #3e506e !important;
            border-color: #3e506e !important;
            color: #ffffff !important;
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background-color: #2e3d54 !important;
            border-color: #2e3d54 !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(62, 80, 110, 0.25) !important;
        }

        .btn-outline-primary {
            color: #3e506e !important;
            border-color: #3e506e !important;
            background-color: transparent !important;
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus,
        .btn-outline-primary:active {
            color: #ffffff !important;
            background-color: #3e506e !important;
            border-color: #3e506e !important;
        }

        .btn-outline-success {
            color: #38a169 !important;
            border-color: #38a169 !important;
            background-color: transparent !important;
        }

        .btn-outline-success:hover {
            color: #ffffff !important;
            background-color: #38a169 !important;
            border-color: #38a169 !important;
        }

        .btn-outline-danger {
            color: #e53e3e !important;
            border-color: #e53e3e !important;
            background-color: transparent !important;
        }

        .btn-outline-danger:hover {
            color: #ffffff !important;
            background-color: #e53e3e !important;
            border-color: #e53e3e !important;
        }

        .btn-outline-secondary {
            color: #718096 !important;
            border-color: #cbd5e1 !important;
            background-color: transparent !important;
        }

        .btn-outline-secondary:hover {
            color: #ffffff !important;
            background-color: #718096 !important;
            border-color: #718096 !important;
        }

        /* Link & Text Accent Overrides */
        .text-orange {
            color: #3e506e !important;
        }

        .text-orange-link {
            color: #3e506e !important;
        }

        .text-orange-link:hover {
            color: #9aa7c7 !important;
        }

        .pagination .page-link:hover {
            background-color: #ebf0f7 !important;
            border-color: var(--accent-orange) !important;
            color: var(--accent-orange) !important;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
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

        <!-- Main Content Page -->
        <div id="content">
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

            <!-- Global Notification block removed to be placed locally above anchor layouts -->