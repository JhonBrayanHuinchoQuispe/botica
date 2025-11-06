import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/product.dart';

class CacheService {
  static const String _productsKey = 'cached_products';
  static const String _lastUpdateKey = 'last_products_update';
  static const String _paginationKey = 'products_pagination_';
  
  // Duración del cache (30 minutos)
  static const Duration _cacheDuration = Duration(minutes: 30);

  // Guardar productos en cache
  Future<void> cacheProducts(List<Product> products) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      
      // Convertir productos a JSON
      final productsJson = products.map((product) => product.toJson()).toList();
      final jsonString = jsonEncode(productsJson);
      
      // Guardar en cache
      await prefs.setString(_productsKey, jsonString);
      await prefs.setInt(_lastUpdateKey, DateTime.now().millisecondsSinceEpoch);
      
      print('✅ Cache guardado: ${products.length} productos');
    } catch (e) {
      print('❌ Error guardando cache: $e');
    }
  }

  // Obtener productos del cache
  Future<List<Product>?> getCachedProducts() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      
      // Verificar si el cache existe
      final jsonString = prefs.getString(_productsKey);
      if (jsonString == null) {
        print('📭 Cache vacío');
        return null;
      }
      
      // Verificar si el cache no ha expirado
      final lastUpdate = prefs.getInt(_lastUpdateKey) ?? 0;
      final cacheAge = DateTime.now().millisecondsSinceEpoch - lastUpdate;
      
      if (cacheAge > _cacheDuration.inMilliseconds) {
        print('⏰ Cache expirado (${Duration(milliseconds: cacheAge).inMinutes} min)');
        await clearCache(); // Limpiar cache expirado
        return null;
      }
      
      // Deserializar productos
      final List<dynamic> productsJson = jsonDecode(jsonString);
      final products = productsJson.map((json) => Product.fromJson(json)).toList();
      
      print('🚀 Cache cargado: ${products.length} productos (${Duration(milliseconds: cacheAge).inMinutes} min de antigüedad)');
      return products;
    } catch (e) {
      print('❌ Error cargando cache: $e');
      await clearCache(); // Limpiar cache corrupto
      return null;
    }
  }

  // Verificar si el cache es válido
  Future<bool> isCacheValid() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      
      final jsonString = prefs.getString(_productsKey);
      if (jsonString == null) return false;
      
      final lastUpdate = prefs.getInt(_lastUpdateKey) ?? 0;
      final cacheAge = DateTime.now().millisecondsSinceEpoch - lastUpdate;
      
      return cacheAge <= _cacheDuration.inMilliseconds;
    } catch (e) {
      return false;
    }
  }

  // Limpiar cache
  Future<void> clearCache() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_productsKey);
      await prefs.remove(_lastUpdateKey);
      
      // Limpiar también cache de paginación
      final keys = prefs.getKeys();
      for (final key in keys) {
        if (key.startsWith(_paginationKey)) {
          await prefs.remove(key);
        }
      }
      
      print('🗑️ Cache limpiado');
    } catch (e) {
      print('❌ Error limpiando cache: $e');
    }
  }

  // Cache para paginación
  Future<void> cachePage(int page, List<Product> products) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final key = '$_paginationKey$page';
      
      final productsJson = products.map((product) => product.toJson()).toList();
      final jsonString = jsonEncode(productsJson);
      
      await prefs.setString(key, jsonString);
      print('📄 Página $page cacheada: ${products.length} productos');
    } catch (e) {
      print('❌ Error cacheando página $page: $e');
    }
  }

  // Obtener página del cache
  Future<List<Product>?> getCachedPage(int page) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final key = '$_paginationKey$page';
      
      final jsonString = prefs.getString(key);
      if (jsonString == null) return null;
      
      final List<dynamic> productsJson = jsonDecode(jsonString);
      final products = productsJson.map((json) => Product.fromJson(json)).toList();
      
      print('📄 Página $page cargada del cache: ${products.length} productos');
      return products;
    } catch (e) {
      print('❌ Error cargando página $page del cache: $e');
      return null;
    }
  }

  // Obtener información del cache
  Future<Map<String, dynamic>> getCacheInfo() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      
      final jsonString = prefs.getString(_productsKey);
      final lastUpdate = prefs.getInt(_lastUpdateKey) ?? 0;
      
      if (jsonString == null) {
        return {
          'hasCache': false,
          'productCount': 0,
          'lastUpdate': null,
          'cacheAge': null,
          'isValid': false,
        };
      }
      
      final List<dynamic> productsJson = jsonDecode(jsonString);
      final cacheAge = DateTime.now().millisecondsSinceEpoch - lastUpdate;
      final isValid = cacheAge <= _cacheDuration.inMilliseconds;
      
      return {
        'hasCache': true,
        'productCount': productsJson.length,
        'lastUpdate': DateTime.fromMillisecondsSinceEpoch(lastUpdate),
        'cacheAge': Duration(milliseconds: cacheAge),
        'isValid': isValid,
      };
    } catch (e) {
      return {
        'hasCache': false,
        'productCount': 0,
        'lastUpdate': null,
        'cacheAge': null,
        'isValid': false,
        'error': e.toString(),
      };
    }
  }

  // Forzar actualización del cache
  Future<void> forceRefresh() async {
    await clearCache();
    print('🔄 Cache forzado a actualizar');
  }
}