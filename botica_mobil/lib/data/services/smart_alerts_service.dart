import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:workmanager/workmanager.dart';
import '../models/product.dart';
import 'product_service.dart';

class SmartAlertsService {
  static final SmartAlertsService _instance = SmartAlertsService._internal();
  factory SmartAlertsService() => _instance;
  SmartAlertsService._internal();

  final FlutterLocalNotificationsPlugin _notifications = FlutterLocalNotificationsPlugin();
  final ProductService _productService = ProductService();

  // Configuración de alertas
  static const int _lowStockThreshold = 10;
  static const int _expiryWarningDays = 30;
  static const int _criticalExpiryDays = 7;

  // Categorías críticas de medicamentos
  static const List<String> _criticalCategories = [
    'Antibióticos',
    'Analgésicos',
    'Antiinflamatorios',
    'Antihipertensivos',
    'Antidiabéticos',
    'Broncodilatadores',
    'Antihistamínicos',
    'Medicamentos de emergencia'
  ];

  Future<void> initialize() async {
    await _initializeNotifications();
    await _requestPermissions();
    await _initializeWorkManager();
    await _schedulePeriodicChecks();
  }

  Future<void> _initializeNotifications() async {
    const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
    const iosSettings = DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );

    const initSettings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );

    await _notifications.initialize(
      initSettings,
      onDidReceiveNotificationResponse: _onNotificationTapped,
    );
  }

  Future<void> _requestPermissions() async {
    await Permission.notification.request();
    
    if (await Permission.notification.isDenied) {
      debugPrint('⚠️ Permisos de notificación denegados');
    }
  }

  Future<void> _initializeWorkManager() async {
    await Workmanager().initialize(
      callbackDispatcher,
      isInDebugMode: false,
    );
  }

  Future<void> _schedulePeriodicChecks() async {
    // Verificar alertas cada 4 horas
    await Workmanager().registerPeriodicTask(
      "smart-alerts-check",
      "smartAlertsCheck",
      frequency: const Duration(hours: 4),
      constraints: Constraints(
        networkType: NetworkType.connected,
      ),
    );

    // Verificación diaria de productos críticos
    await Workmanager().registerPeriodicTask(
      "critical-products-check",
      "criticalProductsCheck",
      frequency: const Duration(hours: 24),
      constraints: Constraints(
        networkType: NetworkType.connected,
      ),
    );
  }

  Future<void> checkAndSendAlerts() async {
    try {
      debugPrint('🔍 Verificando alertas inteligentes...');
      
      final products = await _productService.getAllProducts();
      
      await _checkLowStockAlerts(products);
      await _checkExpiryAlerts(products);
      await _checkCriticalCategoryAlerts(products);
      
      await _saveLastCheckTime();
      
    } catch (e) {
      debugPrint('❌ Error verificando alertas: $e');
    }
  }

  Future<void> _checkLowStockAlerts(List<Product> products) async {
    final lowStockProducts = products.where((p) => p.stock <= _lowStockThreshold).toList();
    
    for (final product in lowStockProducts) {
      await _sendNotification(
        id: product.id.hashCode,
        title: '📦 Stock Bajo',
        body: '${product.name} tiene solo ${product.stock} unidades',
        payload: json.encode({
          'type': 'low_stock',
          'productId': product.id,
          'productName': product.name,
          'stock': product.stock,
        }),
        priority: Priority.high,
        category: 'stock',
      );
    }

    if (lowStockProducts.isNotEmpty) {
      debugPrint('📦 Enviadas ${lowStockProducts.length} alertas de stock bajo');
    }
  }

  Future<void> _checkExpiryAlerts(List<Product> products) async {
    final now = DateTime.now();
    
    // Productos próximos a vencer (30 días)
    final expiringSoonProducts = products.where((p) {
      final daysUntilExpiry = p.expiryDate.difference(now).inDays;
      return daysUntilExpiry <= _expiryWarningDays && daysUntilExpiry > _criticalExpiryDays;
    }).toList();

    // Productos críticos por vencer (7 días)
    final criticalExpiryProducts = products.where((p) {
      final daysUntilExpiry = p.expiryDate.difference(now).inDays;
      return daysUntilExpiry <= _criticalExpiryDays && daysUntilExpiry > 0;
    }).toList();

    // Productos vencidos
    final expiredProducts = products.where((p) => p.expiryDate.isBefore(now)).toList();

    // Enviar notificaciones
    for (final product in expiringSoonProducts) {
      final daysLeft = product.expiryDate.difference(now).inDays;
      await _sendNotification(
        id: product.id.hashCode + 1000,
        title: '⏰ Próximo a Vencer',
        body: '${product.name} vence en $daysLeft días',
        payload: json.encode({
          'type': 'expiry_warning',
          'productId': product.id,
          'productName': product.name,
          'daysLeft': daysLeft,
        }),
        priority: Priority.defaultPriority,
        category: 'expiry',
      );
    }

    for (final product in criticalExpiryProducts) {
      final daysLeft = product.expiryDate.difference(now).inDays;
      await _sendNotification(
        id: product.id.hashCode + 2000,
        title: '🚨 CRÍTICO: Por Vencer',
        body: '${product.name} vence en $daysLeft días',
        payload: json.encode({
          'type': 'critical_expiry',
          'productId': product.id,
          'productName': product.name,
          'daysLeft': daysLeft,
        }),
        priority: Priority.max,
        category: 'critical',
      );
    }

    for (final product in expiredProducts) {
      await _sendNotification(
        id: product.id.hashCode + 3000,
        title: '❌ PRODUCTO VENCIDO',
        body: '${product.name} ya está vencido',
        payload: json.encode({
          'type': 'expired',
          'productId': product.id,
          'productName': product.name,
        }),
        priority: Priority.max,
        category: 'expired',
      );
    }

    debugPrint('⏰ Alertas de vencimiento: ${expiringSoonProducts.length} próximos, ${criticalExpiryProducts.length} críticos, ${expiredProducts.length} vencidos');
  }

  Future<void> _checkCriticalCategoryAlerts(List<Product> products) async {
    for (final category in _criticalCategories) {
      final categoryProducts = products.where((p) => 
        p.category?.toLowerCase().contains(category.toLowerCase()) == true
      ).toList();

      final lowStockCritical = categoryProducts.where((p) => p.stock <= _lowStockThreshold).toList();

      if (lowStockCritical.isNotEmpty) {
        await _sendNotification(
          id: category.hashCode,
          title: '🚨 MEDICAMENTO CRÍTICO',
          body: 'Stock bajo en $category: ${lowStockCritical.length} productos',
          payload: json.encode({
            'type': 'critical_category',
            'category': category,
            'count': lowStockCritical.length,
            'products': lowStockCritical.map((p) => p.name).toList(),
          }),
          priority: Priority.max,
          category: 'critical_medicine',
        );
      }
    }
  }

  Future<void> _sendNotification({
    required int id,
    required String title,
    required String body,
    String? payload,
    Priority priority = Priority.defaultPriority,
    String category = 'general',
  }) async {
    // Verificar si ya se envió esta notificación recientemente
    if (await _wasRecentlySent(id)) {
      return;
    }

    final androidDetails = AndroidNotificationDetails(
      'smart_alerts_$category',
      'Alertas Inteligentes',
      channelDescription: 'Notificaciones automáticas del sistema',
      importance: _getImportance(priority),
      icon: '@mipmap/ic_launcher',
      color: _getCategoryColor(category),
      enableVibration: priority == Priority.max,
      playSound: true,
      styleInformation: const BigTextStyleInformation(''),
    );

    const iosDetails = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );

    final details = NotificationDetails(
      android: androidDetails,
      iOS: iosDetails,
    );

    await _notifications.show(id, title, body, details, payload: payload);
    await _markAsSent(id);
    
    debugPrint('📱 Notificación enviada: $title');
  }

  Importance _getImportance(Priority priority) {
    switch (priority) {
      case Priority.max:
        return Importance.max;
      case Priority.high:
        return Importance.high;
      case Priority.defaultPriority:
        return Importance.defaultImportance;
      default:
        return Importance.low;
    }
  }



  Color _getCategoryColor(String category) {
    switch (category) {
      case 'critical':
      case 'expired':
      case 'critical_medicine':
        return Colors.red;
      case 'stock':
        return Colors.orange;
      case 'expiry':
        return Colors.amber;
      default:
        return Colors.blue;
    }
  }

  Future<bool> _wasRecentlySent(int id) async {
    final prefs = await SharedPreferences.getInstance();
    final lastSent = prefs.getInt('notification_$id') ?? 0;
    final now = DateTime.now().millisecondsSinceEpoch;
    
    // No enviar la misma notificación en las últimas 4 horas
    return (now - lastSent) < (4 * 60 * 60 * 1000);
  }

  Future<void> _markAsSent(int id) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt('notification_$id', DateTime.now().millisecondsSinceEpoch);
  }

  Future<void> _saveLastCheckTime() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt('last_alert_check', DateTime.now().millisecondsSinceEpoch);
  }

  Future<DateTime?> getLastCheckTime() async {
    final prefs = await SharedPreferences.getInstance();
    final timestamp = prefs.getInt('last_alert_check');
    return timestamp != null ? DateTime.fromMillisecondsSinceEpoch(timestamp) : null;
  }

  void _onNotificationTapped(NotificationResponse response) {
    if (response.payload != null) {
      try {
        final data = json.decode(response.payload!);
        debugPrint('🔔 Notificación tocada: ${data['type']}');
        
        // Aquí puedes navegar a la pantalla correspondiente
        // NavigationService.navigateToProduct(data['productId']);
        
      } catch (e) {
        debugPrint('❌ Error procesando notificación: $e');
      }
    }
  }

  Future<void> testNotification() async {
    await _sendNotification(
      id: 999999,
      title: '🧪 Notificación de Prueba',
      body: 'El sistema de alertas inteligentes está funcionando correctamente',
      priority: Priority.high,
      category: 'test',
    );
  }

  Future<void> cancelAllNotifications() async {
    await _notifications.cancelAll();
  }

  Future<void> cancelNotification(int id) async {
    await _notifications.cancel(id);
  }
}

// Callback para WorkManager
@pragma('vm:entry-point')
void callbackDispatcher() {
  Workmanager().executeTask((task, inputData) async {
    debugPrint('🔄 Ejecutando tarea en segundo plano: $task');
    
    try {
      final alertsService = SmartAlertsService();
      await alertsService.checkAndSendAlerts();
      return Future.value(true);
    } catch (e) {
      debugPrint('❌ Error en tarea de segundo plano: $e');
      return Future.value(false);
    }
  });
}