import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:intl/intl.dart';
import '../../../core/config/theme.dart';
import '../inventory/inventory_screens.dart';
import '../auth/login_screen.dart';
import '../ai_alerts/ai_alerts_screen.dart';
import '../layout/header.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _selectedIndex = 0;

  final String userName = "Brayan";

  // Datos simulados que normalmente vendrían de tu backend Laravel
  final _dashboardData = {
    'totalSales': 5420.50,
    'dailySales': 850.75,
    'lowStockProducts': 8,
    'expiringProducts': 15,
    'topSellingProducts': [
      {'name': 'Paracetamol', 'sales': 120},
      {'name': 'Ibuprofeno', 'sales': 95},
      {'name': 'Vitamina C', 'sales': 75},
    ],
    'recentAlerts': [
      {
        'type': 'stock',
        'message': 'Paracetamol stock bajo',
        'icon': Icons.warning_rounded,
        'color': Colors.orange,
      },
      {
        'type': 'expiration',
        'message': 'Vitamina C próxima a vencer',
        'icon': Icons.calendar_today,
        'color': Colors.red,
      },
    ]
  };

  final List<Widget> _screens = [
    const HomeContent(),
    const InventoryScreen(),
    const AIAlertsScreen(),  
    const Center(child: Text('Reportes')),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: _selectedIndex == 1 
          ? null 
          : BarraCompartida(
              titulo: _selectedIndex == 0 ? 'Inicio' : 
                      _selectedIndex == 2 ? 'Asistente IA' : 'Reportes',
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
                  icon: Icon(Icons.analytics_outlined, size: 26),
                  activeIcon: Icon(Icons.analytics, size: 26),
                  label: 'Reportes',
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
  const HomeContent({super.key});

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
    final nextFourMonths = _getNextFourMonths();
    final values = [15, 7, 32, 40]; // Valores de vencimientos
    final int maxValue = ((values.reduce((a, b) => a > b ? a : b) / 10).ceil() * 10) + 10;
    
    return SingleChildScrollView(
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
                    value: 'S/ 850.75',
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
                    value: '8',
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
                    value: '15',
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
                    value: 'S/ 5420.50',
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
              title: 'Productos Más Vendidos',
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SizedBox(
                    height: 250,
                    child: PieChart(
                      PieChartData(
                        centerSpaceRadius: 0,
                        sectionsSpace: 5,
                        sections: [
                          PieChartSectionData(
                            value: 120,
                            color: const Color(0xFFE53E3E),
                            title: 'Paracetamol\n120 uds',
                            radius: 100,
                            titleStyle: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          PieChartSectionData(
                            value: 95,
                            color: const Color(0xFFFF5252),
                            title: 'Ibuprofeno\n95 uds',
                            radius: 90,
                            titleStyle: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          PieChartSectionData(
                            value: 75,
                            color: const Color(0xFFFF7B7B),
                            title: 'Vitamina C\n75 uds',
                            radius: 80,
                            titleStyle: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          PieChartSectionData(
                            value: 50,
                            color: const Color(0xFFFFAAAA),
                            title: 'Aspirina\n50 uds',
                            radius: 70,
                            titleStyle: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
            
            // Sección de Alertas Recientes
            _buildChartSection(
              title: 'Alertas Recientes',
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildAlertItem(
                    icon: Icons.warning_rounded,
                    message: 'Stock bajo de Paracetamol',
                    color: Colors.orange,
                  ),
                  const SizedBox(height: 8),
                  _buildAlertItem(
                    icon: Icons.calendar_today,
                    message: 'Vitamina C próxima a vencer',
                    color: Colors.red,
                  ),
                  const SizedBox(height: 8),
                  _buildAlertItem(
                    icon: Icons.inventory_2_outlined,
                    message: 'Nuevo lote de Ibuprofeno recibido',
                    color: Colors.green,
                  ),
                  const SizedBox(height: 16),
                  Center(
                    child: TextButton(
                      onPressed: () {
                        // Navegar a pantalla de todas las alertas
                      },
                      child: Text(
                        'Ver todas las alertas',
                        style: TextStyle(
                          color: AppTheme.primaryRed,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
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
    required String title,
    required Widget child,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
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
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
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
            padding: const EdgeInsets.all(16.0),
            child: child,
          ),
        ),
      ],
    );
  }

  Widget _buildAlertItem({
    required IconData icon,
    required String message,
    required Color color,
  }) {
    return Row(
      children: [
        Icon(icon, color: color, size: 20),
        const SizedBox(width: 8),
        Text(
          message,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w300,
          ),
        ),
      ],
    );
  }
}