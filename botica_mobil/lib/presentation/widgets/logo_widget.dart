import 'package:flutter/material.dart';
import '../../core/config/theme.dart';

class LogoWidget extends StatelessWidget {
  final double size;
  final bool showText;

  const LogoWidget({
    super.key,
    this.size = 80,
    this.showText = true,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: size,
          height: size,
          decoration: BoxDecoration(
            boxShadow: [
              BoxShadow(
                color: AppTheme.primaryRed.withOpacity(0.3),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Image.asset(
            'assets/images/logo.png',
            width: size,
            height: size,
            fit: BoxFit.contain,
          ),
        ),
        if (showText) ...[          
          const SizedBox(height: 16),
          Text(
            'Botica San Antonio',
            style: TextStyle(
              fontSize: size * 0.2,
              fontWeight: FontWeight.bold,
              color: AppTheme.primaryRed,
            ),
          ),
          Text(
            'Sistema de Administración',
            style: TextStyle(
              fontSize: size * 0.12,
              color: AppTheme.mediumGray,
            ),
          ),
        ],
      ],
    );
  }
}