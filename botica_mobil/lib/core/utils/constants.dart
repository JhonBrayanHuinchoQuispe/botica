class AppConstants {
  // URLs de API
  static const String baseUrl = 'http://10.233.173.37:8000/api';
  static const String loginEndpoint = '/mobile/login';
  static const String productsEndpoint = '/mobile/products';
  
  // Claves de SharedPreferences
  static const String tokenKey = 'auth_token';
  static const String userKey = 'user_data';
  static const String rememberMeKey = 'remember_me';
  
  // Configuraciones
  static const int timeoutDuration = 30; // segundos
}