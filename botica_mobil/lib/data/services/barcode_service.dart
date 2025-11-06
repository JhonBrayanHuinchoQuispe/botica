import 'dart:convert';
import 'package:connectivity_plus/connectivity_plus.dart';
import '../models/product.dart';
import 'local_database_service.dart';
import 'product_service.dart';

class BarcodeService {
  static final BarcodeService _instance = BarcodeService._internal();
  factory BarcodeService() => _instance;
  BarcodeService._internal();

  final LocalDatabaseService _localDb = LocalDatabaseService();
  final ProductService _productService = ProductService();

  // Buscar producto por código de barras (lógica principal)
  Future<BarcodeSearchResult> buscarProductoPorCodigo(String codigoBarras) async {
    try {
      // 1. Buscar primero en cache local (súper rápido)
      Product? productoLocal = await _localDb.buscarPorCodigoBarras(codigoBarras);
      
      if (productoLocal != null) {
        return BarcodeSearchResult(
          producto: productoLocal,
          encontrado: true,
          esNuevo: false,
          origen: 'cache_local',
        );
      }

      // 2. Si no está en cache, buscar en servidor
      final conectividad = await Connectivity().checkConnectivity();
      if (conectividad == ConnectivityResult.none) {
        return BarcodeSearchResult(
          encontrado: false,
          esNuevo: true,
          origen: 'sin_conexion',
          codigoBarras: codigoBarras,
        );
      }

      // 3. Buscar en servidor
      Product? productoRemoto = await _buscarEnServidor(codigoBarras);
      
      if (productoRemoto != null) {
        // Validar que el código realmente coincide
        if ((productoRemoto.barcode ?? '').trim() != codigoBarras.trim()) {
          return BarcodeSearchResult(
            encontrado: false,
            esNuevo: true,
            origen: 'mismatch',
            codigoBarras: codigoBarras,
          );
        }
        // Guardar en cache para próximas búsquedas
        await _localDb.guardarProducto(productoRemoto);
        
        return BarcodeSearchResult(
          producto: productoRemoto,
          encontrado: true,
          esNuevo: false,
          origen: 'servidor',
        );
      }

      // 4. Producto no encontrado - es nuevo
      return BarcodeSearchResult(
        encontrado: false,
        esNuevo: true,
        origen: 'no_encontrado',
        codigoBarras: codigoBarras,
      );

    } catch (e) {
      print('Error en búsqueda por código de barras: $e');
      return BarcodeSearchResult(
        encontrado: false,
        esNuevo: true,
        origen: 'error',
        codigoBarras: codigoBarras,
        error: e.toString(),
      );
    }
  }

  // Buscar en servidor Laravel
  Future<Product?> _buscarEnServidor(String codigoBarras) async {
    try {
      // Buscar directamente por código en el backend
      final producto = await _productService.findByBarcode(codigoBarras);
      return producto; // puede ser null si no existe
    } catch (e) {
      print('Error buscando en servidor: $e');
      return null;
    }
  }

  // Sincronizar cache con servidor
  Future<void> sincronizarCache() async {
    try {
      final conectividad = await Connectivity().checkConnectivity();
      if (conectividad == ConnectivityResult.none) {
        print('Sin conexión para sincronizar cache');
        return;
      }

      // Obtener todos los productos del servidor
      final productos = await _productService.getAllProducts();
      
      // Filtrar solo productos con código de barras
      final productosConCodigo = productos.where(
        (p) => p.barcode != null && p.barcode!.isNotEmpty
      ).toList();

      // Sincronizar con base de datos local
      await _localDb.sincronizarProductos(productosConCodigo);
      
      print('Cache sincronizado: ${productosConCodigo.length} productos');
    } catch (e) {
      print('Error sincronizando cache: $e');
    }
  }

  // Verificar y actualizar cache si es necesario
  Future<void> verificarYActualizarCache() async {
    try {
      final esActualizado = await _localDb.esCacheActualizado();
      final cantidadProductos = await _localDb.contarProductos();
      
      // Actualizar si el cache está desactualizado o vacío
      if (!esActualizado || cantidadProductos == 0) {
        await sincronizarCache();
      }
    } catch (e) {
      print('Error verificando cache: $e');
    }
  }

  // Agregar nuevo producto con código de barras
  Future<bool> agregarNuevoProducto(Product producto) async {
    try {
      // Guardar en servidor usando ProductService
      final exito = await _productService.createProductWithStock(producto);
      
      if (exito) {
        // Guardar en cache local también
        await _localDb.guardarProducto(producto);
        return true;
      }
      
      return false;
    } catch (e) {
      print('Error agregando nuevo producto: $e');
      return false;
    }
  }

  // Actualizar stock de producto existente
  Future<bool> actualizarStockProducto(Product producto, int nuevaCantidad) async {
    try {
      // Crear copia del producto con nuevo stock
      final productoActualizado = Product(
        id: producto.id,
        name: producto.name,
        brand: producto.brand,
        description: producto.description,
        price: producto.price,
        stock: nuevaCantidad,
        minStock: producto.minStock,
        expiryDate: producto.expiryDate,
        category: producto.category,
        barcode: producto.barcode,
        expirationDate: producto.expirationDate,
        maxStock: producto.maxStock,
        supplier: producto.supplier,
        location: producto.location,
        isActive: producto.isActive,
        createdAt: producto.createdAt,
        updatedAt: DateTime.now(),
      );

      // Actualizar en servidor
      final exito = await _productService.updateProduct(producto.id, productoActualizado);
      
      if (exito) {
        // Actualizar en cache local
        await _localDb.guardarProducto(productoActualizado);
        return true;
      }
      
      return false;
    } catch (e) {
      print('Error actualizando stock: $e');
      return false;
    }
  }

  // Limpiar cache
  Future<void> limpiarCache() async {
    await _localDb.limpiarCache();
  }
}

// Clase para el resultado de búsqueda
class BarcodeSearchResult {
  final Product? producto;
  final bool encontrado;
  final bool esNuevo;
  final String origen;
  final String? codigoBarras;
  final String? error;

  BarcodeSearchResult({
    this.producto,
    required this.encontrado,
    required this.esNuevo,
    required this.origen,
    this.codigoBarras,
    this.error,
  });
}