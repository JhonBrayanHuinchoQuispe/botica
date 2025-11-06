import 'package:flutter/material.dart';
import '../../../core/config/theme.dart';
import 'package:intl/intl.dart';

class AIAlertsScreen extends StatefulWidget {
  const AIAlertsScreen({super.key});

  @override
  State<AIAlertsScreen> createState() => _AIAlertsScreenState();
}

class _AIAlertsScreenState extends State<AIAlertsScreen> with SingleTickerProviderStateMixin {
  String _selectedFilter = 'all';
  bool _isLoading = false;
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  String? _expandedAlertId;

  // Datos de ejemplo para las recomendaciones inteligentes
  final List<Map<String, dynamic>> _alerts = [
    {
      'id': '1',
      'title': 'Paracetamol 500mg',
      'subtitle': 'Próximo a vencer',
      'description': 'Vence en 6 días - Stock: 15 unidades',
      'suggestion': 'Aplicar descuento del 30% para acelerar la rotación y evitar pérdidas por vencimiento',
      'explanation': 'El producto está próximo a vencer y tiene un stock considerable. Un descuento estratégico puede convertir una pérdida potencial en una venta con margen reducido pero positivo.',
      'urgency': 'high',
      'type': 'discount',
      'action': 'Aplicar Descuento',
      'icon': Icons.local_offer_rounded,
      'color': const Color(0xFFFF4757),
      'bgColor': const Color(0xFFFFF5F5),
      'confidence': '92%',
      'impact': 'Alto',
    },
    {
      'id': '2',
      'title': 'Vitamina C 500mg',
      'subtitle': 'Baja rotación',
      'description': 'Sin movimiento por 65 días',
      'suggestion': 'Crear combo con Vitamina D y Zinc para impulsar las ventas con un margen del 35%',
      'explanation': 'Los productos con baja rotación pueden beneficiarse de estrategias de venta cruzada. La combinación con vitaminas complementarias es atractiva para los clientes.',
      'urgency': 'medium',
      'type': 'cross_sell',
      'action': 'Crear Combo',
      'icon': Icons.group_work_rounded,
      'color': const Color(0xFF4CAF50),
      'bgColor': const Color(0xFFF1F8E9),
      'confidence': '85%',
        'impact': 'Medio',
    },
    {
      'id': '3',
      'title': 'Antigripales',
      'subtitle': 'Predicción estacional',
      'description': 'Aumento esperado del 45%',
      'suggestion': 'Incrementar stock para la temporada de invierno basado en patrones históricos',
      'explanation': 'El análisis de datos históricos muestra un incremento significativo en la demanda de antigripales durante los meses de invierno. Es recomendable aumentar el inventario.',
      'urgency': 'high',
      'type': 'stock',
      'action': 'Comprar Stock',
      'icon': Icons.trending_up_rounded,
      'color': const Color(0xFF2196F3),
      'bgColor': const Color(0xFFE3F2FD),
      'confidence': '78%',
        'impact': 'Alto',
    },
    {
      'id': '4',
      'title': 'Ibuprofeno 400mg',
      'subtitle': 'Stock crítico',
      'description': 'Solo 3 unidades - Se venden 8 al día',
      'suggestion': 'Comprar 100 unidades urgente del proveedor A que tiene mejor precio y entrega inmediata',
      'explanation': 'El stock actual es insuficiente para cubrir la demanda diaria. Es crítico realizar una compra urgente para evitar desabastecimiento.',
      'urgency': 'critical',
      'type': 'stock',
      'action': 'Comprar Ahora',
      'icon': Icons.shopping_cart_rounded,
      'color': const Color(0xFFE74C3C),
      'bgColor': const Color(0xFFFFF5F5),
      'confidence': '70%',
        'impact': 'Medio',
    },
    {
      'id': '5',
      'title': 'Productos de higiene',
      'subtitle': 'Optimización de ubicación',
      'description': 'Zona de baja visibilidad',
      'suggestion': 'Reubicar al estante principal para aumentar la visibilidad y las ventas en un 25%',
      'explanation': 'La ubicación actual de estos productos reduce su visibilidad. Moverlos a una zona de mayor tráfico puede incrementar significativamente las ventas.',
      'urgency': 'medium',
      'type': 'layout',
      'action': 'Reubicar',
      'icon': Icons.store_rounded,
      'color': const Color(0xFF9C27B0),
      'bgColor': const Color(0xFFF3E5F5),
      'confidence': '82%',
        'impact': 'Alto',
    },
    {
      'id': '6',
      'title': 'Kit Cuidado Personal',
      'subtitle': 'Oportunidad de bundle',
      'description': 'Productos frecuentemente comprados juntos',
      'suggestion': 'Crear bundle con Shampoo, Acondicionador y Jabón antibacterial',
      'explanation': 'El análisis de patrones de compra muestra que estos productos se adquieren frecuentemente en conjunto. Un bundle puede aumentar el ticket promedio.',
      'urgency': 'low',
      'type': 'bundle',
      'action': 'Crear Bundle',
      'icon': Icons.child_care_rounded,
      'color': const Color(0xFFFF9800),
      'bgColor': const Color(0xFFFFF3E0),
      'confidence': '88%',
        'impact': 'Alto',
    },
  ];

  // Simula la fecha de última actualización (en producción, esto vendría de la base de datos o backend)
  final DateTime _lastUpdate = DateTime.now();

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeOut),
    );
    _animationController.forward();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  // Función para obtener el conteo de cada tipo
  int _getFilterCount(String filterType) {
    if (filterType == 'all') return _alerts.length;
    return _alerts.where((alert) => alert['type'] == filterType).length;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: FadeTransition(
        opacity: _fadeAnimation,
        child: CustomScrollView(
          slivers: [
            _buildAppBar(),
            SliverToBoxAdapter(
              child: Column(
                children: [
                  // Indicador de última actualización
                  Padding(
                    padding: const EdgeInsets.only(top: 8, bottom: 0),
                    child: Text(
                      'Última actualización: ${DateFormat('dd/MM/yyyy').format(_lastUpdate)}',
                      style: const TextStyle(
                        color: Colors.grey,
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                  _buildFilterTabs(),
                  _buildAlertsList(),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAppBar() {
    return SliverAppBar(
      expandedHeight: 90, // Reducido de 120 a 100
      floating: false,
      pinned: false,
      backgroundColor: const Color(0xFFF8FAFC),
      elevation: 0,
      flexibleSpace: FlexibleSpaceBar(
        background: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [
                Color(0xFFE74C3C),
                Color(0xFFFF4757),
              ],
            ),
            borderRadius: BorderRadius.only(
              bottomLeft: Radius.circular(24),
              bottomRight: Radius.circular(24),
            ),
          ),
          child: SafeArea(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 16), // Reducido padding vertical
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(
                          Icons.psychology_rounded,
                          color: Colors.white,
                          size: 24,
                        ),
                      ),
                      const SizedBox(width: 12),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Asistente IA',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 18,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            Text(
                              'Recomendaciones inteligentes',
                              style: TextStyle(
                                color: Colors.white70,
                                fontSize: 14,
                              ),
                            ),
                          ],
                        ),
                      ),
                      _buildRefreshButton(),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildRefreshButton() {
    // espaciado para arriba
    
    return Container(
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.2),
        borderRadius: BorderRadius.circular(12),
      ),
      child: IconButton(
        icon: _isLoading
            ? const SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                ),
              )
            : const Icon(Icons.refresh_rounded, color: Colors.white),
        onPressed: _isLoading ? null : _refreshData,
      ),
    );
  }

  Widget _buildFilterTabs() {
    final filters = [
      {
        'label': 'Todos',
        'value': 'all',
        'icon': Icons.all_inclusive,
        'color': const Color(0xFF2196F3), // Azul
      },
      {
        'label': 'Descuentos',
        'value': 'discount',
        'icon': Icons.local_offer_rounded,
        'color': const Color(0xFFFF4757), // Rojo
      },
      {
        'label': 'Stock',
        'value': 'stock',
        'icon': Icons.inventory_2_rounded,
        'color': const Color(0xFFD32F2F), // Rojo oscuro para stock crítico
      },
      {
        'label': 'Combos',
        'value': 'cross_sell',
        'icon': Icons.group_work_rounded,
        'color': const Color(0xFF4CAF50), // Verde
      },
      {
        'label': 'Ubicación',
        'value': 'layout',
        'icon': Icons.store_rounded,
        'color': const Color(0xFF9C27B0), // Púrpura
      },
    ];

    return Container(
      margin: const EdgeInsets.fromLTRB(16, 18, 16, 5), // Menos espacio abajo
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: filters.map((filter) {
            final isSelected = _selectedFilter == filter['value'];
            final count = _getFilterCount(filter['value'] as String);
            final color = filter['color'] as Color;
            return Padding(
              padding: const EdgeInsets.only(right: 12),
              child: Stack(
                clipBehavior: Clip.none,
                children: [
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    child: Material(
                      color: Colors.transparent,
                      child: InkWell(
                        borderRadius: BorderRadius.circular(25),
                        onTap: () => setState(() => _selectedFilter = filter['value'] as String),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                          decoration: BoxDecoration(
                            color: isSelected ? color : Colors.white,
                            borderRadius: BorderRadius.circular(25),
                            border: Border.all(
                              color: isSelected ? color : Colors.grey.shade200,
                              width: 2,
                            ),
                            boxShadow: isSelected ? [
                              BoxShadow(
                                color: color.withOpacity(0.3),
                                blurRadius: 15,
                                offset: const Offset(0, 6),
                              ),
                            ] : [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.05),
                                blurRadius: 10,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                filter['icon'] as IconData,
                                color: isSelected ? Colors.white : color,
                                size: 18,
                              ),
                              const SizedBox(width: 8),
                              Text(
                                filter['label'] as String,
                                style: TextStyle(
                                  color: isSelected ? Colors.white : color,
                                  fontWeight: FontWeight.w600,
                                  fontSize: 14,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                  // Badge contador
                  if (count > 0)
                    Positioned(
                      top: -5,
                      right: -8,
                      child: Container(
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(
                          color: isSelected ? Colors.white : color,
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: isSelected ? color : Colors.white,
                            width: 2,
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: color.withOpacity(0.3),
                              blurRadius: 8,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Text(
                          count.toString(),
                          style: TextStyle(
                            color: isSelected ? color : Colors.white,
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  Widget _buildAlertsList() {
    final filteredAlerts = _selectedFilter == 'all'
        ? _alerts
        : _alerts.where((alert) => alert['type'] == _selectedFilter).toList();

    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: filteredAlerts.map((alert) => _buildAlertCard(alert)).toList(),
      ),
    );
  }

  // Cambiar el diseño de la card de alerta para que el borde izquierdo sea como en inventario
  Widget _buildAlertCard(Map<String, dynamic> alert) {
    final isExpanded = _expandedAlertId == alert['id'];
    Color getBorderColor(Map<String, dynamic> alert) {
      switch (alert['type']) {
        case 'discount':
          return const Color(0xFFFF4757); // Rojo
        case 'pricing':
          return const Color(0xFF2196F3); // Azul
        case 'stock':
          if (alert['urgency'] == 'critical') {
            return const Color(0xFFD32F2F); // Rojo oscuro para stock crítico
          } else {
            return const Color(0xFFE74C3C); // Rojo para stock bajo
          }
        case 'cross_sell':
          return const Color(0xFF4CAF50); // Verde
        case 'layout':
          return const Color(0xFF9C27B0); // Púrpura
        case 'bundle':
          return const Color(0xFFFF9800); // Naranja
        case 'competitive':
          return const Color(0xFF607D8B); // Gris azulado
        case 'liquidation':
          return const Color(0xFFFF5722); // Naranja rojizo
        default:
          return const Color(0xFF6C63FF);
      }
    }

    final borderColor = getBorderColor(alert);

    return GestureDetector(
      onTap: () {
        setState(() {
          _expandedAlertId = isExpanded ? null : alert['id'];
        });
      },
      child: Container(
        width: double.infinity,
        margin: const EdgeInsets.only(bottom: 8),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border(
            left: BorderSide(
              color: borderColor,
              width: 4,
            ),
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Fila: badge a la izquierda, icono decorativo a la derecha
              Row(
                children: [
                  // Badge del tipo de alerta
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: borderColor.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: borderColor.withOpacity(0.3),
                        width: 1,
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 6,
                          height: 6,
                          decoration: BoxDecoration(
                            color: borderColor,
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 6),
                        Text(
                          alert['subtitle'] ?? '',
                          style: TextStyle(
                            color: borderColor,
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Spacer(),
                  // Icono decorativo a la derecha
                  Icon(
                    alert['icon'] as IconData,
                    size: 24,
                    color: borderColor.withOpacity(0.4),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              // Nombre del producto (debajo de la fila)
              Text(
                alert['title'] ?? '',
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Colors.black87,
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 6),
              Text(
                alert['description'] ?? '',
                style: TextStyle(fontSize: 14, color: Colors.grey[600]),
              ),
              const SizedBox(height: 10),
              // Métricas de la recomendación
              Row(
                children: [
                  if (alert['confidence'] != null) ...[
                    _buildMetricChip(
                      'Confianza: ${alert['confidence']}',
                      Icons.psychology_rounded,
                      borderColor.withOpacity(0.8),
                    ),
                    const SizedBox(width: 8),
                  ],
                  if (alert['impact'] != null)
                    _buildMetricChip(
                      'Impacto: ${alert['impact']}',
                      Icons.trending_up_rounded,
                      _getImpactColor(alert['impact']),
                    ),
                ],
              ),
              if (isExpanded) ...[
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.grey[200]!),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        alert['icon'] as IconData,
                        color: borderColor,
                        size: 18,
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          alert['suggestion'] ?? '',
                          style: TextStyle(
                            color: borderColor,
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                // Explicabilidad de la IA
                if (alert['explanation'] != null && (alert['explanation'] as String).isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE3F2FD),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(Icons.info_outline_rounded, color: Color(0xFF1976D2), size: 18),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            alert['explanation'],
                            style: const TextStyle(
                              color: Color(0xFF1976D2),
                              fontSize: 13,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
                const SizedBox(height: 16),
                Row(
                  children: [
                    // Botón Ignorar (izquierda)
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => _handleDismiss(alert),
                        icon: const Icon(Icons.close_rounded, size: 18),
                        label: const Text(
                          'Ignorar',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
                        ),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Colors.grey[700],
                          side: BorderSide(color: Colors.grey[300]!, width: 1.5),
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    // Botón acción principal (derecha)
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () => _handleApply(alert),
                        icon: Icon(
                          _getActionIcon(alert['action']),
                          color: Colors.white,
                          size: 18,
                        ),
                        label: Text(
                          alert['action'] ?? '',
                          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: _getActionColor(alert['type'], alert['urgency']),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          elevation: 2,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ]
            ],
          ),
        ),
      ),
    );
  }

  void _handleApply(Map<String, dynamic> alert) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Aplicando: ${alert['title']}'),
        backgroundColor: alert['color'],
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        margin: const EdgeInsets.all(16),
      ),
    );
  }

  void _handleDismiss(Map<String, dynamic> alert) {
    setState(() {
      _alerts.removeWhere((a) => a['id'] == alert['id']);
    });
    
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Alerta ignorada: ${alert['title']}'),
        backgroundColor: Colors.grey[600],
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        margin: const EdgeInsets.all(16),
        action: SnackBarAction(
          label: 'Deshacer',
          textColor: Colors.white,
          onPressed: () {
            setState(() {
              // Restaurar la alerta (en producción guardarías el estado)
            });
          },
        ),
      ),
    );
  }

  Future<void> _refreshData() async {
    setState(() => _isLoading = true);
    
    // Simular carga de datos
    await Future.delayed(const Duration(seconds: 1));
    
    setState(() => _isLoading = false);
    
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Alertas actualizadas'),
        behavior: SnackBarBehavior.floating,
        margin: EdgeInsets.all(16),
        duration: Duration(seconds: 2),
      ),
    );
  }

  IconData _getActionIcon(String? action) {
    switch (action) {
      case 'Aplicar Descuento':
        return Icons.local_offer_rounded;
      case 'Ajustar Precio':
        return Icons.price_change_rounded;
      case 'Crear Combo':
        return Icons.group_work_rounded;
      case 'Crear Bundle':
        return Icons.inventory_2_rounded;
      case 'Reubicar':
        return Icons.store_rounded;
      case 'Comprar Ahora':
        return Icons.shopping_cart_rounded;
      case 'Liquidar':
        return Icons.schedule_rounded;
      default:
        return Icons.check_circle_rounded;
    }
  }

  Color _getActionColor(String? type, [String? urgency]) {
    switch (type) {
      case 'discount':
        return const Color(0xFFFF4757); // Rojo
      case 'pricing':
        return const Color(0xFF2196F3); // Azul
      case 'stock':
        if (urgency == 'critical') {
          return const Color(0xFFD32F2F); // Rojo oscuro para stock crítico
        } else {
          return const Color(0xFFE74C3C); // Rojo para stock bajo
        }
      case 'cross_sell':
        return const Color(0xFF4CAF50); // Verde
      case 'layout':
        return const Color(0xFF9C27B0); // Púrpura
      case 'bundle':
        return const Color(0xFFFF9800); // Naranja
      case 'competitive':
        return const Color(0xFF607D8B); // Gris azulado
      case 'liquidation':
        return const Color(0xFFFF5722); // Naranja rojizo
      default:
        return Colors.blueGrey;
    }
  }

  Widget _buildMetricChip(String text, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withOpacity(0.3), width: 1),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: color),
          const SizedBox(width: 4),
          Text(
            text,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  Color _getImpactColor(String impact) {
    switch (impact.toLowerCase()) {
      case 'alto':
        return const Color(0xFFE74C3C); // Rojo
      case 'crítico':
        return const Color(0xFFD32F2F); // Rojo oscuro
      case 'medio':
        return const Color(0xFFFF9800); // Naranja
      case 'bajo':
        return const Color(0xFF4CAF50); // Verde
      default:
        return const Color(0xFF607D8B); // Gris
    }
  }
}