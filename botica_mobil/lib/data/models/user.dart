class User {
  final int id;
  final String name;
  final String? nombres;
  final String? apellidos;
  final String email;
  final String? telefono;
  final String? cargo;
  final String? direccion;
  final String? avatar;
  final String? fullName;
  final String? initials;
  final List<String> roles;
  final List<String> permissions;
  final DateTime? lastLoginAt;
  final bool? forcePasswordChange;

  User({
    required this.id,
    required this.name,
    this.nombres,
    this.apellidos,
    required this.email,
    this.telefono,
    this.cargo,
    this.direccion,
    this.avatar,
    this.fullName,
    this.initials,
    this.roles = const [],
    this.permissions = const [],
    this.lastLoginAt,
    this.forcePasswordChange,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      nombres: json['nombres'],
      apellidos: json['apellidos'],
      email: json['email'],
      telefono: json['telefono'],
      cargo: json['cargo'],
      direccion: json['direccion'],
      avatar: json['avatar'],
      fullName: json['full_name'],
      initials: json['initials'],
      roles: json['roles'] != null ? List<String>.from(json['roles']) : [],
      permissions: json['permissions'] != null ? List<String>.from(json['permissions']) : [],
      lastLoginAt: json['last_login_at'] != null ? DateTime.parse(json['last_login_at']) : null,
      forcePasswordChange: json['force_password_change'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'nombres': nombres,
      'apellidos': apellidos,
      'email': email,
      'telefono': telefono,
      'cargo': cargo,
      'direccion': direccion,
      'avatar': avatar,
      'full_name': fullName,
      'initials': initials,
      'roles': roles,
      'permissions': permissions,
      'last_login_at': lastLoginAt?.toIso8601String(),
      'force_password_change': forcePasswordChange,
    };
  }

  // Getters de conveniencia
  String get displayName => fullName ?? nombres ?? name;
  String get displayInitials => initials ?? name.substring(0, 2).toUpperCase();
  String get displayPhone => telefono ?? '';
  String get displayPosition => cargo ?? '';
  
  // Métodos de utilidad
  bool hasRole(String role) => roles.contains(role);
  bool hasPermission(String permission) => permissions.contains(permission);
  bool get isAdmin => hasRole('admin') || hasRole('administrador');
  bool get needsPasswordChange => forcePasswordChange == true;
}