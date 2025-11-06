import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../auth/login_screen.dart';
import '../../../core/config/theme.dart';
import '../../../core/constants.dart';
import '../../../data/services/auth_service.dart';
import '../../../data/models/user.dart';
import '../../../core/notifiers/user_notifier.dart';
import '../../../core/notifiers/theme_notifier.dart';
import '../settings/settings_screens.dart';
import '../inventory/inventory_screens.dart';
import '../ai_alerts/ai_alerts_screen.dart';

class NotificationsModal extends StatelessWidget {
  const NotificationsModal({Key? key}) : super(key: key);

  void _navigateBasedOnNotification(BuildContext context, String type, String product) {
    switch (type) {
      case 'vencimiento':
      case 'stock_critico':
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => const InventoryScreen(),
          ),
        );
        break;
      case 'baja_rotacion':
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => const AIAlertsScreen(),
          ),
        );
        break;
      default:
        // Si no hay navegación específica, cerrar el modal
        Navigator.pop(context);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(20),
          topRight: Radius.circular(20),
        ),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Notificaciones',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close, color: Colors.grey),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
          ),
          _buildOtherNotifications(context),
        ],
      ),
    );
  }

  Widget _buildOtherNotifications(BuildContext context) {
    return Column(
      children: [
        _buildSingleNotification(
          context: context,
          icon: Icons.calendar_today_rounded,
          color: Colors.pink[100]!,
          title: 'Paracetamol',
          subtitle: 'Próximo a vencer en 30 días',
          time: '3h',
          type: 'vencimiento',
        ),
        _buildSingleNotification(
          context: context,
          icon: Icons.trending_down_rounded,
          color: Colors.orange[100]!,
          title: 'Ibuprofeno',
          subtitle: 'Baja rotación en últimos 30 días',
          time: '4h',
          type: 'baja_rotacion',
        ),
        _buildSingleNotification(
          context: context,
          icon: Icons.warning_amber_rounded,
          color: Colors.blue[100]!,
          title: 'Vitamina C',
          subtitle: 'Stock crítico - Reponer inmediatamente',
          time: '5h',
          type: 'stock_critico',
        ),
        _buildSingleNotification(
          context: context,
          icon: Icons.inventory_2_outlined,
          color: Colors.red[100]!,
          title: 'Aspirina',
          subtitle: 'Stock muy bajo - Menos de 10 unidades',
          time: '6h',
          type: 'stock_critico',
        ),
        _buildSingleNotification(
          context: context,
          icon: Icons.local_pharmacy_outlined,
          color: Colors.green[100]!,
          title: 'Amoxicilina',
          subtitle: 'Stock bajo - Menos de 20 unidades',
          time: '7h',
          type: 'stock_critico',
        ),
      ],
    );
  }

  Widget _buildSingleNotification({
    required BuildContext context,
    required IconData icon,
    required Color color,
    required String title,
    required String subtitle,
    required String time,
    required String type,
  }) {
    return ListTile(
      onTap: () => _navigateBasedOnNotification(context, type, title),
      leading: Container(
        width: 40,
        height: 40,
        decoration: BoxDecoration(
          color: color,
          shape: BoxShape.circle,
        ),
        child: Center(
          child: Icon(icon, color: color.withOpacity(0.7), size: 24),
        ),
      ),
      title: Text(
        title,
        style: const TextStyle(
          fontSize: 14,
          fontWeight: FontWeight.bold,
        ),
      ),
      subtitle: Text(
        subtitle,
        style: const TextStyle(
          fontSize: 12,
          color: Colors.grey,
        ),
      ),
      trailing: Text(
        time,
        style: const TextStyle(
          fontSize: 12,
          color: Colors.grey,
        ),
      ),
    );
  }
}

class BarraCompartida extends StatefulWidget implements PreferredSizeWidget {
  final String titulo;
  final String? nombreUsuario; // Ahora es opcional
  
  const BarraCompartida({
    Key? key,
    required this.titulo,
    this.nombreUsuario,
  }) : super(key: key);

  @override
  Size get preferredSize => const Size.fromHeight(70);

  @override
  State<BarraCompartida> createState() => _BarraCompartidaState();
}

class _BarraCompartidaState extends State<BarraCompartida> {
  User? _currentUser;
  final AuthService _authService = AuthService();

  @override
  void initState() {
    super.initState();
    _loadUserData();
    // Escuchar cambios en el usuario
    userNotifier.addListener(_onUserChanged);
  }

  @override
  void dispose() {
    userNotifier.removeListener(_onUserChanged);
    super.dispose();
  }

  void _onUserChanged() {
    if (mounted) {
      setState(() {
        _currentUser = userNotifier.currentUser;
      });
      print('🔄 Header actualizado con nuevo usuario: ${_currentUser?.name}');
    }
  }

  Future<void> _loadUserData() async {
    try {
      print('🔄 Cargando datos del usuario...');
      
      // Primero intentar obtener datos locales
      final user = await _authService.getCurrentUser();
      print('👤 Usuario local obtenido: ${user?.name ?? 'null'}');
      if (user != null) {
        setState(() {
          _currentUser = user;
        });
        // Actualizar el notificador global
        userNotifier.updateUser(user);
        print('✅ Estado actualizado con usuario local: ${_currentUser?.name}');
      }

      // Luego refrescar desde el servidor
      print('🌐 Intentando refrescar datos desde servidor...');
      final refreshedUser = await _authService.refreshUserData();
      print('🔄 Usuario del servidor: ${refreshedUser?.name ?? 'null'}');
      if (refreshedUser != null && mounted) {
        setState(() {
          _currentUser = refreshedUser;
        });
        // Actualizar el notificador global
        userNotifier.updateUser(refreshedUser);
        print('✅ Estado actualizado con usuario del servidor: ${_currentUser?.name}');
      }
    } catch (e) {
      print('❌ Error loading user data: $e');
    }
  }

  String get _displayName {
    print('🏷️ Obteniendo nombre para mostrar...');
    print('👤 _currentUser: ${_currentUser?.name}');
    print('📝 widget.nombreUsuario: ${widget.nombreUsuario}');
    
    if (_currentUser != null) {
      // Obtener solo el primer nombre
      final fullName = _currentUser!.displayName;
      final firstName = fullName.split(' ').first;
      print('✅ Nombre completo: $fullName, Primer nombre: $firstName');
      return firstName;
    }
    final fallbackName = widget.nombreUsuario ?? 'Usuario';
    print('⚠️ Usando nombre fallback: $fallbackName');
    return fallbackName;
  }

  String? get _avatarUrl {
    print('🖼️ Obteniendo URL del avatar...');
    print('👤 _currentUser: ${_currentUser?.name}');
    print('📸 avatar field: ${_currentUser?.avatar}');
    
    if (_currentUser?.avatar != null && _currentUser!.avatar!.isNotEmpty) {
      String avatarUrl;
      // Si la URL del avatar es relativa, agregar la base URL
      if (_currentUser!.avatar!.startsWith('http')) {
        avatarUrl = _currentUser!.avatar!;
      } else {
        avatarUrl = '${ApiConstants.storageBaseUrl}/storage/${_currentUser!.avatar}';
      }
      print('🔗 URL final del avatar: $avatarUrl');
      return avatarUrl;
    }
    print('⚠️ No hay avatar disponible');
    return null;
  }

  Widget _buildUserAvatar() {
    final avatarUrl = _avatarUrl;
    print('🎨 Construyendo avatar widget...');
    print('🔗 Avatar URL: $avatarUrl');
    
    if (avatarUrl != null && avatarUrl.isNotEmpty) {
      print('✅ Usando NetworkImage para avatar');
      return CircleAvatar(
        radius: 16,
        backgroundColor: Colors.white24,
        child: ClipOval(
          child: Image.network(
            avatarUrl,
            width: 32,
            height: 32,
            fit: BoxFit.cover,
            loadingBuilder: (context, child, loadingProgress) {
              if (loadingProgress == null) {
                print('✅ Avatar cargado exitosamente');
                return child;
              }
              print('⏳ Cargando avatar...');
              return Container(
                width: 32,
                height: 32,
                color: Colors.white24,
                child: const Center(
                  child: SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                    ),
                  ),
                ),
              );
            },
            errorBuilder: (context, error, stackTrace) {
              print('❌ Error cargando avatar: $error');
              print('🔗 URL que falló: $avatarUrl');
              // Mostrar iniciales en caso de error
              return Container(
                width: 32,
                height: 32,
                decoration: const BoxDecoration(
                  color: Colors.white24,
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Text(
                    _currentUser?.displayInitials ?? 'U',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              );
            },
          ),
        ),
      );
    } else {
      print('🔤 Usando avatar con iniciales');
      // Avatar por defecto con iniciales
      return CircleAvatar(
        radius: 16,
        backgroundColor: Colors.white24,
        child: Text(
          _currentUser?.displayInitials ?? 'U',
          style: const TextStyle(
            color: Colors.white,
            fontSize: 12,
            fontWeight: FontWeight.bold,
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return AppBar(
      toolbarHeight: 70,
      flexibleSpace: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [AppTheme.primaryRed, AppTheme.darkRed],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
      ),
      automaticallyImplyLeading: false,
      title: Row(
        children: [
          Container(
            height: 45,
            width: 45,
            padding: const EdgeInsets.all(1),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Image.asset(
              'assets/images/logo.png',
              fit: BoxFit.contain,
            ),
          ),
          const SizedBox(width: 12),
          Text(
            widget.titulo,
            style: const TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.w600,
              color: Colors.white,
            ),
          ),
          const Spacer(),
          Stack(
            children: [
              IconButton(
                icon: const Icon(
                  Icons.notifications_outlined,
                  color: Colors.white,
                  size: 28,
                ),
                onPressed: () {
                  showModalBottomSheet(
                    context: context,
                    shape: const RoundedRectangleBorder(
                      borderRadius: BorderRadius.only(
                        topLeft: Radius.circular(20),
                        topRight: Radius.circular(20),
                      ),
                    ),
                    builder: (context) => const NotificationsModal(),
                  );
                },
              ),
              Positioned(
                right: 8,
                top: 8,
                child: Container(
                  height: 16,
                  width: 16,
                  decoration: BoxDecoration(
                    color: const Color(0xFFFF9800),
                    shape: BoxShape.circle,
                    border: Border.all(color: Colors.white, width: 2),
                  ),
                  child: const Center(
                    child: Text(
                      '2',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
          PopupMenuButton<String>(
            offset: const Offset(0, 50),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            itemBuilder: (context) => [
              PopupMenuItem<String>(
                value: 'modo_oscuro',
                child: Consumer<ThemeNotifier>(
                  builder: (context, themeNotifier, child) {
                    return Row(
                      children: [
                        Icon(
                          themeNotifier.isDarkMode 
                            ? Icons.light_mode_outlined 
                            : Icons.dark_mode_outlined, 
                          color: Colors.grey, 
                          size: 20
                        ),
                        const SizedBox(width: 8),
                        Text(themeNotifier.isDarkMode ? 'Modo Claro' : 'Modo Oscuro'),
                      ],
                    );
                  },
                ),
              ),
              const PopupMenuDivider(),
              PopupMenuItem<String>(
                value: 'cerrar_sesion',
                child: Row(
                  children: const [
                    Icon(Icons.logout_outlined, color: Colors.grey, size: 20),
                    SizedBox(width: 8),
                    Text('Cerrar Sesión'),
                  ],
                ),
              ),
            ],
            onSelected: (value) async {
              if (value == 'modo_oscuro') {
                // Cambiar el modo oscuro
                final themeNotifier = Provider.of<ThemeNotifier>(context, listen: false);
                themeNotifier.toggleTheme();
              } else if (value == 'cerrar_sesion') {
                // Mostrar diálogo de confirmación
                final shouldLogout = await showDialog<bool>(
                  context: context,
                  builder: (context) => AlertDialog(
                    title: const Text('Cerrar Sesión'),
                    content: const Text('¿Estás seguro de que quieres cerrar sesión?'),
                    actions: [
                      TextButton(
                        onPressed: () => Navigator.of(context).pop(false),
                        child: const Text('Cancelar'),
                      ),
                      TextButton(
                        onPressed: () => Navigator.of(context).pop(true),
                        style: TextButton.styleFrom(
                          foregroundColor: AppTheme.primaryRed,
                        ),
                        child: const Text('Cerrar Sesión'),
                      ),
                    ],
                  ),
                );

                if (shouldLogout == true) {
                  try {
                    // Usar el servicio de autenticación para logout
                    final authService = AuthService();
                    await authService.logout();
                    
                    // Navegar al login
                    Navigator.pushAndRemoveUntil(
                      context,
                      MaterialPageRoute(builder: (context) => const LoginScreen()),
                      (route) => false,
                    );
                  } catch (e) {
                    // En caso de error, mostrar mensaje y navegar al login de todas formas
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Error al cerrar sesión, pero se ha cerrado localmente'),
                        backgroundColor: Colors.orange,
                      ),
                    );
                    Navigator.pushAndRemoveUntil(
                      context,
                      MaterialPageRoute(builder: (context) => const LoginScreen()),
                      (route) => false,
                    );
                  }
                }
              }
            },
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              margin: const EdgeInsets.only(left: 8),
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.2),
                borderRadius: BorderRadius.circular(30),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  _buildUserAvatar(),
                  const SizedBox(width: 8),
                  Text(
                    _displayName,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}