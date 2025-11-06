import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/categoria.dart';
import '../../core/config/api_config.dart';

class CategoriaService {
  static const String _baseUrl = ApiConfig.baseUrl;

  Future<List<Categoria>> getCategorias() async {
    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/api/categorias'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);
        final List<dynamic> categoriasJson = data['data'] ?? data['categorias'] ?? [];
        
        return categoriasJson.map((json) => Categoria.fromJson(json)).toList();
      } else {
        throw Exception('Error al cargar categorías: ${response.statusCode}');
      }
    } catch (e) {
      print('Error en getCategorias: $e');
      // Retornar categorías por defecto en caso de error
      return [
        Categoria(id: '1', nombre: 'Analgésicos'),
        Categoria(id: '2', nombre: 'Antibióticos'),
        Categoria(id: '3', nombre: 'Antiinflamatorios'),
        Categoria(id: '4', nombre: 'Vitaminas'),
        Categoria(id: '5', nombre: 'Suplementos'),
        Categoria(id: '6', nombre: 'Cuidado Personal'),
        Categoria(id: '7', nombre: 'Primeros Auxilios'),
        Categoria(id: '8', nombre: 'Medicamentos Genéricos'),
        Categoria(id: '9', nombre: 'Otros'),
      ];
    }
  }
}