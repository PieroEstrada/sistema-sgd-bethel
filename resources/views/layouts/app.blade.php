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

    <!-- Vite Assets (Alpine.js + Echo para WebSockets) -->
    @vite(['resources/js/app.js'])

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

        /* ⚡ NOTIFICATION BELL */
        .notification-bell {
            background: none;
            border: none;
            color: #6c757d;
            padding: 0.5rem;
            transition: all 0.2s ease;
        }

        .notification-bell:hover {
            color: var(--bethel-secondary);
        }

        .notification-badge {
            font-size: 0.65rem;
            padding: 0.25rem 0.45rem;
        }

        .notification-dropdown {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .notification-item {
            border-bottom: 1px solid #f1f1f1;
            transition: background-color 0.2s ease;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background-color: #f8f9fc;
        }

        .notification-icon {
            width: 24px;
            text-align: center;
        }

        /* ⚡ GLOBAL LOADER OVERLAY */
        .global-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }

        .global-loader.active {
            opacity: 1;
            visibility: visible;
        }

        .global-loader .loader-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .global-loader .loader-text {
            color: #fff;
            margin-top: 15px;
            font-size: 1.1rem;
            font-weight: 500;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
    
    @stack('styles')
</head>

<body>
    <!-- Global Loading Overlay -->
    <div class="global-loader" id="globalLoader">
        <div class="loader-spinner"></div>
        <div class="loader-text">Cargando...</div>
    </div>

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
                <a class="nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                    <i class="fas fa-ticket-alt"></i>
                    <span class="nav-text">Tickets</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}" href="{{ route('chat.index') }}">
                    <i class="fas fa-comments"></i>
                    <span class="nav-text">Mensajes</span>
                </a>
            </div>

            {{-- Digitalización: Módulo desactivado
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('digitalizacion.*') ? 'active' : '' }}" href="#">
                    <i class="fas fa-folder-open"></i>
                    <span class="nav-text">Digitalización</span>
                </a>
            </div>
            --}}
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
            
            <div class="d-flex align-items-center">
                <!-- Notification Bell -->
                <div class="dropdown me-3">
                    @php
                        $unreadNotifications = auth()->user()->unreadNotifications->take(10);
                        $unreadCount = auth()->user()->unreadNotifications->count();
                    @endphp
                    <button class="btn position-relative notification-bell" type="button"
                            id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fa-lg"></i>
                        @if($unreadCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
                        <li><h6 class="dropdown-header">Notificaciones</h6></li>
                        @forelse($unreadNotifications as $notification)
                            <li>
                                <a class="dropdown-item notification-item py-2" href="{{ $notification->data['url'] ?? '#' }}"
                                   onclick="markAsRead('{{ $notification->id }}')">
                                    <div class="d-flex align-items-start">
                                        <div class="notification-icon me-2">
                                            @if(isset($notification->data['tipo']))
                                                @if($notification->data['tipo'] === 'renovacion')
                                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                                @elseif($notification->data['tipo'] === 'ticket')
                                                    <i class="fas fa-ticket-alt text-info"></i>
                                                @else
                                                    <i class="fas fa-bell text-primary"></i>
                                                @endif
                                            @else
                                                <i class="fas fa-bell text-primary"></i>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold small">{{ $notification->data['titulo'] ?? 'Notificación' }}</div>
                                            <div class="text-muted small text-truncate" style="max-width: 280px;">
                                                {{ $notification->data['mensaje'] ?? '' }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li>
                                <div class="dropdown-item text-center text-muted py-3">
                                    <i class="fas fa-check-circle mb-2 d-block"></i>
                                    No tienes notificaciones nuevas
                                </div>
                            </li>
                        @endforelse
                        @if($unreadCount > 0)
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="px-3 py-2">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                        Marcar todas como leídas
                                    </button>
                                </form>
                            </li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a href="{{ route('notifications.index') }}" class="dropdown-item text-center text-primary py-2">
                                <i class="fas fa-list me-1"></i> Ver todas las notificaciones
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn dropdown-toggle d-flex align-items-center" type="button"
                            id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        {{-- <span class="d-none d-sm-inline small me-2">Usuario Demo</span> --}}
                        <span class="d-none d-sm-inline small me-2">{{ auth()->user()->name }}</span>

                        <i class="fas fa-user-circle fa-lg"></i>
                    </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><h6 class="dropdown-header">{{ auth()->user()->name }}</h6></li>
                    <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->rol->getDisplayName() }}</span></li>
                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user me-2"></i>Mi Perfil
                        </a>
                    </li>


                    


                    @if(auth()->user()->rol->value === 'administrador')
                        <li>
                            <a class="dropdown-item" href="{{ route('usuarios.index') }}">
                                <i class="fas fa-users me-2"></i>Usuarios
                            </a>
                        </li>
                    @endif

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                            </button>
                        </form>
                    </li>
                </ul>
                </div>
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

        // ⚡ MARK NOTIFICATION AS READ
        function markAsRead(notificationId) {
            fetch(`/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).catch(err => console.log('Error marking notification as read:', err));
        }

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

        // ⚡ GLOBAL LOADER FUNCTIONS
        const globalLoader = document.getElementById('globalLoader');

        function showLoader(text = 'Cargando...') {
            if (globalLoader) {
                globalLoader.querySelector('.loader-text').textContent = text;
                globalLoader.classList.add('active');
            }
        }

        function hideLoader() {
            if (globalLoader) {
                globalLoader.classList.remove('active');
            }
        }

        // Auto-show loader on navigation and form submit
        document.addEventListener('DOMContentLoaded', function() {
            // Hide loader when page finishes loading
            hideLoader();

            // Show loader on internal link clicks (navigation)
            document.querySelectorAll('a[href]:not([target="_blank"]):not([href^="#"]):not([href^="javascript"]):not([download])').forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    // Skip external links and anchors
                    if (href && !href.startsWith('http') && !href.startsWith('mailto:') && !href.startsWith('tel:')) {
                        showLoader('Cargando...');
                    }
                });
            });

            // Show loader on form submit
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Skip forms with data-no-loader attribute
                    if (!this.hasAttribute('data-no-loader')) {
                        showLoader('Procesando...');
                    }
                });
            });

            // Show loader on buttons with data-loader attribute
            document.querySelectorAll('[data-loader]').forEach(btn => {
                btn.addEventListener('click', function() {
                    const text = this.getAttribute('data-loader') || 'Cargando...';
                    showLoader(text);
                    // For download links, hide after a delay
                    if (this.hasAttribute('href') && (this.hasAttribute('download') || this.href.includes('exportar'))) {
                        setTimeout(hideLoader, 3000);
                    }
                });
            });

            // Show loader on export links (Excel/PDF)
            document.querySelectorAll('a[href*="exportar"]').forEach(link => {
                link.addEventListener('click', function() {
                    showLoader('Exportando...');
                    // Hide after delay since we can't detect download completion
                    setTimeout(hideLoader, 4000);
                });
            });
        });

        // Hide loader on page show (back/forward navigation)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                hideLoader();
            }
        });

        // Global AJAX/Fetch interceptor (optional - for fetch requests)
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            return originalFetch.apply(this, args).finally(() => {
                // Don't auto-hide for fetch, let specific code handle it
            });
        };
    </script>
    
    @stack('scripts')
</body>
</html>