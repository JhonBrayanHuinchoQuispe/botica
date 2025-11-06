import '../models/proveedor.dart';
import 'api_service.dart';

class ProveedorService {
  final ApiService _apiService = ApiService();

  Future<List<Proveedor>> getProveedores() async {
    try {
      final response = await _apiService.get<Map<String, dynamic>>(
        '/api/proveedores',
        requireAuth: true,
      );

      if (response.success && response.data != null) {
        final List<dynamic> proveedoresJson = response.data!['proveedores'] ?? response.data!['data'] ?? [];
        
        return proveedoresJson.map((json) => Proveedor.fromJson(json)).toList();
      } else {
        throw Exception('Error al cargar proveedores: ${response.message}');
      }
    } catch (e) {
      print('Error en getProveedores: $e');
      // Retornar proveedores por defecto en caso de error
      return [
        Proveedor(id: '1', razonSocial: 'Laboratorios ABC S.A.', nombreComercial: 'ABC Pharma'),
        Proveedor(id: '2', razonSocial: 'Distribuidora Médica XYZ', nombreComercial: 'MediXYZ'),
        Proveedor(id: '3', razonSocial: 'Farmacéutica Nacional', nombreComercial: 'FarmaNac'),
        Proveedor(id: '4', razonSocial: 'Importadora de Medicamentos', nombreComercial: 'ImportMed'),
        Proveedor(id: '5', razonSocial: 'Laboratorio Genérico S.A.', nombreComercial: 'GenLab'),
      ];
    }
  }
}