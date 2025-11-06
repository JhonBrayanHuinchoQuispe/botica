import 'package:flutter/material.dart';
import '../../core/config/theme.dart';

class CustomButton extends StatelessWidget {
  final String text;
  final VoidCallback? onPressed;
  final bool isLoading;
  final Color? backgroundColor;
  final Color? textColor;
  final double? width;
  final double height;
  final IconData? icon;

  const CustomButton({
    super.key,
    required this.text,
    this.onPressed,
    this.isLoading = false,
    this.backgroundColor,
    this.textColor,
    this.width,
    this.height = 56,
    this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: width ?? double.infinity,
      height: height,
      child: ElevatedButton(
        onPressed: isLoading ? null : onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: backgroundColor ?? AppTheme.primaryRed,
          foregroundColor: textColor ?? AppTheme.white,
          elevation: 0,
          shadowColor: Colors.transparent,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(18),
          ),
          padding: EdgeInsets.zero,
        ).copyWith(
          overlayColor: WidgetStateProperty.resolveWith<Color?>(
            (Set<WidgetState> states) {
              if (states.contains(WidgetState.pressed)) {
                return AppTheme.white.withOpacity(0.1);
              }
              if (states.contains(WidgetState.hovered)) {
                return AppTheme.white.withOpacity(0.05);
              }
              return null;
            },
          ),
        ),
        child: Container(
          width: double.infinity,
          height: height,
          decoration: BoxDecoration(
            color: isLoading ? AppTheme.primaryRed : null, // Añadimos color base para el estado de carga
            gradient: isLoading 
                ? null 
                : LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [
                      backgroundColor ?? AppTheme.primaryRed,
                      (backgroundColor ?? AppTheme.primaryRed).withOpacity(0.8),
                    ],
                  ),
            borderRadius: BorderRadius.circular(18),
          ),
          child: Center(
            child: isLoading
                ? SizedBox(
                    height: 24,
                    width: 24,
                    child: CircularProgressIndicator(
                      strokeWidth: 2.5,
                      valueColor: AlwaysStoppedAnimation<Color>(
                        Colors.white, // Mantenemos el loading en blanco para contraste
                      ),
                    ),
                  )
                : Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      if (icon != null) ...[
                        Icon(
                          icon,
                          color: textColor ?? AppTheme.white,
                          size: 20,
                        ),
                        const SizedBox(width: 8),
                      ],
                      Text(
                        text,
                        style: TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.w700,
                          letterSpacing: 0.5,
                          color: textColor ?? AppTheme.white,
                        ),
                      ),
                    ],
                  ),
          ),
        ),
      ),
    );
  }
}