class ApiConstants {
  // URLs de la API - Conectando con Laravel Backend
  // Usamos ADB reverse para desarrollo: adb reverse tcp:8000 tcp:8000
  static const String baseUrl = 'http://127.0.0.1:8000/api';
  static const String storageBaseUrl = 'http://127.0.0.1:8000'; // Para archivos de storage
  static const String mobileBaseUrl = '$baseUrl/mobile';
  
  // Endpoints de autenticación
  static const String loginUrl = '$mobileBaseUrl/login';
  static const String logoutUrl = '$mobileBaseUrl/logout';
  static const String meUrl = '$mobileBaseUrl/me';
  static const String changePasswordUrl = '$mobileBaseUrl/change-password';
  static const String verifyTokenUrl = '$mobileBaseUrl/verify-token';
  
  // Endpoints de recuperación de contraseña
  static const String forgotPasswordUrl = '$mobileBaseUrl/forgot-password';
  static const String verifyResetCodeUrl = '$mobileBaseUrl/verify-reset-code';
  static const String resetPasswordUrl = '$mobileBaseUrl/reset-password';
  static const String resendResetCodeUrl = '$mobileBaseUrl/resend-reset-code';
  
  // Endpoints de productos (usando las rutas existentes de Laravel)
  static const String productsUrl = '$baseUrl/productos';
  static const String categoriesUrl = '$baseUrl/inventario/categoria/api';
  static const String presentationsUrl = '$baseUrl/inventario/presentacion/api';
  
  // Endpoints de ubicaciones
  static const String locationsUrl = '$baseUrl/ubicaciones';
  static const String stockUrl = '$baseUrl/stock';
  
  // Timeout
  static const Duration timeout = Duration(seconds: 60);
  
  // Headers
  static const Map<String, String> headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };
}

class StorageKeys {
  static const String token = 'auth_token';
  static const String userData = 'user_data';
  static const String rememberMe = 'remember_me';
  static const String userId = 'user_id';
  static const String userEmail = 'user_email';
  static const String userName = 'user_name';
  static const String userRoles = 'user_roles';
  static const String lastLoginTime = 'last_login_time';
}

class AppConstants {
  static const String appName = 'Botica Móvil';
  static const String appVersion = '1.0.0';
  
  // Configuraciones de la app
  static const int maxLoginAttempts = 5;
  static const Duration lockoutDuration = Duration(minutes: 30);
  
  // Configuraciones de caché
  static const Duration cacheExpiration = Duration(hours: 24);
  
  // Configuraciones de red
  static const int maxRetries = 3;
  static const Duration retryDelay = Duration(seconds: 2);
}