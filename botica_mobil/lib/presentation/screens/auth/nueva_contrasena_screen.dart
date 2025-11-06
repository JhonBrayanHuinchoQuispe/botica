import 'package:flutter/material.dart';
import '../../../core/config/theme.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/custom_button.dart';
import '../../../core/utils/validators.dart';
import '../../../data/services/password_recovery_service.dart';
import '../../../presentation/screens/auth/login_screen.dart';

class NuevaContrasenaScreen extends StatefulWidget {
  final String email;
  final String code;

  const NuevaContrasenaScreen({
    super.key, 
    required this.email,
    required this.code,
  });

  @override
  State<NuevaContrasenaScreen> createState() => _NuevaContrasenaScreenState();
}

class _NuevaContrasenaScreenState extends State<NuevaContrasenaScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nuevaContrasenaController = TextEditingController();
  final _confirmarContrasenaController = TextEditingController();
  bool _isLoading = false;
  bool _obscureNuevaContrasena = true;
  bool _obscureConfirmarContrasena = true;

  @override
  void dispose() {
    _nuevaContrasenaController.dispose();
    _confirmarContrasenaController.dispose();
    super.dispose();
  }

  void _cambiarContrasena() async {
    if (_formKey.currentState!.validate()) {
      if (_nuevaContrasenaController.text != _confirmarContrasenaController.text) {
        _mostrarErrorDialog('Las contraseñas no coinciden');
        return;
      }

      setState(() {
        _isLoading = true;
      });

      try {
        await PasswordRecoveryService.resetPassword(
           widget.email,
           widget.code,
           _nuevaContrasenaController.text,
         );
        
        _mostrarExitoDialog();
      } catch (e) {
        _mostrarErrorDialog('Error al cambiar la contraseña. Intenta nuevamente.');
      } finally {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  void _mostrarDialogoError(String mensaje) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Error'),
          content: Text(mensaje),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('OK'),
            ),
          ],
        );
      },
    );
  }

  void _mostrarErrorDialog(String mensaje) {
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
                  'Error',
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
                  mensaje,
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

  void _mostrarExitoDialog() {
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
                // Ícono de éxito
                Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    color: AppTheme.primaryRed.withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    Icons.check_circle_outline,
                    color: AppTheme.primaryRed,
                    size: 50,
                  ),
                ),
                const SizedBox(height: 20),
                
                // Título
                Text(
                  'Contraseña Actualizada',
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
                  'Tu contraseña ha sido actualizada exitosamente.',
                  style: TextStyle(
                    fontSize: 16,
                    color: AppTheme.mediumGray,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 24),
                
                // Botón de continuar
                ElevatedButton(
                  onPressed: () {
                    // Navegar a la pantalla de login
                    Navigator.pushAndRemoveUntil(
                      context,
                      MaterialPageRoute(builder: (context) => const LoginScreen()),
                      (Route<dynamic> route) => false,
                    );
                  },
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
                    'Continuar',
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

  @override
  Widget build(BuildContext context) {
    final screenHeight = MediaQuery.of(context).size.height;
    final screenWidth = MediaQuery.of(context).size.width;
    final keyboardHeight = MediaQuery.of(context).viewInsets.bottom;

    return GestureDetector(
      onTap: () => FocusScope.of(context).unfocus(),
      child: Scaffold(
        resizeToAvoidBottomInset: true,
        body: SingleChildScrollView(
          child: Container(
            width: double.infinity,
            height: screenHeight,
            child: Stack(
              children: [
                // Fondo rojo
                Container(
                  width: double.infinity,
                  height: screenHeight * 0.75,
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
                          SizedBox(height: screenHeight * 0.05),
                          
                          // Logo y título superior
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
                          
                          // Tarjeta de nueva contraseña flotante
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
                                      'Nueva Contraseña',
                                      style: TextStyle(
                                        fontSize: 18,
                                        color: AppTheme.darkGray,
                                        fontWeight: FontWeight.w600,
                                      ),
                                      textAlign: TextAlign.center,
                                    ),
                                    const SizedBox(height: 16),
                                    
                                    Text(
                                      'Ingresa tu nueva contraseña para ${widget.email}',
                                      style: TextStyle(
                                        fontSize: 14,
                                        color: AppTheme.mediumGray,
                                        fontWeight: FontWeight.w400,
                                      ),
                                      textAlign: TextAlign.center,
                                    ),
                                    const SizedBox(height: 24),
                                    
                                    // Campo Nueva Contraseña
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
                                        controller: _nuevaContrasenaController,
                                        label: 'Nueva Contraseña',
                                        prefixIcon: Icons.lock_outline,
                                        obscureText: _obscureNuevaContrasena,
                                        validator: Validators.password,
                                        suffixIcon: IconButton(
                                          icon: Icon(
                                            _obscureNuevaContrasena 
                                              ? Icons.visibility_off 
                                              : Icons.visibility,
                                            color: AppTheme.mediumGray,
                                          ),
                                          onPressed: () {
                                            setState(() {
                                              _obscureNuevaContrasena = !_obscureNuevaContrasena;
                                            });
                                          },
                                        ),
                                      ),
                                    ),
                                    const SizedBox(height: 16),
                                    
                                    // Campo Confirmar Contraseña
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
                                        controller: _confirmarContrasenaController,
                                        label: 'Confirmar Contraseña',
                                        prefixIcon: Icons.lock_outline,
                                        obscureText: _obscureConfirmarContrasena,
                                        validator: Validators.password,
                                        suffixIcon: IconButton(
                                          icon: Icon(
                                            _obscureConfirmarContrasena 
                                              ? Icons.visibility_off 
                                              : Icons.visibility,
                                            color: AppTheme.mediumGray,
                                          ),
                                          onPressed: () {
                                            setState(() {
                                              _obscureConfirmarContrasena = !_obscureConfirmarContrasena;
                                            });
                                          },
                                        ),
                                      ),
                                    ),
                                    const SizedBox(height: 24),
                                    
                                    // Botón Cambiar Contraseña
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
                                        text: 'Cambiar Contraseña',
                                        icon: Icons.lock_reset_rounded,
                                        onPressed: _isLoading ? null : _cambiarContrasena,
                                        isLoading: _isLoading,
                                        height: 60,
                                      ),
                                    ),
                                    const SizedBox(height: 16),
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