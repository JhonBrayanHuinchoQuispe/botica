import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/config/api_config.dart';
import 'api_service.dart';

class DashboardService {
  static final DashboardService _instance = DashboardService._internal();
  factory DashboardService() => _instance;
  DashboardService._internal();

  final ApiService _apiService = ApiService();

  // Obtener datos del dashboard
  Future<Map<String, dynamic>> getDashboardData({String periodo = 'hoy', bool forceRefresh = false}) async {
    try {
      print('🔄 Obteniendo datos del dashboard... (forceRefresh: $forceRefresh)');
      
      // Agregar timestamp para evitar caché
      final timestamp = DateTime.now().millisecondsSinceEpoch;
      final endpoint = '/mobile/dashboard?t=$timestamp';
      print('🌐 URL: $endpoint');
      
      final response = await _apiService.get<dynamic>(
        endpoint,
        requireAuth: true,
      );

      print('📊 Respuesta del dashboard: ${response.success}');
      print('📊 Datos recibidos: ${response.data}');
      print('📊 Response data type: ${response.data.runtimeType}');

      if (response.success && response.data != null) {
        // Verificar si response.data tiene la estructura esperada
        Map<String, dynamic> data;
        if (response.data is Map<String, dynamic> && response.data.containsKey('data')) {
          data = response.data['data'] as Map<String, dynamic>;
        } else if (response.data is Map<String, dynamic>) {
          data = response.data as Map<String, dynamic>;
        } else {
          throw Exception('Estructura de datos inesperada');
        }
        
        print('✅ Procesando datos reales del backend...');
        print('📊 Top productos raw: ${data['topProducts']?.length ?? 0}');
        print('📊 Alertas recientes raw: ${data['recentAlerts']?.length ?? 0}');
        
        final result = {
          'dailySales': _parseDouble(data['dailySales'] ?? 0.0),
          'lowStockProducts': _parseInt(data['lowStockProducts'] ?? 0),
          'expiringProducts': _parseInt(data['expiringProducts'] ?? 0),
          'totalSales': _parseDouble(data['totalSales'] ?? 0.0),
          'totalProducts': _parseInt(data['totalProducts'] ?? 0),
          'topSellingProducts': _processTopProducts(data['topProducts'] ?? []),
          'criticalStockProducts': _processCriticalStock(data['criticalProducts'] ?? []),
          'recentNotifications': _processNotifications(data['recentAlerts'] ?? []),
          'lastUpdated': data['lastUpdated'] ?? DateTime.now().toIso8601String(),
          'refreshTimestamp': timestamp,
        };
        print('📊 Total productos: ${result['totalProducts']}');
        print('📊 Ventas diarias: ${result['dailySales']}');
        print('📊 Stock bajo: ${result['lowStockProducts']}');
        print('📊 Top productos procesados: ${result['topSellingProducts'].length}');
        print('📊 Notificaciones procesadas: ${result['recentNotifications'].length}');
        return result;
      } else {
        print('❌ Error en respuesta del backend: ${response.message}');
        print('📊 Response success: ${response.success}');
        print('📊 Response message: ${response.message}');
        throw Exception('Error al obtener datos del dashboard: ${response.message}');
      }
    } catch (e) {
      print('❌ Error en getDashboardData: $e');
      print('📊 Stack trace: ${StackTrace.current}');
      print('📊 Usando datos básicos como respaldo');
      final basicData = await _getBasicDashboardData();
      return basicData;
    }
  }

  // Procesar datos del dashboard desde Laravel
  Map<String, dynamic> _processDashboardData(dynamic data) {
    return {
      'totalSales': _parseDouble(data['ingresosMes'] ?? 0),
      'dailySales': _parseDouble(data['ventasHoy'] ?? 0) * 50.0, // Estimación de venta promedio
      'lowStockProducts': _parseInt(data['productosStockBajo'] ?? 0),
      'expiringProducts': _parseInt(data['productosProximosVencer'] ?? 0),
      'totalProducts': _parseInt(data['totalProductos'] ?? 0),
      'monthlyRevenue': _parseDouble(data['ingresosMes'] ?? 0),
      'monthlyExpenses': _parseDouble(data['gastosMes'] ?? 0),
      'performance': _parseDouble(data['rendimiento'] ?? 0),
      'salesChange': _parseInt(data['cambioVentas'] ?? 0),
      'topSellingProducts': _processTopProducts(data['productosMasVendidos'] ?? []),
      'criticalStockProducts': _processCriticalStock(data['productosStockCritico'] ?? []),
      'recentAlerts': _generateRecentAlerts(data),
      'salesByPeriod': _processSalesData(data['ventasPorDia'] ?? []),
    };
  }

  // Procesar productos más vendidos
  List<Map<String, dynamic>> _processTopProducts(dynamic products) {
    print('🔍 Procesando productos más vendidos...');
    print('📊 Datos recibidos: $products');
    
    if (products is! List) {
      print('❌ Los datos de productos no son una lista válida');
      return [];
    }
    
    if (products.isEmpty) {
      print('⚠️ No hay productos más vendidos disponibles');
      return [];
    }
    
    print('✅ Procesando ${products.length} productos más vendidos');
    
    final processedProducts = products.take(3).map<Map<String, dynamic>>((product) {
      print('📦 Procesando producto: ${product['nombre']} - Vendido: ${product['total_vendido']} - Cantidad: ${product['cantidad_vendida']}');
      
      return {
        'name': product['nombre'] ?? 'Producto',
        'sales': _parseInt(product['total_vendido'] ?? product['cantidad_vendida'] ?? 0),
        'revenue': _parseDouble(product['ingresos_producto'] ?? product['total_ingresos'] ?? 0),
        'category': product['categoria'] ?? 'Sin categoría',
        'brand': product['marca'] ?? 'Sin marca',
        'quantity': _parseInt(product['cantidad_vendida'] ?? product['total_vendido'] ?? 0),
      };
    }).toList();
    
    print('✅ Productos procesados: ${processedProducts.length}');
    for (var product in processedProducts) {
      print('   - ${product['name']}: ${product['sales']} ventas, ${product['quantity']} unidades');
    }
    
    return processedProducts;
  }

  // Procesar productos con stock crítico
  List<Map<String, dynamic>> _processCriticalStock(dynamic products) {
    if (products is! List) return [];
    
    return products.take(3).map<Map<String, dynamic>>((product) {
      return {
        'name': product['nombre'] ?? 'Producto',
        'currentStock': _parseInt(product['stock_actual'] ?? 0),
        'minStock': _parseInt(product['stock_minimo'] ?? 0),
        'category': product['categoria'] ?? 'Sin categoría',
      };
    }).toList();
  }

  // Procesar notificaciones recientes
  List<Map<String, dynamic>> _processNotifications(dynamic notifications) {
    if (notifications is! List) return [];
    
    return notifications.take(3).map<Map<String, dynamic>>((notification) {
      return {
        'type': notification['type'] ?? 'info',
        'title': notification['title'] ?? 'Notificación',
        'message': notification['description'] ?? 'Sin descripción',
        'time': notification['time'] ?? 'Ahora',
        'icon': _getNotificationIcon(notification['type']),
        'color': _getNotificationColor(notification['type']),
      };
    }).toList();
  }

  // Obtener icono según el tipo de notificación
  String _getNotificationIcon(String? type) {
    switch (type) {
      case 'low_stock':
        return 'warning';
      case 'expiring':
        return 'calendar';
      case 'price_update':
        return 'update';
      default:
        return 'info';
    }
  }

  // Obtener color según el tipo de notificación
  String _getNotificationColor(String? type) {
    switch (type) {
      case 'low_stock':
        return 'orange';
      case 'expiring':
        return 'red';
      case 'price_update':
        return 'blue';
      default:
        return 'gray';
    }
  }

  // Generar alertas recientes basadas en los datos
  List<Map<String, dynamic>> _generateRecentAlerts(dynamic data) {
    List<Map<String, dynamic>> alerts = [];
    
    int lowStock = _parseInt(data['productosStockBajo'] ?? 0);
    int expiring = _parseInt(data['productosProximosVencer'] ?? 0);
    int expired = _parseInt(data['productosVencidos'] ?? 0);
    
    if (lowStock > 0) {
      alerts.add({
        'type': 'stock',
        'message': '$lowStock productos con stock bajo',
        'icon': 'warning',
        'color': 'orange',
        'time': '1h',
      });
    }
    
    if (expiring > 0) {
      alerts.add({
        'type': 'expiration',
        'message': '$expiring productos próximos a vencer',
        'icon': 'calendar',
        'color': 'red',
        'time': '2h',
      });
    }
    
    if (expired > 0) {
      alerts.add({
        'type': 'expired',
        'message': '$expired productos vencidos',
        'icon': 'error',
        'color': 'red',
        'time': '30m',
      });
    }
    
    // Agregar alertas positivas
    if (alerts.isEmpty) {
      alerts.add({
        'type': 'success',
        'message': 'Sistema funcionando correctamente',
        'icon': 'check',
        'color': 'green',
        'time': '5m',
      });
    }
    
    return alerts.take(3).toList();
  }

  // Procesar datos de ventas por período
  List<Map<String, dynamic>> _processSalesData(dynamic salesData) {
    if (salesData is! List) return [];
    
    return salesData.map<Map<String, dynamic>>((sale) {
      return {
        'period': sale['fecha'] ?? '',
        'total': _parseDouble(sale['total'] ?? 0),
        'sales': _parseInt(sale['ventas'] ?? 0),
      };
    }).toList();
  }

  // Obtener datos básicos reales cuando falla la conexión principal
  Future<Map<String, dynamic>> _getBasicDashboardData() async {
    try {
      print('🔄 Intentando obtener datos básicos...');
      
      // Intentar obtener productos para datos básicos
      final productsResponse = await _apiService.get<dynamic>(
        '/mobile/products',
        requireAuth: true,
      );
      
      int totalProducts = 0;
      int lowStockProducts = 0;
      List<Map<String, dynamic>> criticalProducts = [];
      
      if (productsResponse.success && productsResponse.data != null) {
        final products = productsResponse.data['data'] ?? [];
        totalProducts = products.length;
        
        // Contar productos con stock bajo
        for (var product in products) {
          if (product['stock_actual'] != null && product['stock_actual'] <= 5) {
            lowStockProducts++;
            if (criticalProducts.length < 3) {
              criticalProducts.add({
                'name': product['nombre'] ?? 'Producto',
                'currentStock': _parseInt(product['stock_actual'] ?? 0),
                'minStock': _parseInt(product['stock_minimo'] ?? 0),
                'category': product['categoria'] ?? 'Sin categoría',
              });
            }
          }
        }
      }
      
      print('✅ Datos básicos obtenidos: $totalProducts productos, $lowStockProducts con stock bajo');
      
      return {
        'totalSales': 0.0,
        'dailySales': 0.0,
        'lowStockProducts': lowStockProducts,
        'expiringProducts': 0,
        'totalProducts': totalProducts,
        'topSellingProducts': [],
        'criticalStockProducts': criticalProducts,
        'recentNotifications': [
          {
            'type': 'info',
            'title': 'Información',
            'message': 'Datos limitados disponibles',
            'time': 'Ahora',
            'icon': 'info',
            'color': 'blue',
          }
        ],
        'lastUpdated': DateTime.now().toIso8601String(),
      };
    } catch (e) {
      print('❌ Error obteniendo datos básicos: $e');
      return _getEmptyDashboardData();
    }
  }

  // Datos vacíos como último recurso
  Map<String, dynamic> _getEmptyDashboardData() {
    return {
      'totalSales': 0.0,
      'dailySales': 0.0,
      'lowStockProducts': 0,
      'expiringProducts': 0,
      'totalProducts': 0,
      'topSellingProducts': [],
      'criticalStockProducts': [],
      'recentNotifications': [
        {
          'type': 'error',
          'title': 'Error de Conexión',
          'message': 'No se pudieron cargar los datos',
          'time': 'Ahora',
          'icon': 'error',
          'color': 'red',
        }
      ],
      'lastUpdated': DateTime.now().toIso8601String(),
    };
  }

  // Utilidades para parsing seguro
  double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }

  int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is double) return value.round();
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }
}