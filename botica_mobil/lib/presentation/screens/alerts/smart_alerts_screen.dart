import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../../data/services/smart_alerts_service.dart';

class SmartAlertsScreen extends StatefulWidget {
  const SmartAlertsScreen({Key? key}) : super(key: key);

  @override
  State<SmartAlertsScreen> createState() => _SmartAlertsScreenState();
}

class _SmartAlertsScreenState extends State<SmartAlertsScreen> {
  final SmartAlertsService _alertsService = SmartAlertsService();
  
  bool _alertsEnabled = true;
  bool _lowStockAlerts = true;
  bool _expiryAlerts = true;
  bool _criticalCategoryAlerts = true;
  int _lowStockThreshold = 10;
  int _expiryWarningDays = 30;
  DateTime? _lastCheckTime;
  
  @override
  void initState() {
    super.initState();
    _loadSettings();
    _loadLastCheckTime();
  }

  Future<void> _loadSettings() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _alertsEnabled = prefs.getBool('alerts_enabled') ?? true;
      _lowStockAlerts = prefs.getBool('low_stock_alerts') ?? true;
      _expiryAlerts = prefs.getBool('expiry_alerts') ?? true;
      _criticalCategoryAlerts = prefs.getBool('critical_category_alerts') ?? true;
      _lowStockThreshold = prefs.getInt('low_stock_threshold') ?? 10;
      _expiryWarningDays = prefs.getInt('expiry_warning_days') ?? 30;
    });
  }

  Future<void> _saveSettings() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('alerts_enabled', _alertsEnabled);
    await prefs.setBool('low_stock_alerts', _lowStockAlerts);
    await prefs.setBool('expiry_alerts', _expiryAlerts);
    await prefs.setBool('critical_category_alerts', _criticalCategoryAlerts);
    await prefs.setInt('low_stock_threshold', _lowStockThreshold);
    await prefs.setInt('expiry_warning_days', _expiryWarningDays);
  }

  Future<void> _loadLastCheckTime() async {
    final lastCheck = await _alertsService.getLastCheckTime();
    setState(() {
      _lastCheckTime = lastCheck;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey.shade50,
      appBar: AppBar(
        title: const Text(
          'Alertas Inteligentes',
          style: TextStyle(
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
        backgroundColor: const Color(0xFF2E7D32),
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.white),
        actions: [
          IconButton(
            icon: const Icon(Icons.help_outline),
            onPressed: _showHelpDialog,
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildStatusCard(),
            const SizedBox(height: 16),
            _buildMainToggle(),
            const SizedBox(height: 16),
            _buildAlertTypesSection(),
            const SizedBox(height: 16),
            _buildConfigurationSection(),
            const SizedBox(height: 16),
            _buildActionsSection(),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            _alertsEnabled ? Colors.green.shade400 : Colors.grey.shade400,
            _alertsEnabled ? Colors.green.shade600 : Colors.grey.shade600,
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 8,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          Icon(
            _alertsEnabled ? Icons.notifications_active : Icons.notifications_off,
            size: 48,
            color: Colors.white,
          ),
          const SizedBox(height: 12),
          Text(
            _alertsEnabled ? 'Alertas Activas' : 'Alertas Desactivadas',
            style: const TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 8),
          if (_lastCheckTime != null)
            Text(
              'Última verificación: ${_formatDateTime(_lastCheckTime!)}',
              style: const TextStyle(
                fontSize: 14,
                color: Colors.white70,
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildMainToggle() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          Icon(
            Icons.power_settings_new,
            color: _alertsEnabled ? Colors.green : Colors.grey,
            size: 28,
          ),
          const SizedBox(width: 16),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Activar Alertas Inteligentes',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                Text(
                  'Recibe notificaciones automáticas',
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey,
                  ),
                ),
              ],
            ),
          ),
          Switch(
            value: _alertsEnabled,
            onChanged: (value) {
              setState(() {
                _alertsEnabled = value;
              });
              _saveSettings();
              if (value) {
                _alertsService.initialize();
              }
            },
            activeColor: Colors.green,
          ),
        ],
      ),
    );
  }

  Widget _buildAlertTypesSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Tipos de Alertas',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Color(0xFF2E7D32),
          ),
        ),
        const SizedBox(height: 12),
        _buildAlertTypeCard(
          icon: Icons.inventory_2,
          title: 'Stock Bajo',
          subtitle: 'Productos con pocas unidades',
          value: _lowStockAlerts,
          onChanged: _alertsEnabled ? (value) {
            setState(() {
              _lowStockAlerts = value;
            });
            _saveSettings();
          } : null,
          color: Colors.orange,
        ),
        const SizedBox(height: 8),
        _buildAlertTypeCard(
          icon: Icons.schedule,
          title: 'Próximos a Vencer',
          subtitle: 'Productos cerca de la fecha de vencimiento',
          value: _expiryAlerts,
          onChanged: _alertsEnabled ? (value) {
            setState(() {
              _expiryAlerts = value;
            });
            _saveSettings();
          } : null,
          color: Colors.amber,
        ),
        const SizedBox(height: 8),
        _buildAlertTypeCard(
          icon: Icons.local_hospital,
          title: 'Medicamentos Críticos',
          subtitle: 'Medicamentos de emergencia con stock bajo',
          value: _criticalCategoryAlerts,
          onChanged: _alertsEnabled ? (value) {
            setState(() {
              _criticalCategoryAlerts = value;
            });
            _saveSettings();
          } : null,
          color: Colors.red,
        ),
      ],
    );
  }

  Widget _buildAlertTypeCard({
    required IconData icon,
    required String title,
    required String subtitle,
    required bool value,
    required ValueChanged<bool>? onChanged,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: value && _alertsEnabled ? color.withOpacity(0.3) : Colors.grey.shade200,
          width: 1,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(
              icon,
              color: color,
              size: 24,
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
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                Text(
                  subtitle,
                  style: const TextStyle(
                    fontSize: 14,
                    color: Colors.grey,
                  ),
                ),
              ],
            ),
          ),
          Switch(
            value: value,
            onChanged: onChanged,
            activeColor: color,
          ),
        ],
      ),
    );
  }

  Widget _buildConfigurationSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Configuración',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Color(0xFF2E7D32),
          ),
        ),
        const SizedBox(height: 12),
        _buildConfigCard(
          icon: Icons.trending_down,
          title: 'Umbral de Stock Bajo',
          subtitle: 'Alertar cuando queden $_lowStockThreshold unidades o menos',
          onTap: _alertsEnabled ? _showStockThresholdDialog : null,
        ),
        const SizedBox(height: 8),
        _buildConfigCard(
          icon: Icons.calendar_today,
          title: 'Días de Aviso de Vencimiento',
          subtitle: 'Alertar $_expiryWarningDays días antes del vencimiento',
          onTap: _alertsEnabled ? _showExpiryDaysDialog : null,
        ),
      ],
    );
  }

  Widget _buildConfigCard({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback? onTap,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ListTile(
        leading: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: Colors.blue.withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(
            icon,
            color: Colors.blue,
            size: 24,
          ),
        ),
        title: Text(
          title,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
        subtitle: Text(subtitle),
        trailing: onTap != null ? const Icon(Icons.chevron_right) : null,
        onTap: onTap,
        enabled: onTap != null,
      ),
    );
  }

  Widget _buildActionsSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Acciones',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Color(0xFF2E7D32),
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: ElevatedButton.icon(
                onPressed: _alertsEnabled ? _checkNow : null,
                icon: const Icon(Icons.refresh),
                label: const Text('Verificar Ahora'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.blue,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: ElevatedButton.icon(
                onPressed: _alertsEnabled ? _testNotification : null,
                icon: const Icon(Icons.notifications),
                label: const Text('Probar'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Future<void> _checkNow() async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const AlertDialog(
        content: Row(
          children: [
            CircularProgressIndicator(),
            SizedBox(width: 16),
            Text('Verificando alertas...'),
          ],
        ),
      ),
    );

    try {
      await _alertsService.checkAndSendAlerts();
      await _loadLastCheckTime();
      
      if (mounted) {
        Navigator.of(context).pop();
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('✅ Verificación completada'),
            backgroundColor: Colors.green,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        Navigator.of(context).pop();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('❌ Error: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _testNotification() async {
    try {
      await _alertsService.testNotification();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('🧪 Notificación de prueba enviada'),
          backgroundColor: Colors.blue,
        ),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('❌ Error enviando notificación: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  void _showStockThresholdDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Umbral de Stock Bajo'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('Selecciona el número mínimo de unidades:'),
            const SizedBox(height: 16),
            Slider(
              value: _lowStockThreshold.toDouble(),
              min: 1,
              max: 50,
              divisions: 49,
              label: _lowStockThreshold.toString(),
              onChanged: (value) {
                setState(() {
                  _lowStockThreshold = value.round();
                });
              },
            ),
            Text('$_lowStockThreshold unidades'),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: () {
              _saveSettings();
              Navigator.of(context).pop();
            },
            child: const Text('Guardar'),
          ),
        ],
      ),
    );
  }

  void _showExpiryDaysDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Días de Aviso'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('Días antes del vencimiento para alertar:'),
            const SizedBox(height: 16),
            Slider(
              value: _expiryWarningDays.toDouble(),
              min: 7,
              max: 90,
              divisions: 83,
              label: _expiryWarningDays.toString(),
              onChanged: (value) {
                setState(() {
                  _expiryWarningDays = value.round();
                });
              },
            ),
            Text('$_expiryWarningDays días'),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: () {
              _saveSettings();
              Navigator.of(context).pop();
            },
            child: const Text('Guardar'),
          ),
        ],
      ),
    );
  }

  void _showHelpDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Ayuda - Alertas Inteligentes'),
        content: const SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                '📦 Stock Bajo:',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              Text('Notifica cuando un producto tiene pocas unidades disponibles.\n'),
              
              Text(
                '⏰ Próximos a Vencer:',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              Text('Alerta sobre productos que están cerca de su fecha de vencimiento.\n'),
              
              Text(
                '🚨 Medicamentos Críticos:',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              Text('Alertas especiales para medicamentos de emergencia con stock bajo.\n'),
              
              Text(
                '🔄 Verificación Automática:',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              Text('El sistema verifica automáticamente cada 4 horas y envía notificaciones cuando es necesario.'),
            ],
          ),
        ),
        actions: [
          ElevatedButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Entendido'),
          ),
        ],
      ),
    );
  }

  String _formatDateTime(DateTime dateTime) {
    return '${dateTime.day}/${dateTime.month}/${dateTime.year} ${dateTime.hour}:${dateTime.minute.toString().padLeft(2, '0')}';
  }
}