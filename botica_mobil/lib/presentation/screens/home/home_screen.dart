import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:intl/intl.dart';
import '../../../core/config/theme.dart';
import '../inventory/inventory_screens.dart';
import '../auth/login_screen.dart';
import '../ai_alerts/ai_alerts_screen.dart';
import '../settings/settings_screens.dart';
import '../layout/header.dart';
import 'package:flutter/widgets.dart';

import '../../../data/services/dashboard_service.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _selectedIndex = 0;
  final String userName = "Brayan";
  final DashboardService _dashboardService = DashboardService();
  
  // Datos del dashboard que se cargarán desde el backend
  Map<String, dynamic> _dashboardData = {};
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadDashboardData();
  }

  // Cargar datos reales del dashboard desde Laravel
  Future<void> _loadDashboardData() async {
    try {
      setState(() => _isLoading = true);
      final data = await _dashboardService.getDashboardData();
      setState(() {
        _dashboardData = data;
        _isLoading = false;
      });
    } catch (e) {
      print('Error cargando datos del dashboard: $e');
      setState(() => _isLoading = false);
      // Los datos por defecto ya están en el servicio
    }
  }

  List<Widget> get _screens => [
    HomeContent(
      dashboardData: _dashboardData,
      isLoading: _isLoading,
      onRefresh: _loadDashboardData,
    ),
    const InventoryScreen(),
    const AIAlertsScreen(),
    const SettingsScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: _selectedIndex == 1 || _selectedIndex == 3
          ? null 
          : BarraCompartida(
              titulo: _selectedIndex == 0 ? 'Inicio' : 'Asistente IA',
            ),
      body: _screens[_selectedIndex],
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 20,
              offset: const Offset(0, -5),
            ),
          ],
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
            child: BottomNavigationBar(
              currentIndex: _selectedIndex,
              onTap: (index) => setState(() => _selectedIndex = index),
              type: BottomNavigationBarType.fixed,
              backgroundColor: Colors.transparent,
              selectedItemColor: const Color(0xFFE53E3E),
              unselectedItemColor: const Color(0xFF8B8B8B),
              selectedLabelStyle: const TextStyle(
                fontWeight: FontWeight.w600,
                fontSize: 12,
              ),
              unselectedLabelStyle: const TextStyle(
                fontWeight: FontWeight.w600,
                fontSize: 12,
              ),
              elevation: 0,
              items: const [
                BottomNavigationBarItem(
                  icon: Icon(Icons.home_outlined, size: 26),
                  activeIcon: Icon(Icons.home, size: 26),
                  label: 'Inicio',
                ),
                BottomNavigationBarItem(
                  icon: Icon(Icons.inventory_outlined, size: 26),
                  activeIcon: Icon(Icons.inventory, size: 26),
                  label: 'Control Stock',
                ),
                BottomNavigationBarItem(
                  icon: Icon(Icons.psychology_outlined, size: 26),  
                  activeIcon: Icon(Icons.psychology, size: 26),     
                  label: 'Asistente IA',
                ),
                BottomNavigationBarItem(
                  icon: Icon(Icons.settings_outlined, size: 26),
                  activeIcon: Icon(Icons.settings, size: 26),
                  label: 'Ajustes',
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }


}

class HomeContent extends StatelessWidget {
  final Map<String, dynamic> dashboardData;
  final bool isLoading;
  final VoidCallback onRefresh;

  const HomeContent({
    super.key,
    required this.dashboardData,
    required this.isLoading,
    required this.onRefresh,
  });

  List<String> _getNextFourMonths() {
    final now = DateTime.now();
    final months = <String>[];
    for (var i = 0; i < 4; i++) {
      final month = DateTime(now.year, now.month + i);
      months.add(DateFormat('MMM').format(month).toUpperCase());
    }
    return months;
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return const Center(
        child: CircularProgressIndicator(
          valueColor: AlwaysStoppedAnimation<Color>(Color(0xFFE53E3E)),
        ),
      );
    }

    final nextFourMonths = _getNextFourMonths();
    final values = [15, 7, 32, 40]; // Valores de vencimientos
    final int maxValue = ((values.reduce((a, b) => a > b ? a : b) / 10).ceil() * 10) + 10;
    
    return RefreshIndicator(
      onRefresh: () async => onRefresh(),
      color: const Color(0xFFE53E3E),
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                height: 205,
                child: GridView.count(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  crossAxisCount: 2,
                  crossAxisSpacing: 16,
                  mainAxisSpacing: 16,
                  childAspectRatio: 1.7,
                  children: [
                    _buildSummaryCard(
                      title: 'Ventas Diarias',
                      value: 'S/ ${(dashboardData['dailySales'] ?? 0.0).toStringAsFixed(2)}',
                      icon: Icons.attach_money_rounded,
                      gradient: LinearGradient(
                        colors: [
                          const Color(0xFF2196F3).withOpacity(0.7),
                          const Color(0xFF1976D2).withOpacity(0.7),
                        ],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      trend: 'Hoy',
                      isPositiveTrend: true,
                    ),
                    _buildSummaryCard(
                      title: 'Productos Bajos',
                      value: '${dashboardData['lowStockProducts'] ?? 0}',
                      icon: Icons.inventory_2_outlined,
                      gradient: LinearGradient(
                        colors: [
                          const Color(0xFFFF5252).withOpacity(0.7),
                          const Color(0xFFD32F2F).withOpacity(0.7),
                        ],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      trend: 'Stock',
                      isPositiveTrend: false,
                    ),
                    _buildSummaryCard(
                      title: 'Próx. Vencer',
                      value: '${dashboardData['expiringProducts'] ?? 0}',
                      icon: Icons.calendar_today,
                      gradient: LinearGradient(
                        colors: [
                          const Color(0xFFFF9800).withOpacity(0.7),
                          const Color(0xFFF57C00).withOpacity(0.7),
                        ],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      trend: 'Productos',
                      isPositiveTrend: false,
                    ),
                    _buildSummaryCard(
                      title: 'Ventas Total',
                      value: 'S/ ${(dashboardData['totalSales'] ?? 0.0).toStringAsFixed(2)}',
                      icon: Icons.trending_up,
                      gradient: LinearGradient(
                        colors: [
                          const Color(0xFFE53E3E).withOpacity(0.7),
                          const Color(0xFFAB2B2B).withOpacity(0.7),
                        ],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      trend: 'Mes',
                      isPositiveTrend: true,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              _buildChartSection(
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Padding(
                        padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                        child: Text(
                          'Top 3 Productos Más Vendidos',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: Colors.black,
                          ),
                        ),
                      ),
                      _buildPieChart(),
                      ..._buildTopProductsList(),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                      child: Text(
                        'Notificaciones Importantes',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Colors.black,
                        ),
                      ),
                    ),
                    ..._buildRecentAlertsList(),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSummaryCard({
    required String title,
    required String value,
    required IconData icon,
    required LinearGradient gradient,
    required String trend,
    required bool isPositiveTrend,
  }) {
    return Container(
      decoration: BoxDecoration(
        gradient: gradient,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(12.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                Icon(icon, color: Colors.white, size: 20),
              ],
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  value,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Icon(
                      isPositiveTrend ? Icons.trending_up : Icons.trending_down,
                      color: Colors.white,
                      size: 14,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      trend,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.w300,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildChartSection({
    String? title,
    required Widget child,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (title != null) ...[
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 8.0),
            child: Text(
              title,
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: AppTheme.darkGray,
              ),
            ),
          ),
          const SizedBox(height: 16),
        ],
        child,
      ],
    );
  }

  Widget _buildProductRankingItem({
    required int position,
    required String productName,
    required int units,
    required double revenue,
    required Color color,
    IconData? icon,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
      child: Row(
        children: [
          // Ícono de posición
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              shape: BoxShape.circle,
            ),
            child: Center(
              child: icon != null
                ? Icon(icon, color: color, size: 24)
                : Text(
                    '$position°',
                    style: TextStyle(
                      color: color,
                      fontWeight: FontWeight.bold,
                      fontSize: 16,
                    ),
                  ),
            ),
          ),
          
          const SizedBox(width: 16),
          
          // Información del producto
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  productName,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Unidades: $units',
                  style: const TextStyle(
                    fontSize: 12,
                    color: Colors.grey,
                  ),
                ),
              ],
            ),
          ),
          
          // Ganancia
          Text(
            'S/ ${revenue.toStringAsFixed(2)}',
            style: TextStyle(
              fontSize: 14,
              color: color,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildNotificationItem({
    required IconData icon,
    required Color color,
    required String title,
    required String description,
    required String time,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: color.withOpacity(0.4),
              shape: BoxShape.circle,
            ),
            child: Center(
              child: Icon(icon, color: color, size: 24),
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  description,
                  style: const TextStyle(
                    fontSize: 12,
                    color: Colors.grey,
                  ),
                ),
              ],
            ),
          ),
          Text(
            time,
            style: const TextStyle(
              fontSize: 12,
              color: Colors.grey,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPieChart() {
    List<dynamic> topProducts = dashboardData['topSellingProducts'] ?? [];
    
    if (topProducts.isEmpty) {
      return const SizedBox(
        height: 200,
        child: Center(
          child: Text(
            'No hay datos disponibles',
            style: TextStyle(color: Colors.grey),
          ),
        ),
      );
    }

    // Calcular el total de ventas para obtener porcentajes
    double totalSales = topProducts.fold(0.0, (sum, product) => sum + (product['sales']?.toDouble() ?? 0.0));
    
    List<PieChartSectionData> sections = [];
    List<Color> colors = [
      const Color(0xFFC53030),
      const Color(0xFFED4F4F),
      const Color(0xFFFFA5A5),
    ];

    for (int i = 0; i < topProducts.length && i < 3; i++) {
      double value = (topProducts[i]['sales']?.toDouble() ?? 0.0);
      double percentage = totalSales > 0 ? (value / totalSales) * 100 : 0;
      
      sections.add(
        PieChartSectionData(
          color: colors[i],
          value: percentage,
          title: '${percentage.toStringAsFixed(0)}%',
          radius: 50,
          titleStyle: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
      );
    }

    return SizedBox(
      height: 200,
      child: PieChart(
        PieChartData(
          sectionsSpace: 2,
          centerSpaceRadius: 40,
          sections: sections,
        ),
      ),
    );
  }

  List<Widget> _buildTopProductsList() {
    List<dynamic> topProducts = dashboardData['topSellingProducts'] ?? [];
    List<Widget> widgets = [];
    
    List<Color> colors = [
      const Color(0xFFC53030),
      const Color(0xFFED4F4F),
      const Color(0xFFFFA5A5),
    ];
    
    List<IconData> icons = [
      Icons.looks_one,
      Icons.looks_two,
      Icons.looks_3,
    ];

    for (int i = 0; i < topProducts.length && i < 3; i++) {
      var product = topProducts[i];
      
      widgets.add(
        _buildProductRankingItem(
          position: i + 1,
          productName: product['name'] ?? 'Producto ${i + 1}',
          units: product['sales']?.toInt() ?? 0,
          revenue: product['revenue']?.toDouble() ?? 0.0,
          color: colors[i],
          icon: icons[i],
        ),
      );
      
      if (i < topProducts.length - 1 && i < 2) {
        widgets.add(
          Divider(
            height: 1,
            color: Colors.grey.withOpacity(0.2),
            indent: 16,
            endIndent: 16,
          ),
        );
      }
    }

    if (widgets.isEmpty) {
      widgets.add(
        const Padding(
          padding: EdgeInsets.all(16.0),
          child: Center(
            child: Text(
              'No hay datos de productos disponibles',
              style: TextStyle(color: Colors.grey),
            ),
          ),
        ),
      );
    }

    return widgets;
  }

  List<Widget> _buildRecentAlertsList() {
    List<dynamic> recentAlerts = dashboardData['recentNotifications'] ?? [];
    List<Widget> widgets = [];

    for (int i = 0; i < recentAlerts.length && i < 3; i++) {
      var alert = recentAlerts[i];
      
      widgets.add(
        _buildNotificationItem(
          icon: _getAlertIcon(alert['type']),
          title: alert['title'] ?? 'Notificación',
          description: alert['message'] ?? 'Sin descripción',
          time: alert['time'] ?? 'Ahora',
          color: _getAlertColor(alert['type']),
        ),
      );
      
      if (i < recentAlerts.length - 1 && i < 2) {
        widgets.add(
          Divider(
            height: 1,
            color: Colors.grey.withOpacity(0.2),
            indent: 16,
            endIndent: 16,
          ),
        );
      }
    }

    if (widgets.isEmpty) {
      widgets.add(
        const Padding(
          padding: EdgeInsets.all(16.0),
          child: Center(
            child: Text(
              'No hay alertas recientes',
              style: TextStyle(color: Colors.grey),
            ),
          ),
        ),
      );
    }

    return widgets;
  }

  IconData _getAlertIcon(String? type) {
    switch (type) {
      case 'low_stock':
        return Icons.shopping_cart;
      case 'price_update':
        return Icons.price_change;
      case 'new_product':
        return Icons.new_releases;
      case 'expiring':
        return Icons.calendar_today;
      default:
        return Icons.notifications;
    }
  }

  Color _getAlertColor(String? type) {
    switch (type) {
      case 'low_stock':
        return const Color(0xFF4299E1);
      case 'price_update':
        return const Color(0xFFED8936);
      case 'new_product':
        return const Color(0xFF48BB78);
      case 'expiring':
        return const Color(0xFFFF9800);
      default:
        return const Color(0xFF718096);
    }
  }
}