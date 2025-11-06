import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user.dart';
import '../../core/constants.dart';

class ApiResponse<T> {
  final bool success;
  final String message;
  final T? data;
  final Map<String, dynamic>? errors;
  final int statusCode;

  ApiResponse({
    required this.success,
    required this.message,
    this.data,
    this.errors,
    required this.statusCode,
  });

  factory ApiResponse.fromJson(Map<String, dynamic> json, T? data, int statusCode) {
    return ApiResponse<T>(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: data,
      errors: json['errors'],
      statusCode: statusCode,
    );
  }
}

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  // Headers base para todas las peticiones
  Future<Map<String, String>> _getHeaders({bool includeAuth = false}) async {
    Map<String, String> headers = Map.from(ApiConstants.headers);
    
    if (includeAuth) {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString(StorageKeys.token);
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }
    }
    
    return headers;
  }

  // Manejo de errores HTTP
  ApiResponse<T> _handleError<T>(http.Response response) {
    try {
      final Map<String, dynamic> body = json.decode(response.body);
      return ApiResponse<T>(
        success: false,
        message: body['message'] ?? 'Error desconocido',
        errors: body['errors'],
        statusCode: response.statusCode,
      );
    } catch (e) {
      return ApiResponse<T>(
        success: false,
        message: 'Error de conexión: ${response.statusCode}',
        statusCode: response.statusCode,
      );
    }
  }

  // GET request
  Future<ApiResponse<T>> get<T>(String endpoint, {bool requireAuth = false}) async {
    try {
      final headers = await _getHeaders(includeAuth: requireAuth);
      final url = '${ApiConstants.baseUrl}$endpoint';
      print('🌐 GET -> $url');
      print('📋 Headers: $headers');
      final response = await http.get(
        Uri.parse(url),
        headers: headers,
      ).timeout(ApiConstants.timeout);
      print('📥 GET Status: ${response.statusCode}');
      if (response.statusCode >= 200 && response.statusCode < 300) {
        print('📥 GET Body: ${response.body.substring(0, response.body.length > 300 ? 300 : response.body.length)}');
      } else {
        print('❌ GET Error Body: ${response.body}');
      }

      if (response.statusCode >= 200 && response.statusCode < 300) {
        final Map<String, dynamic> body = json.decode(response.body);
        return ApiResponse<T>(
          success: body['success'] ?? true,
          message: body['message'] ?? 'Éxito',
          data: body['data'],
          statusCode: response.statusCode,
        );
      } else {
        return _handleError<T>(response);
      }
    } on SocketException {
      return ApiResponse<T>(
        success: false,
        message: 'Sin conexión a internet',
        statusCode: 0,
      );
    } on HttpException {
      return ApiResponse<T>(
        success: false,
        message: 'Error de servidor',
        statusCode: 500,
      );
    } catch (e) {
      return ApiResponse<T>(
        success: false,
        message: 'Error inesperado: $e',
        statusCode: 0,
      );
    }
  }

  // POST request
  Future<ApiResponse<T>> post<T>(String endpoint, Map<String, dynamic> data, {bool requireAuth = false}) async {
    try {
      final headers = await _getHeaders(includeAuth: requireAuth);
      final url = '${ApiConstants.baseUrl}$endpoint';
      print('🌐 Intentando conectar a: $url');
      print('📤 Datos enviados: ${json.encode(data)}');
      print('📋 Headers: $headers');
      
      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: json.encode(data),
      ).timeout(ApiConstants.timeout);

      print('📥 Respuesta recibida - Status: ${response.statusCode}');
      print('📥 Respuesta body: ${response.body}');

      if (response.statusCode >= 200 && response.statusCode < 300) {
        final Map<String, dynamic> body = json.decode(response.body);
        return ApiResponse<T>(
          success: body['success'] ?? true,
          message: body['message'] ?? 'Éxito',
          data: body['data'],
          statusCode: response.statusCode,
        );
      } else {
        return _handleError<T>(response);
      }
    } on SocketException catch (e) {
      print('❌ SocketException: $e');
      return ApiResponse<T>(
        success: false,
        message: 'Sin conexión a internet. Verifica tu conexión de red y que el servidor esté accesible desde tu dispositivo.',
        statusCode: 0,
      );
    } on HttpException catch (e) {
      print('❌ HttpException: $e');
      return ApiResponse<T>(
        success: false,
        message: 'Error de servidor: $e',
        statusCode: 500,
      );
    } on FormatException catch (e) {
      print('❌ FormatException: $e');
      return ApiResponse<T>(
        success: false,
        message: 'Error de formato en la respuesta del servidor',
        statusCode: 0,
      );
    } catch (e) {
      print('❌ Error inesperado: $e');
      return ApiResponse<T>(
        success: false,
        message: 'Error inesperado: $e. URL: ${ApiConstants.baseUrl}$endpoint',
        statusCode: 0,
      );
    }
  }

  // PUT request
  Future<ApiResponse<T>> put<T>(String endpoint, Map<String, dynamic> data, {bool requireAuth = true}) async {
    try {
      final headers = await _getHeaders(includeAuth: requireAuth);
      final response = await http.put(
        Uri.parse('${ApiConstants.baseUrl}$endpoint'),
        headers: headers,
        body: json.encode(data),
      ).timeout(ApiConstants.timeout);

      if (response.statusCode >= 200 && response.statusCode < 300) {
        final Map<String, dynamic> body = json.decode(response.body);
        return ApiResponse<T>(
          success: body['success'] ?? true,
          message: body['message'] ?? 'Éxito',
          data: body['data'],
          statusCode: response.statusCode,
        );
      } else {
        return _handleError<T>(response);
      }
    } on SocketException {
      return ApiResponse<T>(
        success: false,
        message: 'Sin conexión a internet',
        statusCode: 0,
      );
    } on HttpException {
      return ApiResponse<T>(
        success: false,
        message: 'Error de servidor',
        statusCode: 500,
      );
    } catch (e) {
      return ApiResponse<T>(
        success: false,
        message: 'Error inesperado: $e',
        statusCode: 0,
      );
    }
  }

  // DELETE request
  Future<ApiResponse<T>> delete<T>(String endpoint, {bool requireAuth = true}) async {
    try {
      final headers = await _getHeaders(includeAuth: requireAuth);
      final response = await http.delete(
        Uri.parse('${ApiConstants.baseUrl}$endpoint'),
        headers: headers,
      ).timeout(ApiConstants.timeout);

      if (response.statusCode >= 200 && response.statusCode < 300) {
        final Map<String, dynamic> body = json.decode(response.body);
        return ApiResponse<T>(
          success: body['success'] ?? true,
          message: body['message'] ?? 'Éxito',
          data: body['data'],
          statusCode: response.statusCode,
        );
      } else {
        return _handleError<T>(response);
      }
    } on SocketException {
      return ApiResponse<T>(
        success: false,
        message: 'Sin conexión a internet',
        statusCode: 0,
      );
    } on HttpException {
      return ApiResponse<T>(
        success: false,
        message: 'Error de servidor',
        statusCode: 500,
      );
    } catch (e) {
      return ApiResponse<T>(
        success: false,
        message: 'Error inesperado: $e',
        statusCode: 0,
      );
    }
  }

  // POST Multipart request (para archivos)
  Future<ApiResponse<T>> postMultipart<T>(
    String endpoint, {
    Map<String, dynamic>? data,
    Map<String, String>? files,
    bool requireAuth = true,
  }) async {
    try {
      final uri = Uri.parse('${ApiConstants.baseUrl}$endpoint');
      final request = http.MultipartRequest('POST', uri);

      // Agregar headers de autenticación
      if (requireAuth) {
        final prefs = await SharedPreferences.getInstance();
        final token = prefs.getString(StorageKeys.token);
        if (token != null) {
          request.headers['Authorization'] = 'Bearer $token';
        }
      }

      // Agregar datos del formulario
      if (data != null) {
        data.forEach((key, value) {
          request.fields[key] = value.toString();
        });
      }

      // Agregar archivos
      if (files != null) {
        for (final entry in files.entries) {
          final file = File(entry.value);
          if (await file.exists()) {
            final multipartFile = await http.MultipartFile.fromPath(
              entry.key,
              entry.value,
            );
            request.files.add(multipartFile);
          }
        }
      }

      final streamedResponse = await request.send().timeout(ApiConstants.timeout);
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode >= 200 && response.statusCode < 300) {
        final Map<String, dynamic> body = json.decode(response.body);
        return ApiResponse<T>(
          success: body['success'] ?? true,
          message: body['message'] ?? 'Éxito',
          data: body['data'],
          statusCode: response.statusCode,
        );
      } else {
        return _handleError<T>(response);
      }
    } on SocketException {
      return ApiResponse<T>(
        success: false,
        message: 'Sin conexión a internet',
        statusCode: 0,
      );
    } on HttpException {
      return ApiResponse<T>(
        success: false,
        message: 'Error de servidor',
        statusCode: 500,
      );
    } catch (e) {
      return ApiResponse<T>(
        success: false,
        message: 'Error inesperado: $e',
        statusCode: 0,
      );
    }
  }

  // Verificar conectividad
  Future<bool> checkConnectivity() async {
    try {
      final response = await http.get(
        Uri.parse('${ApiConstants.baseUrl}/test'),
        headers: await _getHeaders(),
      ).timeout(const Duration(seconds: 5));
      
      return response.statusCode == 200;
    } catch (e) {
      return false;
    }
  }
}