<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'SI-ABSEN') ?> - Sistem Informasi Presensi & Pembinaan</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 50%, #3b82f6 100%);
            --sidebar-width: 260px;
            --brand-color: #6366f1;
            
            /* Enhanced Dark Theme Tokens */
            --bg-body: #070a13;
            --bg-sidebar: #0d1322;
            --bg-card: #121a2d;
            --bg-card-header: #162038;
            --bg-input: #0a0f1d;
            --border-color: rgba(255, 255, 255, 0.09);
            --border-focus: #6366f1;
            --text-main: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
        }

        html, body {
            background-color: var(--bg-body) !important;
            color: var(--text-main) !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #070a13;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }

        /* Dark Overrides for Bootstrap Utility Classes */
        .text-dark {
            color: #f8fafc !important;
        }
        .text-secondary {
            color: #94a3b8 !important;
        }
        .text-muted, small.text-muted, div.text-muted, span.text-muted, p.text-muted {
            color: #94a3b8 !important;
        }
        .bg-white {
            background-color: var(--bg-card) !important;
            color: var(--text-main) !important;
        }
        .bg-light {
            background-color: #0a0f1d !important;
            color: #cbd5e1 !important;
        }
        .bg-body {
            background-color: var(--bg-body) !important;
            color: var(--text-main) !important;
        }
        .bg-body-secondary {
            background-color: #0d1322 !important;
        }
        .bg-body-tertiary {
            background-color: #121a2d !important;
        }
        .border, .border-bottom, .border-top, .border-start, .border-end {
            border-color: var(--border-color) !important;
        }

        /* Cards in Dark Mode */
        .card, .card-custom {
            background-color: var(--bg-card) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 16px;
            color: var(--text-main) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-custom:hover {
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.45);
        }

        .card-header {
            background-color: var(--bg-card-header) !important;
            border-bottom: 1px solid var(--border-color) !important;
            color: var(--text-main) !important;
        }

        .card-body {
            background-color: transparent !important;
            color: var(--text-main) !important;
        }

        .card-footer {
            background-color: var(--bg-card-header) !important;
            border-top: 1px solid var(--border-color) !important;
            color: var(--text-secondary) !important;
        }

        /* Tables in Dark Mode */
        .table {
            --bs-table-bg: transparent;
            --bs-table-color: #e2e8f0;
            --bs-table-striped-bg: rgba(255, 255, 255, 0.02);
            --bs-table-hover-bg: rgba(255, 255, 255, 0.04);
            --bs-table-border-color: var(--border-color);
            color: #e2e8f0 !important;
            border-color: var(--border-color) !important;
        }

        .table th, .table td {
            border-color: var(--border-color) !important;
        }

        .table-custom th {
            background-color: #162038 !important;
            color: #94a3b8 !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color) !important;
            padding: 14px 16px;
        }

        .table-custom td {
            background-color: transparent !important;
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color) !important;
            color: #e2e8f0 !important;
        }

        .table-hover tbody tr:hover td, .table-hover tbody tr:hover th {
            background-color: rgba(255, 255, 255, 0.04) !important;
        }

        .table-light, .table-light th, .table-light td {
            background-color: #162038 !important;
            color: #cbd5e1 !important;
            border-color: var(--border-color) !important;
        }

        .table-primary, .table-primary th, .table-primary td {
            background-color: rgba(99, 102, 241, 0.2) !important;
            color: #e0e7ff !important;
            border-color: var(--border-color) !important;
        }

        .table-danger, .table-danger th, .table-danger td {
            background-color: rgba(239, 68, 68, 0.2) !important;
            color: #fecaca !important;
            border-color: var(--border-color) !important;
        }

        .table-warning, .table-warning th, .table-warning td {
            background-color: rgba(245, 158, 11, 0.2) !important;
            color: #fef3c7 !important;
            border-color: var(--border-color) !important;
        }

        .table-info, .table-info th, .table-info td {
            background-color: rgba(14, 165, 233, 0.2) !important;
            color: #e0f2fe !important;
            border-color: var(--border-color) !important;
        }

        .table-bordered {
            border-color: var(--border-color) !important;
        }

        /* Alerts in Dark Mode */
        .alert {
            border-radius: 12px;
            border-width: 1px;
        }
        .alert-info {
            background-color: rgba(14, 165, 233, 0.12) !important;
            border-color: rgba(14, 165, 233, 0.28) !important;
            color: #7dd3fc !important;
        }
        .alert-success {
            background-color: rgba(16, 185, 129, 0.12) !important;
            border-color: rgba(16, 185, 129, 0.28) !important;
            color: #6ee7b7 !important;
        }
        .alert-warning {
            background-color: rgba(245, 158, 11, 0.12) !important;
            border-color: rgba(245, 158, 11, 0.28) !important;
            color: #fde68a !important;
        }
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.12) !important;
            border-color: rgba(239, 68, 68, 0.28) !important;
            color: #fca5a5 !important;
        }
        .alert-primary {
            background-color: rgba(99, 102, 241, 0.12) !important;
            border-color: rgba(99, 102, 241, 0.28) !important;
            color: #a5b4fc !important;
        }

        /* Forms & Inputs in Dark Mode */
        .form-control, .form-select {
            background-color: var(--bg-input) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #f8fafc !important;
            border-radius: 10px;
        }

        .form-control:focus, .form-select:focus {
            background-color: #070b14 !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25) !important;
            color: #ffffff !important;
        }

        .form-control::placeholder {
            color: #475569 !important;
        }

        .input-group-text {
            background-color: #162038 !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #94a3b8 !important;
        }

        .form-check-input {
            background-color: #0a0f1d !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        .form-check-input:checked {
            background-color: #6366f1 !important;
            border-color: #6366f1 !important;
        }

        /* Modals in Dark Mode */
        .modal-content {
            background-color: #121a2d !important;
            border: 1px solid var(--border-color) !important;
            color: #f8fafc !important;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6) !important;
            border-radius: 16px;
        }

        .modal-header {
            background-color: #162038 !important;
            border-bottom: 1px solid var(--border-color) !important;
        }

        .modal-footer {
            background-color: #162038 !important;
            border-top: 1px solid var(--border-color) !important;
        }

        .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* Dropdowns in Dark Mode */
        .dropdown-menu {
            background-color: #121a2d !important;
            border: 1px solid var(--border-color) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
        }

        .dropdown-item {
            color: #e2e8f0 !important;
        }

        .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.06) !important;
            color: #ffffff !important;
        }

        .dropdown-divider {
            border-top-color: var(--border-color) !important;
        }

        .dropdown-header {
            color: #94a3b8 !important;
        }

        /* List Group in Dark Mode */
        .list-group-item {
            background-color: var(--bg-card) !important;
            border-color: var(--border-color) !important;
            color: var(--text-main) !important;
        }

        /* Buttons in Dark Mode */
        .btn-outline-primary {
            border-color: #6366f1 !important;
            color: #a5b4fc !important;
        }
        .btn-outline-primary:hover {
            background-color: #6366f1 !important;
            color: #ffffff !important;
        }
        .btn-outline-secondary {
            border-color: rgba(255, 255, 255, 0.2) !important;
            color: #cbd5e1 !important;
        }
        .btn-outline-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
        }
        .btn-outline-info {
            border-color: #0ea5e9 !important;
            color: #7dd3fc !important;
        }
        .btn-outline-success {
            border-color: #10b981 !important;
            color: #6ee7b7 !important;
        }
        .btn-outline-danger {
            border-color: #ef4444 !important;
            color: #fca5a5 !important;
        }
        .btn-outline-warning {
            border-color: #f59e0b !important;
            color: #fde68a !important;
        }

        /* App Wrapper */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* Sidebar Styling */
        .app-sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1040;
            transition: transform 0.25s ease;
            box-shadow: 0 0 35px rgba(0, 0, 0, 0.4);
        }

        .sidebar-brand {
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
            text-decoration: none;
        }

        .brand-icon {
            background: var(--primary-gradient);
            color: white;
            font-size: 1.25rem;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
        }

        .brand-text {
            line-height: 1.2;
        }

        .brand-title {
            font-weight: 800;
            font-size: 1.15rem;
            color: #ffffff !important;
            letter-spacing: -0.3px;
        }

        .brand-subtitle {
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .sidebar-nav {
            padding: 12px 14px;
            overflow-y: auto;
            flex-grow: 1;
        }

        .nav-category {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.8px;
            padding: 14px 10px 6px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 14px;
            color: #94a3b8;
            font-weight: 600;
            font-size: 0.88rem;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s ease;
            margin-bottom: 3px;
        }

        .sidebar-link i {
            font-size: 1.15rem;
            color: #64748b;
            transition: color 0.2s ease;
        }

        .sidebar-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.06);
        }

        .sidebar-link:hover i {
            color: #818cf8;
        }

        .sidebar-link.active {
            color: #ffffff;
            background: var(--primary-gradient);
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.35);
        }

        .sidebar-link.active i {
            color: #ffffff;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border-color);
            background: rgba(13, 19, 34, 0.8);
        }

        /* Main Content Area */
        .app-main {
            flex-grow: 1;
            margin-left: var(--sidebar-width);
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left 0.25s ease;
        }

        /* Top Header */
        .app-topbar {
            height: 68px;
            background: rgba(13, 19, 34, 0.88);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1020;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }

        /* Mobile Backdrop */
        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(7, 10, 19, 0.75);
            backdrop-filter: blur(3px);
            z-index: 1030;
            display: none;
        }

        @media (max-width: 991.98px) {
            .app-sidebar {
                transform: translateX(-100%);
            }
            .app-sidebar.show {
                transform: translateX(0);
            }
            .app-main {
                margin-left: 0 !important;
            }
            .sidebar-backdrop.show {
                display: block;
            }
        }

        /* Status Pills for Attendance */
        .status-pill-radio {
            display: none;
        }

        .status-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            border: 2px solid rgba(255, 255, 255, 0.1);
            background: #0f172a;
            color: #94a3b8;
            transition: all 0.2s ease;
        }

        .status-label:hover {
            border-color: #6366f1;
            color: #ffffff;
        }

        /* Status Colors */
        .status-pill-radio[value="H"]:checked + .status-label {
            background: #10b981;
            border-color: #10b981;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.45);
        }

        .status-pill-radio[value="S"]:checked + .status-label {
            background: #3b82f6;
            border-color: #3b82f6;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.45);
        }

        .status-pill-radio[value="I"]:checked + .status-label {
            background: #f59e0b;
            border-color: #f59e0b;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.45);
        }

        .status-pill-radio[value="A"]:checked + .status-label {
            background: #ef4444;
            border-color: #ef4444;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.45);
        }

        /* Badges */
        .badge.bg-light {
            background-color: rgba(255, 255, 255, 0.08) !important;
            color: #cbd5e1 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
        }
        .badge-h { background-color: rgba(16, 185, 129, 0.2); color: #6ee7b7; font-weight: 600; }
        .badge-s { background-color: rgba(59, 130, 246, 0.2); color: #93c5fd; font-weight: 600; }
        .badge-i { background-color: rgba(245, 158, 11, 0.2); color: #fde68a; font-weight: 600; }
        .badge-a { background-color: rgba(239, 68, 68, 0.2); color: #fca5a5; font-weight: 600; }

        /* Buttons */
        .btn-primary-custom {
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
            transition: all 0.2s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 22px rgba(99, 102, 241, 0.5);
            color: white;
        }

        .btn-light {
            background-color: rgba(255, 255, 255, 0.08) !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            color: #e2e8f0 !important;
        }

        .btn-light:hover {
            background-color: rgba(255, 255, 255, 0.14) !important;
            color: #ffffff !important;
        }

        /* Nav Pills Header */
        .nav-pills .nav-link {
            color: #94a3b8;
        }
        .nav-pills .nav-link.active {
            background: var(--primary-gradient) !important;
            color: #ffffff !important;
        }

        /* Footer */
        footer {
            background-color: var(--bg-sidebar) !important;
            border-top-color: var(--border-color) !important;
            color: #64748b !important;
        }

        @media print {
            .app-sidebar, .app-topbar, .sidebar-backdrop, footer {
                display: none !important;
            }
            .app-main {
                margin-left: 0 !important;
            }
        }
    </style>
</head>
<body>

    <?php if (session()->get('logged_in')): ?>
        <!-- Mobile Sidebar Backdrop -->
        <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

        <div class="app-wrapper">
            <!-- Sidebar -->
            <aside class="app-sidebar" id="appSidebar">
                <!-- Brand Header -->
                <a href="<?= site_url() ?>" class="sidebar-brand">
                    <div class="brand-icon">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <div class="brand-text">
                        <div class="brand-title">SI-ABSEN</div>
                        <div class="brand-subtitle">Presensi & Pembinaan</div>
                    </div>
                </a>

                <!-- Navigation Menu List -->
                <nav class="sidebar-nav">
                    <?php $role = session()->get('role'); ?>

                    <?php if ($role === 'admin'): ?>
                        <!-- Admin Menu -->
                        <div class="nav-category">Utama</div>
                        <a class="sidebar-link <?= uri_string() === 'admin' ? 'active' : '' ?>" href="<?= site_url('admin') ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>

                        <div class="nav-category">Data Master</div>
                        <a class="sidebar-link <?= (str_starts_with(uri_string(), 'admin/guru') && !str_starts_with(uri_string(), 'admin/jadwal-guru')) ? 'active' : '' ?>" href="<?= site_url('admin/guru') ?>">
                            <i class="bi bi-person-badge"></i>
                            <span>Data Guru</span>
                        </a>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'admin/jadwal-guru') ? 'active' : '' ?>" href="<?= site_url('admin/jadwal-guru') ?>">
                            <i class="bi bi-calendar2-week"></i>
                            <span>Jadwal Mengajar</span>
                        </a>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'admin/kelas') ? 'active' : '' ?>" href="<?= site_url('admin/kelas') ?>">
                            <i class="bi bi-door-open"></i>
                            <span>Data Kelas</span>
                        </a>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'admin/siswa') ? 'active' : '' ?>" href="<?= site_url('admin/siswa') ?>">
                            <i class="bi bi-people"></i>
                            <span>Data Siswa</span>
                        </a>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'admin/users') ? 'active' : '' ?>" href="<?= site_url('admin/users') ?>">
                            <i class="bi bi-shield-lock"></i>
                            <span>Akun Pengguna</span>
                        </a>

                        <div class="nav-category">Layanan Piket</div>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'piket/guru-absen') ? 'active' : '' ?>" href="<?= site_url('piket/guru-absen') ?>">
                            <i class="bi bi-person-check-fill"></i>
                            <span>Presensi Guru</span>
                        </a>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'admin/keterlambatan-guru') ? 'active' : '' ?>" href="<?= site_url('admin/keterlambatan-guru') ?>">
                            <i class="bi bi-clock-history"></i>
                            <span>Keterlambatan Guru</span>
                        </a>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'piket/siswa-terlambat') ? 'active' : '' ?>" href="<?= site_url('piket/siswa-terlambat') ?>">
                            <i class="bi bi-alarm-fill"></i>
                            <span>Siswa Terlambat</span>
                        </a>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'admin/penugasan-piket') ? 'active' : '' ?>" href="<?= site_url('admin/penugasan-piket') ?>">
                            <i class="bi bi-calendar-check"></i>
                            <span>Jadwal Guru Piket</span>
                        </a>
                        <a class="sidebar-link <?= uri_string() === 'piket' ? 'active' : '' ?>" href="<?= site_url('piket') ?>">
                            <i class="bi bi-shield-check"></i>
                            <span>Portal Piket</span>
                        </a>

                        <div class="nav-category">Kesiswaan & Pembinaan</div>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'admin/pembinaan') ? 'active' : '' ?>" href="<?= site_url('admin/pembinaan') ?>">
                            <i class="bi bi-shield-exclamation"></i>
                            <span>Riwayat Pembinaan</span>
                        </a>

                        <div class="nav-category">Laporan & Analisis</div>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'admin/monitoring') ? 'active' : '' ?>" href="<?= site_url('admin/monitoring') ?>">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>Monitoring Kehadiran</span>
                        </a>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'admin/laporan') ? 'active' : '' ?>" href="<?= site_url('admin/laporan') ?>">
                            <i class="bi bi-file-earmark-bar-graph"></i>
                            <span>Laporan Global</span>
                        </a>

                    <?php elseif ($role === 'walas'): ?>
                        <!-- Walas Menu -->
                        <div class="nav-category">Utama</div>
                        <a class="sidebar-link <?= uri_string() === 'walas' ? 'active' : '' ?>" href="<?= site_url('walas') ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard Walas</span>
                        </a>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'walas/absen') ? 'active' : '' ?>" href="<?= site_url('walas/absen') ?>">
                            <i class="bi bi-pencil-square"></i>
                            <span>Input Presensi Harian</span>
                        </a>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'walas/rekap') ? 'active' : '' ?>" href="<?= site_url('walas/rekap') ?>">
                            <i class="bi bi-calendar-check"></i>
                            <span>Rekap Presensi</span>
                        </a>

                        <div class="nav-category">Siswa & Kredensial</div>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'walas/akun-siswa') ? 'active' : '' ?>" href="<?= site_url('walas/akun-siswa') ?>">
                            <i class="bi bi-person-badge-fill"></i>
                            <span>Akun Perwakilan Kelas</span>
                        </a>

                        <div class="nav-category">Layanan Piket</div>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'piket') ? 'active' : '' ?>" href="<?= site_url('piket') ?>">
                            <i class="bi bi-shield-check"></i>
                            <span>Tugas Guru Piket</span>
                        </a>

                    <?php elseif ($role === 'guru_piket'): ?>
                        <!-- Guru Piket Menu -->
                        <div class="nav-category">Utama</div>
                        <a class="sidebar-link <?= uri_string() === 'piket' ? 'active' : '' ?>" href="<?= site_url('piket') ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard Piket</span>
                        </a>

                        <div class="nav-category">Layanan Piket</div>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'piket/guru-absen') ? 'active' : '' ?>" href="<?= site_url('piket/guru-absen') ?>">
                            <i class="bi bi-person-check-fill"></i>
                            <span>Presensi Guru</span>
                        </a>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'piket/siswa-terlambat') ? 'active' : '' ?>" href="<?= site_url('piket/siswa-terlambat') ?>">
                            <i class="bi bi-alarm-fill"></i>
                            <span>Siswa Terlambat</span>
                        </a>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'piket/rekap') ? 'active' : '' ?>" href="<?= site_url('piket/rekap') ?>">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Rekap Piket</span>
                        </a>

                    <?php elseif ($role === 'pj_kelas'): ?>
                        <!-- PJ Kelas Menu -->
                        <div class="nav-category">Presensi Harian</div>
                        <a class="sidebar-link <?= uri_string() === 'pj' ? 'active' : '' ?>" href="<?= site_url('pj') ?>">
                            <i class="bi bi-clipboard-check"></i>
                            <span>Input Absen Harian</span>
                        </a>
                        <a class="sidebar-link <?= str_starts_with(uri_string(), 'pj/rekap') ? 'active' : '' ?>" href="<?= site_url('pj/rekap') ?>">
                            <i class="bi bi-journal-text"></i>
                            <span>Rekap Kelas</span>
                        </a>
                    <?php endif; ?>
                </nav>

                <!-- Sidebar Footer User Profile -->
                <div class="sidebar-footer">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 fs-5 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 0.85rem;"><?= esc(session()->get('nama_lengkap')) ?></div>
                                <div class="text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                                    <?= esc(session()->get('role')) ?>
                                </div>
                            </div>
                        </div>
                        <a href="<?= site_url('auth/logout') ?>" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-2" title="Keluar" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                            <i class="bi bi-box-arrow-right fs-6"></i>
                        </a>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="app-main">
                <!-- Topbar -->
                <header class="app-topbar">
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" class="btn btn-light border rounded-3 p-2 d-lg-none" onclick="toggleSidebar()">
                            <i class="bi bi-list fs-5 text-dark"></i>
                        </button>
                        <div class="d-none d-sm-block">
                            <span class="text-muted small">Sistem Informasi Presensi & Pembinaan</span>
                        </div>
                    </div>

                    <!-- Right Topbar Profile Dropdown -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill px-3 py-2 bg-black bg-opacity-40 border border-secondary border-opacity-25 text-info d-none d-md-inline-flex align-items-center gap-2 me-1">
                            <i class="bi bi-moon-stars-fill text-warning"></i> <span class="fw-semibold">Tema Gelap</span>
                        </span>
                        <div class="text-end d-none d-md-block me-2">
                            <div class="fw-bold text-dark lh-1 small"><?= esc(session()->get('nama_lengkap')) ?></div>
                            <small class="text-muted text-uppercase" style="font-size: 0.68rem;">
                                <span class="badge bg-light text-primary border">Role: <?= esc(session()->get('role')) ?></span>
                                <?php if (session()->get('nama_kelas')): ?>
                                    <span class="badge bg-light text-success border ms-1"><?= esc(session()->get('nama_kelas')) ?></span>
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-light rounded-circle shadow-sm border p-2" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear-fill text-secondary"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2">
                                <li><h6 class="dropdown-header">Pengaturan Akun (<?= esc(session()->get('username')) ?>)</h6></li>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= site_url('auth/ganti-password') ?>">
                                        <i class="bi bi-key me-2 text-warning"></i> Ganti Password
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item py-2 text-danger" href="<?= site_url('auth/logout') ?>">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </header>

                <!-- Main Container Content -->
                <main class="flex-grow-1 p-3 p-md-4">
                    <!-- Flash Alerts -->
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2" role="alert">
                            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                            <div><?= session()->getFlashdata('success') ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                            <div><?= session()->getFlashdata('error') ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('warning')): ?>
                        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2" role="alert">
                            <i class="bi bi-exclamation-circle-fill fs-5 text-warning"></i>
                            <div><?= session()->getFlashdata('warning') ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('info')): ?>
                        <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2" role="alert">
                            <i class="bi bi-info-circle-fill fs-5 text-info"></i>
                            <div><?= session()->getFlashdata('info') ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terdapat kesalahan pengisian formulir:</div>
                            <ul class="mb-0 ps-3">
                                <?php foreach (session()->getFlashdata('errors') as $err): ?>
                                    <li><?= esc($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Render Content -->
                    <?= $this->renderSection('content') ?>
                </main>

                <!-- Footer -->
                <footer class="bg-white border-top py-3 mt-auto px-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center text-muted small">
                        <div>
                            &copy; <?= date('Y') ?> <strong>SI-ABSEN</strong>. Sistem Informasi Absensi & Pembinaan Siswa.
                        </div>
                        <div class="mt-2 mt-sm-0">
                            <span class="badge bg-light text-muted border">Versi Si-Absen 2.0</span>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

    <?php else: ?>
        <!-- Public / Non-Logged In View (e.g. Login page) -->
        <main class="min-vh-100 d-flex flex-column justify-content-center">
            <?= $this->renderSection('content') ?>
        </main>
    <?php endif; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById('appSidebar');
            var backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar) {
                sidebar.classList.toggle('show');
            }
            if (backdrop) {
                backdrop.classList.toggle('show');
            }
        }
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
