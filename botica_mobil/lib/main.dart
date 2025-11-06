import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:provider/provider.dart';
import 'core/config/theme.dart';
import 'core/notifiers/theme_notifier.dart';
import 'presentation/screens/auth/login_screen.dart';
import 'presentation/screens/home/home_screen.dart';
import 'presentation/screens/inventory/inventory_screens.dart';
import 'presentation/screens/splash_screen.dart';
import 'presentation/screens/inventory/scan_product_screen.dart';  
import 'presentation/screens/ai_alerts/ai_alerts_screen.dart';
import 'presentation/screens/alerts/smart_alerts_screen.dart';
import 'presentation/screens/settings/settings_screens.dart';

import 'presentation/widgets/auth_wrapper.dart';
import 'data/services/smart_alerts_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Configurar orientación
  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);
  
  // Inicializar servicio de alertas inteligentes
  try {
    final alertsService = SmartAlertsService();
    await alertsService.initialize();
    debugPrint('✅ Servicio de alertas inteligentes inicializado');
  } catch (e) {
    debugPrint('❌ Error inicializando alertas: $e');
  }
  
  runApp(const BoticaSanAntonioApp());
}

class BoticaSanAntonioApp extends StatelessWidget {
  const BoticaSanAntonioApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (context) => ThemeNotifier(),
      child: Consumer<ThemeNotifier>(
        builder: (context, themeNotifier, child) {
          return MaterialApp(
            title: 'Botica San Antonio',
            theme: themeNotifier.currentTheme,
            home: const SplashScreen(),
            debugShowCheckedModeBanner: false,
            // Configuración de localización en español
            locale: const Locale('es', 'ES'),
            localizationsDelegates: const [
              GlobalMaterialLocalizations.delegate,
              GlobalWidgetsLocalizations.delegate,
              GlobalCupertinoLocalizations.delegate,
            ],
            supportedLocales: const [
              Locale('es', 'ES'), // Español
              Locale('en', 'US'), // Inglés como respaldo
            ],
            routes: {
              '/splash': (context) => const SplashScreen(),
              '/login': (context) => const LoginScreen(),
              '/home': (context) => const AuthWrapper(child: HomeScreen()),
              '/inventory': (context) => const AuthWrapper(child: InventoryScreen()),
              '/scan': (context) => const AuthWrapper(child: ScanProductScreen()),
              '/ai_alerts': (context) => const AuthWrapper(child: AIAlertsScreen()), 
              '/smart_alerts': (context) => const AuthWrapper(child: SmartAlertsScreen()),
              '/settings': (context) => const AuthWrapper(child: SettingsScreen()),
            },
          );
        },
      ),
    );
  }
}