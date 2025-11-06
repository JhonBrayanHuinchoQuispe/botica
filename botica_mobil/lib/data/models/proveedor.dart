class Proveedor {
  final String id;
  final String codigoProveedor;
  final String razonSocial;
  final String? nombreComercial;
  final String? ruc;
  final String? telefono;
  final String? email;
  final String? direccion;
  final String? ciudad;
  final String? departamento;
  final String? contactoPrincipal;
  final String? telefonoContacto;
  final String? emailContacto;
  final String? observaciones;
  final double? limiteCredito;
  final int? diasCredito;
  final String? categoriaProveedor;
  final String estado;

  Proveedor({
    required this.id,
    String? codigoProveedor,
    required this.razonSocial,
    this.nombreComercial,
    this.ruc,
    this.telefono,
    this.email,
    this.direccion,
    this.ciudad,
    this.departamento,
    this.contactoPrincipal,
    this.telefonoContacto,
    this.emailContacto,
    this.observaciones,
    this.limiteCredito,
    this.diasCredito,
    this.categoriaProveedor,
    this.estado = 'activo',
  }) : codigoProveedor = codigoProveedor ?? 'PROV-$id';

  factory Proveedor.fromJson(Map<String, dynamic> json) {
    return Proveedor(
      id: json['id'].toString(),
      codigoProveedor: json['codigo_proveedor'] ?? 'PROV-${json['id']}',
      razonSocial: json['razon_social'] ?? '',
      nombreComercial: json['nombre_comercial'],
      ruc: json['ruc'],
      telefono: json['telefono'],
      email: json['email'],
      direccion: json['direccion'],
      ciudad: json['ciudad'],
      departamento: json['departamento'],
      contactoPrincipal: json['contacto_principal'],
      telefonoContacto: json['telefono_contacto'],
      emailContacto: json['email_contacto'],
      observaciones: json['observaciones'],
      limiteCredito: json['limite_credito']?.toDouble(),
      diasCredito: json['dias_credito'],
      categoriaProveedor: json['categoria_proveedor'],
      estado: json['estado'] ?? 'activo',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'codigo_proveedor': codigoProveedor,
      'razon_social': razonSocial,
      'nombre_comercial': nombreComercial,
      'ruc': ruc,
      'telefono': telefono,
      'email': email,
      'direccion': direccion,
      'ciudad': ciudad,
      'departamento': departamento,
      'contacto_principal': contactoPrincipal,
      'telefono_contacto': telefonoContacto,
      'email_contacto': emailContacto,
      'observaciones': observaciones,
      'limite_credito': limiteCredito,
      'dias_credito': diasCredito,
      'categoria_proveedor': categoriaProveedor,
      'estado': estado,
    };
  }

  String get displayName => nombreComercial ?? razonSocial;

  @override
  String toString() => displayName;

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is Proveedor && runtimeType == other.runtimeType && id == other.id;

  @override
  int get hashCode => id.hashCode;
}