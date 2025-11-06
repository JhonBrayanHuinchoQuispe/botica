import 'package:flutter/material.dart';
import '../../../core/config/theme.dart';
import '../../widgets/logo_widget.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/custom_button.dart';
import '../../../core/utils/validators.dart';
import '../../../data/services/auth_service.dart';
import '../home/home_screen.dart';
import 'recuperarContrasena_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;
  bool _obscurePassword = true;

  // Nuevas variables de seguridad
  int _failedAttempts = 0;
  DateTime? _lastFailedAttempt;
  static const int MAX_ATTEMPTS = 5;
  static const Duration LOCKOUT_DURATION = Duration(minutes: 5);

  @override
  void initState() {
    super.initState();
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }



  bool _canAttemptLogin() {
    if (_failedAttempts < MAX_ATTEMPTS) return true;

    if (_lastFailedAttempt == null) return false;

    // Verificar si ha pasado el tiempo de bloqueo
    final timeSinceLastAttempt = DateTime.now().difference(_lastFailedAttempt!);
    return timeSinceLastAttempt >= LOCKOUT_DURATION;
  }

  Duration _getRemainingLockoutTime() {
    if (_lastFailedAttempt == null) return Duration.zero;

    final timeSinceLastAttempt = DateTime.now().difference(_lastFailedAttempt!);
    return LOCKOUT_DURATION - timeSinceLastAttempt;
  }

  Future<void> _login() async {
    // Ocultar teclado antes de validar
    FocusScope.of(context).unfocus();

    // Verificar límite de intentos
    if (!_canAttemptLogin()) {
      final remainingTime = _getRemainingLockoutTime();
      _showErrorDialog(
        'Demasiados intentos',
        'Intenta nuevamente en ${remainingTime.inMinutes} minutos y ${remainingTime.inSeconds % 60} segundos.',
      );
      return;
    }

    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      // Usar el servicio de autenticación real con Laravel
      final authService = AuthService();
      final result = await authService.login(
        _emailController.text.trim(),
        _passwordController.text,
      );

      if (mounted) {
        setState(() => _isLoading = false);

        if (result.success) {
          // Reiniciar intentos fallidos al iniciar sesión correctamente
          _failedAttempts = 0;
          _lastFailedAttempt = null;

          // Mostrar diálogo de bienvenida bonito
          _showWelcomeDialog(result.user?.displayName ?? 'Usuario');
        } else {
          // Incrementar intentos fallidos
          _failedAttempts++;
          _lastFailedAttempt = DateTime.now();

          // Mostrar diálogo de error personalizado
          _showErrorDialog(
            'Error de Autenticación',
            result.message ?? (_failedAttempts < MAX_ATTEMPTS 
              ? 'Intento ${_failedAttempts} de $MAX_ATTEMPTS. Verifica tu correo y contraseña.' 
              : 'Demasiados intentos fallidos. Cuenta bloqueada temporalmente.'),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        _showErrorDialog(
          'Error de Conexión',
          'No se pudo conectar al servidor. Verifica tu conexión a internet.',
        );
      }
    }
  }



  // Método para mostrar un diálogo de error elegante
  void _showErrorDialog(String title, String message) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return Dialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
          ),
          child: Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.1),
                  blurRadius: 20,
                  offset: const Offset(0, 10),
                ),
              ],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Ícono de error
                Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    color: AppTheme.primaryRed.withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    Icons.error_outline,
                    color: AppTheme.primaryRed,
                    size: 50,
                  ),
                ),
                const SizedBox(height: 20),
                
                // Título
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.darkGray,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 12),
                
                // Mensaje
                Text(
                  message,
                  style: TextStyle(
                    fontSize: 16,
                    color: AppTheme.mediumGray,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 24),
                
                // Botón de cerrar
                ElevatedButton(
                  onPressed: () => Navigator.of(context).pop(),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primaryRed,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(15),
                    ),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 40,
                      vertical: 12,
                    ),
                  ),
                  child: const Text(
                    'Entendido',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  // Método para mostrar un diálogo de bienvenida elegante
  void _showWelcomeDialog(String userName) {
    showDialog(
      context: context,
      barrierDismissible: false, // No se puede cerrar tocando fuera
      builder: (BuildContext context) {
        // Auto cerrar el diálogo después de 2.5 segundos
        Future.delayed(const Duration(milliseconds: 2500), () {
          if (Navigator.of(context).canPop()) {
            Navigator.of(context).pop(); // Cerrar diálogo
            // Navegar a la pantalla principal
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(builder: (context) => const HomeScreen()),
            );
          }
        });
        
        return Dialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(25),
          ),
          child: Container(
            padding: const EdgeInsets.all(30),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(25),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.15),
                  blurRadius: 25,
                  offset: const Offset(0, 15),
                ),
              ],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Ícono de bienvenida con animación
                Container(
                  width: 100,
                  height: 100,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [
                        Colors.green.shade400,
                        Colors.green.shade600,
                      ],
                    ),
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.green.withOpacity(0.3),
                        blurRadius: 15,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.check_circle_outline,
                    color: Colors.white,
                    size: 60,
                  ),
                ),
                const SizedBox(height: 25),
                
                // Mensaje de éxito más corto
                Text(
                  'Inicio de sesión con éxito',
                  style: TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.darkGray,
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final screenHeight = MediaQuery.of(context).size.height;
    final screenWidth = MediaQuery.of(context).size.width;
    final keyboardHeight = MediaQuery.of(context).viewInsets.bottom;

    return GestureDetector(
      // Cerrar teclado al tocar fuera de los campos
      onTap: () => FocusScope.of(context).unfocus(),
      child: Scaffold(
        // Permitir que el contenido se ajuste cuando aparece el teclado
        resizeToAvoidBottomInset: true,
        body: SingleChildScrollView(
          child: Container(
        width: double.infinity,
            height: screenHeight,
        child: Stack(
          children: [
            // Fondo rojo que ocupa más espacio
            Container(
              width: double.infinity,
              height: screenHeight * 0.75, // 75% de la pantalla
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [
                    AppTheme.primaryRed,
                    AppTheme.darkRed,
                    AppTheme.primaryRed.withOpacity(0.9),
                  ],
                  stops: const [0.0, 0.6, 1.0],
                ),
              ),
            ),
            
            // Fondo blanco inferior
            Positioned(
              bottom: 0,
              left: 0,
              right: 0,
              child: Container(
                height: screenHeight * 0.35,
                decoration: BoxDecoration(
                  color: Colors.grey.shade50,
                  borderRadius: const BorderRadius.only(
                    topLeft: Radius.circular(30),
                    topRight: Radius.circular(30),
                  ),
                ),
              ),
            ),
            
            // Contenido principal
            SafeArea(
              child: SingleChildScrollView(
                padding: EdgeInsets.only(bottom: keyboardHeight),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: Column(
                    children: [
                      SizedBox(height: screenHeight * 0.08),
                      
                      // Logo y título superior en el área roja
                      Column(
                        children: [
                          // Logo con diseño mejorado
                          Container(
                            padding: const EdgeInsets.all(20),
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.15),
                              shape: BoxShape.circle,
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withOpacity(0.3),
                                  blurRadius: 30,
                                  offset: const Offset(0, 15),
                                ),
                                BoxShadow(
                                  color: Colors.white.withOpacity(0.1),
                                  blurRadius: 20,
                                  offset: const Offset(0, -5),
                                ),
                              ],
                            ),
                            child: Container(
                              width: 80,
                              height: 80,
                              decoration: BoxDecoration(
                                color: Colors.white,
                                shape: BoxShape.circle,
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.1),
                                    blurRadius: 15,
                                    offset: const Offset(0, 5),
                                  ),
                                ],
                              ),
                              child: ClipOval(
                                child: Image.asset(
                                  'assets/images/logo.png',
                                  width: 80,
                                  height: 80,
                                  fit: BoxFit.cover,
                                  errorBuilder: (context, error, stackTrace) {
                                    return Icon(
                                      Icons.local_pharmacy,
                                      size: 50,
                                      color: AppTheme.primaryRed,
                                    );
                                  },
                                ),
                              ),
                            ),
                          ),
                              const SizedBox(height: 10),
                          
                          Text(
                            'Botica San Antonio',
                            style: TextStyle(
                              fontSize: 32,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.white,
                              letterSpacing: 0.5,
                              shadows: [
                                Shadow(
                                  color: Colors.black.withOpacity(0.3),
                                  offset: const Offset(0, 2),
                                  blurRadius: 8,
                                ),
                              ],
                            ),
                          ),
                              const SizedBox(height: 5),
                          Text(
                            'Sistema de Gestión',
                            style: TextStyle(
                              fontSize: 18,
                              color: AppTheme.white.withOpacity(0.95),
                              fontWeight: FontWeight.w500,
                              letterSpacing: 0.5,
                            ),
                          ),
                        ],
                      ),
                      
                          SizedBox(height: screenHeight * 0.05),
                      
                      // Tarjeta de login flotante
                      Container(
                        width: double.infinity,
                        constraints: BoxConstraints(
                          maxWidth: screenWidth > 400 ? 400 : screenWidth - 32,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(32),
                          boxShadow: [
                            // Sombra principal
                            BoxShadow(
                              color: Colors.black.withOpacity(0.15),
                              blurRadius: 50,
                              offset: const Offset(0, 25),
                              spreadRadius: 0,
                            ),
                            // Sombra secundaria
                            BoxShadow(
                              color: AppTheme.primaryRed.withOpacity(0.1),
                              blurRadius: 30,
                              offset: const Offset(0, 15),
                              spreadRadius: 0,
                            ),
                            // Sombra sutil
                            BoxShadow(
                              color: Colors.black.withOpacity(0.08),
                              blurRadius: 20,
                              offset: const Offset(0, 10),
                              spreadRadius: -5,
                            ),
                          ],
                        ),
                        child: Padding(
                          padding: const EdgeInsets.all(36),
                          child: Form(
                            key: _formKey,
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                // Indicador visual superior
                                Center(
                                  child: Container(
                                    width: 60,
                                    height: 4,
                                    decoration: BoxDecoration(
                                      color: AppTheme.primaryRed.withOpacity(0.3),
                                      borderRadius: BorderRadius.circular(2),
                                    ),
                                  ),
                                ),
                                    const SizedBox(height: 16),
                                
                                Text(
                                  'Accede a tu cuenta',
                                  style: TextStyle(
                                    fontSize: 18,
                                    color: AppTheme.darkGray,
                                    fontWeight: FontWeight.w600,
                                  ),
                                  textAlign: TextAlign.center,
                                ),
                                    const SizedBox(height: 24),
                                
                                // Campo Usuario/Email
                                Container(
                                  decoration: BoxDecoration(
                                    borderRadius: BorderRadius.circular(20),
                                    boxShadow: [
                                      BoxShadow(
                                        color: const Color.fromARGB(255, 251, 237, 220).withOpacity(0.4),
                                        blurRadius: 15,
                                        offset: const Offset(0, 6),
                                      ),
                                    ],
                                  ),
                                  child: CustomTextField(
                                    controller: _emailController,
                                        label: 'Correo electrónico',
                                        prefixIcon: Icons.email_outlined,
                                    validator: Validators.email,
                                    keyboardType: TextInputType.emailAddress,
                                  ),
                                ),
                                    const SizedBox(height: 16),
                                
                                // Campo Contraseña
                                Container(
                                  decoration: BoxDecoration(
                                    borderRadius: BorderRadius.circular(20),
                                    boxShadow: [
                                      BoxShadow(
                                        color: const Color.fromARGB(255, 251, 237, 220).withOpacity(0.4),
                                        blurRadius: 15,
                                        offset: const Offset(0, 6),
                                      ),
                                    ],
                                  ),
                                  child: CustomTextField(
                                    controller: _passwordController,
                                    label: 'Contraseña',
                                        prefixIcon: Icons.lock_outline,
                                        obscureText: _obscurePassword,
                                        validator: Validators.password,
                                    suffixIcon: IconButton(
                                      icon: Icon(
                                            _obscurePassword ? Icons.visibility_off : Icons.visibility,
                                        color: AppTheme.mediumGray,
                                      ),
                                      onPressed: () {
                                            setState(() {
                                              _obscurePassword = !_obscurePassword;
                                            });
                                      },
                                    ),
                                  ),
                                ),
                                    const SizedBox(height: 10),
                                
                                // ¿Olvidaste tu contraseña?
                                Align(
                                  alignment: Alignment.centerRight,
                                  child: TextButton(
                                    onPressed: () {
                                      Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (context) => const RecuperarContrasenaScreen(),
                                        ),
                                      );
                                    },
                                    style: TextButton.styleFrom(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 8,
                                        vertical: 4,
                                      ),
                                      minimumSize: Size.zero,
                                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                    ),
                                    child: Text(
                                      '¿Olvidaste tu contraseña?',
                                      style: TextStyle(
                                        color: AppTheme.primaryRed,
                                        fontSize: 14,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ),
                                ),
                                    const SizedBox(height: 24),
                                
                                // Botón Iniciar Sesión
                                Container(
                                  decoration: BoxDecoration(
                                    borderRadius: BorderRadius.circular(20),
                                    boxShadow: [
                                      BoxShadow(
                                        color: AppTheme.primaryRed.withOpacity(0.4),
                                        blurRadius: 25,
                                        offset: const Offset(0, 12),
                                      ),
                                      BoxShadow(
                                        color: AppTheme.primaryRed.withOpacity(0.2),
                                        blurRadius: 40,
                                        offset: const Offset(0, 20),
                                      ),
                                    ],
                                  ),
                                    child: CustomButton(
                                    text: 'Iniciar Sesión',
                                    icon: Icons.login_rounded, // Icono representativo de iniciar sesión
                                    onPressed: _isLoading ? null : _login,
                                    isLoading: _isLoading,
                                    height: 60,
                                  ),
                                ),

                                const SizedBox(height: 8),
                              ],
                            ),
                          ),
                        ),
                      ),
                      
                          SizedBox(height: screenHeight * 0.02),
                    ],
                  ),
                ),
              ),
            ),
          ],
            ),
          ),
        ),
      ),
    );
  }
}