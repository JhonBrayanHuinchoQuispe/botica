import '../models/product.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class ProductService {
  // Reemplaza esta URL con la de tu API
  static const String _baseUrl = 'http://10.0.2.2:3000/api';

  // Buscar producto por código de barras
  static Future<Product?> findByBarcode(String barcode) async {
    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/products/barcode/$barcode'),
        headers: {'Content-Type': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        print('Respuesta del servidor: $data'); // Para depurar
        return Product.fromJson(data);
      } else {
        print('Error ${response.statusCode}: ${response.body}'); // Para depurar
      }
      return null;
    } catch (e) {
      print('Error buscando producto: $e');
      return null;
    }
  }

  // Guardar nuevo producto
  static Future<void> saveProduct(Product product) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/products'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode(product.toJson()),
      );

      if (response.statusCode != 201) {
        throw Exception('Error al guardar el producto: ${response.body}');
      }
    } catch (e) {
      throw Exception('Error de conexión: $e');
    }
  }

  // Actualizar producto existente
  static Future<void> updateProduct(String id, Product product) async {
    try {
      final response = await http.put(
        Uri.parse('$_baseUrl/products/$id'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode(product.toJson()),
      );

      if (response.statusCode != 200) {
        throw Exception('Error al actualizar el producto: ${response.body}');
      }
    } catch (e) {
      throw Exception('Error de conexión: $e');
    }
  }

  // Registrar entrada de stock
  static Future<void> registerStockEntry({
    required String productId,
    required int quantity,
    required String batchNumber,
    required DateTime expiryDate,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/products/$productId/stock/entry'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'quantity': quantity,
          'batchNumber': batchNumber,
          'expiryDate': expiryDate.toIso8601String(),
        }),
      );

      if (response.statusCode != 200) {
        throw Exception('Error al registrar entrada: ${response.body}');
      }
    } catch (e) {
      throw Exception('Error de conexión: $e');
    }
  }
}