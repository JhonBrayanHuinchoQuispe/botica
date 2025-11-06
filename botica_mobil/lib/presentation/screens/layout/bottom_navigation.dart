import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';

class BottomNavigation extends StatelessWidget {
  final int currentIndex;

  const BottomNavigation({
    super.key, 
    required this.currentIndex
  });

  @override
  Widget build(BuildContext context) {
    return BottomNavigationBar(
      currentIndex: currentIndex,
      onTap: (index) {
        switch (index) {
          case 0:
            Navigator.pushReplacementNamed(context, '/home');
            break;
          case 1:
            Navigator.pushReplacementNamed(context, '/inventory');
            break;
          case 2:
            Navigator.pushReplacementNamed(context, '/ai_alerts');
            break;
          case 3:
            Navigator.pushReplacementNamed(context, '/settings');
            break;
        }
      },
      type: BottomNavigationBarType.fixed,
      selectedItemColor: const Color(0xFFFF4757),
      unselectedItemColor: Colors.grey,
      items: const [
        BottomNavigationBarItem(
          icon: Icon(Icons.home_rounded, size: 26),
          label: 'Inicio',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.inventory_outlined, size: 26),
          label: 'Control Stock',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.psychology, size: 26),
          label: 'Asistente IA',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.settings_rounded, size: 26),
          label: 'Ajustes',
        ),
      ],
    );
  }
}