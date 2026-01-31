<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Iniciar Sesión - Sistema SGD Bethel</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bethel-primary: #2c3e50;
            --bethel-secondary: #3498db;
            --bethel-success: #27ae60;
            --bethel-danger: #e74c3c;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, var(--bethel-primary) 0%, var(--bethel-secondary) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--bethel-primary) 0%, #34495e 100%);
            color: white;
            padding: 1.5rem 1rem;
            text-align: center;
        }
        
        .login-header i {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }
        
        .login-header h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .login-header p {
            opacity: 0.8;
            margin: 0;
            font-size: 0.85rem;
        }
        
        .login-body {
            padding: 1.5rem 1rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--bethel-primary);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .form-control:focus {
            border-color: var(--bethel-secondary);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group .input-group-text {
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
            background: #f8f9fa;
            color: var(--bethel-primary);
            padding: 0.75rem 0.75rem;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: var(--bethel-secondary);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--bethel-secondary) 0%, #2980b9 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
            color: white;
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .form-check-input:checked {
            background-color: var(--bethel-secondary);
            border-color: var(--bethel-secondary);
        }
        
        .form-check-label {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            font-size: 0.9rem;
        }
        
        .demo-credentials {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--bethel-secondary);
        }
        
        .demo-credentials h6 {
            color: var(--bethel-primary);
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .demo-credentials small {
            color: #6c757d;
            line-height: 1.4;
            font-size: 0.8rem;
        }
        
        .login-footer {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            color: #6c757d;
            font-size: 0.8rem;
        }
        
        /* ⚡ RESPONSIVE DESIGN */
        
        /* Tablets y pantallas medianas */
        @media (max-width: 768px) {
            .login-container {
                max-width: 100%;
                padding: 0.5rem;
            }
            
            .login-header {
                padding: 1.25rem 1rem;
            }
            
            .login-header i {
                font-size: 2.25rem;
            }
            
            .login-header h1 {
                font-size: 1.1rem;
            }
            
            .login-body {
                padding: 1.25rem 1rem;
            }
            
            .demo-credentials {
                padding: 0.875rem;
            }
            
            .demo-credentials small {
                font-size: 0.75rem;
            }
        }
        
        /* Móviles pequeños */
        @media (max-width: 480px) {
            body {
                padding: 0.5rem;
            }
            
            .login-container {
                padding: 0.25rem;
            }
            
            .login-card {
                border-radius: 12px;
            }
            
            .login-header {
                padding: 1rem 0.875rem;
            }
            
            .login-header i {
                font-size: 2rem;
                margin-bottom: 0.25rem;
            }
            
            .login-header h1 {
                font-size: 1rem;
                margin-bottom: 0.125rem;
            }
            
            .login-header p {
                font-size: 0.8rem;
            }
            
            .login-body {
                padding: 1rem 0.875rem;
            }
            
            .form-label {
                font-size: 0.85rem;
                margin-bottom: 0.375rem;
            }
            
            .form-control, .input-group-text {
                padding: 0.625rem 0.75rem;
                font-size: 0.9rem;
            }
            
            .btn-login {
                padding: 0.625rem 1rem;
                font-size: 0.9rem;
            }
            
            .demo-credentials {
                padding: 0.75rem;
                margin-bottom: 1.25rem;
            }
            
            .demo-credentials h6 {
                font-size: 0.85rem;
                margin-bottom: 0.375rem;
            }
            
            .demo-credentials small {
                font-size: 0.7rem;
                line-height: 1.3;
            }
            
            .login-footer {
                padding: 0.75rem;
                font-size: 0.75rem;
            }
            
            /* Botones de llenado rápido más pequeños */
            .quick-fill-container .btn {
                font-size: 0.7rem;
                padding: 0.25rem 0.5rem;
                margin: 0.125rem;
            }
        }
        
        /* Móviles muy pequeños */
        @media (max-width: 360px) {
            .form-control, .input-group-text {
                padding: 0.5rem 0.625rem;
                font-size: 0.85rem;
            }
            
            .btn-login {
                padding: 0.5rem 0.875rem;
                font-size: 0.85rem;
            }
            
            .demo-credentials h6 {
                font-size: 0.8rem;
            }
            
            .demo-credentials small {
                font-size: 0.65rem;
            }
            
            .quick-fill-container {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                gap: 0.25rem;
            }
            
            .quick-fill-container .btn {
                font-size: 0.65rem;
                padding: 0.2rem 0.4rem;
                margin: 0 !important;
            }
        }
        
        /* Pantallas grandes */
        @media (min-width: 1200px) {
            .login-container {
                max-width: 450px;
            }
            
            .login-header i {
                font-size: 3rem;
                margin-bottom: 0.75rem;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }
            
            .login-body {
                padding: 2rem 1.5rem;
            }
        }
        
        /* Orientación horizontal en móviles */
        @media (max-height: 600px) and (orientation: landscape) {
            body {
                padding: 0.25rem;
            }
            
            .login-header {
                padding: 0.75rem 1rem;
            }
            
            .login-header i {
                font-size: 1.75rem;
                margin-bottom: 0.25rem;
            }
            
            .login-header h1 {
                font-size: 1rem;
            }
            
            .login-body {
                padding: 1rem;
            }
            
            .demo-credentials {
                padding: 0.5rem;
                margin-bottom: 1rem;
            }
            
            .demo-credentials small {
                font-size: 0.7rem;
                line-height: 1.2;
            }
            
            .login-footer {
                padding: 0.5rem;
                font-size: 0.7rem;
            }
        }
        
        /* Mejoras de accesibilidad */
        @media (prefers-reduced-motion: reduce) {
            .btn-login {
                transition: none;
            }
            
            .btn-login:hover {
                transform: none;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <i class="fas fa-broadcast-tower"></i>
                <h1>SGD Bethel</h1>
                <p>Sistema de Gestión y Digitalización</p>
            </div>
            
            <!-- Body -->
            <div class="login-body">
                <!-- Mostrar alertas -->
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

                <!-- Credenciales de demo -->
                <div class="demo-credentials">
                    <h6><i class="fas fa-key me-2"></i>Credenciales de Prueba</h6>
                    <small>
                        <strong>Admin:</strong> acueto@betheltv.tv / bethel2024<br>
                        <strong>Sectorista:</strong> rbravo@betheltv.tv / bethel2024<br>
                        <strong>Enc. Lab.:</strong> rcastillo@betheltv.tv / bethel2024<br>
                        <strong>Visor:</strong> jespiritu@betheltv.tv / bethel2024
                    </small>
                </div>
                
                <!-- Formulario de login -->
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-2"></i>Correo Electrónico
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="usuario@bethel.pe" 
                                   required 
                                   autofocus>
                        </div>
                        @error('email')
                            <div class="text-danger mt-1">
                                <small><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</small>
                            </div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Contraseña
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-key"></i>
                            </span>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="••••••••" 
                                   required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger mt-1">
                                <small><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</small>
                            </div>
                        @enderror
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Recordar mi sesión
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Iniciar Sesión
                    </button>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="login-footer">
                <i class="fas fa-shield-alt me-1"></i>
                Sistema Seguro • Asociación Cultural Bethel {{ date('Y') }}
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ⚡ TOGGLE PASSWORD VISIBILITY
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }
        
        // ⚡ AUTO-HIDE ALERTS
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('show')) {
                    alert.classList.remove('show');
                    alert.classList.add('fade');
                }
            });
        }, 5000);
        
        // ⚡ RESPONSIVE QUICK-FILL BUTTONS
        function setupResponsiveQuickFill() {
            const demoCredentials = document.querySelector('.demo-credentials');
            const quickFillContainer = document.createElement('div');
            quickFillContainer.className = 'mt-2 quick-fill-container';
            
            // Detectar tamaño de pantalla
            const isVerySmallScreen = window.innerWidth <= 360;
            
            if (isVerySmallScreen) {
                // Grid 2x2 para pantallas muy pequeñas
                quickFillContainer.innerHTML = `
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillCredentials('acueto@betheltv.tv', 'bethel2024')">Admin</button>
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="fillCredentials('rbravo@betheltv.tv', 'bethel2024')">Sector</button>
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="fillCredentials('rcastillo@betheltv.tv', 'bethel2024')">Lab</button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="fillCredentials('jespiritu@betheltv.tv', 'bethel2024')">Visor</button>
                `;
            } else {
                // Layout normal en línea
                quickFillContainer.innerHTML = `
                    <div class="d-flex gap-1 flex-wrap justify-content-center">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillCredentials('acueto@betheltv.tv', 'bethel2024')">Admin</button>
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="fillCredentials('rbravo@betheltv.tv', 'bethel2024')">Sectorista</button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="fillCredentials('rcastillo@betheltv.tv', 'bethel2024')">Enc. Lab</button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="fillCredentials('jespiritu@betheltv.tv', 'bethel2024')">Visor</button>
                    </div>
                `;
            }
            
            demoCredentials.appendChild(quickFillContainer);
        }
        
        // ⚡ FILL CREDENTIALS FUNCTION
        function fillCredentials(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            
            // Efecto visual
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            emailInput.style.borderColor = '#28a745';
            passwordInput.style.borderColor = '#28a745';
            
            setTimeout(() => {
                emailInput.style.borderColor = '';
                passwordInput.style.borderColor = '';
            }, 1500);
        }
        
        // ⚡ INITIALIZATION
        document.addEventListener('DOMContentLoaded', function() {
            setupResponsiveQuickFill();
            
            // Recrear botones cuando cambie el tamaño de pantalla
            let resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(function() {
                    const existingContainer = document.querySelector('.quick-fill-container');
                    if (existingContainer) {
                        existingContainer.remove();
                        setupResponsiveQuickFill();
                    }
                }, 250);
            });
        });
    </script>
</body>
</html>