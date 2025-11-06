class Presentacion {
  final String id;
  final String nombre;
  final String? descripcion;

  Presentacion({
    required this.id,
    required this.nombre,
    this.descripcion,
  });

  factory Presentacion.fromJson(Map<String, dynamic> json) {
    return Presentacion(
      id: json['id'].toString(),
      nombre: json['nombre'] ?? '',
      descripcion: json['descripcion'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nombre': nombre,
      'descripcion': descripcion,
    };
  }

  @override
  String toString() => nombre;

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is Presentacion && runtimeType == other.runtimeType && id == other.id;

  @override
  int get hashCode => id.hashCode;
}