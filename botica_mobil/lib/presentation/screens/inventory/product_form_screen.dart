import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:io';
import '../../../core/config/theme.dart';
import '../../../data/models/product.dart';
import '../../../data/models/categoria.dart';
import '../../../data/models/presentacion.dart';
import '../../../data/models/proveedor.dart';
import '../../../data/services/barcode_service.dart';
import '../../../data/services/categoria_service.dart';
import '../../../data/services/presentacion_service.dart';
import '../../../data/services/proveedor_service.dart';
import '../../../data/services/product_service.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/custom_button.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

class ProductFormScreen extends StatefulWidget {
  final bool isNewProduct;
  final Product? existingProduct;
  final String scannedBarcode;
  final VoidCallback onProductSaved;

  const ProductFormScreen({
    super.key,
    required this.isNewProduct,
    this.existingProduct,
    required this.scannedBarcode,
    required this.onProductSaved,
  });

  @override
  State<ProductFormScreen> createState() => _ProductFormScreenState();
}

class _ProductFormScreenState extends State<ProductFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _barcodeController = TextEditingController();
  final _nameController = TextEditingController();
  final _concentrationController = TextEditingController(); // Cambio: descripción -> concentración
  final _brandController = TextEditingController(); // Nuevo: marca
  final _priceController = TextEditingController();
  final _purchasePriceController = TextEditingController(); // Nuevo: precio de compra
  final _stockController = TextEditingController();
  final _minStockController = TextEditingController();
  final _quantityController = TextEditingController();

  DateTime? _selectedExpiryDate;
  bool _isLoading = false;
  bool _isRefreshing = false; // Estado específico para la actualización automática
  Product? _existingProduct;
  bool _showExistingProduct = false;
  File? _selectedImage; // Nuevo: imagen del producto
  
  // Servicios
  final BarcodeService _barcodeService = BarcodeService();
  final CategoriaService _categoriaService = CategoriaService();
  final PresentacionService _presentacionService = PresentacionService();
  final ProveedorService _proveedorService = ProveedorService();
  
  // Listas para dropdowns
  List<Presentacion> _presentaciones = [];
  List<Categoria> _categorias = [];
  List<Proveedor> _proveedores = [];
  
  // Valores seleccionados
  Presentacion? _selectedPresentacion;
  Categoria? _selectedCategoria;
  Proveedor? _selectedProveedor;

  @override
  void initState() {
    super.initState();
    _barcodeController.text = widget.scannedBarcode;
    _loadInitialData();
    
    // Actualizar datos del producto de forma asíncrona sin bloquear la UI
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _refreshProductData();
    });
  }

  Future<void> _loadInitialData() async {
    setState(() {
      _isLoading = true;
    });

    try {
      // Cargar datos en paralelo
      final results = await Future.wait([
        _presentacionService.getPresentaciones(),
        _categoriaService.getCategorias(),
        _proveedorService.getProveedores(),
      ]);

      setState(() {
        _presentaciones = results[0] as List<Presentacion>;
        _categorias = results[1] as List<Categoria>;
        _proveedores = results[2] as List<Proveedor>;
      });
    } catch (e) {
      print('Error cargando datos iniciales: $e');
      // Los servicios ya tienen datos por defecto en caso de error
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _refreshProductData() async {
    // Solo refrescar si tenemos un código de barras
    if (widget.scannedBarcode.isNotEmpty) {
      setState(() {
        _isRefreshing = true;
      });
      
      try {
        // Crear una instancia de ProductService para buscar directamente
        final productService = ProductService();
        
        // Buscar directamente en el servidor para obtener datos actualizados
        final productoActualizado = await productService.findByBarcode(widget.scannedBarcode);
        
        if (productoActualizado != null) {
          setState(() {
            _existingProduct = productoActualizado;
            _showExistingProduct = true;
          });
          print('Producto actualizado automáticamente desde servidor');
        } else if (widget.existingProduct != null) {
          // Si no se encuentra en servidor, usar el producto existente
          setState(() {
            _existingProduct = widget.existingProduct;
            _showExistingProduct = true;
          });
        }
      } catch (e) {
        print('Error refrescando datos del producto: $e');
        // En caso de error, usar el producto existente si está disponible
        if (widget.existingProduct != null) {
          setState(() {
            _existingProduct = widget.existingProduct;
            _showExistingProduct = true;
          });
        }
      } finally {
        setState(() {
          _isRefreshing = false;
        });
      }
    }
  }

  @override
  void dispose() {
    _barcodeController.dispose();
    _nameController.dispose();
    _concentrationController.dispose();
    _brandController.dispose();
    _priceController.dispose();
    _purchasePriceController.dispose();
    _stockController.dispose();
    _minStockController.dispose();
    _quantityController.dispose();
    super.dispose();
  }

  Future<void> _selectExpirationDate() async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _selectedExpiryDate ?? DateTime.now().add(const Duration(days: 365)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 3650)),
      locale: const Locale('es', 'ES'), // Configurar calendario en español
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: ColorScheme.light(
              primary: AppTheme.primaryRed,
              onPrimary: Colors.white,
              surface: Colors.white,
              onSurface: Colors.black,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null && picked != _selectedExpiryDate) {
      setState(() {
        _selectedExpiryDate = picked;
      });
    }
  }

  Future<void> _saveProduct() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
    });

    try {
      if (widget.isNewProduct) {
        await _createNewProduct();
      } else {
        await _updateExistingProduct();
      }
    } catch (e) {
      _showError('Error al guardar: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _createNewProduct() async {
    // Validaciones adicionales
    if (_selectedExpiryDate == null) {
      _showError('Seleccione una fecha de vencimiento');
      return;
    }
    
    if (_selectedPresentacion == null) {
      _showError('Seleccione una presentación');
      return;
    }
    
    if (_selectedCategoria == null) {
      _showError('Seleccione una categoría');
      return;
    }
    
    if (_selectedProveedor == null) {
      _showError('Seleccione un proveedor');
      return;
    }

    final product = Product(
      id: '0', // Se asignará en el servidor
      name: _nameController.text.trim(),
      brand: _brandController.text.trim(),
      description: _concentrationController.text.trim(), // Concentración en lugar de descripción
      price: double.parse(_priceController.text),
      costPrice: double.parse(_purchasePriceController.text), // Precio de compra
      stock: int.parse(_stockController.text),
      minStock: int.tryParse(_minStockController.text) ?? 5,
      expiryDate: _selectedExpiryDate!,
      category: _selectedCategoria!.nombre,
      barcode: _barcodeController.text.trim(),
      // Campos adicionales que podrían ser necesarios para la API
      supplier: _selectedProveedor!.displayName,
      // imageUrl: _selectedImage != null ? 'path_to_uploaded_image' : null, // Se implementaría la subida de imagen
      isActive: true,
      createdAt: DateTime.now(),
      updatedAt: DateTime.now(),
    );

    final success = await _barcodeService.agregarNuevoProducto(product);
    
    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Producto creado exitosamente'),
          backgroundColor: Colors.green,
        ),
      );
      Navigator.of(context).pop();
    } else {
      _showError('Error al crear el producto');
    }
  }

  Future<void> _updateExistingProduct() async {
    final cantidadAgregar = int.tryParse(_quantityController.text) ?? 0;
    if (cantidadAgregar <= 0) {
      _showError('Ingrese una cantidad válida para agregar');
      return;
    }

    final nuevoStock = widget.existingProduct!.stock + cantidadAgregar;
    
    final success = await _barcodeService.actualizarStockProducto(
      _existingProduct ?? widget.existingProduct!,
      nuevoStock,
    );
    
    if (success) {
      widget.onProductSaved();
    } else {
      _showError('Error al actualizar el stock');
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
      ),
    );
  }

  Future<void> _selectImage() async {
    try {
      final ImagePicker picker = ImagePicker();
      final XFile? image = await picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 800,
        maxHeight: 800,
        imageQuality: 85,
      );

      if (image != null) {
        setState(() {
          _selectedImage = File(image.path);
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error al seleccionar imagen: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.transparent,
      body: Container(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // Header
            Container(
              width: 40,
              height: 4,
              margin: const EdgeInsets.only(bottom: 20),
              decoration: BoxDecoration(
                color: Colors.grey[300],
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            
            // Título
            Text(
              widget.isNewProduct ? 'Nuevo Producto' : 'Ajustar Stock',
              style: const TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: Colors.black87,
              ),
            ),
            
            const SizedBox(height: 20),
            
            // Formulario optimizado
            Expanded(
              child: Form(
                key: _formKey,
                child: ListView(
                  physics: const BouncingScrollPhysics(),
                  padding: EdgeInsets.zero,
                  children: [
                    if (widget.isNewProduct) ..._buildNewProductFields()
                    else ..._buildExistingProductFields(),
                    
                    const SizedBox(height: 30),
                    
                    // Botón guardar - Optimizado
                    RepaintBoundary(child: _buildModernActionButton()),
                    
                    const SizedBox(height: 16),
                    
                    // Botón cancelar - Optimizado
                    RepaintBoundary(child: _buildModernCancelButton()),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  List<Widget> _buildNewProductFields() {
    return [
      // Código de barras (readonly)
      CustomTextField(
        controller: _barcodeController,
        label: 'Código de Barras',
        prefixIcon: Icons.qr_code,
        readOnly: true,
      ),
      
      const SizedBox(height: 16),
      
      // Nombre del producto
      CustomTextField(
        controller: _nameController,
        label: 'Nombre del Producto *',
        prefixIcon: Icons.medication,
        validator: (value) {
          if (value == null || value.trim().isEmpty) {
            return 'El nombre es obligatorio';
          }
          return null;
        },
      ),
      
      const SizedBox(height: 16),
      
      // Concentración (antes descripción)
      CustomTextField(
        controller: _concentrationController,
        label: 'Concentración',
        prefixIcon: Icons.science,
        hintText: 'Ej: 500mg, 10ml, etc.',
      ),
      
      const SizedBox(height: 16),
      
      // Marca
      CustomTextField(
        controller: _brandController,
        label: 'Marca *',
        prefixIcon: Icons.branding_watermark,
        validator: (value) {
          if (value == null || value.trim().isEmpty) {
            return 'La marca es obligatoria';
          }
          return null;
        },
      ),
      
      const SizedBox(height: 16),
      
      // Presentación
      DropdownButtonFormField<Presentacion>(
        value: _selectedPresentacion,
        decoration: InputDecoration(
          labelText: 'Presentación *',
          prefixIcon: const Icon(Icons.medical_services),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        items: _presentaciones.map((presentacion) {
          return DropdownMenuItem(
            value: presentacion,
            child: Text(presentacion.nombre),
          );
        }).toList(),
        onChanged: (value) {
          setState(() {
            _selectedPresentacion = value;
          });
        },
        validator: (value) {
          if (value == null) {
            return 'Seleccione una presentación';
          }
          return null;
        },
      ),
      
      const SizedBox(height: 16),
      
      // Categoría
      DropdownButtonFormField<Categoria>(
        value: _selectedCategoria,
        decoration: InputDecoration(
          labelText: 'Categoría *',
          prefixIcon: const Icon(Icons.category),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        items: _categorias.map((categoria) {
          return DropdownMenuItem(
            value: categoria,
            child: Text(categoria.nombre),
          );
        }).toList(),
        onChanged: (value) {
          setState(() {
            _selectedCategoria = value;
          });
        },
        validator: (value) {
          if (value == null) {
            return 'Seleccione una categoría';
          }
          return null;
        },
      ),
      
      const SizedBox(height: 16),
      
      // Precio de compra y Precio de venta
      Row(
        children: [
          Expanded(
            child: CustomTextField(
              controller: _purchasePriceController,
              label: 'Precio de Compra *',
              prefixIcon: Icons.shopping_cart,
              keyboardType: TextInputType.number,
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Precio de compra requerido';
                }
                if (double.tryParse(value) == null || double.parse(value) <= 0) {
                  return 'Precio inválido';
                }
                return null;
              },
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: CustomTextField(
              controller: _priceController,
              label: 'Precio de Venta *',
              prefixIcon: Icons.attach_money,
              keyboardType: TextInputType.number,
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Precio de venta requerido';
                }
                if (double.tryParse(value) == null || double.parse(value) <= 0) {
                  return 'Precio inválido';
                }
                return null;
              },
            ),
          ),
        ],
      ),
      
      const SizedBox(height: 16),
      
      // Stock inicial y Stock mínimo
      Row(
        children: [
          Expanded(
            child: CustomTextField(
              controller: _stockController,
              label: 'Stock Inicial *',
              prefixIcon: Icons.inventory,
              keyboardType: TextInputType.number,
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Stock requerido';
                }
                if (int.tryParse(value) == null || int.parse(value) < 0) {
                  return 'Stock inválido';
                }
                return null;
              },
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: CustomTextField(
              controller: _minStockController,
              label: 'Stock Mínimo',
              prefixIcon: Icons.warning,
              keyboardType: TextInputType.number,
            ),
          ),
        ],
      ),
      
      const SizedBox(height: 16),
      
      // Fecha de vencimiento (en español)
      InkWell(
        onTap: _selectExpirationDate,
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            border: Border.all(color: Colors.grey.shade400),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Row(
            children: [
              Icon(Icons.event_busy, color: Colors.grey.shade500),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Fecha de Vencimiento *',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      _selectedExpiryDate == null
                          ? 'Seleccionar fecha de vencimiento'
                          : DateFormat('dd/MM/yyyy', 'es').format(_selectedExpiryDate!),
                      style: TextStyle(
                        fontSize: 16,
                        color: _selectedExpiryDate == null ? Colors.grey.shade500 : Colors.black,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
              Icon(Icons.arrow_drop_down, color: Colors.grey.shade500),
            ],
          ),
        ),
      ),
      
      const SizedBox(height: 16),
      
      // Proveedor
      DropdownButtonFormField<Proveedor>(
        value: _selectedProveedor,
        decoration: InputDecoration(
          labelText: 'Proveedor *',
          prefixIcon: Icon(Icons.business, color: Colors.grey.shade500),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        items: _proveedores.map((proveedor) {
          return DropdownMenuItem(
            value: proveedor,
            child: Text(proveedor.displayName),
          );
        }).toList(),
        onChanged: (value) {
          setState(() {
            _selectedProveedor = value;
          });
        },
        validator: (value) {
          if (value == null) {
            return 'Seleccione un proveedor';
          }
          return null;
        },
      ),
      
      const SizedBox(height: 16),
      
      // Imagen del producto
      Container(
        width: double.infinity,
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          border: Border.all(color: Colors.grey),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          children: [
            if (_selectedImage != null) ...[
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: Image.file(
                  _selectedImage!,
                  height: 150,
                  width: 150,
                  fit: BoxFit.cover,
                ),
              ),
              const SizedBox(height: 12),
            ],
            ElevatedButton.icon(
              onPressed: _selectImage,
              icon: const Icon(Icons.camera_alt),
              label: Text(_selectedImage == null ? 'Seleccionar Imagen' : 'Cambiar Imagen'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryRed,
                foregroundColor: Colors.white,
              ),
            ),
          ],
        ),
      ),
    ];
  }

  List<Widget> _buildExistingProductFields() {
    // Usar el producto refrescado si está disponible, sino usar el original
    final product = _existingProduct ?? widget.existingProduct!;
    
    return [
      // Información del producto (readonly) - Diseño mejorado
      Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              Colors.white,
              Colors.grey[50]!,
            ],
          ),
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.grey.withOpacity(0.1),
              spreadRadius: 1,
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
          border: Border.all(color: Colors.grey[200]!, width: 1),
        ),
        child: Column(
          children: [
            // Header con título
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppTheme.primaryRed.withOpacity(0.05),
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(16),
                  topRight: Radius.circular(16),
                ),
                border: Border(
                  bottom: BorderSide(color: Colors.grey[200]!, width: 1),
                ),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryRed.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(
                      Icons.inventory_2,
                      color: AppTheme.primaryRed,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Text(
                    'Producto Encontrado',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.primaryRed,
                    ),
                  ),
                  if (_isRefreshing) ...[
                    const SizedBox(width: 12),
                    SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation<Color>(AppTheme.primaryRed),
                      ),
                    ),
                  ],
                ],
              ),
            ),
            
            // Contenido principal
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  // Nombre del producto - destacado
                  _buildHighlightedInfoCard(
                    icon: Icons.medication,
                    label: 'Nombre',
                    value: product.name,
                    color: Colors.blue,
                  ),
                  
                  const SizedBox(height: 12),
                  
                  // Información en grid de 2 columnas
                  Row(
                    children: [
                      if (product.concentration != null && product.concentration!.isNotEmpty)
                        Expanded(
                          child: _buildInfoCard(
                            icon: Icons.science,
                            label: 'Concentración',
                            value: product.concentration!,
                            color: Colors.purple,
                          ),
                        ),
                      if (product.concentration != null && product.concentration!.isNotEmpty)
                        const SizedBox(width: 8),
                      Expanded(
                        child: _buildInfoCard(
                          icon: Icons.category,
                          label: 'Categoría',
                          value: product.category ?? 'Sin categoría',
                          color: Colors.orange,
                        ),
                      ),
                    ],
                  ),
                  
                  const SizedBox(height: 8),
                  
                  Row(
                    children: [
                      if (product.laboratory != null && product.laboratory!.isNotEmpty)
                        Expanded(
                          child: _buildInfoCard(
                            icon: Icons.biotech,
                            label: 'Laboratorio',
                            value: product.laboratory!,
                            color: Colors.teal,
                          ),
                        ),
                      if (product.laboratory != null && product.laboratory!.isNotEmpty)
                        const SizedBox(width: 8),
                      Expanded(
                        child: _buildInfoCard(
                          icon: Icons.inventory,
                          label: 'Stock',
                          value: '${product.stock} unidades',
                          color: product.stock > 0 ? Colors.green : Colors.red,
                        ),
                      ),
                    ],
                  ),
                  
                  const SizedBox(height: 8),
                  
                  Row(
                    children: [
                      Expanded(
                        child: _buildInfoCard(
                          icon: Icons.sell,
                          label: 'Precio Venta',
                          value: 'S/ ${product.price.toStringAsFixed(2)}',
                          color: Colors.green,
                        ),
                      ),
                      const SizedBox(width: 8),
                      if (product.costPrice != null)
                        Expanded(
                          child: _buildInfoCard(
                            icon: Icons.attach_money,
                            label: 'Precio Compra',
                            value: 'S/ ${product.costPrice!.toStringAsFixed(2)}',
                            color: Colors.blue,
                          ),
                        ),
                    ],
                  ),
                  
                  const SizedBox(height: 8),
                  
                  // Información adicional
                  if (product.batchNumber != null && product.batchNumber!.isNotEmpty)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 8),
                      child: _buildInfoCard(
                        icon: Icons.qr_code,
                        label: 'Lote Actual',
                        value: product.batchNumber!,
                        color: Colors.indigo,
                        fullWidth: true,
                      ),
                    ),
                  
                  if (product.expirationDate != null)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 8),
                      child: _buildInfoCard(
                        icon: Icons.schedule,
                        label: 'Vence',
                        value: DateFormat('dd/MM/yyyy').format(product.expirationDate!),
                        color: _getExpirationColor(product.expirationDate!),
                        fullWidth: true,
                      ),
                    ),
                  
                  // Estado del producto
                  _buildStatusCard(_getProductStatus()),
                ],
              ),
            ),
          ],
        ),
      ),
      
      const SizedBox(height: 24),
      
      // Sección de formularios optimizada
      Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.blue[300]!, width: 2),
        ),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Título de la sección - Optimizado
              Row(
                children: [
                  Icon(
                    Icons.edit_note,
                    color: Colors.blue,
                    size: 22,
                  ),
                  const SizedBox(width: 12),
                  Text(
                    'Información del Ajuste',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Colors.grey[800],
                    ),
                  ),
                ],
              ),
              
              const SizedBox(height: 20),
              
              // Campo para agregar cantidad - Mejorado
               _buildModernTextField(
                 controller: _quantityController,
                 label: 'Cantidad a Agregar',
                 icon: Icons.add_box,
                 color: Colors.green,
                 keyboardType: TextInputType.number,
                 inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                 isRequired: true,
                 validator: (value) {
                   if (value == null || value.isEmpty) {
                     return 'Ingrese la cantidad a agregar';
                   }
                   final cantidad = int.tryParse(value);
                   if (cantidad == null || cantidad <= 0) {
                     return 'Ingrese una cantidad válida';
                   }
                   return null;
                 },
               ),
               
               const SizedBox(height: 16),
               
               // Proveedor - Mejorado
               _buildModernDropdown(),
              
              const SizedBox(height: 16),
              
              // Precio de compra - Separado
               _buildModernTextField(
                 controller: _purchasePriceController,
                 label: 'Precio de Compra',
                 icon: Icons.attach_money,
                 color: Colors.green,
                 keyboardType: TextInputType.numberWithOptions(decimal: true),
                 inputFormatters: [
                   FilteringTextInputFormatter.allow(RegExp(r'^\d*\.?\d{0,2}')),
                 ],
               ),
               
               const SizedBox(height: 16),
               
               // Precio de venta - Separado
               _buildModernTextField(
                 controller: _priceController,
                 label: 'Precio de Venta',
                 icon: Icons.sell,
                 color: Colors.green,
                 keyboardType: TextInputType.numberWithOptions(decimal: true),
                 inputFormatters: [
                   FilteringTextInputFormatter.allow(RegExp(r'^\d*\.?\d{0,2}')),
                 ],
               ),
              
              const SizedBox(height: 16),
              
              // Número de lote - Mejorado
               _buildModernTextField(
                 controller: _nameController,
                 label: 'Número de Lote',
                 icon: Icons.qr_code,
                 color: Colors.green,
                 isRequired: true,
                 validator: (value) {
                   if (value == null || value.isEmpty) {
                     return 'Ingrese el número de lote';
                   }
                   return null;
                 },
               ),
              
              const SizedBox(height: 16),
              
              // Fecha de vencimiento - Mejorada
              _buildModernDateField(),
            ],
          ),
        ),
      ),
      
      const SizedBox(height: 20),
      
      // Mostrar nuevo stock calculado
        if (_quantityController.text.isNotEmpty)
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.green[50],
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.green[200]!),
            ),
            child: Row(
              children: [
                Icon(Icons.info, color: Colors.green[700]),
                const SizedBox(width: 8),
                Text(
                  'Nuevo stock: ${(_existingProduct ?? widget.existingProduct!).stock + (int.tryParse(_quantityController.text) ?? 0)} unidades',
                  style: TextStyle(
                    color: Colors.green[700],
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
    ];
  }

  String _getProductStatus() {
    final product = _existingProduct ?? widget.existingProduct;
    if (product == null) return 'Desconocido';
    final now = DateTime.now();
    
    // Verificar si está vencido
    if (product.expirationDate != null && product.expirationDate!.isBefore(now)) {
      return 'Vencido';
    }
    
    // Verificar si está por vencer (próximos 30 días)
    if (product.expirationDate != null) {
      final daysUntilExpiry = product.expirationDate!.difference(now).inDays;
      if (daysUntilExpiry <= 30 && daysUntilExpiry > 0) {
        return 'Por vencer ($daysUntilExpiry días)';
      }
    }
    
    // Verificar stock
    if (product.stock <= 0) {
      return 'Sin stock';
    } else if (product.stock <= product.minStock) {
      return 'Stock bajo';
    }
    
    // Si no está activo
    if (product.isActive != null && !product.isActive!) {
      return 'Inactivo';
    }
    
    return 'Normal';
  }

  Widget _buildHighlightedInfoCard({
    required IconData icon,
    required String label,
    required String value,
    required Color color,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withOpacity(0.05),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withOpacity(0.2)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[600],
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Colors.grey[800],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoCard({
    required IconData icon,
    required String label,
    required String value,
    required Color color,
    bool fullWidth = false,
  }) {
    return Container(
      width: fullWidth ? double.infinity : null,
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.grey[200]!),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.05),
            spreadRadius: 1,
            blurRadius: 3,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, color: color, size: 16),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  label,
                  style: TextStyle(
                    fontSize: 11,
                    color: Colors.grey[600],
                    fontWeight: FontWeight.w500,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.bold,
              color: Colors.grey[800],
            ),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildStatusCard(String status) {
    Color statusColor;
    IconData statusIcon;
    
    switch (status.toLowerCase()) {
      case 'normal':
        statusColor = Colors.green;
        statusIcon = Icons.check_circle;
        break;
      case 'vencido':
        statusColor = Colors.red;
        statusIcon = Icons.error;
        break;
      case 'sin stock':
        statusColor = Colors.red;
        statusIcon = Icons.inventory_2_outlined;
        break;
      case 'stock bajo':
        statusColor = Colors.orange;
        statusIcon = Icons.warning;
        break;
      case 'inactivo':
        statusColor = Colors.grey;
        statusIcon = Icons.block;
        break;
      default:
        if (status.contains('Por vencer')) {
          statusColor = Colors.orange;
          statusIcon = Icons.schedule;
        } else {
          statusColor = Colors.blue;
          statusIcon = Icons.info;
        }
    }
    
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: statusColor.withOpacity(0.1),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: statusColor.withOpacity(0.3)),
      ),
      child: Row(
        children: [
          Icon(statusIcon, color: statusColor, size: 20),
          const SizedBox(width: 8),
          Text(
            'Estado: $status',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: statusColor,
            ),
          ),
        ],
      ),
    );
  }

  Color _getExpirationColor(DateTime expirationDate) {
    final now = DateTime.now();
    final daysUntilExpiry = expirationDate.difference(now).inDays;
    
    if (expirationDate.isBefore(now)) {
      return Colors.red; // Vencido
    } else if (daysUntilExpiry <= 30) {
      return Colors.orange; // Por vencer
    } else {
      return Colors.green; // Normal
    }
  }

  // Método para crear campos de texto modernos
  Widget _buildModernTextField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    required Color color,
    TextInputType? keyboardType,
    List<TextInputFormatter>? inputFormatters,
    bool isRequired = false,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      inputFormatters: inputFormatters,
      validator: validator,
      decoration: InputDecoration(
        labelText: isRequired ? '$label *' : label,
        labelStyle: TextStyle(
          color: Colors.grey[600],
          fontSize: 14,
        ),
        prefixIcon: Icon(
          icon,
          color: color,
          size: 22,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: color, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.red, width: 1),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.red, width: 2),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        filled: true,
        fillColor: Colors.grey[50],
      ),
    );
  }

  // Método optimizado para crear dropdown
  Widget _buildModernDropdown() {
    return DropdownButtonFormField<Proveedor>(
      value: _selectedProveedor,
      isExpanded: true,
      decoration: InputDecoration(
        labelText: 'Proveedor *',
        labelStyle: TextStyle(
          color: Colors.grey[600],
          fontSize: 14,
        ),
        prefixIcon: Icon(
          Icons.business,
          color: Colors.green,
          size: 22,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.green, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.red, width: 1),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.red, width: 2),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        filled: true,
        fillColor: Colors.grey[50],
      ),
      items: _proveedores.map((proveedor) {
          return DropdownMenuItem(
            value: proveedor,
            child: Text(
              proveedor.displayName,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey[800],
              ),
            ),
          );
        }).toList(),
        onChanged: (value) {
          setState(() {
            _selectedProveedor = value;
          });
        },
        validator: (value) {
          if (value == null) {
            return 'Seleccione un proveedor';
          }
          return null;
        },
        menuMaxHeight: 200,
        dropdownColor: Colors.white,
        icon: Icon(Icons.keyboard_arrow_down, color: Colors.grey[600]),
    );
  }

  // Método optimizado para crear campo de fecha
  Widget _buildModernDateField() {
    return GestureDetector(
      onTap: _selectExpirationDate,
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey[300]!),
          color: Colors.grey[50],
        ),
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Icon(
              Icons.calendar_today,
              color: Colors.green,
              size: 22,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Fecha de Vencimiento *',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey[600],
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    _selectedExpiryDate == null
                        ? 'Seleccionar fecha'
                        : DateFormat('dd/MM/yyyy', 'es').format(_selectedExpiryDate!),
                    style: TextStyle(
                      fontSize: 16,
                      color: _selectedExpiryDate == null ? Colors.grey[500] : Colors.grey[800],
                    ),
                  ),
                ],
              ),
            ),
            Icon(
              Icons.keyboard_arrow_down,
              color: Colors.grey[600],
            ),
          ],
        ),
      ),
    );
  }

  // Método optimizado para crear botón de acción
  Widget _buildModernActionButton() {
    final isNewProduct = widget.isNewProduct;
    final buttonText = isNewProduct ? 'Crear Producto' : 'Actualizar Stock';
    final buttonIcon = isNewProduct ? Icons.add_circle : Icons.update;
    final buttonColor = isNewProduct ? Colors.blue : Colors.green;

    return SizedBox(
      width: double.infinity,
      height: 56,
      child: ElevatedButton.icon(
        onPressed: (_isLoading || _isRefreshing) ? null : _saveProduct,
        style: ElevatedButton.styleFrom(
          backgroundColor: (_isLoading || _isRefreshing) ? Colors.grey[400] : buttonColor,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          elevation: 0,
        ),
        icon: _isLoading || _isRefreshing
            ? SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                ),
              )
            : Icon(buttonIcon, size: 24),
        label: Text(
          _isLoading || _isRefreshing
              ? (_isRefreshing ? 'Actualizando...' : 'Procesando...')
              : buttonText,
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }

  // Método para crear botón de cancelar moderno
  Widget _buildModernCancelButton() {
    return Container(
      width: double.infinity,
      height: 48,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[300]!, width: 1.5),
        color: Colors.white,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(12),
          onTap: () => Navigator.of(context).pop(),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.close,
                  color: Colors.grey[600],
                  size: 20,
                ),
                const SizedBox(width: 8),
                Text(
                  'Cancelar',
                  style: TextStyle(
                    color: Colors.grey[700],
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              label,
              style: const TextStyle(
                fontWeight: FontWeight.w500,
                color: Colors.grey,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }
}