import 'package:flutter/material.dart';

class Product {
  final String id;
  final String name;
  final String brand;
  final String? description;
  final int stock;
  final int minStock;
  final DateTime expiryDate;
  final double price;
  final double? costPrice;
  final String? barcode;
  final String? category;
  final String? imageUrl;
  final String? batchNumber;
  final String? laboratory;
  final String? location; // Ubicación del producto
  final DateTime? expirationDate; // Fecha de vencimiento (alias para expiryDate)
  final int? maxStock; // Stock máximo
  final String? supplier; // Proveedor
  final bool? isActive; // Estado activo/inactivo
  final String? concentration; // Concentración del producto
  final String? presentation; // Presentación del producto
  final DateTime createdAt;
  final DateTime updatedAt;
  
  // Campos calculados de la API
  final bool? apiIsLowStock;
  final bool? apiIsExpiringSoon;
  final bool? apiIsExpired;
  final bool? apiIsOutOfStock;
  final String? apiStatus;
  final String? apiStatusText;
  final String? apiStatusColor;
  final int? apiDaysUntilExpiry;

  Product({
    String? id,
    required this.name,
    required this.brand,
    this.description,
    required this.stock,
    required this.minStock,
    required this.expiryDate,
    required this.price,
    this.costPrice,
    this.barcode,
    this.category,
    this.imageUrl,
    this.batchNumber,
    this.laboratory,
    this.location,
    this.expirationDate,
    this.maxStock,
    this.supplier,
    this.isActive,
    this.concentration,
    this.presentation,
    DateTime? createdAt,
    DateTime? updatedAt,
    this.apiIsLowStock,
    this.apiIsExpiringSoon,
    this.apiIsExpired,
    this.apiIsOutOfStock,
    this.apiStatus,
    this.apiStatusText,
    this.apiStatusColor,
    this.apiDaysUntilExpiry,
  }) : 
    this.id = id ?? DateTime.now().millisecondsSinceEpoch.toString(),
    this.createdAt = createdAt ?? DateTime.now(),
    this.updatedAt = updatedAt ?? DateTime.now();

  // Constructor para crear un nuevo producto desde el escáner
  factory Product.fromScanner({
    required String barcode,
    required String name,
    required double price,
    required int stockEntry,
    required DateTime expiryDate,
    String? batchNumber,
    String? laboratory,
    String? category,
  }) {
    return Product(
      name: name,
      brand: laboratory ?? 'Sin laboratorio',
      stock: stockEntry,
      minStock: 5, // Valor por defecto
      expiryDate: expiryDate,
      price: price,
      barcode: barcode,
      batchNumber: batchNumber,
      laboratory: laboratory,
      category: category,
    );
  }

  // Constructor para crear una entrada de stock
  factory Product.stockEntry({
    required Product existingProduct,
    required int quantity,
    required String batchNumber,
    required DateTime expiryDate,
  }) {
    return existingProduct.copyWith(
      stock: existingProduct.stock + quantity,
      batchNumber: batchNumber,
      expiryDate: expiryDate,
      updatedAt: DateTime.now(),
    );
  }

  Product addStock(int quantity) {
    return copyWith(
      stock: stock + quantity,
      updatedAt: DateTime.now(),
    );
  }

  Product removeStock(int quantity) {
    if (quantity > stock) {
      throw Exception('No hay suficiente stock disponible');
    }
    return copyWith(
      stock: stock - quantity,
      updatedAt: DateTime.now(),
    );
  }

  Product updateInfo({
    String? newName,
    String? newBrand,
    String? newDescription,
    double? newPrice,
    double? newCostPrice,
    String? newCategory,
    int? newMinStock,
    DateTime? newExpiryDate,
    String? newBatchNumber,
    String? newLaboratory,
  }) {
    return copyWith(
      name: newName,
      brand: newBrand,
      description: newDescription,
      price: newPrice,
      costPrice: newCostPrice,
      category: newCategory,
      minStock: newMinStock,
      expiryDate: newExpiryDate,
      batchNumber: newBatchNumber,
      laboratory: newLaboratory,
      updatedAt: DateTime.now(),
    );
  }

  // Getters para estado del producto (usa valores de API si están disponibles)
  bool get isLowStock => apiIsLowStock ?? (stock <= minStock);
  
  bool get isExpiringSoon {
    if (apiIsExpiringSoon != null) return apiIsExpiringSoon!;
    final now = DateTime.now();
    final daysUntilExpiry = expiryDate.difference(now).inDays;
    return daysUntilExpiry <= 30 && daysUntilExpiry > 0;
  }

  bool get isExpired {
    if (apiIsExpired != null) return apiIsExpired!;
    final now = DateTime.now();
    return expiryDate.isBefore(now);
  }

  bool get isOutOfStock => apiIsOutOfStock ?? (stock <= 0);

  Color get statusColor {
    if (apiStatusColor != null) {
      try {
        return Color(int.parse(apiStatusColor!.replaceFirst('#', '0xFF')));
      } catch (e) {
        // Fallback a colores por defecto si hay error parseando
      }
    }
    
    if (isExpired) return Colors.red.shade700;
    if (isOutOfStock) return Colors.grey.shade700;
    if (isLowStock) return Colors.orange.shade600;
    if (isExpiringSoon) return Colors.amber.shade600;
    return Colors.green.shade600;
  }

  String get statusText {
    if (apiStatusText != null) return apiStatusText!;
    
    if (isExpired) return 'Vencido';
    if (isOutOfStock) return 'Agotado';
    if (isLowStock) return 'Stock Bajo';
    if (isExpiringSoon) return 'Por Vencer';
    return 'En Stock';
  }

  double get stockProgress {
    if (minStock == 0) return 1.0;
    final maxStock = minStock * 3;
    return (stock / maxStock).clamp(0.0, 1.0);
  }

  Color get stockProgressColor {
    if (isOutOfStock) return Colors.red.shade700;
    if (isLowStock) return Colors.red.shade600;
    if (stock <= minStock * 1.5) return Colors.orange.shade600;
    return Colors.green.shade600;
  }

  int get daysUntilExpiry {
    if (apiDaysUntilExpiry != null) return apiDaysUntilExpiry!;
    final now = DateTime.now();
    return expiryDate.difference(now).inDays;
  }

  factory Product.fromJson(Map<String, dynamic> json) {
    // Función auxiliar para parsear fechas de manera segura
    DateTime? parseDate(dynamic dateValue) {
      if (dateValue == null) return null;
      try {
        if (dateValue is String) {
          return DateTime.parse(dateValue);
        }
        return null;
      } catch (e) {
        return null;
      }
    }

    // Función auxiliar para parsear números de manera segura
    double parseDouble(dynamic value, {double defaultValue = 0.0}) {
      if (value == null) return defaultValue;
      try {
        if (value is double) return value;
        if (value is int) return value.toDouble();
        if (value is String) {
          return double.parse(value);
        }
        return defaultValue;
      } catch (e) {
        return defaultValue;
      }
    }

    // Función auxiliar para parsear enteros de manera segura
    int parseInt(dynamic value, {int defaultValue = 0}) {
      if (value == null) return defaultValue;
      try {
        if (value is int) return value;
        if (value is double) return value.toInt();
        if (value is String) {
          return int.parse(value);
        }
        return defaultValue;
      } catch (e) {
        return defaultValue;
      }
    }

    // Extraer campos por separado
    String productName = json['nombre'] ?? json['name'] ?? '';
    String? concentration = json['concentracion']?.toString();
    String? presentation = json['presentacion']?.toString();
    
    // Debug prints para verificar los datos
    print('DEBUG Product fromJson:');
    print('  productName: $productName');
    print('  concentration: $concentration');
    print('  presentation: $presentation');
    print('  json[presentacion]: ${json['presentacion']}');
    print('  json keys: ${json.keys.toList()}');
    
    // Construir descripción solo con presentación si está disponible
    String? fullDescription;
    if (presentation != null && presentation.isNotEmpty) {
      fullDescription = 'Presentación: $presentation';
    } else {
      fullDescription = json['descripcion'] ?? json['description'];
    }

    return Product(
      id: json['id']?.toString() ?? '',
      name: productName,
      brand: json['marca'] ?? json['laboratorio'] ?? json['brand'] ?? json['laboratory'] ?? 'Sin laboratorio',
      description: fullDescription,
      stock: parseInt(json['stock_actual'] ?? json['stock']),
      minStock: parseInt(json['stock_minimo'] ?? json['minStock']),
      expiryDate: parseDate(json['fecha_vencimiento'] ?? json['expiryDate']) ?? DateTime.now().add(Duration(days: 365)),
      price: parseDouble(json['precio_venta'] ?? json['price']),
      costPrice: json['precio_compra'] != null || json['costPrice'] != null 
          ? parseDouble(json['precio_compra'] ?? json['costPrice']) 
          : null,
      barcode: json['codigo_barras'] ?? json['barcode'],
      category: json['categoria'] ?? json['category'],
      imageUrl: json['imagen_url'] ?? json['imageUrl'],
      batchNumber: json['lote'] ?? json['batchNumber'],
      laboratory: json['marca'] ?? json['laboratorio'] ?? json['laboratory'] ?? 'Sin laboratorio',
      location: json['ubicacion'] ?? json['location'],
      expirationDate: parseDate(json['fecha_vencimiento'] ?? json['expirationDate']),
      maxStock: json['stock_maximo'] != null ? parseInt(json['stock_maximo']) : null,
      supplier: json['proveedor'] ?? json['supplier'],
      isActive: json['activo'] ?? json['isActive'] ?? true,
      concentration: concentration,
      presentation: presentation,
      createdAt: parseDate(json['created_at'] ?? json['createdAt']) ?? DateTime.now(),
      updatedAt: parseDate(json['updated_at'] ?? json['updatedAt']) ?? DateTime.now(),
      // Campos calculados de la API
      apiIsLowStock: json['is_low_stock'],
      apiIsExpiringSoon: json['is_expiring_soon'],
      apiIsExpired: json['is_expired'],
      apiIsOutOfStock: json['is_out_of_stock'],
      apiStatus: json['status'],
      apiStatusText: json['status_text'],
      apiStatusColor: json['status_color'],
      apiDaysUntilExpiry: json['dias_para_vencer'] != null ? parseInt(json['dias_para_vencer']) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'brand': brand,
      'description': description,
      'stock': stock,
      'minStock': minStock,
      'expiryDate': expiryDate.toIso8601String(),
      'price': price,
      'costPrice': costPrice,
      'barcode': barcode,
      'category': category,
      'imageUrl': imageUrl,
      'batchNumber': batchNumber,
      'laboratory': laboratory,
      'location': location,
      'expirationDate': expirationDate?.toIso8601String(),
      'maxStock': maxStock,
      'supplier': supplier,
      'isActive': isActive,
      'createdAt': createdAt.toIso8601String(),
      'updatedAt': updatedAt.toIso8601String(),
    };
  }

  Product copyWith({
    String? id,
    String? name,
    String? brand,
    String? description,
    int? stock,
    int? minStock,
    DateTime? expiryDate,
    double? price,
    double? costPrice,
    String? barcode,
    String? category,
    String? imageUrl,
    String? batchNumber,
    String? laboratory,
    String? location,
    DateTime? expirationDate,
    int? maxStock,
    String? supplier,
    bool? isActive,
    String? concentration,
    String? presentation,
    DateTime? createdAt,
    DateTime? updatedAt,
    bool? apiIsLowStock,
    bool? apiIsExpiringSoon,
    bool? apiIsExpired,
    bool? apiIsOutOfStock,
    String? apiStatus,
    String? apiStatusText,
    String? apiStatusColor,
    int? apiDaysUntilExpiry,
  }) {
    return Product(
      id: id ?? this.id,
      name: name ?? this.name,
      brand: brand ?? this.brand,
      description: description ?? this.description,
      stock: stock ?? this.stock,
      minStock: minStock ?? this.minStock,
      expiryDate: expiryDate ?? this.expiryDate,
      price: price ?? this.price,
      costPrice: costPrice ?? this.costPrice,
      barcode: barcode ?? this.barcode,
      category: category ?? this.category,
      imageUrl: imageUrl ?? this.imageUrl,
      batchNumber: batchNumber ?? this.batchNumber,
      laboratory: laboratory ?? this.laboratory,
      location: location ?? this.location,
      expirationDate: expirationDate ?? this.expirationDate,
      maxStock: maxStock ?? this.maxStock,
      supplier: supplier ?? this.supplier,
      isActive: isActive ?? this.isActive,
      concentration: concentration ?? this.concentration,
      presentation: presentation ?? this.presentation,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      apiIsLowStock: apiIsLowStock ?? this.apiIsLowStock,
      apiIsExpiringSoon: apiIsExpiringSoon ?? this.apiIsExpiringSoon,
      apiIsExpired: apiIsExpired ?? this.apiIsExpired,
      apiIsOutOfStock: apiIsOutOfStock ?? this.apiIsOutOfStock,
      apiStatus: apiStatus ?? this.apiStatus,
      apiStatusText: apiStatusText ?? this.apiStatusText,
      apiStatusColor: apiStatusColor ?? this.apiStatusColor,
      apiDaysUntilExpiry: apiDaysUntilExpiry ?? this.apiDaysUntilExpiry,
    );
  }

  @override
  String toString() {
    return 'Product(id: $id, name: $name, brand: $brand, stock: $stock)';
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is Product && other.id == id;
  }

  @override
  int get hashCode => id.hashCode;
}