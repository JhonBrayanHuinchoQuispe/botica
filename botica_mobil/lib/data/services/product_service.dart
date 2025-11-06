import '../models/product.dart';
import 'api_service.dart';
import 'cache_service.dart';

class ProductService {
  final ApiService _apiService = ApiService();
  final CacheService _cacheService = CacheService();

  // Obtener productos críticos (bajo stock, por vencer, vencidos, agotados)
  Future<List<Product>> getCriticalProducts({
    String? type,
    int? limit,
  }) async {
    try {
      String endpoint = '/mobile/products/critical';
      
      // Agregar parámetros de consulta si existen
      List<String> queryParams = [];
      if (type != null) {
        queryParams.add('type=$type');
      }
      if (limit != null) {
        queryParams.add('limit=$limit');
      }
      
      if (queryParams.isNotEmpty) {
        endpoint += '?${queryParams.join('&')}';
      }

      final response = await _apiService.get<dynamic>(
        endpoint,
        requireAuth: true,
      );

      if (response.success && response.data != null) {
        // La API puede devolver los datos de diferentes formas
        List<dynamic> productsJson;
        
        if (response.data is Map<String, dynamic>) {
          // Si viene como mapa, extraer la lista de 'data'
          productsJson = response.data['data'] ?? [];
        } else if (response.data is List) {
          // Si viene directamente como lista
          productsJson = response.data;
        } else {
          throw Exception('Formato de respuesta inesperado');
        }
        
        return productsJson.map((json) => Product.fromJson(json)).toList();
      } else {
        throw Exception(response.message ?? 'Error al obtener productos críticos');
      }
    } catch (e) {
      print('Error al obtener productos críticos: $e');
      // Retornar datos de ejemplo en caso de error
      return _getExampleCriticalProducts();
    }
  }

  // Obtener todos los productos (con cache)
  Future<List<Product>> getAllProducts({bool forceRefresh = false}) async {
    try {
      // Si no se fuerza la actualización, intentar cargar del cache
      if (!forceRefresh) {
        final cachedProducts = await _cacheService.getCachedProducts();
        if (cachedProducts != null) {
          return cachedProducts;
        }
      }

      // Si no hay cache válido o se fuerza la actualización, obtener de la API
      final response = await _apiService.get<dynamic>(
        '/mobile/products?per_page=1000', // Solicitar muchos productos para obtener todos
        requireAuth: true,
      );

      if (response.success && response.data != null) {
        // La API devuelve una estructura paginada: { data: { data: [...], current_page: 1, ... } }
        List<dynamic> productsJson;
        
        if (response.data is Map<String, dynamic>) {
          // Verificar si es una respuesta paginada
          if (response.data.containsKey('data') && response.data['data'] is Map<String, dynamic>) {
            // Estructura paginada: extraer la lista de productos de data.data
            final paginatedData = response.data['data'] as Map<String, dynamic>;
            productsJson = paginatedData['data'] ?? [];
          } else if (response.data.containsKey('data') && response.data['data'] is List) {
            // Lista directa en data
            productsJson = response.data['data'];
          } else {
            // Fallback: buscar directamente en data
            productsJson = response.data['data'] ?? [];
          }
        } else if (response.data is List) {
          // Si viene directamente como lista
          productsJson = response.data;
        } else {
          throw Exception('Formato de respuesta inesperado');
        }
        
        final products = productsJson.map((json) => Product.fromJson(json)).toList();
        
        // Guardar en cache
        await _cacheService.cacheProducts(products);
        
        print('Productos obtenidos de la API: ${products.length}');
        return products;
      } else {
        throw Exception(response.message ?? 'Error al obtener productos');
      }
    } catch (e) {
      print('Error al obtener todos los productos: $e');
      
      // Intentar cargar del cache como fallback
      final cachedProducts = await _cacheService.getCachedProducts();
      if (cachedProducts != null) {
        print('Usando productos del cache como fallback');
        return cachedProducts;
      }
      
      // Retornar datos de ejemplo en caso de error
      return _getExampleCriticalProducts();
    }
  }

  // Buscar producto por código de barras
  Future<Product?> findByBarcode(String barcode) async {
    try {
      final response = await _apiService.get<dynamic>(
        '/mobile/products/barcode/$barcode',
        requireAuth: true,
      );

      if (response.success && response.data != null) {
        // La API puede devolver los datos de diferentes formas
        Map<String, dynamic> productJson;
        
        if (response.data is Map<String, dynamic>) {
          // Si viene como mapa, puede tener 'data' o ser el producto directamente
          if (response.data.containsKey('data')) {
            productJson = response.data['data'];
          } else {
            productJson = response.data;
          }
        } else {
          throw Exception('Formato de respuesta inesperado para búsqueda por código de barras');
        }
        
        return Product.fromJson(productJson);
      } else {
        return null; // Producto no encontrado
      }
    } catch (e) {
      print('Error al buscar producto: $e');
      return null;
    }
  }

  // Guardar nuevo producto
  Future<bool> saveProduct(Product product) async {
    try {
      final response = await _apiService.post<dynamic>(
        '/mobile/products',
        {
          'name': product.name,
          'brand': product.brand,
          'category': product.category,
          'price': product.price,
          'stock': product.stock,
          'minStock': product.minStock,
          'expiryDate': product.expiryDate?.toIso8601String(),
          'barcode': product.barcode,
          'batchNumber': product.batchNumber,
        },
        requireAuth: true,
      );

      return response.success;
    } catch (e) {
      print('Error al guardar producto: $e');
      return false;
    }
  }

  // Actualizar producto existente
  Future<bool> updateProduct(String id, Product product) async {
    try {
      final response = await _apiService.put<dynamic>(
        '/mobile/products/$id',
        {
          'name': product.name,
          'brand': product.brand,
          'description': product.description,
          'stock': product.stock,
          'minStock': product.minStock,
          'expiryDate': product.expiryDate?.toIso8601String(),
          'price': product.price,
          'costPrice': product.costPrice,
          'barcode': product.barcode,
          'batchNumber': product.batchNumber,
        },
        requireAuth: true,
      );

      return response.success;
    } catch (e) {
      print('Error al actualizar producto: $e');
      return false;
    }
  }

  // Eliminar producto
  Future<bool> deleteProduct(String id) async {
    try {
      final response = await _apiService.delete<dynamic>(
        '/mobile/products/$id',
        requireAuth: true,
      );

      return response.success;
    } catch (e) {
      print('Error al eliminar producto: $e');
      return false;
    }
  }

  // Registrar entrada de stock
  Future<bool> registerStockEntry({
    required String productId,
    required int quantity,
    required String batchNumber,
    required DateTime expiryDate,
  }) async {
    try {
      final response = await _apiService.post<dynamic>(
        '/mobile/products/$productId/add-stock',
        {
          'quantity': quantity,
          'batch_number': batchNumber,
          'expiry_date': expiryDate.toIso8601String(),
        },
        requireAuth: true,
      );

      return response.success;
    } catch (e) {
      print('Error al registrar entrada de stock: $e');
      return false;
    }
  }

  // Obtener productos con paginación
  Future<Map<String, dynamic>> getProductsPaginated({
    int page = 1,
    int perPage = 20,
    String? search,
    String? category,
    String? sortBy,
    String? sortOrder,
  }) async {
    try {
      // Construir parámetros de consulta
      List<String> queryParams = [
        'page=$page',
        'per_page=$perPage',
      ];
      
      if (search != null && search.isNotEmpty) {
        queryParams.add('search=${Uri.encodeComponent(search)}');
      }
      if (category != null && category.isNotEmpty) {
        queryParams.add('category=${Uri.encodeComponent(category)}');
      }
      if (sortBy != null && sortBy.isNotEmpty) {
        queryParams.add('sort_by=$sortBy');
      }
      if (sortOrder != null && sortOrder.isNotEmpty) {
        queryParams.add('sort_order=$sortOrder');
      }
      
      final endpoint = '/mobile/products?${queryParams.join('&')}';
      
      final response = await _apiService.get<dynamic>(
        endpoint,
        requireAuth: true,
      );

      if (response.success && response.data != null) {
        final data = response.data['data'] as Map<String, dynamic>;
        final productsJson = data['data'] as List<dynamic>;
        final products = productsJson.map((json) => Product.fromJson(json)).toList();
        
        // Cache esta página
        await _cacheService.cachePage(page, products);
        
        return {
          'products': products,
          'currentPage': data['current_page'] ?? page,
          'lastPage': data['last_page'] ?? 1,
          'total': data['total'] ?? products.length,
          'perPage': data['per_page'] ?? perPage,
        };
      } else {
        throw Exception(response.message ?? 'Error al obtener productos paginados');
      }
    } catch (e) {
      print('Error al obtener productos paginados: $e');
      
      // Intentar cargar de cache
      final cachedProducts = await _cacheService.getCachedPage(page);
      if (cachedProducts != null) {
        return {
          'products': cachedProducts,
          'currentPage': page,
          'lastPage': page,
          'total': cachedProducts.length,
          'perPage': perPage,
          'fromCache': true,
        };
      }
      
      // Fallback a datos de ejemplo
      final exampleProducts = _getExampleCriticalProducts();
      return {
        'products': exampleProducts,
        'currentPage': 1,
        'lastPage': 1,
        'total': exampleProducts.length,
        'perPage': perPage,
        'isExample': true,
      };
    }
  }

  // Búsqueda local en productos cacheados
  Future<List<Product>> searchProductsLocally(String query) async {
    try {
      final cachedProducts = await _cacheService.getCachedProducts();
      if (cachedProducts == null) {
        return [];
      }
      
      final lowercaseQuery = query.toLowerCase();
      
      return cachedProducts.where((product) {
        return product.name.toLowerCase().contains(lowercaseQuery) ||
               product.brand.toLowerCase().contains(lowercaseQuery) ||
               (product.category?.toLowerCase().contains(lowercaseQuery) ?? false) ||
               (product.barcode?.toLowerCase().contains(lowercaseQuery) ?? false) ||
               (product.batchNumber?.toLowerCase().contains(lowercaseQuery) ?? false);
      }).toList();
    } catch (e) {
      print('Error en búsqueda local: $e');
      return [];
    }
  }

  // Filtrar productos localmente
  Future<List<Product>> filterProductsLocally({
    String? category,
    bool? lowStock,
    bool? expiringSoon,
    bool? expired,
    double? minPrice,
    double? maxPrice,
  }) async {
    try {
      final cachedProducts = await _cacheService.getCachedProducts();
      if (cachedProducts == null) {
        return [];
      }
      
      return cachedProducts.where((product) {
        // Filtro por categoría
        if (category != null && category.isNotEmpty) {
          if ((product.category?.toLowerCase() ?? '') != category.toLowerCase()) {
            return false;
          }
        }
        
        // Filtro por stock bajo
        if (lowStock == true) {
          if (product.stock > product.minStock) {
            return false;
          }
        }
        
        // Filtro por productos próximos a vencer (30 días)
        if (expiringSoon == true && product.expiryDate != null) {
          final daysUntilExpiry = product.expiryDate!.difference(DateTime.now()).inDays;
          if (daysUntilExpiry > 30 || daysUntilExpiry < 0) {
            return false;
          }
        }
        
        // Filtro por productos vencidos
        if (expired == true && product.expiryDate != null) {
          if (product.expiryDate!.isAfter(DateTime.now())) {
            return false;
          }
        }
        
        // Filtro por precio mínimo
        if (minPrice != null && product.price < minPrice) {
          return false;
        }
        
        // Filtro por precio máximo
        if (maxPrice != null && product.price > maxPrice) {
          return false;
        }
        
        return true;
      }).toList();
    } catch (e) {
      print('Error en filtrado local: $e');
      return [];
    }
  }

  // Obtener información del cache
  Future<Map<String, dynamic>> getCacheInfo() async {
    return await _cacheService.getCacheInfo();
  }

  // Limpiar cache
  Future<void> clearCache() async {
    await _cacheService.clearCache();
  }

  // Forzar actualización del cache
  Future<void> forceRefresh() async {
    await _cacheService.forceRefresh();
  }

  // Buscar producto por código de barras (específico para escaneo)
  Future<Product?> searchByBarcode(String barcode) async {
    try {
      final response = await _apiService.get<dynamic>(
        '/api/producto/barcode/$barcode',
        requireAuth: true,
      );

      if (response.success && response.data != null) {
        Map<String, dynamic> productJson;
        
        if (response.data is Map<String, dynamic>) {
          if (response.data.containsKey('data')) {
            productJson = response.data['data'];
          } else {
            productJson = response.data;
          }
        } else {
          throw Exception('Formato de respuesta inesperado');
        }
        
        return Product.fromJson(productJson);
      } else {
        return null; // Producto no encontrado
      }
    } catch (e) {
      print('Error al buscar producto por código de barras: $e');
      return null;
    }
  }

  // Crear nuevo producto con entrada de mercadería
  Future<bool> createProductWithStock(Product product) async {
    try {
      final response = await _apiService.post<dynamic>(
        '/api/productos/entrada-mercaderia',
        {
          'nombre': product.name,
          'descripcion': product.description,
          'precio': product.price,
          'stock': product.stock,
          'categoria': product.category,
          'codigo_barras': product.barcode,
          'fecha_vencimiento': product.expirationDate?.toIso8601String(),
          'stock_minimo': product.minStock,
          'stock_maximo': product.maxStock,
          'proveedor': product.supplier,
          'ubicacion': product.location,
          'activo': product.isActive ?? true,
        },
        requireAuth: true,
      );

      if (response.success) {
        // Limpiar cache para forzar actualización
        await _cacheService.clearCache();
        return true;
      }
      return false;
    } catch (e) {
      print('Error al crear producto: $e');
      return false;
    }
  }

  // Actualizar stock de producto existente
  Future<bool> updateProductStock(Product product, int newStock) async {
    try {
      final response = await _apiService.put<dynamic>(
        '/api/productos/${product.id}/stock',
        {
          'stock': newStock,
          'motivo': 'Entrada por código de barras',
        },
        requireAuth: true,
      );

      if (response.success) {
        // Limpiar cache para forzar actualización
        await _cacheService.clearCache();
        return true;
      }
      return false;
    } catch (e) {
      print('Error al actualizar stock: $e');
      return false;
    }
  }

  // Datos de ejemplo para modo offline
  List<Product> _getExampleCriticalProducts() {
    return [
      Product(
        id: '1',
        name: 'Paracetamol 500mg',
        brand: 'Laboratorio ABC',
        category: 'Analgésicos',
        price: 15.50,
        stock: 5,
        minStock: 10,
        expiryDate: DateTime.now().add(Duration(days: 30)),
        barcode: '7501234567890',
        batchNumber: 'LOTE001',
      ),
      Product(
        id: '2',
        name: 'Ibuprofeno 400mg',
        brand: 'Laboratorio XYZ',
        category: 'Antiinflamatorios',
        price: 22.00,
        stock: 0,
        minStock: 15,
        expiryDate: DateTime.now().subtract(Duration(days: 5)),
        barcode: '7501234567891',
        batchNumber: 'LOTE002',
      ),
    ];
  }
}