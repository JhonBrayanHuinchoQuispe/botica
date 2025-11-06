class ApiConstants {
  // URLs de la API - Conectando con Laravel Backend
  static const String baseUrl = 'http://10.233.173.37:8000/api';
  static const String storageBaseUrl = 'http://10.233.173.37:8000'; // Para archivos de storage
  static const String mobileBaseUrl = '$baseUrl/mobile';
  
  // Endpoints de autenticación
  static const String loginUrl = '$mobileBaseUrl/login';
  static const String logoutUrl = '$mobileBaseUrl/logout';
  static const String meUrl = '$mobileBaseUrl/me';
  static const String changePasswordUrl = '$mobileBaseUrl/change-password';
  static const String verifyTokenUrl = '$mobileBaseUrl/verify-token';
  static const String updateProfileUrl = '$mobileBaseUrl/update-profile';
  
  // Endpoints de recuperación de contraseña
  static const String forgotPasswordUrl = '$mobileBaseUrl/forgot-password';
  static const String verifyResetCodeUrl = '$mobileBaseUrl/verify-reset-code';
  static const String resetPasswordUrl = '$mobileBaseUrl/reset-password';
  static const String resendResetCodeUrl = '$mobileBaseUrl/resend-reset-code';
  
  // Endpoints de productos
  static const String productsUrl = '$mobileBaseUrl/products';
  static const String categoriesUrl = '$mobileBaseUrl/categories';
  static const String stockUrl = '$mobileBaseUrl/stock';
  

  
  // Headers por defecto
  static const Map<String, String> headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };
  
  // Headers para multipart
  static const Map<String, String> multipartHeaders = {
    'Accept': 'application/json',
  };
  
  // Configuraciones de tiempo
  static const int timeoutSeconds = 30;
  static const int maxRetries = 3;
}