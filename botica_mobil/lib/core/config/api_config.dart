class ApiConfig {
  // static const String baseUrl = 'http://10.0.2.2:8000'; // Para emulador Android
  // static const String baseUrl = 'http://localhost:8000'; // Para web/desktop
  // Modo dispositivo físico con ADB reverse:
  // Ejecuta: adb reverse tcp:8000 tcp:8000 y usa 127.0.0.1
  static const String baseUrl = 'http://127.0.0.1:8000';
  
  static const Duration timeout = Duration(seconds: 30);
  
  // Endpoints
  static const String categorias = '/api/categorias';
  static const String presentaciones = '/api/presentaciones';
  static const String proveedores = '/api/proveedores';
  static const String productos = '/api/productos';
  static const String dashboard = '/api/mobile/dashboard';
}