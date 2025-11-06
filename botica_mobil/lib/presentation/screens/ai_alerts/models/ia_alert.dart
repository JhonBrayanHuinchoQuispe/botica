import 'package:flutter/material.dart';

enum AlertType {
  expiration,
  rotation,
  stock,
  prediction
}

enum AlertPriority {
  high,
  medium,
  low
}

class AIAlert {
  final String title;
  final String description;
  final String suggestion;
  final Map<String, dynamic> details;
  final AlertType type;
  final AlertPriority priority;
  final IconData icon;
  final Color color;
  final DateTime createdAt;
  bool isResolved;

  AIAlert({
    required this.title,
    required this.description,
    required this.suggestion,
    required this.details,
    required this.type,
    required this.priority,
    required this.icon,
    required this.color,
    DateTime? createdAt,
    this.isResolved = false,
  }) : createdAt = createdAt ?? DateTime.now();

  Color get priorityColor {
    switch (priority) {
      case AlertPriority.high:
        return const Color(0xFFE53E3E);
      case AlertPriority.medium:
        return const Color(0xFFFF9800);
      case AlertPriority.low:
        return const Color(0xFF2196F3);
    }
  }

  IconData get typeIcon {
    switch (type) {
      case AlertType.expiration:
        return Icons.warning_amber_rounded;
      case AlertType.rotation:
        return Icons.trending_down;
      case AlertType.stock:
        return Icons.inventory_2;
      case AlertType.prediction:
        return Icons.trending_up;
    }
  }

  String get typeLabel {
    switch (type) {
      case AlertType.expiration:
        return 'Vencimiento';
      case AlertType.rotation:
        return 'Rotación';
      case AlertType.stock:
        return 'Stock';
      case AlertType.prediction:
        return 'Predicción';
    }
  }

  Map<String, dynamic> toMap() {
    return {
      'title': title,
      'description': description,
      'suggestion': suggestion,
      'details': details,
      'type': type.toString(),
      'priority': priority.toString(),
      'icon': icon.codePoint,
      'color': color.value,
      'createdAt': createdAt.toIso8601String(),
      'isResolved': isResolved,
    };
  }

  factory AIAlert.fromMap(Map<String, dynamic> map) {
    return AIAlert(
      title: map['title'],
      description: map['description'],
      suggestion: map['suggestion'],
      details: Map<String, dynamic>.from(map['details']),
      type: AlertType.values.firstWhere(
        (e) => e.toString() == map['type'],
        orElse: () => AlertType.stock,
      ),
      priority: AlertPriority.values.firstWhere(
        (e) => e.toString() == map['priority'],
        orElse: () => AlertPriority.medium,
      ),
      icon: IconData(map['icon'], fontFamily: 'MaterialIcons'),
      color: Color(map['color']),
      createdAt: DateTime.parse(map['createdAt']),
      isResolved: map['isResolved'] ?? false,
    );
  }

  AIAlert copyWith({
    String? title,
    String? description,
    String? suggestion,
    Map<String, dynamic>? details,
    AlertType? type,
    AlertPriority? priority,
    IconData? icon,
    Color? color,
    DateTime? createdAt,
    bool? isResolved,
  }) {
    return AIAlert(
      title: title ?? this.title,
      description: description ?? this.description,
      suggestion: suggestion ?? this.suggestion,
      details: details ?? this.details,
      type: type ?? this.type,
      priority: priority ?? this.priority,
      icon: icon ?? this.icon,
      color: color ?? this.color,
      createdAt: createdAt ?? this.createdAt,
      isResolved: isResolved ?? this.isResolved,
    );
  }

  @override
  String toString() {
    return 'AIAlert(title: $title, type: $type, priority: $priority, isResolved: $isResolved)';
  }
}