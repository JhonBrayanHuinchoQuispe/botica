<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/login.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('imagen/logo.png') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="background-container">
        <div class="blur-overlay"></div>
        <div class="container h-100 d-flex align-items-center justify-content-center">
            <div class="login-card">
                <div class="text-center mb-3">
                    <img src="{{ asset('imagen/logo.png') }}" alt="Logo Botica" class="logo mb-2">
                    <h2 class="fw-bold text-danger">Botica San Antonio</h2>
                    <p class="text-muted">Sistema de Administración</p>
                </div>
                
                <h3 class="text-center">Iniciar Sesión</h3>
                <div class="text-center mb-2">
                    <a href="{{ route('diagnostico') }}" class="btn btn-outline-secondary btn-sm">
                        Diagnóstico
                    </a>
                </div>
                
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="input-container">
                        <!-- Icono inline SVG (fallback si FontAwesome no carga) -->
                        <span class="input-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="#dc3545" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5zm0 2c-3.866 0-7 3.134-7 7h2c0-2.761 2.239-5 5-5s5 2.239 5 5h2c0-3.866-3.134-7-7-7z"/>
                            </svg>
                        </span>
                        <input type="email" class="form-control input-field @error('email') is-invalid @enderror" 
                               name="email" value="{{ old('email') }}" placeholder="Correo electrónico" required autofocus aria-label="Correo electrónico" autocomplete="username">
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <div class="input-container">
                        <!-- Icono inline SVG (fallback si FontAwesome no carga) -->
                        <span class="input-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="#dc3545" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17 8V7a5 5 0 00-10 0v1H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V10a2 2 0 00-2-2h-2zm-8-1a3 3 0 016 0v1H9V7zm10 13H5V10h14v10z"/>
                            </svg>
                        </span>
                        <input type="password" class="form-control input-field @error('password') is-invalid @enderror" 
                               name="password" placeholder="Contraseña" required>
                        <span class="password-toggle" onclick="togglePassword()" role="button" aria-label="Mostrar u ocultar contraseña">
                            <!-- Icono inline SVG para toggle (fallback) -->
                            <svg id="toggleIcon" width="18" height="18" viewBox="0 0 24 24" fill="#6c757d" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 5c-7.633 0-10.89 6.763-10.99 6.96L1 12.06c.1.197 3.357 6.96 10.99 6.96s10.89-6.763 10.99-6.96L23 11.96C22.9 11.763 19.633 5 12 5zm0 12c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6zm0-10a4 4 0 100 8 4 4 0 000-8z"/>
                            </svg>
                        </span>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" 
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Recordar sesión
                            </label>
                        </div>
                        <a href="{{ route('password.request') }}" class="forgot-link" style="color: #dc3545 !important; font-weight: 500; text-decoration: none; font-size: 14px;">¿Olvidó su contraseña?</a>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="login-btn">
                            Iniciar Sesión
                            <span class="ms-2" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 17l5-5-5-5v3H3v4h7v3zm10-12h-8v2h8v12h-8v2h8a2 2 0 002-2V7a2 2 0 00-2-2z"/>
                                </svg>
                            </span>
                        </button>
                    </div>
                    
                    @if(session('error'))
                    <div class="alert alert-danger mt-3">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/admin/login.js') }}"></script>
    
    @if(session('password_reset_success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Contraseña Restablecida!',
            text: '{{ session('password_reset_success') }}',
            confirmButtonText: 'Continuar',
            confirmButtonColor: '#dc3545',
            background: '#fff',
            color: '#333',
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        });
    </script>
    @endif
</body>
</html>