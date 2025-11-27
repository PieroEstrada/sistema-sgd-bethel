<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Sistema SGD Bethel')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --bethel-primary: #2c3e50;
            --bethel-secondary: #3498db;
            --bethel-success: #27ae60;
            --bethel-danger: #e74c3c;
            --bethel-warning: #f39c12;
            --bethel-info: #17a2b8;
            --sidebar-width-collapsed: 70px;
            --sidebar-width-expanded: 250px;
            --transition-duration: 0.3s;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
            overflow-x: hidden;
        }

        /* ⚡ SIDEBAR BASE */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width-collapsed);
            background: linear-gradient(180deg, var(--bethel-primary) 0%, #34495e 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: width var(--transition-duration) ease;
            z-index: 1000;
            overflow-x: hidden;
        }

        .sidebar.expanded {
            width: var(--sidebar-width-expanded);
        }

        /* ⚡ SIDEBAR BRAND */
        .sidebar-brand {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-brand i {
            font-size: 1.5rem;
            min-width: 40px;
        }

        .sidebar-brand .brand-text {
            margin-left: 15px;
            font-weight: 700;
            opacity: 0;
            transition: opacity var(--transition-duration) ease;
        }

        .sidebar.expanded .brand-text {
            opacity: 1;
        }

        /* ⚡ TOGGLE BUTTON */
        .sidebar-toggle {
            position: absolute;
            top: 15px;
            right: -15px;
            width: 30px;
            height: 30px;
            background: var(--bethel-secondary);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            transition: all var(--transition-duration) ease;
            z-index: 1001;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-toggle:hover {
            background: #2980b9;
            transform: scale(1.1);
        }

        /* ⚡ NAVIGATION */
        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8) !important;
            text-decoration: none;
            transition: all var(--transition-duration) ease;
            white-space: nowrap;
            overflow: hidden;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white !important;
        }

        .nav-link.active {
            background-color: var(--bethel-secondary);
            color: white !important;
        }

        .nav-link i {
            font-size: 1.1rem;
            min-width: 30px;
            text-align: center;
        }

        .nav-link .nav-text {
            margin-left: 15px;
            opacity: 0;
            transition: opacity var(--transition-duration) ease;
        }

        .sidebar.expanded .nav-text {
            opacity: 1;
        }

        /* ⚡ MAIN CONTENT */
        .main-content {
            margin-left: var(--sidebar-width-collapsed);
            transition: margin-left var(--transition-duration) ease;
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width-collapsed));
        }

        .sidebar.expanded ~ .main-content {
            margin-left: var(--sidebar-width-expanded);
            width: calc(100% - var(--sidebar-width-expanded));
        }

        /* ⚡ TOP NAVBAR */
        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* ⚡ MOBILE STYLES */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                transform: translateX(-100%);
                transition: all var(--transition-duration) ease;
            }

            .sidebar.mobile-open {
                width: var(--sidebar-width-expanded);
                transform: translateX(0);
            }

            .sidebar.mobile-open .brand-text,
            .sidebar.mobile-open .nav-text {
                opacity: 1;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                transition: none;
            }

            .sidebar.expanded ~ .main-content {
                margin-left: 0;
                width: 100%;
            }

            .sidebar-toggle {
                display: none;
            }

            .mobile-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: all var(--transition-duration) ease;
            }

            .mobile-overlay.active {
                opacity: 1;
                visibility: visible;
            }

            .mobile-hamburger {
                display: block;
                background: none;
                border: none;
                font-size: 1.5rem;
                color: var(--bethel-primary);
                cursor: pointer;
            }

            .sidebar-close {
                position: absolute;
                top: 15px;
                right: 15px;
                background: none;
                border: none;
                color: white;
                font-size: 1.5rem;
                cursor: pointer;
                z-index: 1002;
            }
        }

        @media (min-width: 769px) {
            .mobile-hamburger {
                display: none;
            }

            .sidebar-close {
                display: none;
            }

            .mobile-overlay {
                display: none;
            }
        }

        /* ⚡ CARD STYLES */
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.25);
        }

        .border-left-primary { border-left: 0.25rem solid var(--bethel-primary) !important; }
        .border-left-success { border-left: 0.25rem solid var(--bethel-success) !important; }
        .border-left-info { border-left: 0.25rem solid var(--bethel-info) !important; }
        .border-left-warning { border-left: 0.25rem solid var(--bethel-warning) !important; }
        .border-left-danger { border-left: 0.25rem solid var(--bethel-danger) !important; }

        /* ⚡ PAGINATION STYLES */
        .pagination {
            margin-bottom: 0;
        }
        
        .pagination .page-link {
            padding: 0.5rem 0.75rem;
            margin: 0 2px;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            color: #6c757d;
            background-color: #fff;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.15s ease-in-out;
        }
        
        .pagination .page-link:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
            color: #495057;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--bethel-secondary);
            border-color: var(--bethel-secondary);
            color: #fff;
        }
        
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            cursor: not-allowed;
        }

        /* ⚡ TABLE IMPROVEMENTS */
        .table th {
            border-top: none;
            font-weight: 600;
            background-color: #f8f9fc;
            font-size: 0.875rem;
        }

        .hover-row {
            transition: all 0.2s ease;
        }
        
        .hover-row:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }
    </style>
    
    @stack('styles')
</head>

<body>
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <!-- Toggle Button (Desktop) -->
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-chevron-right"></i>
        </button>

        <!-- Close Button (Mobile) -->
        <button class="sidebar-close" id="sidebarClose">
            <i class="fas fa-times"></i>
        </button>

        <!-- Logo/Brand -->
        <div class="sidebar-brand">
            <i class="fas fa-broadcast-tower"></i>
            <span class="brand-text">SGD Bethel</span>
        </div>
        
        <!-- Navigation -->
        <div class="sidebar-nav">
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('estaciones.*') ? 'active' : '' }}" href="{{ route('estaciones.index') }}">
                    <i class="fas fa-broadcast-tower"></i>
                    <span class="nav-text">Estaciones</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('incidencias.*') ? 'active' : '' }}" href="{{ route('incidencias.index') }}">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="nav-text">Incidencias</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('tramites.*') ? 'active' : '' }}" href="{{ route('tramites.index') }}">
                    <i class="fas fa-file-alt"></i>
                    <span class="nav-text">Trámites MTC</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('digitalizacion.*') ? 'active' : '' }}" href="#" onclick="showComingSoon('Digitalización')">
                    <i class="fas fa-folder-open"></i>
                    <span class="nav-text">Digitalización</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation Bar -->
        <nav class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <!-- Mobile Hamburger -->
                <button class="mobile-hamburger me-3" id="mobileHamburger">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div>
                    <h5 class="mb-0">@yield('title', 'Sistema SGD Bethel')</h5>
                </div>
            </div>
            
            <div class="dropdown">
                <button class="btn dropdown-toggle d-flex align-items-center" type="button" 
                        id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="d-none d-sm-inline small me-2">Usuario Demo</span>
                    <i class="fas fa-user-circle fa-lg"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><h6 class="dropdown-header">Sistema Demo</h6></li>
                    <li><span class="dropdown-item-text small text-muted">Administrador</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                </ul>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid px-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Main Content Area -->
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <script>
        // Global CSRF Token Setup
        window.Laravel = { csrfToken: '{{ csrf_token() }}' };
        
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // Auto-hide alerts
        setTimeout(function() { $('.alert').fadeOut('slow'); }, 5000);

        // ⚡ SIDEBAR FUNCTIONALITY
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileHamburger = document.getElementById('mobileHamburger');
            const sidebarClose = document.getElementById('sidebarClose');
            const mobileOverlay = document.getElementById('mobileOverlay');

            // Desktop Toggle
            sidebarToggle?.addEventListener('click', function() {
                sidebar.classList.toggle('expanded');
                
                // Rotar icono
                const icon = sidebarToggle.querySelector('i');
                if (sidebar.classList.contains('expanded')) {
                    icon.className = 'fas fa-chevron-left';
                } else {
                    icon.className = 'fas fa-chevron-right';
                }
            });

            // Mobile Open
            mobileHamburger?.addEventListener('click', function() {
                sidebar.classList.add('mobile-open');
                mobileOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });

            // Mobile Close
            function closeMobileSidebar() {
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            sidebarClose?.addEventListener('click', closeMobileSidebar);
            mobileOverlay?.addEventListener('click', closeMobileSidebar);

            // Auto-close mobile sidebar on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    closeMobileSidebar();
                }
            });

            // Cerrar sidebar móvil al hacer clic en un link
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        setTimeout(closeMobileSidebar, 100);
                    }
                });
            });
        });

        // ⚡ COMING SOON FUNCTION
        function showComingSoon(module) {
            alert(`El módulo "${module}" estará disponible próximamente.\n\nPor ahora puedes navegar entre Dashboard y Estaciones.`);
        }
    </script>
    
    @stack('scripts')
</body>
</html>