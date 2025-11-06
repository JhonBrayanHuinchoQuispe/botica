import 'dart:convert';
import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import '../models/product.dart';

class LocalDatabaseService {
  static final LocalDatabaseService _instance = LocalDatabaseService._internal();
  factory LocalDatabaseService() => _instance;
  LocalDatabaseService._internal();

  Database? _database;

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDatabase();
    return _database!;
  }

  Future<Database> _initDatabase() async {
    String path = join(await getDatabasesPath(), 'productos_cache.db');
    
    return await openDatabase(
      path,
      version: 1,
      onCreate: _onCreate,
    );
  }

  Future<void> _onCreate(Database db, int version) async {
    await db.execute('''
      CREATE TABLE productos_cache (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo_barras TEXT UNIQUE NOT NULL,
        producto_data TEXT NOT NULL,
        ultima_actualizacion INTEGER NOT NULL
      )
    ''');

    // Crear índice para búsqueda rápida por código de barras
    await db.execute('''
      CREATE INDEX idx_codigo_barras ON productos_cache(codigo_barras)
    ''');
  }

  // Buscar producto por código de barras (súper rápido)
  Future<Product?> buscarPorCodigoBarras(String codigoBarras) async {
    final db = await database;
    
    final List<Map<String, dynamic>> maps = await db.query(
      'productos_cache',
      where: 'codigo_barras = ?',
      whereArgs: [codigoBarras],
    );

    if (maps.isNotEmpty) {
      final productData = jsonDecode(maps.first['producto_data']);
      return Product.fromJson(productData);
    }
    
    return null;
  }

  // Guardar producto en cache
  Future<void> guardarProducto(Product producto) async {
    final db = await database;
    
    await db.insert(
      'productos_cache',
      {
        'codigo_barras': producto.barcode ?? '',
        'producto_data': jsonEncode(producto.toJson()),
        'ultima_actualizacion': DateTime.now().millisecondsSinceEpoch,
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  // Sincronizar múltiples productos
  Future<void> sincronizarProductos(List<Product> productos) async {
    final db = await database;
    
    await db.transaction((txn) async {
      for (Product producto in productos) {
        if (producto.barcode != null && producto.barcode!.isNotEmpty) {
          await txn.insert(
            'productos_cache',
            {
              'codigo_barras': producto.barcode!,
              'producto_data': jsonEncode(producto.toJson()),
              'ultima_actualizacion': DateTime.now().millisecondsSinceEpoch,
            },
            conflictAlgorithm: ConflictAlgorithm.replace,
          );
        }
      }
    });
  }

  // Obtener todos los productos del cache
  Future<List<Product>> obtenerTodosLosProductos() async {
    final db = await database;
    
    final List<Map<String, dynamic>> maps = await db.query('productos_cache');
    
    return List.generate(maps.length, (i) {
      final productData = jsonDecode(maps[i]['producto_data']);
      return Product.fromJson(productData);
    });
  }

  // Limpiar cache
  Future<void> limpiarCache() async {
    final db = await database;
    await db.delete('productos_cache');
  }

  // Verificar si el cache está actualizado (menos de 24 horas)
  Future<bool> esCacheActualizado() async {
    final db = await database;
    
    final List<Map<String, dynamic>> maps = await db.query(
      'productos_cache',
      orderBy: 'ultima_actualizacion DESC',
      limit: 1,
    );

    if (maps.isEmpty) return false;

    final ultimaActualizacion = maps.first['ultima_actualizacion'] as int;
    final ahora = DateTime.now().millisecondsSinceEpoch;
    final diferencia = ahora - ultimaActualizacion;
    
    // Cache válido por 24 horas (86400000 ms)
    return diferencia < 86400000;
  }

  // Contar productos en cache
  Future<int> contarProductos() async {
    final db = await database;
    final result = await db.rawQuery('SELECT COUNT(*) as count FROM productos_cache');
    return Sqflite.firstIntValue(result) ?? 0;
  }
}