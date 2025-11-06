# 📱 Plan de Reestructuración - Dos Aplicaciones Móviles

## 🎯 Objetivo
Dividir la aplicación actual en dos aplicaciones especializadas para optimizar la experiencia del usuario y las funcionalidades.

---

## 📊 APP 1: BOTICA MANAGER (Gestión de Inventario)

### 🏗️ Estructura de Carpetas Propuesta
```
lib/
├── core/
│   ├── config/
│   ├── constants/
│   ├── utils/
│   └── theme/
├── data/
│   ├── models/
│   │   ├── product.dart
│   │   ├── category.dart
│   │   ├── supplier.dart
│   │   ├── location.dart
│   │   ├── stock_movement.dart
│   │   └── purchase_order.dart
│   ├── repositories/
│   │   ├── product_repository.dart
│   │   ├── inventory_repository.dart
│   │   └── purchase_repository.dart
│   └── services/
│       ├── api_service.dart
│       ├── barcode_service.dart
│       ├── sync_service.dart
│       └── notification_service.dart
├── domain/
│   ├── entities/
│   ├── repositories/
│   └── usecases/
│       ├── manage_products.dart
│       ├── control_stock.dart
│       └── handle_purchases.dart
└── presentation/
    ├── screens/
    │   ├── inventory/
    │   │   ├── product_list_screen.dart
    │   │   ├── product_detail_screen.dart
    │   │   ├── add_product_screen.dart
    │   │   ├── edit_product_screen.dart
    │   │   └── stock_adjustment_screen.dart
    │   ├── purchases/
    │   │   ├── purchase_orders_screen.dart
    │   │   ├── create_order_screen.dart
    │   │   └── receive_order_screen.dart
    │   ├── locations/
    │   │   ├── locations_screen.dart
    │   │   └── assign_location_screen.dart
    │   ├── reports/
    │   │   ├── inventory_report_screen.dart
    │   │   ├── expiry_report_screen.dart
    │   │   └── movement_report_screen.dart
    │   └── scanner/
    │       ├── barcode_scanner_screen.dart
    │       └── batch_scanner_screen.dart
    ├── widgets/
    └── providers/
```

### 🔧 Funcionalidades Específicas

#### 1. **Gestión de Productos**
- ✅ CRUD completo de productos
- ✅ Escáner de códigos de barras
- ✅ Gestión de imágenes
- ✅ Control de categorías
- ✅ Gestión de proveedores

#### 2. **Control de Inventario**
- ✅ Visualización de stock actual
- ✅ Alertas de stock bajo
- ✅ Control de fechas de vencimiento
- ✅ Movimientos de inventario
- ✅ Ajustes de stock

#### 3. **Gestión de Compras**
- 🆕 Órdenes de compra
- 🆕 Recepción de mercancía
- 🆕 Gestión de proveedores
- 🆕 Historial de compras

#### 4. **Ubicaciones Físicas**
- 🆕 Gestión de ubicaciones
- 🆕 Asignación de productos
- 🆕 Mapeo de almacén
- 🆕 Optimización de espacios

---

## 🤖 APP 2: BOTICA AI ASSISTANT (Inteligencia Artificial)

### 🏗️ Estructura de Carpetas Propuesta
```
lib/
├── core/
│   ├── config/
│   ├── constants/
│   ├── utils/
│   └── ai/
│       ├── ml_models/
│       ├── prediction_engine.dart
│       └── analytics_engine.dart
├── data/
│   ├── models/
│   │   ├── prediction.dart
│   │   ├── alert.dart
│   │   ├── analytics.dart
│   │   ├── trend.dart
│   │   └── recommendation.dart
│   ├── repositories/
│   │   ├── analytics_repository.dart
│   │   ├── prediction_repository.dart
│   │   └── alert_repository.dart
│   └── services/
│       ├── ai_service.dart
│       ├── prediction_service.dart
│       ├── analytics_service.dart
│       └── recommendation_service.dart
├── domain/
│   ├── entities/
│   ├── repositories/
│   └── usecases/
│       ├── generate_predictions.dart
│       ├── analyze_trends.dart
│       ├── create_recommendations.dart
│       └── manage_alerts.dart
└── presentation/
    ├── screens/
    │   ├── dashboard/
    │   │   ├── ai_dashboard_screen.dart
    │   │   ├── kpi_overview_screen.dart
    │   │   └── executive_summary_screen.dart
    │   ├── predictions/
    │   │   ├── demand_prediction_screen.dart
    │   │   ├── seasonal_analysis_screen.dart
    │   │   └── stock_optimization_screen.dart
    │   ├── analytics/
    │   │   ├── sales_analytics_screen.dart
    │   │   ├── product_performance_screen.dart
    │   │   └── trend_analysis_screen.dart
    │   ├── alerts/
    │   │   ├── smart_alerts_screen.dart
    │   │   ├── alert_configuration_screen.dart
    │   │   └── alert_history_screen.dart
    │   └── recommendations/
    │       ├── purchase_recommendations_screen.dart
    │       ├── pricing_suggestions_screen.dart
    │       └── promotion_ideas_screen.dart
    ├── widgets/
    └── providers/
```

### 🧠 Funcionalidades de IA

#### 1. **Predicciones Inteligentes**
- 🆕 Predicción de demanda
- 🆕 Análisis estacional
- 🆕 Optimización de stock
- 🆕 Predicción de vencimientos

#### 2. **Análisis Avanzado**
- 🆕 Análisis de tendencias de venta
- 🆕 Rendimiento por producto
- 🆕 Análisis de rotación
- 🆕 Patrones de comportamiento

#### 3. **Recomendaciones**
- 🆕 Sugerencias de compra
- 🆕 Optimización de precios
- 🆕 Ideas de promociones
- 🆕 Estrategias de marketing

#### 4. **Alertas Inteligentes**
- ✅ Alertas predictivas (mejoradas)
- 🆕 Configuración personalizada
- 🆕 Historial de alertas
- 🆕 Análisis de impacto

---

## 🔄 Migración de Código Actual

### **Código a Mantener en BOTICA MANAGER:**
- ✅ `inventory_screens.dart` (base)
- ✅ `scan_product_screen.dart`
- ✅ `add_product_barcode_screen.dart`
- ✅ `edit_product_screen.dart`
- ✅ `adjust_stock_screen.dart`
- ✅ Servicios de productos y categorías

### **Código a Migrar a BOTICA AI:**
- ✅ `ai_alerts_screen.dart`
- ✅ `smart_alerts_screen.dart`
- ✅ `smart_alerts_service.dart`
- ✅ Funcionalidades de reportes avanzados

### **Código Compartido:**
- ✅ Modelos base (Product, User, etc.)
- ✅ Servicios de autenticación
- ✅ Configuración de API
- ✅ Temas y estilos

---

## 📋 Cronograma de Implementación

### **Semana 1-2: Preparación**
- [ ] Análisis detallado del código actual
- [ ] Definición de APIs compartidas
- [ ] Configuración de proyectos separados

### **Semana 3-4: BOTICA MANAGER**
- [ ] Migración de funcionalidades de inventario
- [ ] Implementación de gestión de compras
- [ ] Mejoras en escáner y productos

### **Semana 5-6: BOTICA AI**
- [ ] Migración de funcionalidades de IA
- [ ] Implementación de nuevas predicciones
- [ ] Dashboard ejecutivo

### **Semana 7-8: Integración y Testing**
- [ ] Pruebas de ambas aplicaciones
- [ ] Sincronización de datos
- [ ] Optimización de rendimiento

---

## 🔧 Tecnologías y Dependencias

### **Dependencias Compartidas:**
```yaml
dependencies:
  flutter: sdk
  http: ^1.2.0
  shared_preferences: ^2.2.2
  flutter_localizations: sdk
```

### **BOTICA MANAGER Específicas:**
```yaml
dependencies:
  mobile_scanner: ^3.5.6
  image_picker: ^1.0.7
  sqflite: ^2.3.0
  excel: ^2.1.0
  printing: ^5.12.0
```

### **BOTICA AI Específicas:**
```yaml
dependencies:
  fl_chart: ^0.67.0
  ml_kit: ^0.17.0
  tensorflow_lite_flutter: ^0.10.0
  charts_flutter: ^0.12.0
```

---

## 🎯 Beneficios Esperados

### **Para BOTICA MANAGER:**
- ⚡ Rendimiento optimizado para operaciones
- 🎯 Interfaz especializada en inventario
- 📱 Uso offline mejorado
- 🔄 Sincronización eficiente

### **Para BOTICA AI:**
- 🧠 Análisis más profundos
- 📊 Visualizaciones avanzadas
- 🤖 Predicciones más precisas
- 📈 Insights de negocio

### **Generales:**
- 👥 Mejor experiencia de usuario
- 🚀 Desarrollo más ágil
- 🔧 Mantenimiento simplificado
- 📱 Apps más ligeras y rápidas

---

## 🚀 Próximos Pasos Inmediatos

1. **Validar el plan** con el equipo
2. **Configurar repositorios** separados
3. **Definir APIs** compartidas
4. **Comenzar migración** gradual
5. **Establecer pipeline** de CI/CD

---

**📅 Fecha de creación**: Enero 2025  
**👨‍💻 Responsable**: Equipo de Desarrollo Móvil  
**🎯 Objetivo**: Optimizar experiencia y funcionalidades