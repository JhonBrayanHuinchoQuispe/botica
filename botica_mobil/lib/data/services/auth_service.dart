import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user.dart';
import 'api_service.dart';
import '../../core/constants.dart';

class LoginResult {
  final bool success;
  final String message;
  final User? user;
  final String? token;

  LoginResult({
    required this.success,
    required this.message,
    this.user,
    this.token,
  });
}

class AuthService {
  static final AuthService _instance = AuthService._internal();
  factory AuthService() => _instance;
  AuthService._internal();

  final ApiService _apiService = ApiService();

  // Login con el backend Laravel
  Future<LoginResult> login(String email, String password) async {
    try {
      final response = await _apiService.post<Map<String, dynamic>>(
        '/mobile/login',
        {
          'email': email,
          'password': password,
        },
      );

      if (response.success && response.data != null) {
        final userData = response.data!['user'];
        final token = response.data!['token'];

        // Crear objeto User
        final user = User.fromJson(userData);

        // Guardar datos en SharedPreferences
        print('DEBUG: Saving user data to SharedPreferences');
        await _saveUserData(user, token);
        print('DEBUG: User data saved successfully');

        return LoginResult(
          success: true,
          message: response.message,
          user: user,
          token: token,
        );
      } else {
        return LoginResult(
          success: false,
          message: response.message,
        );
      }
    } catch (e) {
      return LoginResult(
        success: false,
        message: 'Error de conexión: $e',
      );
    }
  }

  // Logout
  Future<bool> logout() async {
    try {
      // Intentar hacer logout en el servidor
      final response = await _apiService.post<void>(
        '/mobile/logout',
        {},
        requireAuth: true,
      );

      // Limpiar datos locales independientemente del resultado del servidor
      await _clearUserData();

      return response.success;
    } catch (e) {
      // Limpiar datos locales aunque falle la petición al servidor
      await _clearUserData();
      return false;
    }
  }

  // Verificar si el usuario está logueado
  Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(StorageKeys.token);
    
    if (token == null) return false;

    // Verificar token con el servidor
    try {
      final response = await _apiService.post<Map<String, dynamic>>(
        '/mobile/verify-token',
        {},
        requireAuth: true,
      );

      if (!response.success) {
        await _clearUserData();
        return false;
      }

      return true;
    } catch (e) {
      return false;
    }
  }

  // Obtener usuario actual
  Future<User?> getCurrentUser() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final userData = prefs.getString(StorageKeys.userData);
      
      print('DEBUG: Getting current user from SharedPreferences');
      print('DEBUG: userData from prefs: $userData');
      
      if (userData != null) {
        final userMap = json.decode(userData);
        print('DEBUG: Parsed user data: $userMap');
        final user = User.fromJson(userMap);
        print('DEBUG: Created User object: ${user.name}, ${user.email}');
        return user;
      }
      
      print('DEBUG: No user data found in SharedPreferences');
      return null;
    } catch (e) {
      print('Error getting current user: $e');
      return null;
    }
  }

  // Obtener información actualizada del usuario desde el servidor
  Future<User?> refreshUserData() async {
    try {
      final response = await _apiService.get<Map<String, dynamic>>(
        '/mobile/me',
        requireAuth: true,
      );

      if (response.success && response.data != null) {
        final user = User.fromJson(response.data!['user']);
        
        // Actualizar datos locales
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(StorageKeys.userData, json.encode(user.toJson()));
        
        return user;
      }
    } catch (e) {
      print('Error refreshing user data: $e');
    }
    
    return null;
  }

  // Cambiar contraseña
  Future<ApiResponse<void>> changePassword(String currentPassword, String newPassword, String confirmPassword) async {
    return await _apiService.post<void>(
      '/mobile/change-password',
      {
        'current_password': currentPassword,
        'new_password': newPassword,
        'new_password_confirmation': confirmPassword,
      },
      requireAuth: true,
    );
  }

  // Actualizar perfil
  Future<ApiResponse<Map<String, dynamic>>> updateProfile({
    String? name,
    String? nombres,
    String? apellidos,
    String? email,
    String? avatarPath,
  }) async {
    try {
      Map<String, dynamic> data = {};
      
      if (name != null) data['name'] = name;
      if (nombres != null) data['nombres'] = nombres;
      if (apellidos != null) data['apellidos'] = apellidos;
      if (email != null) data['email'] = email;
      
      final response = await _apiService.postMultipart<Map<String, dynamic>>(
        '/mobile/update-profile',
        data: data,
        files: avatarPath != null ? {'avatar': avatarPath} : null,
        requireAuth: true,
      );

      if (response.success && response.data != null) {
        // Actualizar datos locales con la información del usuario actualizada
        final user = User.fromJson(response.data!['user']);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(StorageKeys.userData, json.encode(user.toJson()));
        await prefs.setString(StorageKeys.userName, user.displayName);
      }

      return response;
    } catch (e) {
      return ApiResponse<Map<String, dynamic>>(
        success: false,
        message: 'Error al actualizar perfil: $e',
        data: null,
        statusCode: 0,
      );
    }
  }

  // Obtener token actual
  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(StorageKeys.token);
  }

  // Verificar si el usuario tiene un rol específico
  Future<bool> hasRole(String role) async {
    final user = await getCurrentUser();
    return user?.hasRole(role) ?? false;
  }

  // Verificar si el usuario tiene un permiso específico
  Future<bool> hasPermission(String permission) async {
    final user = await getCurrentUser();
    return user?.hasPermission(permission) ?? false;
  }

  // Verificar si el usuario es administrador
  Future<bool> isAdmin() async {
    final user = await getCurrentUser();
    return user?.isAdmin ?? false;
  }

  // Guardar datos del usuario
  Future<void> _saveUserData(User user, String token) async {
    final prefs = await SharedPreferences.getInstance();
    
    await prefs.setString(StorageKeys.token, token);
    await prefs.setString(StorageKeys.userData, json.encode(user.toJson()));
    await prefs.setInt(StorageKeys.userId, user.id);
    await prefs.setString(StorageKeys.userEmail, user.email);
    await prefs.setString(StorageKeys.userName, user.displayName);
    await prefs.setStringList(StorageKeys.userRoles, user.roles);
    await prefs.setString(StorageKeys.lastLoginTime, DateTime.now().toIso8601String());
  }

  // Limpiar datos del usuario
  Future<void> _clearUserData() async {
    final prefs = await SharedPreferences.getInstance();
    
    await prefs.remove(StorageKeys.token);
    await prefs.remove(StorageKeys.userData);
    await prefs.remove(StorageKeys.userId);
    await prefs.remove(StorageKeys.userEmail);
    await prefs.remove(StorageKeys.userName);
    await prefs.remove(StorageKeys.userRoles);
    await prefs.remove(StorageKeys.lastLoginTime);
  }

  // Verificar conectividad con el servidor
  Future<bool> checkServerConnection() async {
    return await _apiService.checkConnectivity();
  }
}