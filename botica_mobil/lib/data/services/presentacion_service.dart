import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/presentacion.dart';
import '../../core/config/api_config.dart';

class PresentacionService {
  static const String _baseUrl = ApiConfig.baseUrl;

  Future<List<Presentacion>> getPresentaciones() async {
    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/api/presentaciones'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);
        final List<dynamic> presentacionesJson = data['data'] ?? data['presentaciones'] ?? [];
        
        return presentacionesJson.map((json) => Presentacion.fromJson(json)).toList();
      } else {
        throw Exception('Error al cargar presentaciones: ${response.statusCode}');
      }
    } catch (e) {
      print('Error en getPresentaciones: $e');
      // Retornar presentaciones por defecto en caso de error
      return [
        Presentacion(id: '1', nombre: 'Tabletas'),
        Presentacion(id: '2', nombre: 'Cápsulas'),
        Presentacion(id: '3', nombre: 'Jarabe'),
        Presentacion(id: '4', nombre: 'Suspensión'),
        Presentacion(id: '5', nombre: 'Crema'),
        Presentacion(id: '6', nombre: 'Ungüento'),
        Presentacion(id: '7', nombre: 'Gotas'),
        Presentacion(id: '8', nombre: 'Inyectable'),
        Presentacion(id: '9', nombre: 'Polvo'),
        Presentacion(id: '10', nombre: 'Gel'),
        Presentacion(id: '11', nombre: 'Spray'),
        Presentacion(id: '12', nombre: 'Parches'),
      ];
    }
  }
}